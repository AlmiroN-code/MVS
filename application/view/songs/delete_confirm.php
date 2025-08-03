
<div class="container">
    <h2>Подтверждение удаления</h2>
    <div class="box">
        <p><strong>Вы уверены, что хотите удалить эту песню?</strong></p>
        <p>
            <strong>Исполнитель:</strong> <?php echo htmlspecialchars($song->artist, ENT_QUOTES, 'UTF-8'); ?><br>
            <strong>Название:</strong> <?php echo htmlspecialchars($song->track, ENT_QUOTES, 'UTF-8'); ?>
        </p>
        
        <?php if (isset($song->file_name)): ?>
            <p>
                <strong>Файл:</strong> <?php echo htmlspecialchars($song->file_name, ENT_QUOTES, 'UTF-8'); ?><br>
                <audio controls>
                    <source src="<?php echo URL . 'uploads/' . basename($song->file_path); ?>" type="audio/mpeg">
                    Ваш браузер не поддерживает аудио элемент.
                </audio>
            </p>
        <?php endif; ?>

        <form action="<?php echo URL; ?>songs/deletesong" method="POST" style="display: inline;">
            <input type="hidden" name="song_id" value="<?php echo htmlspecialchars($song->id, ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="submit" name="confirm_delete" value="Да, удалить" style="background-color: #d32f2f; color: white;" onclick="return confirm('Точно удалить эту песню?');" />
        </form>
        
        <a href="<?php echo URL; ?>songs/index" style="margin-left: 10px;">
            <button type="button">Отмена</button>
        </a>
    </div>
</div>
