<?php use Root\Composer\Core\Config\Settings; $settings = Settings::getInstance(); $siteName = $settings->get('app_settings.site_name', '絶・掲示板'); ?>
<footer>
  <div class="footer-content">
    <div class="center white font1-5">
      <p>&copy; 2025 <?= htmlspecialchars($siteName, ENT_QUOTES) ?>. All rights reserved.</p>
      <p>
        <a href="/board/index.php" class="white under">掲示板</a> |
        <a href="/profile/index.php" class="white under">プロフィール</a> |
        <a href="https://github.com/bella2391" class="white under">GitHub</a>
      </p>
    </div>
  </div>
</footer>
