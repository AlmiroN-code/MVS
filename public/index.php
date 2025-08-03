
<?php
// Запускаем сессию в самом начале
session_start();

// Определяем основные константы
define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP', ROOT . 'application' . DIRECTORY_SEPARATOR);

// Подключаем автозагрузку и конфигурацию
require ROOT . 'vendor/autoload.php';
require APP . 'config/config.php';

// Логирование загрузки файлов для отладки (только в dev среде)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES) && ENVIRONMENT === 'development') {
    error_log('Files uploaded: ' . print_r($_FILES, true));
}

// Обработка ошибок загрузки файлов
if (!empty($_FILES)) {
    foreach ($_FILES as $file) {
        if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
            error_log('File upload error: ' . $file['error']);
        }
    }
}

// Запускаем приложение
use Mini\Core\Application;
$app = new Application();
