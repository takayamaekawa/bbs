<?php

use Root\Composer\Core\Auth\Auth;
use Root\Composer\Core\Auth\EmailVerificationService;
use Root\Composer\Core\Config\Settings;
use Root\Composer\Core\Database\Connection;

require_once __DIR__ . '/../../bootstrap.php';

session_start();

// 既にログイン済みの場合はリダイレクト
if (Auth::check()) {
  header('Location: /board/index.php');
  exit();
}

$error = null;
$success = false;

// GETパラメータからユーザーIDとトークンを取得
if (isset($_GET['u'], $_GET['t'])) {
  $userId = (int)$_GET['u'];
  $token = $_GET['t'];

  if ($userId > 0 && !empty($token)) {
    $db = Connection::getConnection();
    $settings = Settings::getInstance();

    if ($db && $settings) {
      $verificationService = new EmailVerificationService($db, $settings);

      if ($verificationService->verifyToken($userId, $token)) {
        $success = true;
      } else {
        $error = '認証リンクが無効または期限切れです。';
      }
    } else {
      $error = 'システムエラーが発生しました。';
    }
  } else {
    $error = '無効なリンクです。';
  }
} else {
  $error = '認証パラメータが不足しています。';
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <?php $settings = \Root\Composer\Core\Config\Settings::getInstance(); $siteName = $settings->get('app_settings.site_name', '絶・掲示板'); ?>
  <title>メール認証 - <?= htmlspecialchars($siteName, ENT_QUOTES) ?></title>
  <link rel="stylesheet" href="/assets/css/main_sp.css" media="screen and (max-width:520px)">
  <link rel="stylesheet" href="/assets/css/main_style.css" media="screen and (min-width:520px) and (max-width:960px)">
  <link rel="stylesheet" href="/assets/css/main_pc.css" media="screen and (min-width:960px)">
  <style>
    .success {
      color: green;
      font-size: 1.2em;
    }

    .error {
      color: red;
      font-size: 1.2em;
    }
  </style>
</head>

<body>
  <?php echo \Root\Composer\Views\Helpers\HeaderHelper::render(); ?>

  <div style="margin-top: 60px;"><br></div>

  <div class="white misaki_gothic">
    <p class="hr2"></p>

    <a href="/" class="right font1-5 white under">ホームページに戻る</a>
    <br><br>

    <div class="center">
      <div class="font3-0">メールアドレス認証</div>
      <br>

      <?php if ($success): ?>
        <div class="success">
          <p>メールアドレスの認証が完了しました！</p>
          <p>ログインして掲示板をお楽しみください。</p>
        </div>
        <br>
        <a href="/profile/login.php" class="repo-btn-blue white font2-0">ログインする</a>
      <?php elseif ($error): ?>
        <div class="error">
          <p><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
        </div>
        <br>
        <div class="font2-0">
          <a href="/profile/register.php" class="repo-btn-blue white">新規登録</a>
          <br><br>
          <a href="/profile/login.php" class="repo-btn-gray white">ログイン</a>
        </div>
      <?php endif; ?>
    </div>
    <br><br>
  </div>

  <?php include __DIR__ . '/../../src/Views/Footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>
