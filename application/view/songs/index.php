
<div class="container">
    <h1>Управление песнями</h1>
    
    <?php if (isset($error_message)): ?>
        <div style="background-color: #ffebee; color: #c62828; padding: 10px; margin: 10px 0; border-radius: 3px;">
            <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success_message)): ?>
        <div style="background-color: #e8f5e8; color: #2e7d32; padding: 10px; margin: 10px 0; border-radius: 3px;">
            <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <!-- Форма добавления песни -->
    <div class="box">
        <h3>Добавить песню</h3>
        <form action="<?php echo URL; ?>songs/addsong" method="POST" enctype="multipart/form-data">
            <label>Исполнитель *</label>
            <input type="text" name="artist" value="" required maxlength="255" />
            
            <label>Название песни *</label>
            <input type="text" name="track" value="" required maxlength="255" />
            
            <label>MP3 файл *</label>
            <input type="file" name="song_file" accept="audio/mp3,audio/wav" required />
            <small style="color: #666;">Максимальный размер файла: 50MB. Поддерживаемые форматы: MP3, WAV</small>
            
            <!-- CSRF токен -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="submit" name="submit_add_song" value="Добавить песню" />
        </form>
    </div>

    <!-- Список песен -->
    <div class="box">
        <h3>Количество песен: <span id="javascript-ajax-result-box"><?php echo $amount_of_songs; ?></span></h3>
        <button id="javascript-ajax-button">Обновить счетчик</button>
        
        <h3>Список всех песен</h3>
        <?php if (!empty($songs)): ?>
            <table>
                <thead style="background-color: #ddd; font-weight: bold;">
                    <tr>
                        <td>ID</td>
                        <td>Исполнитель</td>
                        <td>Название</td>
                        <td>Аудио</td>
                        <td>Действия</td>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($songs as $song): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($song->id, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($song->artist, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($song->track, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php if (isset($song->file_name) && !empty($song->file_name)): ?>
                                <audio controls>
                                    <source src="<?php echo URL . 'uploads/' . basename($song->file_path); ?>" type="audio/mpeg">
                                    Ваш браузер не поддерживает аудио элемент.
                                </audio>
                            <?php else: ?>
                                <em>Нет файла</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo URL . 'songs/editsong/' . htmlspecialchars($song->id, ENT_QUOTES, 'UTF-8'); ?>" 
                               style="margin-right: 10px;">Редактировать</a>
                            <a href="<?php echo URL . 'songs/deletesong/' . htmlspecialchars($song->id, ENT_QUOTES, 'UTF-8'); ?>" 
                               style="color: #d32f2f;">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><em>Песни пока не добавлены.</em></p>
        <?php endif; ?>
    </div>
</div>
