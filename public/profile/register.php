<?php

use Root\Composer\Core\Auth\ProfileController;

require_once __DIR__ . '/../../bootstrap.php';

try {
  $controller = new ProfileController();
  $controller->register();
} catch (Exception $e) {
  error_log('Profile Register Error: ' . $e->getMessage());
  http_response_code(500);
  echo '<!DOCTYPE html><html><head><title>エラー</title></head><body>';
  echo '<h1>エラーが発生しました</h1>';
  echo '<p>システムエラーが発生しました。しばらくしてから再度お試しください。</p>';
  echo '</body></html>';
}

