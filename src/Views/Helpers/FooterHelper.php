<?php

namespace Root\Composer\Views\Helpers;

class FooterHelper
{
  /**
   * アプリケーションのフッターHTMLを生成して返します。
   *
   * @param string $discordUrl Discordの招待URL
   * @param string $copyrightName コピーライトに表示する名前
   * @param int $copyrightStartYear コピーライトの開始年
   * @return string 生成されたフッターHTML
   */
  public static function render(
    string $discordUrl = '',
    string $copyrightName = 'Your Company',
    int $copyrightStartYear = 0
  ): string {
    $currentYear = date('Y');
    $copyrightYearString = (int)$copyrightStartYear === (int)$currentYear || $copyrightStartYear === 0
      ? $currentYear
      : $copyrightStartYear . '-' . $currentYear;

    ob_start();
    ?>
  <footer>
    <div style="line-height: 10px!important; text-align: center; padding: 20px 0;">
      <?php if (!empty($discordUrl)) : ?>
      <a href="<?php echo htmlspecialchars($discordUrl); ?>" target="_blank" rel="noopener noreferrer">
        <img src="/assets/svg/discord_blue.svg" width="100" height="25" alt="Discord">
      </a>
      <br><br> 
      <?php endif; ?>
      <p style="margin: 0; font-size: 0.9em;">Copyright &copy; <?php echo $copyrightYearString; ?> <?php echo htmlspecialchars($copyrightName); ?></p>
    </div>
  </footer>
    <?php
    return ob_get_clean();
  }
}
