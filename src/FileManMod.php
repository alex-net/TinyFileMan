<?php 

namespace AlexNet\TinyFileMan;

use Yii;
use AlexNet\TinyFileMan\components\FileManUrlRule;

class FileManMod extends \yii\base\Module implements \yii\base\BootstrapInterface
{
	/**
	 * настройки редактора TinyMCE5 .. 
	 * @var array
	 */
	public $editorConfig = [];

	/**
	 * Ссылка на каталог filenamager в распакованном архиве . Responsive File Manager 
	 * @example '@app/rfm2/filemanager'  ( каталог распаковки Responsive File Manager = @app/rfm2 )
	 */
	public $RFMlink = '';

	/**
	 * базовые пути для манагера .. ассоциированный массив, ключами которого являются пути по которым будет открываться файловый менеджер .. формат путей аналогичен  свойству pattern из класса \yii\web\UrlRule .. 
	 * значением каждого элемента является массив содержащий права жоступа (ключ perms)  каталог загрузки картинок (ключ uploadPath) и  каталог генерации миниатюр .. thumbs (ключ thumbsPath)
	 */
	public $baseRFMUrls = [
		/*'test-1/<editor:.*>/aa'=>[
			'perms'=>['@'],
			'uploadPath'=>'@webroot/imgs/',
			'thumbsPath'=>'@webroot/thumbs/',
		],
		'test-2/<rrr:.*>/aa'=>[
			'perms'=>['dadas'],
			'uploadPath'=>'@webroot/imgs/test/',
			'thumbsPath'=>'@webroot/thumbs/',
		],*/
	];

	/**
	 * Класс модального окна . ... можно пользовать разный bootstrap  
	 * @var string
	 */
	public $bootstrapModelClass = 'yii\bootstrap\Modal';

	/**
	 * настройки файлового менеджера  .. (позволяют перебить данные из config файла файлового менеджера )
	 * @var array
	 */
	public $fileManConfig = [];


	public function bootstrap($app)
	{
		$app->urlManager->addRules([
			// настройки редактора .. грузим по get запросу ... 
			'tiny-editor-conf/<elid>' => '/' . $this->id . '/file-man/config',
		]);
		foreach (array_keys($this->baseRFMUrls) as $pattern) {
			$app->urlManager->addRules([
				$pattern . '/<elid>/<action>.php' => '/ '. $this->id . '/file-man/<action>',
				$pattern . '/<elid>/<cssscripts:.*>' => '/' . $this->id . '/file-man/css-scripts',
			]);
		}
	}

	public function init()
	{
		parent::init();
		if (empty($this->editorConfig)) {
			$lang = explode('-', Yii::$app->language);

			$this->editorConfig = [
				//'selector'=>'textarea',
				'language' => reset($lang),
				'plugins' => 'image imagetools media code',
				// https://www.tiny.cloud/docs/advanced/available-toolbar-buttons/
				'toolbar' => 'undo redo | styleselect | bold italic | link image | code',
				'menubar' => 'file edit insert view format table tools help',
			];
		}
	}
}