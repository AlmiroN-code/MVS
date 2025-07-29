<?php

namespace Mini\Controller;

use Mini\Model\Song;

/**
 * Базовый класс для всех контроллеров с CSRF-защитой
 */
abstract class BaseController
{
    /**
     * Генерация CSRF токена
     */
    protected function generateCsrfToken()
    {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Проверка CSRF токена
     */
    protected function validateCsrfToken($token)
    {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && 
               hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    /**
     * Проверка CSRF токена для критических операций
     */
    protected function requireValidCsrfToken()
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $_SESSION['error_message'] = 'Недействительный CSRF токен. Попробуйте еще раз.';
            header('location: ' . URL . 'error');
            exit;
        }
    }
}

class SongsController extends BaseController
{
    /**
     * Обработка загрузки файлов
     */
    private function handleFileUpload()
    {
        if (empty($_FILES['song_file']['name'])) {
            error_log('No file was uploaded');
            return null;
        }

        $uploadDir = ROOT . 'public/uploads/';
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                error_log('Failed to create upload directory: ' . $uploadDir);
                return null;
            }
        }

        $fileName = uniqid() . '_' . basename($_FILES['song_file']['name']);
        $filePath = $uploadDir . $fileName;
        $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav'];

        // Проверка MIME типа
        if (!in_array($_FILES['song_file']['type'], $allowedTypes)) {
            error_log('Invalid file type: ' . $_FILES['song_file']['type']);
            return null;
        }

        // Дополнительная проверка расширения файла
        $fileExtension = strtolower(pathinfo($_FILES['song_file']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['mp3', 'wav'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            error_log('Invalid file extension: ' . $fileExtension);
            return null;
        }

        // Проверка размера файла (максимум 50MB)
        if ($_FILES['song_file']['size'] > 50 * 1024 * 1024) {
            error_log('File too large: ' . $_FILES['song_file']['size']);
            return null;
        }

        if (!move_uploaded_file($_FILES['song_file']['tmp_name'], $filePath)) {
            error_log('Failed to move uploaded file to: ' . $filePath);
            error_log('Upload error code: ' . $_FILES['song_file']['error']);
            return null;
        }

        return [
            'name' => $_FILES['song_file']['name'],
            'path' => $filePath
        ];
    }

    /**
     * Главная страница со списком песен
     */
    public function index()
    {
        $csrfToken = $this->generateCsrfToken();
        
        $Song = new Song();
        $songs = $Song->getAllSongs();
        $amount_of_songs = $Song->getAmountOfSongs();

        // Проверяем наличие сообщений об ошибках
        $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
        $success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
        
        // Очищаем сообщения после показа
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        require APP . 'view/_templates/header.php';
        require APP . 'view/songs/index.php';
        require APP . 'view/_templates/footer.php';
    }

    /**
     * Добавление новой песни
     */
    public function addSong()
    {
        if (isset($_POST["submit_add_song"])) {
            // Проверяем CSRF токен
            $this->requireValidCsrfToken();

            // Валидация входных данных
            $artist = trim($_POST["artist"] ?? '');
            $track = trim($_POST["track"] ?? '');
            
            if (empty($artist) || empty($track)) {
                $_SESSION['error_message'] = 'Исполнитель и название песни обязательны для заполнения';
                header('location: ' . URL . 'songs/index');
                return;
            }

            $fileData = $this->handleFileUpload();
            if (!$fileData) {
                $_SESSION['error_message'] = 'Ошибка загрузки файла или файл не выбран';
                header('location: ' . URL . 'songs/index');
                return;
            }

            $Song = new Song();
            $result = $Song->addSong($artist, $track, $fileData);
            
            if ($result) {
                $_SESSION['success_message'] = 'Песня успешно добавлена';
            } else {
                $_SESSION['error_message'] = 'Ошибка при добавлении песни';
            }
        }

        header('location: ' . URL . 'songs/index');
        exit;
    }

    /**
     * Удаление песни (теперь через POST с CSRF)
     */
    public function deleteSong($song_id = null)
    {
        // Если это POST запрос с формы подтверждения
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
            $this->requireValidCsrfToken();
            $song_id = intval($_POST['song_id'] ?? 0);
            
            if ($song_id > 0) {
                $Song = new Song();
                $result = $Song->deleteSong($song_id);
                
                if ($result) {
                    $_SESSION['success_message'] = 'Песня успешно удалена';
                } else {
                    $_SESSION['error_message'] = 'Ошибка при удалении песни';
                }
            }
            
            header('location: ' . URL . 'songs/index');
            exit;
        }
        
        // Если это GET запрос - показываем форму подтверждения
        if (isset($song_id) && is_numeric($song_id)) {
            $csrfToken = $this->generateCsrfToken();
            $Song = new Song();
            $song = $Song->getSong($song_id);
            
            if (!$song) {
                $_SESSION['error_message'] = 'Песня не найдена';
                header('location: ' . URL . 'songs/index');
                exit;
            }
            
            require APP . 'view/_templates/header.php';
            require APP . 'view/songs/delete_confirm.php';
            require APP . 'view/_templates/footer.php';
        } else {
            header('location: ' . URL . 'songs/index');
            exit;
        }
    }

    /**
     * Редактирование песни
     */
    public function editSong($song_id = null)
    {
        if (isset($song_id) && is_numeric($song_id)) {
            $csrfToken = $this->generateCsrfToken();
            
            $Song = new Song();
            $song = $Song->getSong($song_id);
            
            if (!$song) {
                $_SESSION['error_message'] = 'Песня не найдена';
                header('location: ' . URL . 'songs/index');
                exit;
            }
            
            require APP . 'view/_templates/header.php';
            require APP . 'view/songs/edit.php';
            require APP . 'view/_templates/footer.php';
        } else {
            header('location: ' . URL . 'songs/index');
            exit;
        }
    }

    /**
     * Обновление песни
     */
    public function updateSong()
    {
        if (isset($_POST["submit_update_song"])) {
            // Проверяем CSRF токен
            $this->requireValidCsrfToken();
            
            // Валидация входных данных
            $artist = trim($_POST["artist"] ?? '');
            $track = trim($_POST["track"] ?? '');
            $song_id = intval($_POST['song_id'] ?? 0);
            
            if (empty($artist) || empty($track) || $song_id <= 0) {
                $_SESSION['error_message'] = 'Все поля обязательны для заполнения';
                header('location: ' . URL . 'songs/index');
                return;
            }

            $fileData = $this->handleFileUpload();
            $Song = new Song();
            $result = $Song->updateSong($artist, $track, $fileData, $song_id);
            
            if ($result) {
                $_SESSION['success_message'] = 'Песня успешно обновлена';
            } else {
                $_SESSION['error_message'] = 'Ошибка при обновлении песни';
            }
        }

        header('location: ' . URL . 'songs/index');
        exit;
    }

    /**
     * AJAX получение статистики
     */
    public function ajaxGetStats()
    {
        // Для AJAX запросов можно использовать более простую проверку
        $Song = new Song();
        $amount_of_songs = $Song->getAmountOfSongs();
        
        header('Content-Type: application/json');
        echo json_encode(['amount' => $amount_of_songs]);
        exit;
    }
}
