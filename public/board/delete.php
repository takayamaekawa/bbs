<?php

use Root\Composer\Core\Board\BoardController;

require_once __DIR__ . '/../../bootstrap.php';

try {
  $controller = new BoardController();
  $controller->delete();
} catch (Exception $e) {
  error_log('Board Delete Error: ' . $e->getMessage());
  http_response_code(500);
  header('Location: /board/index.php');
  exit();
}
