<?php

namespace Root\Composer\Core\Board;

use Root\Composer\Core\Auth\Auth;
use Root\Composer\Core\Database\Connection;
use Root\Composer\Lib\Helpers\Escape;

class BoardController
{
  private Post $postModel;
  private FileUploadHandler $fileHandler;

  public function __construct()
  {
    $db = Connection::getConnection();
    if (!$db) {
      throw new \RuntimeException('Database connection failed');
    }

    $this->postModel = new Post($db);
    $this->fileHandler = new FileUploadHandler();
  }

  public function index(): void
  {
    session_start();

    $page = (int) ($_GET['p'] ?? 1);
    $page = max(1, $page);

    $reply = null;
    if (isset($_GET['id'])) {
      $reply = $this->postModel->getPostById((int) $_GET['id']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::check()) {
      $this->handlePostSubmission($reply);
      return;
    }

    $this->showBoardIndex($page, $reply);
  }

  private function handlePostSubmission(?array $reply): void
  {
    if (!$this->validateToken()) {
      header('Location: /profile/login.php');
      exit();
    }

    $user = Auth::user();
    $maxId = $this->postModel->getMaxPostId();

    $postData = [
      'name' => $_POST['name'],
      'created_by' => Auth::id(),
      'post' => $_POST['post'],
      'fname' => null,
      'reply_to' => $reply['name'] ?? null,
      'reply_post' => $reply['post'] ?? null,
      'reply_id' => $reply['id'] ?? null,
      'reply_created' => $reply['created'] ?? null,
      'reply_from_id' => $reply ? $maxId + 1 : null,
    ];

    if (isset($_FILES['upfile']) && $_FILES['upfile']['name'] !== '') {
      try {
        $filename = $this->fileHandler->handleUpload($_FILES['upfile']);
        $postData['fname'] = $filename;
      } catch (\RuntimeException $e) {
        error_log($e->getMessage());
      }
    }

    $this->postModel->createPost($postData);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
  }

  private function showBoardIndex(int $page, ?array $reply): void
  {
    $postCount = $this->postModel->getTotalPostCount();
    $times = $this->postModel->getAllCreatedTimes();

    $pagination = $this->generatePagination($page, $times, $postCount);
    $posts = $this->postModel->getPostsPaginated($page, 10);

    $viewData = [
      'posts' => $posts,
      'pagination' => $pagination,
      'reply' => $reply,
      'session' => Auth::check(),
      'user' => Auth::user(),
      'token' => $this->generateToken(),
      'postCount' => $postCount,
      'maxId' => $this->postModel->getMaxPostId(),
      'minId' => $this->postModel->getMinPostId(),
    ];

    $this->renderView('board/index', $viewData);
  }

  public function edit(): void
  {
    session_start();

    if (!Auth::check()) {
      header('Location: /profile/login.php');
      exit();
    }

    $postId = (int) ($_GET['id'] ?? 0);
    $post = $this->postModel->getPostById($postId);

    if (!$post || !$this->postModel->postBelongsToUser($postId, Auth::id())) {
      header('Location: /board/index.php');
      exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->handleEditSubmission($postId);
      return;
    }

    $viewData = [
      'post' => $post,
      'user' => Auth::user(),
      'token' => $this->generateToken(),
    ];

    $this->renderView('board/edit', $viewData);
  }

  private function handleEditSubmission(int $postId): void
  {
    $updateData = [
      'name' => $_POST['name'],
      'post' => $_POST['post'],
      'edit' => $_POST['edit'] ?? 'DONE',
      'fname' => null,
      'extension' => null,
    ];

    if (isset($_FILES['upfile']) && $_FILES['upfile']['name'] !== '') {
      try {
        $filename = $this->fileHandler->handleUpload($_FILES['upfile']);
        $updateData['fname'] = $filename;
        $updateData['extension'] = $this->fileHandler->getFileExtension($filename);
      } catch (\RuntimeException $e) {
        error_log($e->getMessage());
      }
    } elseif (isset($_POST['delete_file'])) {
      $updateData['fname'] = null;
      $updateData['extension'] = null;
    } else {
      $post = $this->postModel->getPostById($postId);
      $updateData['fname'] = $post['fname'];
      $updateData['extension'] = $post['extension'];
    }

    $this->postModel->updatePost($postId, $updateData);
    header('Location: /board/index.php#' . $postId);
    exit();
  }

  public function delete(): void
  {
    session_start();

    if (!Auth::check()) {
      exit();
    }

    $postId = (int) ($_REQUEST['id'] ?? 0);
    $this->postModel->deletePost($postId, Auth::id());

    header('Location: /board/index.php#' . $postId);
    exit();
  }

  private function generatePagination(int $currentPage, array $times, int $totalPosts): array
  {
    $perPage = 10;
    $totalPages = (int) ceil(count($times) / $perPage);

    $offset = ($currentPage - 1) * $perPage;
    $start = $offset + 1;
    $end = min($offset + $perPage, $totalPosts);

    return [
      'current' => $currentPage,
      'total' => $totalPages,
      'start' => $start,
      'end' => $end,
      'totalPosts' => $totalPosts,
      'hasPrev' => $currentPage > 1,
      'hasNext' => $currentPage < $totalPages,
      'prev' => $currentPage - 1,
      'next' => $currentPage + 1,
    ];
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
