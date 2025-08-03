<?php
namespace Mini\Core;

use PDO;
use PDOException;

/**
 * Базовый класс для всех моделей, обеспечивает подключение к БД
 */
class Model
{
    protected $db = null;

    /**
     * Конструктор - автоматически подключается к БД
     */
    public function __construct()
    {
        try {
            $this->connectToDatabase();
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed');
        }
    }

    /**
     * Устанавливает соединение с базой данных
     */
    private function connectToDatabase()
    {
        $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
        ];
        
        $this->db = new PDO(
            DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS,
            $options
        );
    }
}