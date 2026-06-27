<?php
// Heading
$_['heading_title'] = 'Енергиен Етикет';

// Tabs
$_['tab_settings'] = 'Настройки';
$_['tab_classes'] = 'Енергийни Класове';
$_['tab_energy_efficiency'] = 'Енергийна Ефективност';
$_['tab_bulk'] = 'Масов Импорт / Експорт';

// General text
$_['text_extension'] = 'Разширения';
$_['text_home'] = 'Начало';
$_['text_edit'] = 'Редактиране на Настройките за Енергиен Етикет';
$_['text_enabled'] = 'Активиран';
$_['text_disabled'] = 'Деактивиран';
$_['text_yes'] = 'Да';
$_['text_no'] = 'Не';
$_['text_none'] = '-- Не се използва --';

// Success messages
$_['text_success'] = 'Успех: Настройките на модула за Енергиен Етикет са запазени!';
$_['text_class_added'] = 'Успех: Енергийният клас е добавен!';
$_['text_class_updated'] = 'Успех: Енергийният клас е обновен!';
$_['text_class_deleted'] = 'Успех: Енергийният клас е изтрит!';
$_['text_import_success'] = 'Импортът е завършен: %d продукт(а) обновен(и), %d пропуснат(и).';
$_['text_importing'] = 'Импортиране, моля изчакайте…';
$_['text_no_file_selected'] = 'Моля, изберете Excel или CSV файл преди да импортирате.';

// Buttons
$_['button_save'] = 'Запази';
$_['button_save_stay'] = 'Запази & Остани';
$_['button_cancel'] = 'Отказ';
$_['button_add_class'] = 'Добави Клас';
$_['button_save_class'] = 'Запази';
$_['button_edit'] = 'Редактирай';
$_['button_delete'] = 'Изтрий';
$_['button_browse'] = 'Преглед';
$_['button_clear'] = 'Изчисти';
$_['button_remove_file'] = 'Премахни';
$_['button_export'] = 'Изтегли Excel';
$_['button_import'] = 'Качи & Импортирай';

// Settings tab
$_['label_status'] = 'Статус';
$_['label_show_product'] = 'Показвай на Продуктова Страница';
$_['label_show_category'] = 'Показвай в Категории';
$_['label_custom_css'] = 'Потребителски CSS';
$_['help_custom_css'] = 'Вмъква се като &lt;style&gt; блок в &lt;head&gt; на всяка страница, където модулът се зарежда.';

// Energy Classes tab
$_['label_classes'] = 'Енергийни Класове';
$_['column_class_name'] = 'Име на Класа';
$_['column_class_icon'] = 'Икона';
$_['column_sort_order'] = 'Наредба';
$_['column_action'] = 'Действие';

// Product Energy Efficiency tab
$_['label_cooling'] = 'Охлаждане';
$_['label_heating'] = 'Отопление';
$_['label_general'] = 'Общо';
$_['label_energy_class'] = 'Енергиен Клас';
$_['label_eu_label'] = 'ЕС Енергиен Етикет';
$_['label_datasheet'] = 'Продуктов Лист';
$_['help_eu_label'] = 'Позволени формати: jpg, jpeg, png, webp, pdf. Макс. 10 МБ.';
$_['help_datasheet'] = 'Позволен формат: pdf. Макс. 10 МБ.';
$_['confirm_remove_file'] = 'Сигурни ли сте, че искате да премахнете този файл?';

// Bulk Import / Export tab
$_['label_export'] = 'Експорт на Етикети';
$_['label_import'] = 'Импорт на Етикети';
$_['label_import_excel'] = 'Excel / CSV файл (.xlsx или .csv)';
$_['label_import_zip'] = 'ZIP с файлове (по избор)';
$_['help_export'] = 'Изтегля Excel файл с всички продукти, текущите им енергийни класове и имената на съществуващите файлове. Попълнете колоните и качете обратно за масово обновяване.';
$_['help_import'] = 'Качете попълнения Excel (.xlsx) или CSV. Само непразните клетки ще бъдат обновени; празните остават непроменени.';
$_['help_import_zip'] = 'ZIP-ът трябва да съдържа папки: cooling/, heating/, general/ — поставете файловете в съответната папка. Напишете пълното име на файла с разширението в Excel клетката (например: label.pdf, image.jpg).';
// Errors
$_['error_warning'] = 'Внимание: Моля, проверете формуляра внимателно за грешки!';
$_['error_permission'] = 'Внимание: Нямате право да променяте модула за Енергиен Етикет!';
$_['error_icon'] = 'Невалидна икона.';
$_['error_class_name_required'] = 'Внимание: Името на класа е задължително!';
$_['error_class_name_duplicate'] = 'Внимание: Клас с това име вече съществува!';
$_['error_invalid_class'] = 'Внимание: Невалидно ID на класа!';
$_['error_class_in_use'] = 'Внимание: Този клас не може да бъде изтрит, защото се използва от %d продукт(а)!';
$_['error_import_no_file'] = 'Няма качен файл.';
$_['error_import_file_type'] = 'Невалиден тип файл. Моля, качете .xlsx или .csv файл.';
$_['error_import_zip_type'] = 'Невалиден ZIP файл. Моля, качете .zip файл.';
$_['error_import_empty'] = 'Качeният файл не съдържа редове с данни.';
$_['error_import_parse'] = 'Файлът не може да бъде прочетен:';