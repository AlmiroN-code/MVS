<?php
namespace Mini\Model;

use Mini\Core\Model;

class Song extends Model
{
    /**
     * Получить все песни
     */
    public function getAllSongs()
    {
        try {
            $sql = "SELECT id, artist, track, file_name, file_path FROM song ORDER BY id DESC";
            $query = $this->db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (\PDOException $e) {
            error_log('Database error in getAllSongs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Добавить новую песню
     */
    public function addSong($artist, $track, $fileData)
    {
        try {
            $sql = "INSERT INTO song (artist, track, file_name, file_path) VALUES (:artist, :track, :file_name, :file_path)";
            $query = $this->db->prepare($sql);
            $parameters = array(
                ':artist' => $artist,
                ':track' => $track,
                ':file_name' => $fileData['name'],
                ':file_path' => $fileData['path']
            );

            return $query->execute($parameters);
        } catch (\PDOException $e) {
            error_log('Database error in addSong: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Удалить песню
     */
    public function deleteSong($song_id)
    {
        try {
            // Получаем информацию о файле перед удалением
            $song = $this->getSong($song_id);
            if ($song && !empty($song->file_path) && file_exists($song->file_path)) {
                unlink($song->file_path);
            }

            $sql = "DELETE FROM song WHERE id = :song_id";
            $query = $this->db->prepare($sql);
            $parameters = array(':song_id' => $song_id);
            return $query->execute($parameters);
        } catch (\PDOException $e) {
            error_log('Database error in deleteSong: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить песню по ID
     */
    public function getSong($song_id)
    {
        try {
            $sql = "SELECT id, artist, track, file_name, file_path FROM song WHERE id = :song_id LIMIT 1";
            $query = $this->db->prepare($sql);
            $parameters = array(':song_id' => $song_id);
            $query->execute($parameters);
            return $query->fetch();
        } catch (\PDOException $e) {
            error_log('Database error in getSong: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Обновить песню
     */
    public function updateSong($artist, $track, $fileData, $song_id)
    {
        try {
            // Если загружен новый файл, удаляем старый
            if (!empty($fileData['name'])) {
                $oldSong = $this->getSong($song_id);
                if ($oldSong && !empty($oldSong->file_path) && file_exists($oldSong->file_path)) {
                    unlink($oldSong->file_path);
                }

                $sql = "UPDATE song SET artist = :artist, track = :track, file_name = :file_name, file_path = :file_path WHERE id = :song_id";
                $parameters = array(
                    ':artist' => $artist,
                    ':track' => $track,
                    ':file_name' => $fileData['name'],
                    ':file_path' => $fileData['path'],
                    ':song_id' => $song_id
                );
            } else {
                // Обновляем только текстовые поля
                $sql = "UPDATE song SET artist = :artist, track = :track WHERE id = :song_id";
                $parameters = array(
                    ':artist' => $artist,
                    ':track' => $track,
                    ':song_id' => $song_id
                );
            }

            $query = $this->db->prepare($sql);
            return $query->execute($parameters);
        } catch (\PDOException $e) {
            error_log('Database error in updateSong: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить количество песен
     */
    public function getAmountOfSongs()
    {
        try {
            $sql = "SELECT COUNT(id) AS amount_of_songs FROM song";
            $query = $this->db->prepare($sql);
            $query->execute();
            $result = $query->fetch();
            return $result ? $result->amount_of_songs : 0;
        } catch (\PDOException $e) {
            error_log('Database error in getAmountOfSongs: ' . $e->getMessage());
            return 0;
        }
    }
}
