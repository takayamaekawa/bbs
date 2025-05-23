<?php

namespace Root\Composer\Core\Auth;

use PDO;
use PDOException;

class Authentication
{
  private PDO $db;

  private ?array $currentUser = null;

  private bool $isAuthenticated = false;

  private int $sessionLifetime = 3600; // セッションの有効期間 (秒)

  public function __construct(PDO $dbConnection, int $sessionLifetime = 3600)
  {
    $this->db = $dbConnection;
    $this->sessionLifetime = $sessionLifetime;

    // セッションが開始されていなければ開始
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    $this->processLoginState();
  }

  private function processLoginState(): void
  {
    if (
      isset($_SESSION['user_id'], $_SESSION['last_activity']) &&
      ($_SESSION['last_activity'] + $this->sessionLifetime > time())
    ) {
      // セッションが有効期間内
      $_SESSION['last_activity'] = time(); // アクティビティ時間を更新

      try {
        $stmt = $this->db->prepare('SELECT * FROM members WHERE id = :id');
        $stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
          $this->currentUser = $user;
          $this->isAuthenticated = true;
        } else {
          // セッションIDに対応するユーザーがDBに存在しない (削除されたなど)
          $this->logout(); // 不正なセッションなのでログアウトさせる
        }
      } catch (PDOException $e) {
        error_log('Authentication DB error: ' . $e->getMessage());
        // DBエラー時も安全のためログアウト扱いにするか、エラー処理を行う
        $this->logout();
      }
    } else {
      // セッション情報がないか、タイムアウトしている場合
      if (isset($_SESSION['user_id'])) {
        // タイムアウトなどでセッションが無効になった場合、明示的にログアウト処理を行う
        $this->logout();
      }
      $this->isAuthenticated = false;
      $this->currentUser = null;
    }
  }

  /**
   * ユーザーが認証済みかどうかを返します。
   */
  public function isAuthenticated(): bool
  {
    return $this->isAuthenticated;
  }

  /**
   * 現在認証されているユーザーの情報を返します。
   * @return array|null ユーザー情報配列、認証されていなければnull
   */
  public function getUser(): ?array
  {
    return $this->currentUser;
  }

  /**
   * ユーザーIDを返します。
   * @return int|null ユーザーID、認証されていなければnull
   */
  public function getUserId(): ?int
  {
    return $this->currentUser['id'] ?? null;
  }

  /**
   * ログイン処理 (ユーザー認証成功後に呼び出す)
   * @param int $userId ログインするユーザーのID
   * @param bool $regenerateSessionId セッションIDを再生成するか (推奨)
   */
  public function login(int $userId, bool $regenerateSessionId = true): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if ($regenerateSessionId) {
      session_regenerate_id(true); // セッション固定化攻撃対策
    }
    $_SESSION['user_id'] = $userId;
    $_SESSION['last_activity'] = time();

    // ログイン後、状態を即時反映
    $this->processLoginState();
  }

  /**
   * ログアウト処理
   */
  public function logout(): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    $_SESSION = []; // セッション変数を空にする

    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
      );
    }
    session_destroy();

    $this->isAuthenticated = false;
    $this->currentUser = null;
  }

  /**
   * 認証されていない場合に指定されたURLにリダイレクトします。
   * @param string $redirectUrl リダイレクト先のURL (デフォルトは 'login.php')
   */
  public function requireAuth(string $redirectUrl = 'login.php'): void
  {
    if (!$this->isAuthenticated()) {
      header('Location: ' . $redirectUrl);
      exit;
    }
  }
}
