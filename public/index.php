<?php
// アプリケーションの初期化処理
require_once __DIR__ . '/../bootstrap.php';

// 必要なクラスをインポート
use Root\Composer\Core\Auth\Auth;
use Root\Composer\Core\Config\Settings;
use Root\Composer\Core\Database\Connection;
use Root\Composer\Views\Helpers\FooterHelper;
use Root\Composer\Views\Helpers\HeaderHelper;

// --- ヘッダーやページ全体で必要な変数の準備 ---
$settingsInstance = Settings::getInstance(); // 設定インスタンスを取得
$siteName = $settingsInstance->get('app_settings.site_name', 'My Application'); // サイト名を取得
$discordUrl = $settingsInstance->get('footer.discord_url', '');
$copyrightName = $settingsInstance->get('footer.copyright_name', 'My Site');
$copyrightStartYear = (int)$settingsInstance->get('footer.copyright_start_year', date('Y'));

$counter = [ // ダミーデータ。実際の値に置き換えてください。
  'todayipcount' => [0 => 0],
];
// $counterService = new YourCounterService(Connection::getConnection());
// $counter = $counterService->getDisplayCounters();

$defaulticonuploaddir = PROJECT_ROOT . '/public/assets/img/upload/icon/user/*'; // ダミーパス
// $defaulticonuploaddir = $settingsInstance->get('paths.default_icons_user', PROJECT_ROOT . '/public/assets/img/upload/icon/user/*');

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?></title>
    <style>
      body { font-family: sans-serif; margin: 0; }
      main { padding: 20px; }
      footer { background-color: #f0f0f0; padding: 10px; text-align: center; }
      /* HeaderHelper内で生成される<style>タグも読み込まれます */
    </style>
    </head>
  <body>

    <?php
    // HeaderHelper を使ってヘッダーHTMLを生成・表示
    // HeaderHelper::render にサイト名と必要な引数 ($counter, $defaulticonuploaddir) を渡します。
    // HeaderHelper::render() が返すHTMLのインデントは HeaderHelper.php 側で制御されます。
    echo HeaderHelper::render($siteName, $counter, $defaulticonuploaddir);
?>

    <main>
      <h1>Application Main Content</h1>
      <p>Current Site Name (from main content): <?php echo htmlspecialchars($siteName); ?></p>

<?php
// データベース関連の表示 (必要に応じて)
$db = Connection::getConnection();
if ($db) {
  try {
    // データベースタイプに応じて日付取得クエリを変更
    $dbType = strtolower($settingsInstance->get('database.type', 'mysql'));
    $dateQuery = '';
    if ($dbType === 'sqlite') {
      $dateQuery = "SELECT date('now') as today";
    } elseif ($dbType === 'mysql') {
      $dateQuery = 'SELECT CURDATE() as today';
    } else {
      // サポート外のDBタイプの場合のフォールバック
      echo "      <p>Unsupported database type for date query.</p>\n";
    }

    if (!empty($dateQuery)) {
      $stmt = $db->query($dateQuery);
      $result = $stmt->fetch();
      if ($result) {
        echo "      <p>Today's date from DB: " . htmlspecialchars($result['today']) . "</p>\n";
      }
    }
  } catch (\PDOException $e) { // PDOException を明示的にキャッチ
    echo "      <p style='color:red;'>Database query failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    error_log('Index page DB query error: ' . $e->getMessage());
  }
} else {
  echo "      <p style='color:red;'>Failed to obtain database connection for main content.</p>\n";
}

// ログイン状態に応じたメッセージ (メインコンテンツ内)
if (Auth::check()) {
  $currentUser = Auth::user();
  // $currentUser が null でないこと、および 'name' キーが存在することを確認
  $userName = (isset($currentUser) && isset($currentUser['name'])) ? $currentUser['name'] : 'ユーザー';
  echo '      <p>ようこそ、' . htmlspecialchars($userName) . 'さん！ (User ID: ' . htmlspecialchars(Auth::id() ?? '') . ')</p>' . "\n";
} else {
  echo '      <p>ゲストとして閲覧中です。</p>' . "\n";
  // echo '      <p><a href="/login.php">ログインページへ</a></p>' . "\n"; // ログインページへのリンク例
}
?>
    </main>

<?php
  // FooterHelper を使ってフッターHTMLを生成・表示
  echo FooterHelper::render($discordUrl, $copyrightName, $copyrightStartYear); ?>
  </body>
</html>
