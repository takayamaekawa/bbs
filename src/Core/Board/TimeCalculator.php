<?php

namespace Root\Composer\Core\Board;

class TimeCalculator
{
  private ?\PDO $db;

  /**
   * コンストラクタ
   * @param \PDO|null $dbConnection PDOデータベース接続オブジェクト。DB操作が必要なメソッドで使用。
   */
  public function __construct(?\PDO $dbConnection = null)
  {
    $this->db = $dbConnection;
  }

  /**
   * 2つの日時文字列の差を計算するプライベートヘルパー関数。
   * 結果は、年、日、時、分、秒の整数値と、合計日数などの小数値を含む配列。
   *
   * @param string $timeStr1 日時文字列1 (strtotimeで解釈可能な形式)
   * @param string $timeStr2 日時文字列2 (strtotimeで解釈可能な形式)
   * @return array 計算結果の連想配列。キーの例:
   * 'years_floor', 'days_total_floor', 'hours_total_floor', 'minutes_total_floor', 'seconds_total_floor' (整数部)
   * 'days_exact', 'hours_exact', 'minutes_exact', 'seconds_exact' (実数値)
   * 'error' (エラー発生時)
   */
  private function calculateTimeDifferenceInternal(string $timeStr1, string $timeStr2): array
  {
    $timestamp1 = strtotime($timeStr1);
    $timestamp2 = strtotime($timeStr2);

    if ($timestamp1 === false || $timestamp2 === false) {
      return ['error' => 'Invalid date/time string provided for internal calculation.'];
    }

    // 常に $timeStr1 (新しい方) - $timeStr2 (古い方) の差を計算
    $differenceInSeconds = $timestamp1 - $timestamp2;

    // 過去の時刻との差を計算する場合は $timestamp2 が $timestamp1 より小さいため、$differenceInSeconds は正
    // 未来の時刻との差を意図しない限り、 $timeStr1 が現在時刻や新しい時刻、 $timeStr2 が過去の時刻となる想定

    if ($differenceInSeconds < 0) {
      // 通常、timeStr1 (例: 現在時刻) が timeStr2 (例: 過去の投稿時刻) より新しいことを期待
      // もし逆転している場合 (例: 未来の時刻との比較)、差を正にするかエラーとするか仕様による
      // ここでは絶対値を取るのではなく、timeStr1 > timeStr2 を前提としておく
      // 必要であれば abs($differenceInSeconds) を使う
      // 元のスクリプトは ($stt_now - $stt_modified) としていたので、それに従う
    }

    $daysExact = $differenceInSeconds / (60 * 60 * 24);
    $hoursExact = $differenceInSeconds / (60 * 60);
    $minutesExact = $differenceInSeconds / 60;

    return [
      'years_floor' => floor($daysExact / 365), // 単純計算 (閏年非考慮)
      'days_total_floor' => floor($daysExact),
      'hours_total_floor' => floor($hoursExact),
      'minutes_total_floor' => floor($minutesExact),
      'seconds_total_floor' => $differenceInSeconds, // 秒は元々整数
      'days_exact' => $daysExact,
      'hours_exact' => $hoursExact,
      'minutes_exact' => $minutesExact,
      'seconds_exact' => $differenceInSeconds,
    ];
  }

  /**
   * 与えられた投稿データ($post)の 'created' と 'modified' 日時を処理し、
   * 'modified' 日時と現在時刻との差を計算する。
   * 元の calc_time.php のロジックを包含。
   *
   * @param array $post 投稿データ配列。'created' と 'modified' (YYYY-MM-DD HH:MM:SS形式を期待) を含む必要がある。
   * @return array 時間差データおよび元スクリプトで生成されていた日時関連の文字列。
   */
  public function processPostTimes(array $post): array
  {
    if (!isset($post['created']) || !isset($post['modified'])) {
      return ['error' => "Missing 'created' or 'modified' in post data."];
    }

    // $post['modified'] は 'YYYY-MM-DD HH:MM:SS' 形式を期待
    $modifiedTimeForCalc = $post['modified'];
    $currentTime = date('Y-m-d H:i:s');

    $timeDiff = $this->calculateTimeDifferenceInternal($currentTime, $modifiedTimeForCalc);

    // 元のスクリプトで生成されていた日時関連の文字列も返す
    $parsed_created_slashed = preg_replace('/(-)/', '/', $post['created']);
    $parsed_created_month_day = substr($parsed_created_slashed, 5, -3); // 例: "01/15" (MM/DD)
    $parsed_modified_time_part = substr($post['modified'], 11, 8); // HH:MM:SS
    $parsed_created_time_part = substr($post['created'], 11, 8);   // HH:MM:SS
    $parsed_modified_date_part = substr($post['modified'], 0, 10); // YYYY-MM-DD
    $parsed_created_date_part = substr($post['created'], 0, 10);   // YYYY-MM-DD

    return array_merge($timeDiff, [
      'parsed_created_slashed_format' => $parsed_created_slashed,
      'parsed_created_month_day_format' => $parsed_created_month_day,
      'parsed_modified_time' => $parsed_modified_time_part,
      'parsed_created_time' => $parsed_created_time_part,
      'parsed_modified_date' => $parsed_modified_date_part,
      'parsed_created_date' => $parsed_created_date_part,
      'reference_modified_datetime' => $modifiedTimeForCalc,
      'reference_current_datetime' => $currentTime,
    ]);
  }

  /**
   * 最新投稿の作成日時と現在時刻との差を計算する。
   * 元の calc_time_now.php のロジック。
   *
   * @param int $maxId 最新投稿のID。
   * @return array 時間差データ。エラー時は 'error' キーを含む。
   */
  public function getTimeSinceLatestPost(int $maxId): array
  {
    if (!$this->db) {
      return ['error' => 'Database connection not provided to TimeCalculator.'];
    }

    $sql = 'SELECT created FROM posts WHERE id = :id';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':id', $maxId, \PDO::PARAM_INT);
    $stmt->execute();
    $createdTime = $stmt->fetchColumn();

    if (!$createdTime) {
      return ['error' => 'Could not fetch creation time for post ID: ' . $maxId];
    }

    $currentTime = date('Y-m-d H:i:s');
    $result = $this->calculateTimeDifferenceInternal($currentTime, $createdTime);
    return array_merge($result, [
      'reference_created_datetime' => $createdTime,
      'reference_current_datetime' => $currentTime,
    ]);
  }

  /**
   * 指定された返信先投稿の更新日時と現在時刻との差を計算する。
   * 元の calc_time_reply.php のロジック。
   *
   * @param int $replyPostId 返信先投稿のID。
   * @param string|null $customCurrentTime (オプション) 比較対象とする日時文字列。nullの場合、現在の時刻を使用。
   * @return array 時間差データ。エラー時は 'error' キーを含む。
   */
  public function getTimeSinceReplyModified(int $replyPostId, ?string $customCurrentTime = null): array
  {
    if (!$this->db) {
      return ['error' => 'Database connection not provided to TimeCalculator.'];
    }

    $sql = 'SELECT modified FROM posts WHERE id = :id';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':id', $replyPostId, \PDO::PARAM_INT);
    $stmt->execute();
    $modifiedTime = $stmt->fetchColumn();

    if (!$modifiedTime) {
      return ['error' => 'Could not fetch modified time for reply post ID: ' . $replyPostId];
    }

    $currentTime = $customCurrentTime ?? date('Y-m-d H:i:s');
    $result = $this->calculateTimeDifferenceInternal($currentTime, $modifiedTime);
    return array_merge($result, [
      'reference_modified_datetime' => $modifiedTime,
      'reference_current_datetime' => $currentTime,
    ]);
  }
}

/*
// --- 使用例 ---

// 最初にPDOインスタンスを準備します (例: path.php で $db が設定される)
// require_once '/opt/admin2/php/path.php'; // $db のセットアップなど

// ダミーのPDO接続 (実際には path.php などで設定されたものを使用)
try {
    // $db = new \PDO("mysql:host=localhost;dbname=your_database", "username", "password");
    // $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $db = null; // DB操作を試さない場合はnullでも可 (ただしgetTimeSinceLatestPostなどはエラーになる)
} catch (\PDOException $e) {
    // die("Database connection failed: " . $e->getMessage());
    $db = null;
}


$timeCalculator = new \Root\Composer\Core\Board\Calc\TimeCalculator($db);

// 1. calc_time.php に相当する処理
echo "--- Simulating calc_time.php ---\n";
$postData = [
    'created' => '2023-01-15 10:00:00', // YYYY-MM-DD HH:MM:SS
    'modified' => date('Y-m-d H:i:s', strtotime('-2 days -3 hours -30 minutes')) // 約2日3時間30分前の日時
];
$result1 = $timeCalculator->processPostTimes($postData);
print_r($result1);
// 元の $int_fix_count_day は $result1['days_total_floor']
// 元の $int_fix_count_hour は $result1['hours_total_floor']
// 元の $int_fix_count_minute は $result1['minutes_total_floor']
// 元の $int_fix_count_year は $result1['years_floor']


// 2. calc_time_now.php に相当する処理 (DB接続が正しく設定されていると仮定)
// echo "\n--- Simulating calc_time_now.php ---\n";
// if ($db) {
//     // テスト用にDBにデータを挿入するなどの準備が必要
//     // $latestPostId = 1; // 存在するIDに置き換える
//     // $result2 = $timeCalculator->getTimeSinceLatestPost($latestPostId);
//     // print_r($result2);
//     // 元の $int_now_count_day は $result2['days_total_floor']
// } else {
//     echo "DB connection not available for getTimeSinceLatestPost.\n";
// }


// 3. calc_time_reply.php に相当する処理 (DB接続が正しく設定されていると仮定)
// echo "\n--- Simulating calc_time_reply.php ---\n";
// if ($db) {
//     // テスト用にDBにデータを挿入するなどの準備が必要
//     // $replyToPostId = 2; // 存在するIDに置き換える
//     // 元のスクリプトで $NOW がどう定義されていたかにより、$customCurrentTime を渡すか検討
//     // $result3 = $timeCalculator->getTimeSinceReplyModified($replyToPostId);
//     // print_r($result3);
//     // 元の $int_fix_count_day は $result3['days_total_floor']
// } else {
//     echo "DB connection not available for getTimeSinceReplyModified.\n";
// }

*/
