<?php 

namespace AlexNet\TinyFileMan\widgets;

use Yii;

/**
 * виджет для отображения редактора TinyMce и встроенного в него файлового  менеджера (при необходимости ) 
 */
class TinyMCEWidget extends RfmBaseWidget
{
	/**
	 * насьройки редактора .. 
	 * @var array
	 */
	public $editorConfig=[];

	/**
	 * высота элемента textarea .. в строках 
	 */
	public $textareaHeigt=20;

	/**
	 * признак использования файлового манагера ...
	 * @var boolean
	 */
	public $whithRfm=true;

	/**
	 * заголовок окна файлового менеджера ...
	 * @var string
	 */
	public $fileManWindowTitle='Файловый менеджер Responsive File Manager';

	
	public $model;
	public $attribute;

	public function init()
	{
		parent::init();
		if (empty($this->for) && $this->whithRfm)
			throw new \yii\base\InvalidConfigException("Не задано свойство for");

		if (empty($this->inst))
			$this->whithRfm=false;
	}

	public function beforeRun()
	{
		if (!parent::beforeRun()  )
			return false;

		if ($this->whithRfm)
			return $this->checkAccess();

		return true;
	}


	public function run()
	{

		// объединение настроек редактора .... 
		if (!empty($this->inst->editorConfig))
			$this->editorConfig=array_replace_recursive($this->inst->editorConfig,$this->editorConfig);

		// проверяем язык из системы... 
		if (!isset($this->editorConfig['language'])){
			$lang=explode('-',Yii::$app->language);
			$this->editorConfig['language']=reset($lang);
		}


		if ($this->whithRfm){
			$ass=\AlexNet\TinyFileMan\assets\TinyMCEWhithRfmAsset::register($this->view);
			// https://www.codeinhouse.com/install-tinymce-laravel-and-responsive-file-manager/
			$this->editorConfig['plugins'].=' filemanager';
			$filemanPath=\yii\helpers\Url::to(array_merge([$this->inst->id.'/file-man/css-scripts','cssscripts'=>'test.js'],$this->urlParams));
			$filemanPath=dirname($filemanPath);
			$this->editorConfig['external_filemanager_path']=$filemanPath.'/';
			$this->editorConfig['filemanager_title']=$this->fileManWindowTitle;
		}
		else
			\AlexNet\TinyFileMan\assets\TinyMCEStartAsset::register($this->view);


		// настройка тега textarea  ... 
		$tagConfig=[
			'class'=>'textarea-with-tiny '.$this->id,
			'rows'=>$this->textareaHeigt,
		];
		// редактор сделали только для чтения ..надо забить textarea 
		if (!empty($this->editorConfig['readonly']))
			$tagConfig['disabled']='disabled';

		
		// назначение уникального идентификатора . для поля 
		$elid=md5($this->elid);

		// настройки храним в сессии ..
		if ($this->inst){
			$this->saveConfigToSessi([
				'editor'=>$this->editorConfig,
			]);
			// урл получения настроек для редактора 
			$tagConfig['data-confurl']=\yii\helpers\Url::to([$this->inst->id.'/file-man/config','elid'=>$elid]);
		}
		else{
			$tagConfig['data-confkey']=$elid;
			$this->view->registerJsVar('tinyWidget_'.$elid,$this->editorConfig,\yii\web\View::POS_HEAD);
		}
			
		if ($this->model && $this->attribute)
			return \yii\helpers\Html::activeTextarea($this->model,$this->attribute,$tagConfig);
		
		return \yii\helpers\Html::tag('textarea','',$tagConfig);
	}
}