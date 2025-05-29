<?php

// --- 0. プロジェクトルートの定義 ---
if (!defined('PROJECT_ROOT')) {
  define('PROJECT_ROOT', __DIR__);
}

// --- 0.1 タイムゾーン設定 ---
date_default_timezone_set('Asia/Tokyo');

// --- 1. ログ設定 ---
$logFilePath = PROJECT_ROOT . '/logs/bootstrap.log';
// ログディレクトリが存在しない場合は作成 (任意、手動で作成してもOK)
$logDir = dirname($logFilePath);
if (!is_dir($logDir)) {
  if (!mkdir($logDir, 0775, true) && !is_dir($logDir)) {
    // ログディレクトリ作成失敗時はエラーを出力して終了 (またはフォールバック)
    error_log(sprintf('Error: Log directory "%s" could not be created. Please check permissions.', $logDir), 0); // システムログへ
    die('Critical error: Log directory could not be created. Please check server logs.');
  }
}

// シンプルなロギング関数
function bootstrap_log(string $message): void
{
  global $logFilePath; // グローバル変数を参照
  $timestamp = date('Y-m-d H:i:s');
  // message_type = 3 で指定ファイルに追記, extra_headers はここでは不要
  error_log("[$timestamp] " . $message . PHP_EOL, 3, $logFilePath);
}

bootstrap_log('--- Bootstrap process started (TZ: ' . date_default_timezone_get() . ') ---');

// --- 2. オートローダーの読み込み ---
if (file_exists(PROJECT_ROOT . '/vendor/autoload.php')) {
  require_once PROJECT_ROOT . '/vendor/autoload.php';
  bootstrap_log('Composer autoload.php loaded.');
} else {
  // Composer を使用していない場合、手動でクラスファイルを読み込む
  // この部分はプロジェクトの構造に合わせて調整が必要
  $manualLoadFiles = [
    '/src/Core/Config/Settings.php',
    '/src/Core/Database/Connection.php',
    '/src/Core/Auth/Authentication.php',
    '/src/Core/Auth/Auth.php',
    '/src/Core/Auth/User.php',
    '/src/Core/Auth/ProfileController.php',
    '/src/Core/Board/Post.php',
    '/src/Core/Board/FileUploadHandler.php',
    '/src/Core/Board/BoardController.php',
    '/src/Views/Helpers/HeaderHelper.php', // 必要に応じて追加
  ];
  foreach ($manualLoadFiles as $file) {
    if (file_exists(PROJECT_ROOT . $file)) {
      require_once PROJECT_ROOT . $file;
    } else {
      bootstrap_log('Manual load failed: ' . PROJECT_ROOT . $file . ' not found.');
    }
  }
  bootstrap_log('Attempted manual class loading (if Composer not used).');
  // Composerがない場合、致命的なエラーとして終了させることも検討
  // die('Composer autoload not found. Please run "composer install".');
}

// --- 3. エラー表示設定 (開発用) ---
// 本番環境ではエラーをログファイルに記録し、画面には表示しないようにする
error_reporting(E_ALL);
ini_set('display_errors', '0'); // 画面へのエラー表示はオフ
ini_set('log_errors', '1');    // エラーログは有効
ini_set('error_log', PROJECT_ROOT . '/logs/php_errors.log'); // PHPエラーのログファイル
bootstrap_log('Error reporting configured. Display errors: OFF, Log errors: ON to ' . ini_get('error_log'));

// --- 4. 名前空間のエイリアス ---
// use PDOException; // グローバル名前空間なので、この行は不要で警告の原因
use Root\Composer\Core\Auth\Auth;
use Root\Composer\Core\Config\Settings;
use Root\Composer\Core\Database\Connection;

// --- 5. Settings クラスの初期化 ---
$configJsonPath = PROJECT_ROOT . '/config/config.json';
$settings = null;

try {
  $settings = Settings::getInstance($configJsonPath);
  bootstrap_log('Settings initialized successfully from: ' . $configJsonPath);
} catch (\Exception $e) {
  $errorMessage = 'FATAL ERROR: Failed to initialize Settings. ' . $e->getMessage();
  bootstrap_log($errorMessage);
  error_log($errorMessage, 0); // システムログにも記録
  die('Application critical error: Configuration could not be loaded. Please contact administrator. (Details logged)');
}

// --- 6. Connection クラスの初期化とDB接続 ---
$db = null; // DB接続オブジェクトを格納する変数
if ($settings instanceof Settings) {
  try {
    Connection::initialize($settings);
    bootstrap_log('Database Connection class initialized.');

    $db = Connection::getConnection(); // ここでSQLiteファイルがなければ作成される
    if ($db) {
      bootstrap_log('Database connection obtained successfully.');
    } else {
      bootstrap_log('CRITICAL: Failed to get database connection from Connection class. This means Connection::getConnection() returned null. Check Connection.php logs for details (e.g., SQLite path issues, permissions). Auth service will not be initialized.');
    }
  } catch (\Exception $e) {
    $errorMessage = 'FATAL ERROR: Failed to initialize or connect to Database. ' . $e->getMessage();
    bootstrap_log($errorMessage);
    error_log($errorMessage, 0);
    die('Application critical error: Database could not be initialized. Please contact administrator. (Details logged)');
  }
} else {
  $errorMessage = 'FATAL ERROR: Settings object is not available for Database Connection initialization.';
  bootstrap_log($errorMessage);
  error_log($errorMessage, 0);
  die('Application critical error: Core settings are missing. Please contact administrator.');
}

// --- 7. 初期テーブル作成 (members テーブル) ---
if ($db instanceof \PDO) { // $db が有効なPDOインスタンスであることを確認
  try {
    $db->query('SELECT 1 FROM members LIMIT 1');
    bootstrap_log("Table 'members' already exists.");
  } catch (\PDOException $e) { // PDOException を明示的にキャッチ
    $errorMessageLower = strtolower($e->getMessage());
    if (
      strpos($errorMessageLower, 'no such table') !== false ||
      strpos($errorMessageLower, 'table or view not found') !== false ||
      strpos($errorMessageLower, "doesn't exist") !== false ||
      (strpos($errorMessageLower, 'undefined table') !== false && strpos($errorMessageLower, 'members') !== false)
    ) {
      bootstrap_log("Table 'members' not found. Attempting to create it...");
      try {
        $dbType = strtolower($settings->get('database.type', 'mysql'));
        $sqlBase = 'CREATE TABLE members (
                            username VARCHAR(255) NOT NULL UNIQUE,
                            password VARCHAR(255) NOT NULL,
                            name VARCHAR(255),
                            icon VARCHAR(255),
                            email VARCHAR(255) UNIQUE,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )';

        $sql = '';
        if ($dbType === 'sqlite') {
          $sql = 'CREATE TABLE members (
                                id INTEGER PRIMARY KEY AUTOINCREMENT,
                                ' . substr($sqlBase, strpos($sqlBase, 'username'));
        } elseif ($dbType === 'mysql') {
          $sql = 'CREATE TABLE members (
                                id INT PRIMARY KEY AUTO_INCREMENT,
                                ' . substr($sqlBase, strpos($sqlBase, 'username'));
        } else {
          bootstrap_log("Unsupported DB type '{$dbType}' for table creation. Skipping members table creation.");
        }

        if (!empty($sql)) {
          $db->exec($sql);
          bootstrap_log("Table 'members' created successfully for {$dbType}.");
        }
      } catch (\PDOException $ce) { // PDOException を明示的にキャッチ
        $errorMessage = "Failed to create 'members' table: " . $ce->getMessage();
        bootstrap_log($errorMessage);
        error_log($errorMessage, 0);
        die('Critical error: Could not create necessary database tables. Please check logs.');
      }
    } else {
      $errorMessage = "Database error while checking 'members' table: " . $e->getMessage();
      bootstrap_log($errorMessage);
      error_log($errorMessage, 0);
    }
  }

  // --- 7.1. posts テーブルの作成 ---
  try {
    $db->query('SELECT 1 FROM posts LIMIT 1');
    bootstrap_log("Table 'posts' already exists.");
  } catch (\PDOException $e) {
    $errorMessageLower = strtolower($e->getMessage());
    if (
      strpos($errorMessageLower, 'no such table') !== false ||
      strpos($errorMessageLower, 'table or view not found') !== false ||
      strpos($errorMessageLower, "doesn't exist") !== false ||
      (strpos($errorMessageLower, 'undefined table') !== false && strpos($errorMessageLower, 'posts') !== false)
    ) {
      bootstrap_log("Table 'posts' not found. Attempting to create it...");
      try {
        $dbType = strtolower($settings->get('database.type', 'mysql'));
        
        if ($dbType === 'sqlite') {
          $sql = 'CREATE TABLE posts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    created_by INTEGER NOT NULL,
                    post TEXT,
                    fname VARCHAR(255),
                    extension VARCHAR(10),
                    ratio VARCHAR(50),
                    reply_to VARCHAR(255),
                    reply_post TEXT,
                    reply_id INTEGER,
                    reply_created TIMESTAMP,
                    reply_from_id INTEGER,
                    edit VARCHAR(10) DEFAULT NULL,
                    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES members(id)
                  )';
        } elseif ($dbType === 'mysql') {
          $sql = 'CREATE TABLE posts (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(255) NOT NULL,
                    created_by INT NOT NULL,
                    post TEXT,
                    fname VARCHAR(255),
                    extension VARCHAR(10),
                    ratio VARCHAR(50),
                    reply_to VARCHAR(255),
                    reply_post TEXT,
                    reply_id INT,
                    reply_created TIMESTAMP NULL,
                    reply_from_id INT,
                    edit VARCHAR(10) DEFAULT NULL,
                    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES members(id)
                  )';
        } else {
          bootstrap_log("Unsupported DB type '{$dbType}' for posts table creation.");
        }

        if (!empty($sql)) {
          $db->exec($sql);
          bootstrap_log("Table 'posts' created successfully for {$dbType}.");
        }
      } catch (\PDOException $ce) {
        $errorMessage = "Failed to create 'posts' table: " . $ce->getMessage();
        bootstrap_log($errorMessage);
        error_log($errorMessage, 0);
        die('Critical error: Could not create necessary database tables. Please check logs.');
      }
    } else {
      $errorMessage = "Database error while checking 'posts' table: " . $e->getMessage();
      bootstrap_log($errorMessage);
      error_log($errorMessage, 0);
    }
  }
} else {
  bootstrap_log('Skipping table creation check because database connection is not available.');
}

// --- 8. Auth クラスの初期化 ---
if ($db instanceof \PDO) {
  Auth::initialize($db);
  bootstrap_log('Authentication service initialized.');
} else {
  $errorMessage = 'CRITICAL FAILURE: DB connection is not available (was null after Connection::getConnection()). Authentication service cannot be initialized. Check Connection.php for potential errors like SQLite path or permissions. Review bootstrap.log for messages from Connection::getConnection().';
  bootstrap_log($errorMessage);
  error_log($errorMessage, 0);
  die('Critical application error: Unable to initialize authentication service due to database connection failure. Please check bootstrap.log and php_errors.log for details. Database connection might have failed in Connection::getConnection().');
}

bootstrap_log('--- Bootstrap process finished ---');
