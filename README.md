Для работы редактора с файловым менеджером Responsive File Manager .. необходимо скачать последний релиз этого манагера  и положить в любой каталог на сервере .. 

после этого нужно сделать некоторые преобразования (подгонка кода..) 

В корне распакованного архива ..  нам будет нужен каталог filemanager. в этом каталоге  следует пройтись по всем файлам *.php лежащим непосредственно в этом каталоге .  и удалить  подключение config.php  .. этот файл подключется внутри действия .. Удаляем строку содержащую примерно следующее:
$config = include 'config/config.php';

Для корректной загрузки файлов через менеджер  .. нужно сделать правки в файле upload.php .. Нужно найти строчку с присвоением переменнйой $uploadConfig массива с настройками .. и .. убрать  для ключа upload_dir  часть значения  "dirname($_SERVER['SCRIPT_FILENAME'])  . '/' . " .. (путь у нас и так абсолютный .. )

Для корректного просмотра картинок thumbs   в менеджере .. надо найти  в файле dialog.php   блок кода, в котором происходит присвоение значения  переменной $src_thumb и после этого места (как можно ближе к вёрстке внизу) .. зачистить  переменные $src_thumb $src и $mini_src  от пути  document_root ($_SERVER['DOCUMENT_ROOT']) чтобы обсолютный путь к файлу на сервере стал ссылкой на этот файл из web ... нужно обращать внимание на то что в $_SERVER['DOCUMENT_ROOT'] может быть пусть со ссылками .. по этому сам $_SERVER['DOCUMENT_ROOT'] при необходимости можно будет обернуть в realpath(). Также допускается указание каталога thumbsPath в виде абсолютного пути к любому каталогу доступному для  записи. можно применять алиасы и запхнуть его, наприер, в @runtime каталог. Чтобы в итоге картинки-миниатюры открывались корректно, нужно дополнительно провести замену в выше приведенных путях  с использованием Assetа который сидит в переменной $thumbsAsset. Нужно заменить sourcePath на baseUrl. Перед применением нужно обратить внимание на значение $linkAssets у AssetManager .. это значение должно быть равным true.. в противном случае будет произведено копирование каталога.. и ничего хорошего из этого не выйдет..

Таже есть проблема с загрузкой небольших файлов примерно до 100кб.. причиной этому является отсутствие заголовка Content-Range. Устраненять будем в файле filemanager/UploadHandler.php в методе post() следует присвоить переменной $content_range (в случае если её значение окажется null )   массив следующего содержания [0,$size,$size] - $size в данном случае размер закачиваемого файла ... 

Для поддержки переводов интерфейса ..  нужно в начало файла .  include/utils.php  добавить глобальную переменную  $lang_vars
global $lang_vars;

Также можно столкнуться с проблемой нехватки памяти при генераиции миниатюры после загрузки картинки через менеджер .. - Результатом загрузки будет ошибка нехватки памяти .. Связано подобное поведение со способом задания паременной memory_limit в php.ini ... Из коробки можходят варианты указания значения в мегабайтах (в конце цифры буква M) и гигабайтах (в конце цифры буква G).. Но часто встречается такой вариант, когда количество памяти задано просто в байтах (просто набор цифр) тогда нужно  докрутить функцию image_check_memory_usage из файла filemanager/include/utils.php .. добавить вычисление переменной $memory_limit для варианта байтного обозначения  размера памяти ..(одни цифры). 
В той же функции файла utils.php нужно обернуть в try catch строки начиная с getimagesize  и до окончания условия .. в случае всплывания исключения - вернуть false. Дело в том, что для некорректных картинок и картинок с нулевым размером . функция getimagesize выбрасывает исключение, которое перехватить просто некому. Обёртыванием блока в try catch мы ловим это исключение и не даём сломаться скрипту.. 

Ещё одна проблема была найдена при загрузке файлов из внешнего источника (по url) .. В этом случае ..php  не хотел признавать,  что  файл загруженный извне (другой сервер) по ссылке является файлов загруженным по post запросу .. и не сохранял файл на диске ...  Внешне на странице загрузки ничего не происходит кроме очистки поля адреса от указанной ссылки на картинку ..Для починки этого бага .. нужно допилить функцию handle_file_upload() из файла .. /filemanager/UploadHandler.php .. дополнительным условием .. на существование "временного" файла загруженного с другого сервера .. Если файл существует то при помощи file_put_contents копируем файл в нужное место ... В нашем случае вызов будет похож на вызов file_put_contents в последнем else ... но источником файла будет загруженный файлпуть которого может сидеть в $uploaded_file (нужно проверить на file_exists()). Для этого действа может подойти функция rename ... 
Также в строке 89 файла upload.php (привоение массива данных загруженного файла в $_FILES['files'] нужно дополнить значение поля type = вместо null установить тип из загруженного по curl файла через mime_content_type($temp),  

Работа с менеджером через ftp требует следующих правок по коду:
-- из функции ftp_con что в файле include/utils.php убрать все включения файлов связанных с ftp обёрткой .. для её реализации к расширению подключён пакет nicolab/php-ftp-client
-- перед строчкой if (isset($_GET['action']))  в файле execute.php = добавить следующие строки 
if ($ftp && !empty($config['ftp_base_folder']) && $path)
    $path=DIRECTORY_SEPARATOR.$config['ftp_base_folder'].$path;
    таким образом мы расширим путь до корня ftp ... 
-- в функцию url_exists в include/unils.php ... добавить освобождение ресурса через curl_close() ... 
-- для генерации thumbs-картинок (если их нет) .. нужно дописать следующий код в соответствующие места ..
   функция create_img из include/utils.php расширяется на один параметр: в конце списка добавился $ftp=null ;  вместо проверок  isset($config['ftp_host']) && $config['ftp_host']  подставляется просто $ftp (если переменная не пустая .. то идём дальше) ;  В файле dialog.php на уровне условия  if(in_array($file_array['extension'], $config['ext_img'])){  для $ftp нужно всунуть генерацию миниатюр  .. аналогично как это делается для локального каталог  . но пути должны быть другими .. В итоге получится что то вроде этого: 
   if (!in_array($file, $filesThumbs)){
	    $creation_thumb_path = '/'.$config['ftp_base_folder'].$config['ftp_thumbs_dir'].$subdir. $file;
	    if (!create_img($src, $creation_thumb_path, 122, 91, 'crop', $config,$ftp)) 
	        $src_thumb = $mini_src = "";
	}
-- в файле execute.php ... в группе case 'rename_folder':  (переименование каталога) в условие проверки типа пути (если каталог) нужно добавит чтобы  это условие срабатывало только при отстуствии ftp подключения =  можно ругаться  только с локальными файлами .. 
-- для загрузки картинок из внешнего источника .. нужно модернизировать правки которые были внесены ранее в файле UploadHandler.php ..  для локальных файлов .. Теперь вместо rename($uploaded_file, $file_path);   у нас будет стоять условный оператор проверящий наличие ftp соединения  : 
		if (empty($this->options['ftp']))
                rename($uploaded_file, $file_path);
            else{
                $fn=$this->options['storeFolder'].fix_filename(basename($file_path),$this->options['config']);
                $this->options['ftp']->put($fn,$uploaded_file,FTP_BINARY);
            }
-- 
функцию  create_folder из include/utils.php нужно  дописать   рекурсивное создание каталогов для ftp  =  второй параметр в вызове $ftp->mkdir = true 
-----------
функиця 


Ещё одним вариантом (как сделать все правки быстро) .. можно скачать релиз 9.14 Responsive File Manager . и в распакованном архиве (в каталоге responsive_filemanager) инициировать новый git репозитарий ..., сделать базовый комит .. и применить к нему патч с названием for-yii2.patch . После этих действий должно всё заработать .. 

под php8 ломается только файл библиотеки, которая нужна для преобразования картинок  Обновлённая версия этой либы есть по ссылке https://github.com/thanhle7/image_magician-fix-for-php8 

подключение .. 

для использования текстового редактора без файлового менеджера.. достаточно воспользоваться  виджетом TinyMCEWidget (\AlexNet\TinyFileMan\widgets\TinyMCEWidget) с присвоенным полем $whithRfm в false (из коробки файловый менеджер включён ). Виджет может работать как сам по себе (генерируется элемент управление textarea) так и в составе формы  (также генерится тег textarea).

Для использования  виджета совместно с файловым менеджером .. нужно подключить модель FileManMod (TinyFileMan\FileManMod) к проекту и добавить в предзагрузку (bootstrap) для того чтобы были сформированы пути доступа к файловому менеджеру. Вторым этапом является прописывание настроек файлового менеджера (пути доступа), которые указываются в настройках модуля (поле baseRFMUrls) а так же место расположения на сервере файлов файлового менеджера .. (поле RFMlink - поддерживаются алиасы). .. Также через поле $editorConfig можно задать общие настройки редактора .
доступы к редактору предетсалены ассоциированным масивом .. ключами которого являются пути аналогиныче задаваемым для настройеки urlManager, а значения представлены массивом состоящим из ключей perms, uploadPath и thumbsPath. При помощи perms - можно ограничить доступ к файловому менеджеру, а uploadPath и thumbsPath  через абсолютные пути или алиасы задают расположение загруженных файлов и миниатюр картинок .. соответственно .. Пути должны быть доступны из web по этому должны начинаться с алиаса @webroot

После задания указанных выше настроек виджет можно подключить используя параметр $for в который передаётся массив содержазий первым элементом  один из ключей настроенных путей baseRFMUrls (pattern пути)  и остальные параметры (если нужны ) для формирования конкретной ссылки через \yii\helpers\Url::to() 
