<?php

namespace Root\Composer\Core\Database; // プロジェクトの名前空間に合わせて調整してください

use PDO; // Settingsクラスをインポート
use PDOException;
use Root\Composer\Core\Config\Settings;
// Dotenv関連のuseは不要になる
// use Dotenv\Dotenv;
// use Dotenv\Exception\InvalidPathException;

class Connection {
  private static ?PDO $pdoInstance = null;     // PDO接続インスタンスを保持

  private static ?Settings $settings = null;  // Settingsインスタンスを保持

  /**
   * Connectionクラスを初期化します。
   * bootstrap.phpなどで一度だけ呼び出されることを想定しています。
   * @param Settings $settings Settingsクラスのインスタンス
   */
  public static function initialize(Settings $settingsInstance): void {
    if (self::$settings !== null) {
      // 既に初期化されている場合は何もしないか、エラーを出す（設計による）
      // error_log('Connection class is already initialized.');
      return;
    }
    self::$settings = $settingsInstance;
  }

  /**
   * PDO接続インスタンスを取得します。
   * 既にインスタンスが存在する場合はそれを返し、なければ新規に作成します。
   *
   * @return PDO|null 成功した場合はPDOオブジェクト、失敗した場合はnull。
   * Connection::initialize()が呼び出されていない場合はエラーをログに出力してnullを返す。
   */
  public static function getConnection(): ?PDO {
    if (self::$settings === null) {
      error_log('Connection class has not been initialized. Call Connection::initialize() first.');
      return null;
    }

    if (self::$pdoInstance === null) {
      try {
        // Settingsクラスからデータベース設定を取得
        // config.jsonのキー構造に合わせて 'database.name', 'database.host' などとする
        $dbName = self::$settings->get('database.name');
        $dbHost = self::$settings->get('database.host');
        $dbUser = self::$settings->get('database.user');
        $dbPass = self::$settings->get('database.password'); // パスワードはnull許容の場合もある

        if (!$dbName || !$dbHost || !$dbUser) {
          error_log('Database configuration (name, host, user) from Settings is not fully set or missing.');
          return null;
        }

        $dsn = "mysql:dbname={$dbName};host={$dbHost};charset=utf8mb4";

        $options = [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        self::$pdoInstance = new PDO($dsn, $dbUser, $dbPass, $options);
      } /*catch (InvalidPathException $e) { // Dotenv関連なので削除
                error_log("Dotenv Error: Failed to load .env file. " . $e->getMessage());
                return null;
            }*/ catch (PDOException $e) {
        error_log('Database Connection Error: ' . $e->getMessage());
        return null;
      } catch (\Exception $e) { // Settings->get でキーが存在しない場合などに汎用的なエラー
        error_log('Error retrieving database configuration from Settings: ' . $e->getMessage());
        return null;
      }
    }
    return self::$pdoInstance;
  }

  // プライベートコンストラクタにして、外部からのインスタンス化を防ぐ
  private function __construct() {
  }
}
