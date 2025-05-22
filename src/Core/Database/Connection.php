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
          // PROJECT_ROOT が定義されている前提 (bootstrap.phpで定義)
          // config.json のパスがプロジェクトルートからの相対パスの場合、
          // Settingsクラスがconfig.jsonを読み込む際の基準パスと、
          // ここでSQLiteのパスを解決する際の基準パスを合わせる必要がある。
          // Settingsクラスのコンストラクタで渡すconfigFilePathが絶対パスなら、
          // SQLiteのパスも絶対パスにするか、PROJECT_ROOTからの相対パスとして解決する。
          // ここでは、PROJECT_ROOT からの相対パスとして解釈する例
          if (defined('PROJECT_ROOT')) {
            // $dbPathが '../database/app.sqlite' のような相対パスの場合、
            // configファイルのある場所からの相対パスではなく、
            // PROJECT_ROOTからの相対パスとして扱うか、絶対パスにする必要がある。
            // ここでは、config.jsonに書かれたパスがPROJECT_ROOTからの相対パスであると仮定する。
            // 例: "path": "database/app.sqlite" (PROJECT_ROOT/database/app.sqlite)
            // もし "path": "../database/app.sqlite" で、configが project_root/config/config.json なら、
            // これは project_root/database/app.sqlite を指す。
            // より安全なのは、Settingsでconfig.jsonのパスを解決する際に絶対パスにすること。
            // ここでは、Settingsが返すパスがそのまま使えると仮定するか、
            // PROJECT_ROOT を使って絶対パスに変換する。

            // config.jsonの "path" が "database/app.sqlite" のような
            // プロジェクトルートからの相対パスを想定する場合:
            $absoluteDbPath = PROJECT_ROOT . DIRECTORY_SEPARATOR . ltrim($dbPath, '/\\');

            // もしconfig.jsonの "path" が "../database/app.sqlite" のように
            // configファイルからの相対パスで、configファイルが `project_root/config/` にある場合
            // $configDir = dirname(self::$settings->getConfigFilePath()); // Settingsにこのメソッドが必要
            // $absoluteDbPath = realpath($configDir . DIRECTORY_SEPARATOR . $dbPath);

            // 今回はシンプルに、config.jsonの "path" が PROJECT_ROOT からの相対パスであると仮定
            // 例: "path": "database/app.sqlite"
            // もし "path": "../database/app.sqlite" となっていて、configファイルが
            // project_root/config/ にある場合は、
            // $dbPath を 'database/app.sqlite' のように修正するか、
            // パス解決ロジックを調整する必要がある。
            // ここでは、bootstrap.phpで定義されたPROJECT_ROOTを基準にパスを組み立てる。
            // config.jsonの "path": "../database/app.sqlite" は
            // PROJECT_ROOT/../database/app.sqlite を意図しているわけではないはず。
            // config/config.json での "path": "../database/app.sqlite" は
            // (config/ の親ディレクトリ)/database/app.sqlite = PROJECT_ROOT/database/app.sqlite を指す。
            // そのため、realpath() を使うのが堅実。

            // Settingsがconfigファイルの絶対パスを保持していると仮定し、
            // それを基準に相対パスを解決する
            // $configFilePath = self::$settings->getConfigFilePath(); // Settingsクラスにこのメソッドが必要と仮定
            // $baseDir = dirname($configFilePath);
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
