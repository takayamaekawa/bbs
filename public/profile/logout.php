<?php

use Root\Composer\Core\Auth\ProfileController;

require_once __DIR__ . '/../../bootstrap.php';

try {
  $controller = new ProfileController();
  $controller->logout();
} catch (Exception $e) {
  error_log('Profile Logout Error: ' . $e->getMessage());
  http_response_code(500);
  header('Location: /index.php');
  exit();
}
