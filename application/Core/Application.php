<?php
namespace Mini\Core;

/**
 * Основной класс приложения - роутер и инициализатор
 */
class Application
{
    private $urlController = null;
    private $urlAction = null;
    private $urlParams = [];

    /**
     * Инициализирует приложение: запускает сессию, обрабатывает URL и вызывает соответствующий контроллер
     */
    public function __construct()
    {
        $this->initSession();
        $this->splitUrl();
        $this->dispatchRequest();
    }

    /**
     * Инициализирует сессию и CSRF токен
     */
    private function initSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Разбирает URL на компоненты
     */
    private function splitUrl()
    {
        if (isset($_GET['url'])) {
            $url = trim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $urlParts = explode('/', $url);

            $this->urlController = $urlParts[0] ?? null;
            $this->urlAction = $urlParts[1] ?? null;
            
            unset($urlParts[0], $urlParts[1]);
            $this->urlParams = array_values($urlParts);
        }
    }

    /**
     * Перенаправляет запрос в соответствующий контроллер
     */
    private function dispatchRequest()
    {
        // Если контроллер не указан - используем HomeController
        if (!$this->urlController) {
            $this->callControllerMethod(new \Mini\Controller\HomeController(), 'index');
            return;
        }

        $controllerClass = "\\Mini\\Controller\\" . ucfirst($this->urlController) . 'Controller';
        
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            
            // Проверяем, есть ли действие и вызываем соответствующий метод
            $action = $this->urlAction ?? 'index';
            
            if (is_string($action) && method_exists($controller, $action)) {
                $this->callControllerMethod($controller, $action);
            } elseif (empty($this->urlAction)) {
                $this->callControllerMethod($controller, 'index');
            } else {
                $this->redirectToErrorPage();
            }
        } else {
            $this->redirectToErrorPage();
        }
    }

    /**
     * Вызывает метод контроллера с параметрами
     */
    private function callControllerMethod($controller, $method)
    {
        if (!empty($this->urlParams)) {
            call_user_func_array([$controller, $method], $this->urlParams);
        } else {
            $controller->{$method}();
        }
    }

    /**
     * Перенаправляет на страницу ошибки
     */
    private function redirectToErrorPage()
    {
        header('location: ' . URL . 'error');
        exit;
    }
}