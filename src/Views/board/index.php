<?php use Root\Composer\Core\Config\Settings; $settings = Settings::getInstance(); $siteName = $settings->get('app_settings.site_name', '絶・掲示板'); ?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta http-equiv="Pragma" content="no-cache">
  <link rel="stylesheet" href="/assets/css/main_sp.css" media="screen and (max-width:520px)">
  <link rel="stylesheet" href="/assets/css/main_style.css" media="screen and (min-width:520px) and (max-width:960px)">
  <link rel="stylesheet" href="/assets/css/main_pc.css" media="screen and (min-width:960px)">
  <link rel="stylesheet" href="/assets/css/ogp.css">
  <link rel="stylesheet" href="/assets/css/board.php">
  <link rel="stylesheet" href="/assets/css/pagenation.css">
  <script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js?lang=css&skin=sons-of-obsidian"></script>
  <script src='/assets/js/input_retention.js'></script>
  <title><?= htmlspecialchars($siteName, ENT_QUOTES) ?></title>
</head>

<body>
  <?php echo \Root\Composer\Views\Helpers\HeaderHelper::render(); ?>

  <div style="margin-top: 60px;"><br></div>

  <?php if (!$session): ?>
    <div class="form">
      <a href="/profile/login.php" class="kh-dougen16 font1-5 repo-btn-blue white center">ログインはこちら</a>
    </div>
  <?php endif; ?>

  <div class="j-flex">
    <div>
      <h3 class="white" id="title">&nbsp;&nbsp;<?= htmlspecialchars($siteName, ENT_QUOTES) ?></h3>
    </div>
    <div class="f-flex">
      <div>
        <a class="right font1-5 white under" href="/board/photo_list.php">📷画像一覧</a>
      </div>
      <div>
        <a class="right font1-5 white under" href="/board/media_list.php">🎬動画・音声一覧</a>
      </div>
    </div>
  </div>

  <p class="hr2"></p>

  <div class="white">
    <?php if ($session): ?>
      <div class="j-flex">
        <div class="child font1-5 form">
          前回の投稿から時間が経ちました。
        </div>
      </div>

      <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">

        <div class="form">
          <?php if (isset($reply)): ?>
            <div class="flex">
              <div style="padding:5px 0;">
                <input type="hidden" name="name" value="<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>">
                <input readonly type="text" value="<?= htmlspecialchars($user['name'], ENT_QUOTES) ?> >> <?= htmlspecialchars($reply['id'], ENT_QUOTES) ?> >> <?= htmlspecialchars($reply['name'], ENT_QUOTES) ?>に返信中" required maxlength="30" size="30">
              </div>
              <div style="position:absolute;right:0;">
                <a class="repo-btn-gray white" href="#<?= htmlspecialchars($reply['id'], ENT_QUOTES) ?>">戻る</a>
              </div>
            </div>
          <?php else: ?>
            <input readonly type="text" value="<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>" id="name" name="name" required maxlength="20" size="16"><br>
          <?php endif; ?>

          <div class="flex">
            <div>
              <textarea id="textarea" name="post" cols="50" rows="10" placeholder="コメントを入力できます。"></textarea>
            </div>
            <div>
              <input class="verti" type="reset" />
            </div>
          </div>
          <br>
          <div style="margin-top:-49px;" class="white flex">
            <div>
              <input type="file" name="upfile" value="画像を選択">
            </div>
            <div class="post_btn">
              <input type="submit" value="投稿する" class="button02">
            </div>
          </div>
          <p class="font1-5 form_reverse">
            ※最大アップロードサイズ: <?= ini_get('upload_max_filesize') ?><br>
            ※動画・画像・音声ファイルが送れるよ！<br>
          </p>
        </div>
      </form>
    <?php else: ?>
      <form action="" method="post" enctype="multipart/form-data">
        <div class="form">
          <input readonly type="text" value="" id="name" name="name" required maxlength="20" size="16" placeholder="名前はまだ無い...">
          <br>
          <textarea readonly name="post" cols="50" rows="10" placeholder="投稿にはログインが必要です。"></textarea>
          <br>
          <div style="display:flex;">
            <div>
              <button id="fileSelect" type="button" onclick="alert('頼むからログインしてくれや！ｗ')">🔒画像を選択</button>
            </div>
            <div class="non_user_post_btn">
              <input type="button" onclick="alert('頼むからログインしてくれや！ｗ')" name="upload" value="🔒投稿する" class="button02">
            </div>
          </div>
          <p class="font1-5 form_reverse">
            ※最大アップロードサイズ: <?= ini_get('upload_max_filesize') ?><br>
            ※動画・画像・音声ファイルが送れるよ！<br>
          </p>
        </div>
      </form>
    <?php endif; ?>

    <p class="hr2"></p>

    <?php foreach ($posts as $post): ?>
      <div id="<?= htmlspecialchars($post['id'], ENT_QUOTES) ?>" class="anchor3"></div>

      <div class="j-flex">
        <div class="num">
          <?= htmlspecialchars($post['id'], ENT_QUOTES) ?> >>
        </div>
      </div>

      <div class="name_reverse">
        <div class="right_btn">
          <?php if ($session): ?>
            <?php if ($user['id'] == $post['created_by']): ?>
              <a class="repo-btn-blue white" href="/board/fix.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES) ?>">編集</a>
            <?php else: ?>
              <a class="repo-btn-gray white" href="?id=<?= htmlspecialchars($post['id'], ENT_QUOTES) ?>&p=<?= htmlspecialchars($_GET['p'] ?? 1, ENT_QUOTES) ?>">返信</a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <br>

      <div class="j-flex">
        <div class="flex">
          <div class="trim name_icon">
            <?php if (isset($post['icon'])): ?>
              <img src="https://keypforev.f5.si/assets/img/upload/icon/user/<?= htmlspecialchars($post['icon'], ENT_QUOTES) ?>">
            <?php endif; ?>
          </div>
          <div>
            <?= htmlspecialchars($post['name'], ENT_QUOTES) ?>:&nbsp;
          </div>
          <div class="time">
            <?= htmlspecialchars(substr(str_replace('-', '/', $post['created']), 5, -3), ENT_QUOTES) ?>
          </div>
        </div>
        <div class="right_btn">
          <?php if ($session && $user['id'] == $post['created_by']): ?>
            <a class="repo-btn-gray white" href="?id=<?= htmlspecialchars($post['id'], ENT_QUOTES) ?>&p=<?= htmlspecialchars($_GET['p'] ?? 1, ENT_QUOTES) ?>">返信</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="edit">
        <?php if ($post['edit'] === 'DONE'): ?>
          <div class="block">(<div class="block under">編集済み</div>)</div><br>
        <?php endif; ?>
      </div>

      <div class="comment">
        <?= nl2br(htmlspecialchars($post['post'], ENT_QUOTES)) ?>
      </div>

      <div>
        <?php if (!empty($post['fname'])): ?>
          <?php $ext = pathinfo($post['fname'], PATHINFO_EXTENSION); ?>
          <?php if (in_array($ext, ['mp4', 'mkv'])): ?>
            <div class="video">
              <video controls src="/assets/img/upload/<?= htmlspecialchars($post['fname'], ENT_QUOTES) ?>"></video>
            </div>
          <?php elseif (in_array($ext, ['jpeg', 'png', 'gif', 'jpg'])): ?>
            <div class="img">
              <img src="/assets/img/upload/<?= htmlspecialchars($post['fname'], ENT_QUOTES) ?>" style="aspect-ratio: <?= htmlspecialchars($post['ratio'] ?? 'auto', ENT_QUOTES) ?>">
            </div>
          <?php elseif (in_array($ext, ['mp3', 'wav'])): ?>
            <div class="center">
              <audio controls>
                <source src="/assets/img/upload/<?= htmlspecialchars($post['fname'], ENT_QUOTES) ?>">
                <p>※ご利用のブラウザでは再生できません</p>
              </audio>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <?php if (isset($post['reply_id'])): ?>
        <p class="dotted02"></p>
        <div class="f-flex">
          <div class="num">返信先➥</div><br>
          <div class="right">
            <a class="repo-btn-gray white" href="?p=1#<?= htmlspecialchars($post['reply_id'], ENT_QUOTES) ?>">ジャンプ</a>
          </div>
        </div>
        <div class="reply_comment">
          <?= nl2br(htmlspecialchars($post['reply_post'] ?? '投稿は削除されました', ENT_QUOTES)) ?>
        </div>
      <?php endif; ?>

      <p class="hr2"></p>
    <?php endforeach; ?>
  </div>

  <?php if (isset($pagination)): ?>
    <div class="center">
      <div class="white font1-5"><?= $pagination['totalPosts'] ?>件中、<?= $pagination['start'] ?>～<?= $pagination['end'] ?>件を表示中</div>
      <div class="pagination">
        <?php if ($pagination['hasPrev']): ?>
          <a class="white" href="?p=<?= $pagination['prev'] ?>">&laquo;</a>
        <?php else: ?>
          <a class="v-hidden white" href="#">&laquo;</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
          <?php if ($i == $pagination['current']): ?>
            <a class="white active" href="#"><?= $i ?></a>
          <?php else: ?>
            <a class="white" href="?p=<?= $i ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($pagination['hasNext']): ?>
          <a class="white" href="?p=<?= $pagination['next'] ?>">&raquo;</a>
        <?php else: ?>
          <a class="v-hidden white" href="#">&raquo;</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <br><br><br>

  <?php include __DIR__ . '/../Footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function() {
      $('a[href^="#"]').on('click', function(event) {
        event.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
          $('html, body').animate({
            scrollTop: target.offset().top
          }, 1000);
        }
      });
    });
  </script>
</body>

</html>
