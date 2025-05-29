<?php use Root\Composer\Core\Config\Settings; $settings = Settings::getInstance(); $siteName = $settings->get('app_settings.site_name', '絶・掲示板'); ?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <link rel="stylesheet" href="/assets/css/main_sp.css" media="screen and (max-width:520px)">
  <link rel="stylesheet" href="/assets/css/main_style.css" media="screen and (min-width:520px) and (max-width:960px)">
  <link rel="stylesheet" href="/assets/css/main_pc.css" media="screen and (min-width:960px)">
  <link rel="stylesheet" href="/assets/css/ogp.css">
  <link rel="stylesheet" href="/assets/css/board.php">
  <title>投稿編集 - <?= htmlspecialchars($siteName, ENT_QUOTES) ?></title>
</head>

<body>
  <?php echo \Root\Composer\Views\Helpers\HeaderHelper::render(); ?>

  <div style="margin-top: 60px;"><br></div>

  <div class="white">
    <div class="j-flex">
      <div>
        <a class="font1-5"> ＞＞ <?= htmlspecialchars($user['name'], ENT_QUOTES) ?>さん、ようこそ</a>
      </div>
      <div>
        <a class="right font1-5 white under" href="/board/index.php#<?= htmlspecialchars($post['id'], ENT_QUOTES) ?>">掲示板へ戻る</a>
      </div>
    </div>
    <br>
    <h3 class="white" id="title">&nbsp;&nbsp;投稿編集画面</h3>
    <p class="hr2"></p>
    <br>

    <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">

      <div class="form">
        <input readonly type="text" value="<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>" id="name" name="name" required maxlength="20" size="16">
        <br>
        <textarea name="post" cols="50" rows="10" placeholder="コメントを追加できます"><?= htmlspecialchars($post['post'], ENT_QUOTES) ?></textarea>
        <br>

        <div class="flex">
          <div>
            <ul>
              <li>
                <label class="font1-5">
                  <input type="radio" name="reserve" value="false" checked>ファイル情報を更新しない
                </label>
              </li>
              <li>
                <label class="font1-5">
                  <input type="radio" name="reserve" id="b">ファイル情報を更新する
                </label>
              </li>
            </ul>
            <div class="text text02">
              <input type="file" name="upfile" value="画像を選択"><br>
              <label class="font1-5">
                <input type="checkbox" name="delete_file" value="GO">ファイル情報を削除する
              </label>
            </div>
          </div>
          <input type="hidden" name="edit" value="DONE">
          <div class="post_btn">
            <input onclick="return confirm('編集を完了し、掲示板へ戻ります')" type="submit" value="編集する" class="button02">
          </div>
        </div>

        <p class="font1-5 form_reverse">
          ※最大アップロードサイズ: <?= ini_get('upload_max_filesize') ?><br>
          ※動画・画像・音声ファイルが送れるよ！<br>
        </p>
      </div>
    </form>

    <p class="hr2"></p>

    <div id="<?= htmlspecialchars($post['id'], ENT_QUOTES) ?>" class="anchor3"></div>

    <div class="post">
      <div class="j-flex">
        <div class="num">
          <?= htmlspecialchars($post['id'], ENT_QUOTES) ?> >>
        </div>
        <div class="name_reverse">
          <div class="right_btn">
            <a onclick="return confirm('この投稿を削除しますか？')" class="repo-btn-red white" href="/board/delete.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES) ?>">削除</a>
          </div>
        </div>
      </div>
      <br>

      <div class="j-flex">
        <div class="flex">
          <div class="trim name_icon">
            <?php if (isset($user['icon'])): ?>
              <img src="https://keypforev.f5.si/assets/img/upload/icon/user/<?= htmlspecialchars($user['icon'], ENT_QUOTES) ?>">
            <?php endif; ?>
          </div>
          <div>
            <?= htmlspecialchars($post['name'], ENT_QUOTES) ?>:&nbsp;
          </div>
          <div class="time">
            <?= htmlspecialchars(substr(str_replace('-', '/', $post['created']), 5, -3), ENT_QUOTES) ?>
          </div>
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

      <p class="hr2"></p>
    </div>
  </div>

  <br><br><br><br>

  <?php include __DIR__ . '/../Footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(function() {
      $('[name="reserve"]:radio').change(function() {
        if ($('[id=a]').prop('checked')) {
          $('.text').fadeOut();
          $('.text01').fadeIn();
        } else if ($('[id=b]').prop('checked')) {
          $('.text').fadeOut();
          $('.text02').fadeIn();
        }
      });
    });
  </script>
</body>

</html>
