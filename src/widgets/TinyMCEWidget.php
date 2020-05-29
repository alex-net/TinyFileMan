<?php 

namespace AlexNet\TinyFileMan\widgets;

use Yii;

/**
 * виджет для отображения редактора TinyMce и встроенного в него файлового  менеджера (при необходимости ) 
 */
class TinyMCEWidget extends \yii\base\Widget
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

	/**
	 * настройки зависящие от адреса расположения файлового манагера ... (один элемент (ключ) из baseRFMUrls  модуля.. с подстановкой параметров если надо .. Аналогично  \yii\helpers\Url::to()
	 * @var array
	 */
	public $for;

	public $model;
	public $attribute;

	public function init()
	{
		if (empty($this->for) && $this->whithRfm)
			throw new \yii\base\InvalidConfigException("Не задано свойство for");

		$inst=\AlexNet\TinyFileMan\FileManMod::getInstance();
		if (empty($inst))
			$this->whithRfm=false;
	}

	public function run()
	{
		// экземпляр модуля ... 
		$inst=\AlexNet\TinyFileMan\FileManMod::getInstance();

		// назначение уникального идентификатора . для поля 
		$elid=md5('text-area-witch-tiny-'.$this->id);

		// если мы работаем с редактором то .. .. 
		if ($this->whithRfm && !empty($this->for)){
			$params=$this->for;
			$confKey=array_shift($params);
			$params['elid']=$elid;
			$filemanPath=\yii\helpers\Url::to(array_merge([$inst->id.'/file-man/css-scripts','cssscripts'=>'test.js'],$params));
			$filemanPath=dirname($filemanPath);

			// провека наличия определённого ключа в настройках модуля 
			if (empty($inst->baseRFMUrls[$confKey]))
				throw new \yii\base\InvalidConfigException("Нет подходящего пути");
				
			// проверка прав доступа .. 
			if (!empty($inst->baseRFMUrls[$confKey]['perms'])){
				$access=true;
				for($i=0;$i<count($inst->baseRFMUrls[$confKey]['perms']);$i++){
					$p=$inst->baseRFMUrls[$confKey]['perms'][$i];
					$access=$access && ($p=='@' && !Yii::$app->user->isGuest || Yii::$app->user->can($p));
				}
				if (!$access)
					return '';
			}

			
		}

		// объединение настроек редактора .... 
		if (!empty($inst->editorConfig))
			$this->editorConfig=array_replace_recursive($inst->editorConfig,$this->editorConfig);

		// проверяем язык из системы... 
		if (!isset($this->editorConfig['language'])){
			$lang=explode('-',Yii::$app->language);
			$this->editorConfig['language']=reset($lang);
		}
		
		if ($this->whithRfm){
			$ass=\AlexNet\TinyFileMan\assets\TinyMCEWhithRfmAsset::register($this->view);
			// https://www.codeinhouse.com/install-tinymce-laravel-and-responsive-file-manager/
			$this->editorConfig['plugins'].=' filemanager';
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
		

		// настройки храним в сессии ..
		if ($inst){
			$confArr=Yii::$app->session->get('file-man-rfm',[]);
			$confArr[$elid]=[
				'editor'=>$this->editorConfig,
				'filemanKey'=>empty($confKey)?'':$confKey,
			];

			Yii::$app->session->set('file-man-rfm',$confArr);
			$tagConfig['data-confurl']=\yii\helpers\Url::to([$inst->id.'/file-man/config','elid'=>$elid]);
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