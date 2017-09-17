<?php
define("LANG_RENAME", "Переименовать");
define("LANG_RENAME_FILE", "Переименовать файл");
define("LANG_DELETE", "Удалить");
define("LANG_FOLDER_EMPTY", "Папка пуста");
define("LANG_ADMIN_RIGHTS", "Права администратора");

define("LANG_SORT_BY", "Сортировать по");
define("LANG_SB_DATE", "дате");
define("LANG_SB_NAME", "имени");
define("LANG_SB_DESC", "обратно");

define("LANG_PARENT_DIR", "Родительская папка...");
define("LANG_REFRESH_THUMBS", "Обновить эскизы");
define("LANG_PHOTO", "Картинка");

function totalElementsLabel($count)
{
    //Выводим количество выведенных элементов - папок, фоток или текстовиков.
    //Соблюдаем склонения и ед./мн. числа слова "элемент(ы)" в зависимости от значения числа.
    //Ох, как в английском всё просто: никаких склонений не нужно, а с числом ещё проще :)
    $counter_one = $count % 10;//Units
	$counter_ten = $count % 100 - $count % 10;//Tens
	$counter_hng = $counter_ten + $counter_one;//Summ of Tens and Units
    echo "Всего " . $count . " элемент" .
        (
            (
                (($counter_hng > 10) && ($counter_hng < 20 ) ) ||
                (($counter_one > 4) || ($counter_one < 1))
            ) ? "ов" : ($counter_one == 1 ? "" : "а")
        );
}

