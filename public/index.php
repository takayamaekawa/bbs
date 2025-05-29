<?php

use Root\Composer\Core\Auth\Auth;

require_once __DIR__ . '/../bootstrap.php';

session_start();

$isLoggedIn = Auth::check();
$user = null;

if ($isLoggedIn) {
  $user = Auth::user();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>絶・掲示板</title>
  <link rel="stylesheet" href="/assets/css/main_sp.css" media="screen and (max-width:520px)">
  <link rel="stylesheet" href="/assets/css/main_style.css" media="screen and (min-width:520px) and (max-width:960px)">
  <link rel="stylesheet" href="/assets/css/main_pc.css" media="screen and (min-width:960px)">
</head>

<body>
  <?php echo \Root\Composer\Views\Helpers\HeaderHelper::render(); ?>

  <div style="margin-top: 60px;"><br></div>

  <div class="white">
    <div class="center">
      <h1 class="font3-0">絶・掲示板へようこそ！</h1>
      <br>

      <?php if ($isLoggedIn): ?>
        <div class="font2-0">
          <?= htmlspecialchars($user['name'], ENT_QUOTES) ?>さん、お帰りなさい！
        </div>
        <br>
        <a href="/board/index.php" class="repo-btn-blue white font2-0">掲示板に参加する</a>
        <br><br>
        <a href="/profile/index.php" class="repo-btn-gray white font1-5">プロフィール設定</a>
      <?php else: ?>
        <div class="font2-0">
          掲示板に参加するにはログインが必要です
        </div>
        <br>
        <a href="/profile/login.php" class="repo-btn-blue white font2-0">ログイン</a>
        <br><br>
        <a href="/profile/register.php" class="repo-btn-gray white font1-5">新規登録</a>
        <br><br>
        <a href="/board/index.php" class="repo-btn-gray white font1-5">掲示板を見る（ゲスト）</a>
      <?php endif; ?>
    </div>
  </div>

  <?php include __DIR__ . '/../src/Views/Footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>
