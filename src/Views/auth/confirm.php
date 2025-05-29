<?php use Root\Composer\Core\Config\Settings; $settings = Settings::getInstance(); $siteName = $settings->get('app_settings.site_name', '絶・掲示板'); ?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>登録完了 - <?= htmlspecialchars($siteName, ENT_QUOTES) ?></title>
  <link rel="stylesheet" href="/assets/css/main_sp.css" media="screen and (max-width:520px)">
  <link rel="stylesheet" href="/assets/css/main_style.css" media="screen and (min-width:520px) and (max-width:960px)">
  <link rel="stylesheet" href="/assets/css/main_pc.css" media="screen and (min-width:960px)">
</head>

<body>
  <?php echo \Root\Composer\Views\Helpers\HeaderHelper::render(); ?>

  <div style="margin-top: 60px;"><br></div>

  <div class="white misaki_gothic">
    <div class="font3-0">&nbsp;&nbsp;登録完了</div>
    <p class="hr2"></p>

    <div class="center">
      <div class="green font2-0">ユーザー登録が完了しました！</div>
      <br>

      <div class="font2-0">
        登録情報：<br>
        名前: <?= htmlspecialchars($userData['name'], ENT_QUOTES) ?><br>
        メールアドレス: <?= htmlspecialchars($userData['email'], ENT_QUOTES) ?><br>
      </div>
      <br>

      <?php if (isset($_SESSION['verification_email_sent']) && $_SESSION['verification_email_sent']): ?>
        <div class="font1-5" style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
          <strong>📧 メール認証が必要です</strong><br><br>
          登録したメールアドレスに認証メールを送信しました。<br>
          メール内のリンクをクリックして認証を完了してからログインしてください。<br><br>
          <small>※ メールが届かない場合は迷惑メールフォルダもご確認ください</small>
        </div>
        <?php unset($_SESSION['verification_email_sent']); ?>
      <?php endif; ?>

      <a href="/profile/login.php" class="repo-btn-blue white">ログインページへ</a>
    </div>
  </div>

  <?php include __DIR__ . '/../Footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>
