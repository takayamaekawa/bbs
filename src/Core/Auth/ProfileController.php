<?php

namespace Root\Composer\Core\Auth;

use Root\Composer\Core\Database\Connection;
use Root\Composer\Core\Board\FileUploadHandler;

class ProfileController
{
  private User $userModel;
  private FileUploadHandler $fileHandler;

  public function __construct()
  {
    $db = Connection::getConnection();
    if (!$db) {
      throw new \RuntimeException('Database connection failed');
    }

    $this->userModel = new User($db);
    $this->fileHandler = new FileUploadHandler();
  }

  public function login(): void
  {
    session_start();

    if (Auth::check()) {
      header('Location: /board/index.php');
      exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->handleLogin();
      return;
    }

    $this->showLoginForm();
  }

  private function handleLogin(): void
  {
    if (!$this->validateLoginToken()) {
      $this->showLoginForm(['login' => 'failed']);
      return;
    }

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
      $this->showLoginForm(['login' => 'blank']);
      return;
    }

    $user = $this->userModel->findByEmail($email);

    if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
      $this->showLoginForm(['login' => 'failed']);
      return;
    }

    // ログイン成功
    $_SESSION['id'] = $user['id'];
    $_SESSION['time'] = time();

    // リダイレクト先の決定
    $redirectUrl = $this->getRedirectUrl();
    header('Location: ' . $redirectUrl);
    exit();
  }

  private function showLoginForm(array $errors = []): void
  {
    $token = $this->generateToken();
    $_SESSION['logintoken'] = $token;

    $viewData = [
      'token' => $token,
      'errors' => $errors,
      'email' => $_POST['email'] ?? '',
    ];

    $this->renderView('auth/login', $viewData);
  }

  public function register(): void
  {
    session_start();

    if (Auth::check()) {
      header('Location: /board/index.php');
      exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->handleRegister();
      return;
    }

    $this->showRegisterForm();
  }

  private function handleRegister(): void
  {
    if (!$this->validateRegisterToken()) {
      $this->showRegisterForm(['general' => 'アクセスが無効です。']);
      return;
    }

    $errors = $this->validateRegistrationData($_POST);

    if (!empty($errors)) {
      $this->showRegisterForm($errors);
      return;
    }

    // ユーザー作成
    $userData = [
      'name' => $_POST['name'],
      'email' => $_POST['email'],
      'password' => $_POST['password'],
    ];

    if ($this->userModel->create($userData)) {
      $_SESSION['join'] = $_POST;
      header('Location: /profile/confirm.php');
      exit();
    } else {
      $this->showRegisterForm(['general' => 'ユーザー登録に失敗しました。']);
    }
  }

  private function validateRegistrationData(array $data): array
  {
    $errors = [];

    // 名前のバリデーション
    if (empty($data['name'])) {
      $errors['name'] = 'blank';
    } elseif ($this->userModel->isNameTaken($data['name'])) {
      $errors['name'] = 'duplicate';
    }

    // メールアドレスのバリデーション
    if (empty($data['email'])) {
      $errors['email'] = 'blank';
    } elseif ($this->userModel->isEmailTaken($data['email'])) {
      $errors['email'] = 'duplicate';
    }

    // パスワードのバリデーション
    if (empty($data['password'])) {
      $errors['password'] = 'blank';
    } elseif (strlen($data['password']) < 6) {
      $errors['password'] = 'length';
    }

    if (empty($data['password2'])) {
      $errors['password2'] = 'blank';
    } elseif ($data['password'] !== $data['password2']) {
      $errors['password2'] = 'difference';
    }

    return $errors;
  }

  private function showRegisterForm(array $errors = []): void
  {
    $token = $this->generateToken();
    $_SESSION['registertoken'] = $token;

    $viewData = [
      'token' => $token,
      'errors' => $errors,
      'formData' => $_POST,
    ];

    $this->renderView('auth/register', $viewData);
  }

  public function index(): void
  {
    session_start();

    if (!Auth::check()) {
      header('Location: /profile/login.php');
      exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->handleProfileUpdate();
      return;
    }

    $this->showProfile();
  }

  private function handleProfileUpdate(): void
  {
    if (!$this->validateToken()) {
      throw new \RuntimeException('無効なリクエストです', 400);
    }

    if (isset($_FILES['iconup']) && $_FILES['iconup']['name'] !== '') {
      try {
        $filename = $this->fileHandler->handleUpload($_FILES['iconup']);
        $this->userModel->updateIcon(Auth::id(), $filename);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
      } catch (\RuntimeException $e) {
        error_log($e->getMessage());
        throw $e;
      }
    }
  }

  private function showProfile(): void
  {
    $user = Auth::user();
    $token = $this->generateToken();

    $viewData = [
      'user' => $user,
      'token' => $token,
    ];

    $this->renderView('auth/profile', $viewData);
  }

  public function logout(): void
  {
    session_start();
    $_SESSION = [];
    session_destroy();
    header('Location: /index.php');
    exit();
  }

  public function confirm(): void
  {
    session_start();

    if (!isset($_SESSION['join'])) {
      header('Location: /profile/register.php');
      exit();
    }

    $viewData = [
      'userData' => $_SESSION['join'],
    ];

    $this->renderView('auth/confirm', $viewData);
  }

  private function validateLoginToken(): bool
  {
    return isset($_POST['logintoken'], $_SESSION['logintoken']) &&
      $_POST['logintoken'] === $_SESSION['logintoken'];
  }

  private function validateRegisterToken(): bool
  {
    return isset($_POST['registertoken'], $_SESSION['registertoken']) &&
      $_POST['registertoken'] === $_SESSION['registertoken'];
  }

  private function validateToken(): bool
  {
    return isset($_POST['token'], $_SESSION['token']) &&
      $_POST['token'] === $_SESSION['token'];
  }

  private function generateToken(): string
  {
    $token = bin2hex(openssl_random_pseudo_bytes(16));
    $_SESSION['token'] = $token;
    return $token;
  }

  private function getRedirectUrl(): string
  {
    if (isset($_POST['where']) && !empty($_POST['where'])) {
      return $_POST['where'];
    }
    if (isset($_SESSION['where']) && !empty($_SESSION['where'])) {
      $url = $_SESSION['where'];
      unset($_SESSION['where']);
      return $url;
    }
    return '/board/index.php';
  }

  private function renderView(string $view, array $data = []): void
  {
    extract($data);
    $viewFile = __DIR__ . '/../../Views/' . $view . '.php';

    if (file_exists($viewFile)) {
      require $viewFile;
    } else {
      throw new \RuntimeException("View file not found: {$viewFile}");
    }
  }
}
