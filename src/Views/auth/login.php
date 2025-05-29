<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ログイン - 絶・掲示板</title>
  <link rel="stylesheet" href="/assets/css/main_sp.css" media="screen and (max-width:520px)">
  <link rel="stylesheet" href="/assets/css/main_style.css" media="screen and (min-width:520px) and (max-width:960px)">
  <link rel="stylesheet" href="/assets/css/main_pc.css" media="screen and (min-width:960px)">
  <style>
    .error {
      color: red;
      font-size: 0.8em;
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
      <div class="font3-0">ログイン画面</div>
      <br>

      <form action="" method="post">
        <input type="hidden" name="logintoken" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
        <?php if (isset($_SERVER['HTTP_REFERER'])): ?>
          <input type="hidden" name="where" value="<?= htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES) ?>">
        <?php endif; ?>

        <label class="font2-0">
          メールアドレス
          <input type="text" name="email" style="width:150px" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>">
          <?php if (isset($errors['login']) && $errors['login'] == 'blank'): ?>
            <p class="error">メールアドレスとパスワードを入力してください</p>
          <?php endif; ?>
          <?php if (isset($errors['login']) && $errors['login'] == 'failed'): ?>
            <p class="error">メールアドレスかパスワードが間違っています</p>
          <?php endif; ?>
        </label>
        <br>

        <label class="font2-0">
          パスワード
          <input type="password" name="password" style="width:150px">
        </label>
        <br>

        <input type="submit" value="ログインする" class="button">
        <br><br>
      </form>

      <div class="font2-0">
        ユーザー登録がまだの方<br>
        >><a class="repo-btn-blue white" href="/profile/register.php">ユーザ登録する</a>
      </div>
      <br>

      <div class="font2-0">
        パスワードを忘れた方<br>
        >><a class="repo-btn-blue white" href="/profile/reset.php">リセットする</a>
      </div>
    </div>
    <br><br>
  </div>

  <?php include __DIR__ . '/../Footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>
