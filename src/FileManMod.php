<?php 

namespace AlexNet\TinyFileMan;

use Yii;

class FileManMod extends \yii\base\Module implements \yii\base\BootstrapInterface
{
	/**
	 * настройки редактора .. 
	 * @var array
	 */
	public $editorConfig=[];
	/**
	 * право доступа .. 
	 * @var string
	 */
	public $perms=[];
	/**
	 * Ссылка на распакованный архив . Responsive File Manager 
	 */
	public $RFMlink='';

	/**
	 * базовый url для манагера ..
	 */
	public $baseRFMUrl='rfm';

	/**
	 * абсолютный путь к каталогк загрузок картинок на сервере ...  доступный из web
	 * @var string
	 */
	public $uploadPath='@webroot/imgs/';

	public $rfmTittle='Файловый манагер';
	/**
	 * абсолютный путь загрузки для  меньшенных изображений ( генерируются самостоятельно  .. каталог создаётся автоматичесик ..  )
	 * @var string
	 */
	public $thumbsPath='@webroot/thumbs/';

	/**
	 * настройки файлового менеджер .. 
	 * @var array
	 */
	public $fileManConfig=[];


	public function bootstrap($app)
	{
		$app->urlManager->addRules([
			$this->baseRFMUrl.'/el-config/<elid>'=>$this->id.'/file-man/config',
			$this->baseRFMUrl.'/<action>.php'=>$this->id.'/file-man/<action>',
			$this->baseRFMUrl.'/<cssscripts:.*>'=>$this->id.'/file-man/css-scripts',

			//'file-man/get-editor-config'=>$this->id.'/file-man/config'
		]);
	}

	public function init()
	{
		parent::init();
		if (empty($this->editorConfig))
			$this->editorConfig=[
				//'selector'=>'textarea',
				'language'=>Yii::$app->language,
				'plugins'=>'image imagetools media code',
				'toolbar'=>'undo redo | styleselect | bold italic | link image | code',
				'menubar'=>'file edit insert view format table tools help',
			];
	}
}