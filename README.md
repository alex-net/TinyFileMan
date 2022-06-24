Для работы редактора с файловым менеджером Responsive File Manager  необходимо [скачать](https://www.responsivefilemanager.com/index.php) последний релиз этого манагера и разместить содержимое архива в любой каталог на сервере. Далее нужно сделать некоторые преобразования (подгонка кода)

## Выявленные проблемы и их устранине

Работа будет проводиться с файлами внутри каталага filemanager из архива Responsive File Manager.

### Удаляем config.php
В этом каталоге следует пройтись по всем файлам \*.php лежащим непосредственно в каталоге *filemanager* и удалить подключение файла *config.php* (этот файл подключется внутри действия). .. Удаляем строку содержащую примерно следующее:
```php
$config = include 'config/config.php';
```
### Исправление загрузки файлов
Для корректной загрузки файлов через менеджер нужно сделать правки в файле *upload.php*. Для этого находим строку, в которой происходит присвоене массива с настройками переменной *$uploadConfig*, и убраем для ключа *upload_dir* часть значения `dirname($_SERVER['SCRIPT_FILENAME']) . '/' . ` (путь у нас и так абсолютный) 

### Просмотр миниатюр в менеджере
Для корректного просмотра картинок-миниатюр в менеджере (thumbs) в файле *dialog.php* надо найти блок кода, в котором происходит присвоение значения  переменной *$src_thumb* и после этого места (как можно ближе к вёрстке внизу) зачистить переменные *$src_thumb*, *$src* и *$mini_src* от пути *document_root*, который сидит в *$_SERVER['DOCUMENT_ROOT']*, чтобы абсолютный путь к файлу на сервере стал относительным и превратился в ссылку на этот файл из web. Нужно обращать внимание на то, что  *$_SERVER['DOCUMENT_ROOT']* может содержать символичесие ссылки, по этому сам *$_SERVER['DOCUMENT_ROOT']* при необходимости можно будет обернуть в вызов функции `realpath()`. Также допускается указание каталога *thumbsPath* в виде абсолютного пути к любому каталогу доступному для записи. Можно применять алиасы Yii2. 
Если каталог назначения для картинок-миниатюр не доступен из web, нужно дополнительно провести замену в выше приведенных путях с использованием Assetа, который сидит в переменной *$thumbsAsset*, а именно заменить *sourcePath* на *baseUrl*. Также перед применением Assetа обратите внимание на значение *$linkAssets* у AssetManager. Это значение должно быть равным *true* в противном случае будет произведено копирование каталога и ничего хорошего из этого не выйдет. 

### нехватка памяти во время генерации миниатюр.
Также можно столкнуться с проблемой нехватки памяти при генераиции миниатюры после загрузки картинки через менеджер. Результатом загрузки будет ошибка нехватки памяти. Связано подобное поведение со способом задания паременной *memory_limit* в *php.ini*. "Из коробки" можно найти варианты указания этого значения в мегабайтах (в конце цифры буква M) и гигабайтах (в конце цифры буква G). Но часто встречается такой вариант, когда количество памяти задано в байтах (набор цифр), тогда нужно докрутить функцию *image_check_memory_usage* из файла *filemanager/include/utils.php* добавив вычисление переменной *$memory_limit* для варианта байтного обозначения размера памяти (одни цифры).  
В той же функции файла *utils.php* нужно обернуть в `try catch` строки начиная с *getimagesize*  и до окончания условия. В случае всплывания исключения - вернуть *false*. Дело в том, что для некорректных картинок и картинок с нулевым размером функция `getimagesize()` выбрасывает исключение, которое некому перехватить. Обёртыванием блока в `try catch` мы ловим это исключение и не даём сломаться скрипту.

### Загрузка небольших файлов до 100 кБ.

Таже есть проблема с загрузкой небольших файлов примерно до 100кб.. причиной этому является отсутствие заголовка *Content-Range*. Устраненять будем в методе *post()* файла *filemanager/UploadHandler.php* следующим образом: присвоим переменной *$content_range* (в случае если её значение окажется null) массив следующего содержания `[0,$size,$size]`, где $size является размером закачиваемого файла.

### Переводы интерфейса 
Для поддержки переводов интерфейса нужно в начало файла include/utils.php добавить глобальную переменную *$lang_vars*
```php
    global $lang_vars; 
```

### Загрузка файлов по ссылке.
Ещё одна проблема была найдена при загрузке файлов из внешнего источника (по url). В этом случае php не хотел признавать, что файл загруженный извне (другой сервер) является файлов загруженным по post запросу и не сохранял файл на диске. Внешне на странице загрузки ничего не происходит кроме очистки поля адреса от указанной ссылки на картинку. Для починки этого бага нужно допилить функцию `handle_file_upload()` из файла */filemanager/UploadHandler.php* дополнительным условием на существование "временного" файла загруженного с другого сервера. Если файл существует, то при помощи `file_put_contents()` копируем файл в нужное место. В нашем случае вызов будет похож на вызов `file_put_contents()` в последнем else, но источником файла будет загруженный файл, путь которого может сидеть в *$uploaded_file* (нужно проверить на file_exists()). Для этого действа может подойти функция `rename()`  
Также в строке *89* файла *upload.php* (присвоение массива данных загруженного файла в *$_FILES['files']* нужно дополнить значение поля type - вместо null установить mime-тип из загруженного по curl файла через `mime_content_type($temp)`,  

### Правки связанные с работой по FTP
Работа с менеджером через ftp требует следующих правок по коду:
- из функции `ftp_con()`, что в файле *include/utils.php* убрать все включения файлов связанных с ftp обёрткой. Для её реализации к расширению подключён пакет *nicolab/php-ftp-client*
- перед строчкой `if (isset($_GET['action']))`  в файле *execute.php* - добавить следующие строки:
```php
    # таким образом мы расширим путь до корня ftp ... 
    if ($ftp && !empty($config['ftp_base_folder']) && $path) {
        $path = DIRECTORY_SEPARATOR . $config['ftp_base_folder'] . $path;
    }
```

- в функцию `url_exists()` в *include/unils.php* добавить освобождение ресурса через `curl_close()` 
- для генерации thumbs-картинок, если их нет, нужно дописать следующий код в соответствующие места:
   * функция `create_img()` из *include/utils.php* расширяется на один параметр: в конце списка добавился `$ftp = null` 
   *  вместо проверок `isset($config['ftp_host']) && $config['ftp_host']` подставляется  *$ftp* (если переменная не пустая, то идём дальше) ;  
   * В файле *dialog.php* на уровне условия  `if(in_array($file_array['extension'], $config['ext_img'])){`  для *$ftp* нужно всунуть генерацию миниатюр, аналогично как это делается для локального каталога, но пути должны быть другими. В итоге получится что то вроде этого: 
```php
        if (!in_array($file, $filesThumbs)) {
        	$creation_thumb_path = '/' . $config['ftp_base_folder'] . $config['ftp_thumbs_dir'] . $subdir . $file;
            if (!create_img($src, $creation_thumb_path, 122, 91, 'crop', $config,$ftp)) { 
                $src_thumb = $mini_src = "";
            }
        }
```
- в файле *execute.php*, в группе `case 'rename_folder':` (переименование каталога) в условие проверки типа пути (если каталог) нужно добавить чтобы это условие срабатывало только при отстуствии ftp подключения = можно ругаться только с локальными файлами .. 
- для загрузки картинок из внешнего источника, нужно модернизировать правки, которые были внесены ранее в файле *UploadHandler.php* для локальных файлов. Теперь вместо `rename($uploaded_file, $file_path);` у нас будет стоять условный оператор проверящий наличие ftp соединения: 
```php
    if (empty($this->options['ftp'])) {
        rename($uploaded_file, $file_path);
    } else {
        $fn = $this->options['storeFolder'] . fix_filename(basename($file_path), $this->options['config']);
        $this->options['ftp']->put($fn, $uploaded_file, FTP_BINARY);
    }
```
- функцию `create_folder()` из *include/utils.php* нужно дописать рекурсивное создание каталогов для ftp  = второй параметр в вызове `$ftp->mkdir = true;` 

### Более быстрый подход к правкам.
Ещё одним вариантом (как сделать все правки быстро) .. можно скачать релиз 9.14 Responsive File Manager  и в распакованном архиве (в каталоге responsive_filemanager) инициировать новый git репозитарий, сделать базовый комит, и применить к нему патч с названием *for-yii2.patch*. После этих действий должно всё заработать .. 

### php8
под php8 ломается только файл библиотеки, которая нужна для преобразования картинок. Обновлённая версия этой либы есть по [ссылке](https://github.com/thanhle7/image_magician-fix-for-php8) 

## Подключение

Для использования текстового редактора без файлового менеджера.. достаточно воспользоваться виджетом *TinyMCEWidget* (\AlexNet\TinyFileMan\widgets\TinyMCEWidget) с присвоенным полем *$whithRfm* в false ("из коробки" файловый менеджер включён). Виджет может работать как сам по себе (генерируется элемент управление textarea), так и в составе формы (также генерится тег textarea).

Для использования виджета совместно с файловым менеджером нужно:
- подключить модуль *FileManMod* (TinyFileMan\FileManMod) к проекту и добавить в предзагрузку (bootstrap) для того чтобы были сформированы пути доступа к файловому менеджеру. 
- прописать настроеки файлового менеджера (пути доступа), которые указываются в настройках модуля (поле baseRFMUrls), а так же место расположения на сервере файлов файлового менеджера (поле RFMlink - поддерживаются алиасы). 

Также через поле *$editorConfig* можно задать общие настройки редактора. Доступы к редактору предетсалены ассоциированным масивом, ключами которого являются пути аналогиныче задаваемым для настройеки urlManager, а значения представлены массивом состоящим из ключей perms, uploadPath и thumbsPath. При помощи perms можно ограничить доступ к файловому менеджеру, а uploadPath и thumbsPath через абсолютные пути или алиасы задают расположение загруженных файлов и миниатюр картинок соответственно. Пути должны быть доступны из web по этому должны начинаться с алиаса @webroot

После задания указанных выше настроек виджет можно подключить используя параметр *$for*, в который передаётся массив содержазий первым элементом один из ключей настроенных путей baseRFMUrls (pattern пути) и остальные параметры (если нужны) для формирования конкретной ссылки через `\yii\helpers\Url::to()` 
