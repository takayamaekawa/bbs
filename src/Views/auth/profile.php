<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>プロフィール - 絶・掲示板</title>
  <link rel="stylesheet" href="/assets/css/main_sp.css" media="screen and (max-width:520px)">
  <link rel="stylesheet" href="/assets/css/main_style.css" media="screen and (min-width:520px) and (max-width:960px)">
  <link rel="stylesheet" href="/assets/css/main_pc.css" media="screen and (min-width:960px)">
</head>

<body>
  <?php echo \Root\Composer\Views\Helpers\HeaderHelper::render(); ?>

  <div style="margin-top: 60px;"><br></div>

  <div class="white misaki_gothic">
    <div>
      <h3 class="white" id="title">&nbsp;&nbsp;プロフィール変更</h3>
    </div>
    <p class="hr2"></p>

    <a href="/" class="right font1-5 white under">ホームページに戻る</a>

    <div class="center">
      <a class="font2-0">右上のアイコン変更はこちら</a>

      <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="iconup">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
        <input type="submit" value="アイコンを変更">
      </form>

      <p class="hr2"></p>

      <div class="font2-0">
        ユーザー情報<br>
        名前: <?= htmlspecialchars($user['name'], ENT_QUOTES) ?><br>
        メールアドレス: <?= htmlspecialchars($user['email'], ENT_QUOTES) ?><br>
      </div>

      <br>

      <form action="/profile/logout.php" method="post">
        <input type="submit" value="ログアウト" class="button">
      </form>
    </div>
  </div>

  <?php include __DIR__ . '/../Footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>
