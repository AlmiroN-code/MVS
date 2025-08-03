<?php
namespace Mini\Controller;

use Mini\Model\Song;

/**
 * Базовый контроллер с CSRF защитой
 */
abstract class BaseController
{
    /**
     * Генерирует CSRF токен и сохраняет в сессии
     */
    protected function generateCsrfToken(): string
    {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Проверяет валидность CSRF токена
     */
    protected function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && 
               hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    /**
     * Проверяет CSRF токен и перенаправляет на страницу ошибки если невалиден
     */
    protected function requireValidCsrfToken(): void
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        
        if (!$this->validateCsrfToken($token)) {
            $_SESSION['error_message'] = 'Недействительный CSRF токен. Попробуйте еще раз.';
            $this->redirect(URL . 'error');
        }
    }

    /**
     * Перенаправляет на указанный URL
     */
    protected function redirect(string $url): void
    {
        header("location: $url");
        exit;
    }
}

/**
 * Контроллер для управления песнями
 */
class SongsController extends BaseController
{
    private const ALLOWED_MIME_TYPES = ['audio/mpeg', 'audio/mp3', 'audio/wav'];
    private const ALLOWED_EXTENSIONS = ['mp3', 'wav'];
    private const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB

    /**
     * Отображает список всех песен
     */
    public function index()
    {
        $songModel = new Song();
        
        $data = [
            'csrfToken' => $this->generateCsrfToken(),
            'songs' => $songModel->getAllSongs(),
            'amount_of_songs' => $songModel->getAmountOfSongs(),
            'error_message' => $_SESSION['error_message'] ?? null,
            'success_message' => $_SESSION['success_message'] ?? null
        ];

        unset($_SESSION['error_message'], $_SESSION['success_message']);

        $this->renderView('songs/index', $data);
    }

    /**
     * Добавляет новую песню
     */
    public function addSong()
    {
        if (!$this->isPostRequest() || !isset($_POST["submit_add_song"])) {
            $this->redirect(URL . 'songs/index');
        }

        $this->requireValidCsrfToken();

        $artist = trim($_POST["artist"] ?? '');
        $track = trim($_POST["track"] ?? '');
        
        if (empty($artist) || empty($track)) {
            $this->setErrorMessage('Исполнитель и название песни обязательны');
            $this->redirect(URL . 'songs/index');
        }

        $fileData = $this->handleFileUpload();
        if (!$fileData) {
            $this->setErrorMessage('Ошибка загрузки файла или файл не выбран');
            $this->redirect(URL . 'songs/index');
        }

        $songModel = new Song();
        if ($songModel->addSong($artist, $track, $fileData)) {
            $this->setSuccessMessage('Песня успешно добавлена');
        } else {
            $this->setErrorMessage('Ошибка при добавлении песни');
        }

        $this->redirect(URL . 'songs/index');
    }

    /**
     * Удаляет песню после подтверждения
     */
    public function deleteSong($songId = null)
    {
        if ($this->isPostRequest() && isset($_POST['confirm_delete'])) {
            $this->handleDeleteConfirmation();
        } else {
            $this->showDeleteConfirmation($songId);
        }
    }

    /**
     * Отображает форму редактирования песни
     */
    public function editSong($songId = null)
    {
        if (!is_numeric($songId)) {
            $this->redirect(URL . 'songs/index');
        }

        $song = (new Song())->getSong($songId);
        if (!$song) {
            $this->setErrorMessage('Песня не найдена');
            $this->redirect(URL . 'songs/index');
        }

        $this->renderView('songs/edit', [
            'song' => $song,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Обновляет информацию о песне
     */
    public function updateSong()
    {
        if (!$this->isPostRequest() || !isset($_POST["submit_update_song"])) {
            $this->redirect(URL . 'songs/index');
        }

        $this->requireValidCsrfToken();

        $artist = trim($_POST["artist"] ?? '');
        $track = trim($_POST["track"] ?? '');
        $songId = (int) ($_POST['song_id'] ?? 0);
        
        if (empty($artist) || empty($track) || $songId <= 0) {
            $this->setErrorMessage('Все поля обязательны для заполнения');
            $this->redirect(URL . 'songs/index');
        }

        $fileData = $this->handleFileUpload();
        $songModel = new Song();
        
        if ($songModel->updateSong($artist, $track, $fileData, $songId)) {
            $this->setSuccessMessage('Песня успешно обновлена');
        } else {
            $this->setErrorMessage('Ошибка при обновлении песни');
        }

        $this->redirect(URL . 'songs/index');
    }

    /**
     * Возвращает статистику в формате JSON
     */
    public function ajaxGetStats()
    {
        $amount = (new Song())->getAmountOfSongs();
        
        header('Content-Type: application/json');
        echo json_encode(['amount' => $amount]);
        exit;
    }

    /**
     * Обрабатывает загрузку аудиофайла
     */
    private function handleFileUpload(): ?array
    {
        if (empty($_FILES['song_file']['name'])) {
            error_log('No file was uploaded');
            return null;
        }

        $uploadDir = $this->ensureUploadDirectoryExists();
        if (!$uploadDir) {
            return null;
        }

        $file = $_FILES['song_file'];
        if (!$this->validateFile($file)) {
            return null;
        }

        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            error_log("Failed to move uploaded file to: $filePath");
            error_log('Upload error code: ' . $file['error']);
            return null;
        }

        return [
            'name' => $file['name'],
            'path' => $filePath
        ];
    }

    /**
     * Проверяет и создает директорию для загрузок если необходимо
     */
    private function ensureUploadDirectoryExists(): ?string
    {
        $uploadDir = ROOT . 'public/uploads/';
        
        if (!file_exists($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            error_log('Failed to create upload directory: ' . $uploadDir);
            return null;
        }

        return $uploadDir;
    }

    /**
     * Проверяет валидность файла
     */
    private function validateFile(array $file): bool
    {
        // Проверка MIME типа
        if (!in_array($file['type'], self::ALLOWED_MIME_TYPES)) {
            error_log('Invalid file type: ' . $file['type']);
            return false;
        }

        // Проверка расширения
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            error_log('Invalid file extension: ' . $extension);
            return false;
        }

        // Проверка размера
        if ($file['size'] > self::MAX_FILE_SIZE) {
            error_log('File too large: ' . $file['size']);
            return false;
        }

        return true;
    }

    /**
     * Показывает страницу подтверждения удаления
     */
    private function showDeleteConfirmation($songId): void
    {
        if (!is_numeric($songId)) {
            $this->redirect(URL . 'songs/index');
        }

        $song = (new Song())->getSong($songId);
        if (!$song) {
            $this->setErrorMessage('Песня не найдена');
            $this->redirect(URL . 'songs/index');
        }

        $this->renderView('songs/delete_confirm', [
            'song' => $song,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Обрабатывает подтверждение удаления
     */
    private function handleDeleteConfirmation(): void
    {
        $songId = (int) ($_POST['song_id'] ?? 0);
        
        if ($songId > 0) {
            $songModel = new Song();
            if ($songModel->deleteSong($songId)) {
                $this->setSuccessMessage('Песня успешно удалена');
            } else {
                $this->setErrorMessage('Ошибка при удалении песни');
            }
        }

        $this->redirect(URL . 'songs/index');
    }

    /**
     * Устанавливает сообщение об ошибке
     */
    private function setErrorMessage(string $message): void
    {
        $_SESSION['error_message'] = $message;
    }

    /**
     * Устанавливает сообщение об успехе
     */
    private function setSuccessMessage(string $message): void
    {
        $_SESSION['success_message'] = $message;
    }

    /**
     * Проверяет POST запрос
     */
    private function isPostRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Рендерит представление с данными
     */
    private function renderView(string $viewPath, array $data = []): void
    {
        extract($data);
        
        require APP . 'view/_templates/header.php';
        require APP . "view/$viewPath.php";
        require APP . 'view/_templates/footer.php';
    }
}