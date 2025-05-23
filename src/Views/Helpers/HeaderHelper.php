<?php

namespace Root\Composer\Views\Helpers;

use Root\Composer\Core\Auth\Auth; // Authクラスをインポート

class HeaderHelper
{
  /**
   * アプリケーションのヘッダーHTMLを生成して返します。
   * 認証状態に応じて表示内容が変わります。
   *
   * @param string $siteName 表示するサイト名
   * @param array|null $counter カウンター情報を含む配列 (例: ['todayipcount' => [0 => 123]])
   * @param string|null $defaulticonuploaddir 未ログイン時のデフォルトアイコンのパス (globパターン)
   * @return string 生成されたヘッダーHTML
   */
  public static function render(string $siteName = 'Default Site Name', ?array $counter = null, ?string $defaulticonuploaddir = null): string
  {
    $currentUser = Auth::user(); // ログインしていなければ null が返る想定

    ob_start();
?>
    <header>
      <br>
      <div class="header-area">
        <h1 class="green" style="z-index: 5;"><a href="/">&nbsp;&nbsp;<?php echo htmlspecialchars($siteName); ?></a></h1>
        <div class="absolute_right">
          <div class="trim_header profile_icon profile" style="z-index: 5;">
            <?php if (Auth::check() && isset($currentUser['icon'])) : ?>
              <img src="/assets/img/upload/icon/user/<?php echo htmlspecialchars($currentUser['icon']); ?>"></img>
            <?php elseif (!Auth::check() && $defaulticonuploaddir) : ?>
              <?php
              $file_get = glob($defaulticonuploaddir);
              $countfile = 0;
              if ($file_get !== false) {
                $countfile = count($file_get);
              }
              if ($countfile > 0) {
                $random = rand(0, $countfile - 1);
                $default_icon_name = basename($file_get[$random]);
                if (isset($default_icon_name)) {
                  echo '<img src="/assets/img/upload/icon/user/' . htmlspecialchars($default_icon_name) . '"></img>';
                }
              }
              ?>
            <?php endif; ?>
          </div>
        </div>

        <ul class="slide-menu">
          <br>
          <li>
            <div class="f-flex">
              <div class="welcome">
                <?php if (Auth::check() && isset($currentUser['name'])) : ?>
                  <a class="font1-5">&nbsp;&nbsp;<?php echo htmlspecialchars($currentUser['name'], ENT_QUOTES); ?>さん、ようこそ</a>
                <?php else : ?>
                  <a class="font1-5">&nbsp;&nbsp;ななしさん、ようこそ</a>
                <?php endif; ?>
              </div>
              <?php if (Auth::check()) : ?>
                <div class="absolute_right f-flex">
                  <div>
                    <form action="/profile/login.php" method="post">
                      <input type="hidden" name="logout_action" value="true">
                      <input type="hidden" name="reset" value="true">
                      <input style="width:25px;height:25px;margin-right:10px;" type="image" src="/assets/img/icon/exit4.png" alt="ログアウト">
                    </form>
                  </div>
                  <div>
                    <a href="./.dirc.php"><img style="width:25px;height:25px;margin-right:10px;" src="/assets/img/icon/folder.png" alt="ディレクトリ"></img></a>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </li>
          <br><br>
          <li>
            <?php if (Auth::check()) : ?>
              <a href="/profile/index.php" class="kh-dougen16 font1-5 repo-btn-blue white">プロフィール変更はこちら</a>
            <?php else : ?>
              <a href="/profile/login.php" class="kh-dougen16 font1-5 repo-btn-blue white">ログインはこちら</a>
            <?php endif; ?>
          </li>
          <?php if (Auth::check()) : ?>
            <br>
          <?php endif; ?>
        </ul>
        <script>
          const profileElementForMenu = document.querySelector('.header-area .profile');
          const slideMenuElementForMenu = document.querySelector('.header-area .slide-menu');

          if (profileElementForMenu && slideMenuElementForMenu) {
            profileElementForMenu.addEventListener('click', function() {
              this.classList.toggle('active');
              slideMenuElementForMenu.classList.toggle('active');
            });
          }
        </script>
      </div>
      <style>
        /* CSSの構文エラーを修正 */
        /* カスタムタグ nyanya, nyanyanya をクラスセレクタに変更するか、 */
        /* これらのタグがHTMLで正しく定義・認識されることを前提とします。 */
        /* ここでは、これらがdivやspanのような要素のクラスであると仮定して修正します。 */
        .header-area .flex .nyanya img {
          /* クラスとして解釈 */
          margin-top: 5px;
        }

        .header-area .flex .nyanyanya img {
          /* クラスとして解釈 */
          margin-top: 10px;
        }

        /* もし nyanya, nyanyanya がカスタム要素として扱いたい場合、
           CSS自体は問題ないかもしれませんが、HTML側の定義やブラウザの解釈に依存します。
           ここでは、Linterが警告を出す一般的なCSS構文に寄せています。 */
      </style>
      <div class="font1-0 white headscroll" style="line-height: 10px!important;letter-spacing: 5px;padding-right:40px;">
        <span>
          <a class="" href="/counter/index.php">
            <div class="flex">
              <span class="nyanya"> <?php // カスタムタグをspanとクラスに変更する例 
                                    ?>
                <img src="/assets/img/icon/link3.png" width="20px" height="25px">
              </span>
              <span class="nyanyanya" style="padding-top:-3px;!important;"> <?php // カスタムタグをspanとクラスに変更する例 
                                                                            ?>
                あなたは本日<?php echo isset($counter['todayipcount'][0]) ? htmlspecialchars($counter['todayipcount'][0]) : '0'; ?>人目の訪問者です。
              </span>
            </div>
          </a>
        </span>
      </div>
    </header>
<?php
    return ob_get_clean();
  }
}
