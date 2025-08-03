
<div class="container">
    <h2>Редактирование песни</h2>
    <div class="box">
        <form action="<?php echo URL; ?>songs/updatesong" method="POST" enctype="multipart/form-data">
            <label>Исполнитель *</label>
            <input autofocus type="text" name="artist" 
                   value="<?php echo htmlspecialchars($song->artist, ENT_QUOTES, 'UTF-8'); ?>" 
                   required maxlength="255" />
                   
            <label>Название песни *</label>
            <input type="text" name="track" 
                   value="<?php echo htmlspecialchars($song->track, ENT_QUOTES, 'UTF-8'); ?>" 
                   required maxlength="255" />
                   
            <label>MP3 файл (оставьте пустым, чтобы сохранить текущий)</label>
            <input type="file" name="song_file" accept="audio/mp3,audio/wav" />
            <small style="color: #666;">Максимальный размер файла: 50MB. Поддерживаемые форматы: MP3, WAV</small>
            
            <?php if (isset($song->file_name) && !empty($song->file_name)): ?>
                <div style="margin: 10px 0; padding: 10px; background-color: #f5f5f5; border-radius: 3px;">
                    <p><strong>Текущий файл:</strong> <?php echo htmlspecialchars($song->file_name, ENT_QUOTES, 'UTF-8'); ?></p>
                    <audio controls>
                        <source src="<?php echo URL . 'uploads/' . basename($song->file_path); ?>" type="audio/mpeg">
                        Ваш браузер не поддерживает аудио элемент.
                    </audio>
                </div>
            <?php endif; ?>
            
            <!-- Скрытые поля -->
            <input type="hidden" name="song_id" value="<?php echo htmlspecialchars($song->id, ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
            
            <div style="margin-top: 15px;">
                <input type="submit" name="submit_update_song" value="Обновить песню" />
                <a href="<?php echo URL; ?>songs/index" style="margin-left: 10px;">
                    <button type="button">Отмена</button>
                </a>
            </div>
        </form>
    </div>
</div>
