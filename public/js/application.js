
$(function() {
    var demoHeaderBox;
    
    // Демо блок для JavaScript
    if ($('#javascript-header-demo-box').length !== 0) {
        demoHeaderBox = $('#javascript-header-demo-box');
        demoHeaderBox
            .hide()
            .text('Hello from JavaScript! This line has been added by public/js/application.js')
            .css('color', 'green')
            .fadeIn('slow');
    }

    // AJAX кнопка для обновления статистики
    if ($('#javascript-ajax-button').length !== 0) {
        $('#javascript-ajax-button').on('click', function(){
            var button = $(this);
            var resultBox = $('#javascript-ajax-result-box');
            
            // Показываем загрузку
            button.prop('disabled', true).text('Загрузка...');
            
            $.ajax({
                url: url + "/songs/ajaxGetStats",
                method: 'GET',
                dataType: 'json',
                timeout: 5000
            })
            .done(function(result) {
                if (result && typeof result.amount !== 'undefined') {
                    resultBox.html(result.amount);
                    resultBox.fadeOut(100).fadeIn(100); // Мигание для показа обновления
                } else {
                    resultBox.html('Ошибка получения данных');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                resultBox.html('Ошибка соединения');
            })
            .always(function() {
                // Восстанавливаем кнопку
                button.prop('disabled', false).text('Обновить счетчик');
            });
        });
    }

    // Подтверждение удаления
    $('a[href*="/deletesong/"]').on('click', function(e) {
        e.preventDefault();
        var deleteUrl = $(this).attr('href');
        
        if (confirm('Вы уверены, что хотите удалить эту песню? Это действие нельзя отменить.')) {
            window.location.href = deleteUrl;
        }
    });

    // Валидация форм на стороне клиента
    $('form[enctype="multipart/form-data"]').on('submit', function() {
        var fileInput = $(this).find('input[type="file"]');
        var isRequired = fileInput.prop('required');
        
        if (isRequired && fileInput.get(0).files.length === 0) {
            alert('Пожалуйста, выберите файл для загрузки.');
            return false;
        }
        
        if (fileInput.get(0).files.length > 0) {
            var file = fileInput.get(0).files[0];
            var maxSize = 50 * 1024 * 1024; // 50MB
            
            if (file.size > maxSize) {
                alert('Размер файла слишком большой. Максимальный размер: 50MB');
                return false;
            }
            
            var allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav'];
            if (allowedTypes.indexOf(file.type) === -1) {
                alert('Неподдерживаемый тип файла. Разрешены только MP3 и WAV файлы.');
                return false;
            }
        }
        
        return true;
    });

    // Показ прогресса загрузки файлов
    $('input[type="file"]').on('change', function() {
        var file = this.files[0];
        var fileInfo = $(this).siblings('.file-info');
        
        if (fileInfo.length === 0) {
            fileInfo = $('<div class="file-info" style="margin-top: 5px; font-size: 12px; color: #666;"></div>');
            $(this).after(fileInfo);
        }
        
        if (file) {
            var sizeKB = Math.round(file.size / 1024);
            var sizeText = sizeKB < 1024 ? sizeKB + ' KB' : Math.round(sizeKB / 1024) + ' MB';
            fileInfo.text('Выбран файл: ' + file.name + ' (' + sizeText + ')');
        } else {
            fileInfo.text('');
        }
    });
});
