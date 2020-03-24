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
	public $editorConfig=[];

	/**
	 * Ссылка на каталог filenamager в распакованном архиве . Responsive File Manager 
	 * @example '@app/rfm2/filemanager'  ( каталог распаковки Responsive File Manager = @app/rfm2 )
	 */
	public $RFMlink='';

	/**
	 * базовый пути для манагера .. ассоциированный массив, ключами которого являются пути по которым будет открываться файловый менеджер .. формат путей аналогичен  свойству pattern из класса \yii\web\UrlRule .. 
	 * значением каждого элемента является массив содержащий права жоступа (ключ perms)  каталог загрузки картинок (ключ uploadPath) и  каталог генерации миниатюр .. thumbs (ключ thumbsPath)
	 */
	public $baseRFMUrls=[
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
	 * настройки файлового менеджера  .. (позволяют перебить данные из config файла файлового менеджера )
	 * @var array
	 */
	public $fileManConfig=[];


	public function bootstrap($app)
	{
		foreach(array_keys($this->baseRFMUrls) as $pattern)
			$app->urlManager->addRules([
				['class'=>FileManUrlRule::className(),'pattern'=>$pattern.'/el-config/<elid>','route'=>'/'.$this->id.'/file-man/config','patternKey'=>$pattern],
				['class'=>FileManUrlRule::className(),'pattern'=>$pattern.'/<action>.php','route'=>'/'.$this->id.'/file-man/<action>','patternKey'=>$pattern],
				['class'=>FileManUrlRule::className(),'pattern'=>$pattern.'/<cssscripts:.*>','route'=>'/'.$this->id.'/file-man/css-scripts','patternKey'=>$pattern],
				//$this->baseRFMUrl.'/<action>.php'=>'/'.$this->id.'/file-man/<action>',
				

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