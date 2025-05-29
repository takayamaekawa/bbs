<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ユーザー登録 - 絶・掲示板</title>
  <link rel="stylesheet" href="/assets/css/main_sp.css" media="screen and (max-width:520px)">
  <link rel="stylesheet" href="/assets/css/main_style.css" media="screen and (min-width:520px) and (max-width:960px)">
  <link rel="stylesheet" href="/assets/css/main_pc.css" media="screen and (min-width:960px)">
  <link rel="stylesheet" href="/assets/css/register_sch.css">
  <link rel="stylesheet" href="/assets/css/box.css">
  <script src="https://code.jquery.com/jquery.min.js"></script>
  <script>
    $(function() {
      $(".D").click(function() {
        $(".E").slideToggle("");
      });
    });
  </script>
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
    <div class="font3-0">&nbsp;&nbsp;ユーザー登録画面</div>
    <p class="hr2"></p>

    <a href="/" class="right font1-5 white under">ホームページに戻る</a>
    <br><br>

    <div class="center">
      <?php if (isset($errors['general'])): ?>
        <div class="red font1-5"><?= htmlspecialchars($errors['general'], ENT_QUOTES) ?></div>
      <?php endif; ?>

      <div class="D">▼個人情報の取り扱いについて▼</div>
      <div class="E">
        <div class="comment font2-0 box5">
          <p>
            絶・掲示板(以降、当サイト)では、セキュリティ強化とアカウント保護のため、
            メールアドレス認証を行うことにしました。なお、当サイトでは、
            メールアドレスを個人情報に含め、
            当サイトはこのメールアドレスを外部に漏らさないことを約束するともに、
            あらかじめユーザーの同意を得ることなく、使用目的以外のことに使うことなしに、また、第三者にそれらを提供することはありません。<br>
            使用目的は主に、以下の通りです。<br>
            1. ユーザーからのお問い合わせに回答<br>(本人確認を行うことを含む)<br>
            2. メンテナンス、お知らせ、ワンタイムパスワードなど、必要に応じた連絡<br>
            上記に納得する方は以下より、
            有効なメールアドレスを入力し、認証の手続きを進めてください。
          </p>
        </div><br>
      </div><br>

      <form action="" method="post">
        <input type="hidden" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>" name="registertoken">

        <label class="font2-0">
          名前
          <input type="text" name="name" style="width:150px" value="<?= htmlspecialchars($formData['name'] ?? '', ENT_QUOTES) ?>">
          <?php if (isset($errors['name']) && $errors['name'] == 'blank'): ?>
            <p class="error">名前を入力してください</p>
          <?php endif; ?>
          <?php if (isset($errors['name']) && $errors['name'] == 'duplicate'): ?>
            <p class="error">すでにその名前は登録されています。</p>
          <?php endif; ?>
        </label>
        <br>

        <label class="font2-0">
          メールアドレス
          <input type="email" name="email" style="width:220px" value="<?= htmlspecialchars($formData['email'] ?? '', ENT_QUOTES) ?>">
          <?php if (isset($errors['email']) && $errors['email'] == 'blank'): ?>
            <p class="error">メールアドレスを入力してください</p>
          <?php endif; ?>
          <?php if (isset($errors['email']) && $errors['email'] == 'duplicate'): ?>
            <p class="error">すでにそのメールアドレスは登録されています。</p>
          <?php endif; ?>
        </label>
        <br>

        <label class="font2-0">
          パスワード
          <input type="password" name="password" style="width:150px">
          <?php if (isset($errors['password']) && $errors['password'] == 'blank'): ?>
            <p class="error">パスワードを入力してください</p>
          <?php endif; ?>
          <?php if (isset($errors['password']) && $errors['password'] == 'length'): ?>
            <p class="error">6文字以上で指定してください</p>
          <?php endif; ?>
        </label>
        <br>

        <label class="font2-0">
          パスワード再入力<span class="red">*</span>
          <input type="password" name="password2" style="width:150px">
          <?php if (isset($errors['password2']) && $errors['password2'] == 'blank'): ?>
            <p class="error">パスワードを入力してください</p>
          <?php endif; ?>
          <?php if (isset($errors['password2']) && $errors['password2'] == 'difference'): ?>
            <p class="error">パスワードが上記と違います</p>
          <?php endif; ?>
        </label>
        <br>

        <input type="checkbox" id="notes" name="check" value="check" required>
        <label class="font1-5" for="notes">上記「個人情報の取り扱いについて」に同意しますか？</label>
        <br>

        <input type="submit" value="確認する" class="button">
      </form>
    </div>
  </div>

  <?php include __DIR__ . '/../Footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>
