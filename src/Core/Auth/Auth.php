<?php

namespace Root\Composer\Core\Auth;

use PDO;

class Auth
{
  private static ?Authentication $instance = null;

  /**
   * 認証サービスを初期化します。アプリケーションの開始時に一度だけ呼び出します。
   * @param PDO $db PDOデータベース接続
   * @param int $sessionLifetime セッションの有効期間 (秒)
   */
  public static function initialize(PDO $db, int $sessionLifetime = 3600): void
  {
    if (self::$instance === null) {
      self::$instance = new Authentication($db, $sessionLifetime);
    }
  }

  /**
   * Authenticationクラスのインスタンスを取得します。
   * @return Authentication 初期化されていればインスタンスを返す
   * @throws \LogicException 初期化されていない場合にスロー
   */
  private static function getInstance(): Authentication
  {
    if (self::$instance === null) {
      // 通常は initialize() で初期化されるべき
      throw new \LogicException('Authentication service has not been initialized. Call Auth::initialize() first.');
    }
    return self::$instance;
  }

  // Authenticationクラスの便利なメソッドへのショートカット
  public static function check(): bool
  {
    return self::getInstance()->isAuthenticated();
  }

  public static function user(): ?array
  {
    return self::getInstance()->getUser();
  }

  public static function id(): ?int
  {
    return self::getInstance()->getUserId();
  }

  public static function guest(): bool
  {
    return !self::check();
  }

  public static function attemptLogin(int $userId, bool $regenerateSessionId = true): void
  {
    self::getInstance()->login($userId, $regenerateSessionId);
  }

  public static function logout(): void
  {
    self::getInstance()->logout();
  }

  public static function requireLogin(string $redirectUrl = 'login.php'): void
  {
    self::getInstance()->requireAuth($redirectUrl);
  }
}
