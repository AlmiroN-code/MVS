<?php
namespace Mini\Model;

use Mini\Core\Model;
use PDOException;

/**
 * Модель для работы с песнями
 */
class Song extends Model
{
    /**
     * Возвращает все песни из базы данных
     */
    public function getAllSongs(): array
    {
        try {
            $sql = "SELECT id, artist, track, file_name, file_path FROM song ORDER BY id DESC";
            $query = $this->db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (PDOException $e) {
            $this->logDatabaseError('getAllSongs', $e);
            return [];
        }
    }

    /**
     * Добавляет новую песню в базу данных
     */
    public function addSong(string $artist, string $track, array $fileData): bool
    {
        try {
            $sql = "INSERT INTO song (artist, track, file_name, file_path) 
                    VALUES (:artist, :track, :file_name, :file_path)";
                    
            $parameters = [
                ':artist' => $artist,
                ':track' => $track,
                ':file_name' => $fileData['name'],
                ':file_path' => $fileData['path']
            ];

            $query = $this->db->prepare($sql);
            return $query->execute($parameters);
        } catch (PDOException $e) {
            $this->logDatabaseError('addSong', $e);
            return false;
        }
    }

    /**
     * Удаляет песню по ID
     */
    public function deleteSong(int $songId): bool
    {
        try {
            $song = $this->getSong($songId);
            $this->deleteSongFile($song);
            
            $sql = "DELETE FROM song WHERE id = :song_id";
            $query = $this->db->prepare($sql);
            return $query->execute([':song_id' => $songId]);
        } catch (PDOException $e) {
            $this->logDatabaseError('deleteSong', $e);
            return false;
        }
    }

    /**
     * Возвращает песню по ID
     */
    public function getSong(int $songId)
    {
        try {
            $sql = "SELECT id, artist, track, file_name, file_path FROM song WHERE id = :song_id LIMIT 1";
            $query = $this->db->prepare($sql);
            $query->execute([':song_id' => $songId]);
            return $query->fetch();
        } catch (PDOException $e) {
            $this->logDatabaseError('getSong', $e);
            return false;
        }
    }

    /**
     * Обновляет информацию о песне
     */
    public function updateSong(string $artist, string $track, ?array $fileData, int $songId): bool
    {
        try {
            if (!empty($fileData['name'])) {
                $this->handleFileUpdate($songId, $fileData);
                return $this->updateSongWithFile($artist, $track, $fileData, $songId);
            }
            
            return $this->updateSongWithoutFile($artist, $track, $songId);
        } catch (PDOException $e) {
            $this->logDatabaseError('updateSong', $e);
            return false;
        }
    }

    /**
     * Возвращает общее количество песен
     */
    public function getAmountOfSongs(): int
    {
        try {
            $sql = "SELECT COUNT(id) AS amount_of_songs FROM song";
            $query = $this->db->prepare($sql);
            $query->execute();
            $result = $query->fetch();
            return $result ? (int)$result->amount_of_songs : 0;
        } catch (PDOException $e) {
            $this->logDatabaseError('getAmountOfSongs', $e);
            return 0;
        }
    }

    /**
     * Удаляет файл песни если он существует
     */
    private function deleteSongFile($song): void
    {
        if ($song && !empty($song->file_path) && file_exists($song->file_path)) {
            unlink($song->file_path);
        }
    }

    /**
     * Обновляет песню с новым файлом
     */
    private function updateSongWithFile(string $artist, string $track, array $fileData, int $songId): bool
    {
        $sql = "UPDATE song SET artist = :artist, track = :track, 
                file_name = :file_name, file_path = :file_path WHERE id = :song_id";
                
        $parameters = [
            ':artist' => $artist,
            ':track' => $track,
            ':file_name' => $fileData['name'],
            ':file_path' => $fileData['path'],
            ':song_id' => $songId
        ];

        $query = $this->db->prepare($sql);
        return $query->execute($parameters);
    }

    /**
     * Обновляет песню без изменения файла
     */
    private function updateSongWithoutFile(string $artist, string $track, int $songId): bool
    {
        $sql = "UPDATE song SET artist = :artist, track = :track WHERE id = :song_id";
        $parameters = [
            ':artist' => $artist,
            ':track' => $track,
            ':song_id' => $songId
        ];

        $query = $this->db->prepare($sql);
        return $query->execute($parameters);
    }

    /**
     * Обрабатывает обновление файла песни
     */
    private function handleFileUpdate(int $songId, array $fileData): void
    {
        $oldSong = $this->getSong($songId);
        $this->deleteSongFile($oldSong);
    }

    /**
     * Логирует ошибки базы данных
     */
    private function logDatabaseError(string $method, PDOException $e): void
    {
        error_log("Database error in $method: " . $e->getMessage());
    }
}