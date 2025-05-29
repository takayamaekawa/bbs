<?php

use Root\Composer\Core\Board\BoardController;

require_once __DIR__ . '/../../bootstrap.php';

try {
  $controller = new BoardController();
  $controller->edit();
} catch (Exception $e) {
  error_log('Board Edit Error: ' . $e->getMessage());
  http_response_code(500);
  echo '<!DOCTYPE html><html><head><title>エラー</title></head><body>';
  echo '<h1>エラーが発生しました</h1>';
  echo '<p>システムエラーが発生しました。しばらくしてから再度お試しください。</p>';
  echo '</body></html>';
}
