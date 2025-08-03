
<div class="container">
    <h1>Музыкальная коллекция</h1>
    <h2>Последние добавленные песни</h2>
    
    <div class="songs-list">
        <?php if (!empty($songs)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Исполнитель</th>
                        <th>Название</th>
                        <th>Прослушать</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($songs as $song): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($song->artist, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($song->track, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php if (isset($song->file_name) && !empty($song->file_name)): ?>
                                    <audio controls style="width: 200px;">
                                        <source src="<?php echo URL . 'uploads/' . basename($song->file_path); ?>" type="audio/mpeg">
                                        Ваш браузер не поддерживает аудио элемент.
                                    </audio>
                                <?php else: ?>
                                    <em>Нет файла</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top: 15px;">
                <p><strong>Всего песен в коллекции:</strong> <?php echo $amount_of_songs; ?></p>
                <a href="<?php echo URL; ?>songs" style="color: #454545; text-decoration: none; font-weight: bold;">
                    → Посмотреть все песни
                </a>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background-color: #f5f5f5; border-radius: 3px;">
                <p><em>Песни пока не добавлены.</em></p>
                <a href="<?php echo URL; ?>songs" style="color: #454545; text-decoration: none; font-weight: bold;">
                    → Добавить первую песню
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
