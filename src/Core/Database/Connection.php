<?php

namespace Root\Composer\Core\Database;

use PDO;
use PDOException;
use Root\Composer\Core\Config\Settings;

class Connection
{
  private static ?PDO $pdoInstance = null;

  private static ?Settings $settings = null;

  public static function initialize(Settings $settingsInstance): void
  {
    if (self::$settings !== null) {
      return;
    }
    self::$settings = $settingsInstance;
  }

  public static function getConnection(): ?PDO
  {
    if (self::$settings === null) {
      error_log('Connection class has not been initialized. Call Connection::initialize() first.');
      return null;
    }

    if (self::$pdoInstance === null) {
      try {
        $dbType = strtolower(self::$settings->get('database.type', 'mysql')); // デフォルトはmysql

        $options = [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        if ($dbType === 'sqlite') {
          $dbPath = self::$settings->get('database.path');
          if (!$dbPath) {
            error_log('SQLite database path (database.path) is not configured in settings.');
            return null;
          }

          if (defined('PROJECT_ROOT')) {
            $absoluteDbPath = PROJECT_ROOT . DIRECTORY_SEPARATOR . ltrim($dbPath, '/\\');
            // $absoluteDbPath = realpath($baseDir . DIRECTORY_SEPARATOR . $dbPath);

            // 一旦、PROJECT_ROOT があり、dbPath がそれからの相対パスだと仮定する
            // config.json の "path" を "database/app.sqlite" のように書くことを推奨
            if (!defined('PROJECT_ROOT')) {
              error_log('PROJECT_ROOT constant is not defined. Cannot resolve SQLite path.');
              return null;
            }
            $absoluteDbPath = PROJECT_ROOT . DIRECTORY_SEPARATOR . $dbPath;
            // ディレクトリが存在しない場合は作成する (任意)
            $dbDir = dirname($absoluteDbPath);
            if (!is_dir($dbDir)) {
              if (!mkdir($dbDir, 0775, true) && !is_dir($dbDir)) {
                error_log(sprintf('Error: Directory "%s" was not created', $dbDir));
                return null;
              }
            }

            $dsn = 'sqlite:' . $absoluteDbPath;
            self::$pdoInstance = new PDO($dsn, null, null, $options);
          } else {
            error_log('PROJECT_ROOT is not defined. Cannot construct absolute path for SQLite DB.');
            return null;
          }
        } elseif ($dbType === 'mysql') {
          $dbName = self::$settings->get('database.name');
          $dbHost = self::$settings->get('database.host');
          $dbUser = self::$settings->get('database.user');
          $dbPass = self::$settings->get('database.password');

          if (!$dbName || !$dbHost || !$dbUser) {
            error_log('MySQL database configuration (name, host, user) from Settings is not fully set or missing.');
            return null;
          }
          $dsn = "mysql:dbname={$dbName};host={$dbHost};charset=utf8mb4";
          self::$pdoInstance = new PDO($dsn, $dbUser, $dbPass, $options);
        } else {
          error_log("Unsupported database type: {$dbType}");
          return null;
        }
      } catch (PDOException $e) {
        error_log("Database Connection Error ({$dbType}): " . $e->getMessage());
        return null;
      } catch (\Exception $e) {
        error_log('Error retrieving database configuration from Settings or other generic error: ' . $e->getMessage());
        return null;
      }
    }
    return self::$pdoInstance;
  }

  private function __construct()
  {
  }
}
