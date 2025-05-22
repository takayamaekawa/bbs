<?php

// --- 1. プロジェクトルートの定義 ---
// この bootstrap.php ファイルがあるディレクトリをプロジェクトルートとします。
if (!defined('PROJECT_ROOT')) {
  define('PROJECT_ROOT', __DIR__);
}

// --- 2. オートローダーの読み込み ---
// Composer を使用している場合 (推奨):
if (file_exists(PROJECT_ROOT . '/vendor/autoload.php')) {
  require_once PROJECT_ROOT . '/vendor/autoload.php';
} else {
  // Composer を使用していない場合 (手動でクラスファイルを読み込む):
  // このパスはプロジェクト構造に合わせて調整してください。
  // PSR-4 に従っているなら、このような手動読み込みは通常不要です。
  require_once PROJECT_ROOT . '/src/Core/Config/Settings.php';
  require_once PROJECT_ROOT . '/src/Core/Database/Connection.php';
  // 他に必要なクラスがあればここに追加
  // error_log('Composer autoload not found. Consider using Composer for dependency management and autoloading.');
}

// --- 3. エラー表示設定 (開発用) ---
// 本番環境ではエラーをログファイルに記録し、画面には表示しないようにしてください。
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// --- 名前空間のエイリアス (任意) ---
use Root\Composer\Core\Config\Settings;
use Root\Composer\Core\Database\Connection;

// --- 4. Settings クラスの初期化 ---
$configJsonPath = PROJECT_ROOT . '/config/config.json'; // config.json のパス
$settings = null; // スコープ外でも使えるように初期化

try {
  $settings = Settings::getInstance($configJsonPath);
  // echo "Settings initialized successfully.<br>"; // デバッグ用
} catch (\Exception $e) {
  // 設定ファイルの読み込みに失敗した場合、アプリケーションは続行できない可能性が高い
  error_log('FATAL ERROR: Failed to initialize Settings. ' . $e->getMessage());
  // ユーザーフレンドリーなエラーページを表示するか、処理を中断
  die('Application critical error: Configuration could not be loaded. Please contact administrator. (Details logged)');
}

// --- 5. Connection クラスの初期化 ---
// Settingsの初期化が成功した場合のみ実行
if ($settings instanceof Settings) {
  try {
    Connection::initialize($settings);
    // echo "Database Connection class initialized.<br>"; // デバッグ用

    // --- オプション: DB接続テスト ---
    // $db = Connection::getConnection();
    // if ($db) {
    //     echo "Successfully connected to the database!<br>";
    //     // $stmt = $db->query("SELECT VERSION()");
    //     // $version = $stmt->fetchColumn();
    //     // echo "Database version: " . $version . "<br>";
    // } else {
    //     echo "Failed to get database connection from Connection class.<br>";
    //     error_log("Bootstrap: Failed to establish database connection after initialization.");
    // }
  } catch (\Exception $e) {
    error_log('FATAL ERROR: Failed to initialize Database Connection. ' . $e->getMessage());
    die('Application critical error: Database connection could not be initialized. Please contact administrator. (Details logged)');
  }
} else {
  // このケースはSettings初期化失敗時に既にdieしているはずだが、念のため
  error_log('FATAL ERROR: Settings object is not available for Database Connection initialization.');
  die('Application critical error: Core settings are missing. Please contact administrator.');
}

// bootstrap.php が読み込まれた時点で、Settings と Connection (の初期化) が完了しています。
// 他のファイルで、Settings::getInstance() や Connection::getConnection() を使って
// 設定値やDB接続を取得できます。
// 例えば、 $db = Connection::getConnection(); のようにしてPDOインスタンスを取得できます。
// $siteName = Settings::getInstance()->get('app_settings.site_name', 'Default App Name');

// echo "Bootstrap finished.<br>"; // デバッグ用
?>
