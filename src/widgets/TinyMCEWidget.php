<?php 

namespace AlexNet\TinyFileMan\widgets;

use Yii;

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

	public function init()
	{
		if (empty($this->for))
			throw new \yii\base\InvalidConfigException("Не задано свойство for");
	}

	public function run()
	{
		// экземпляр модуля ... 
		$inst=\AlexNet\TinyFileMan\FileManMod::getInstance();

		$params=$this->for;
		$confKey=array_shift($params);
		$filemanPath=\yii\helpers\Url::to(array_merge([$inst->id.'/file-man/css-scripts','cssscripts'=>'test.js'],$params));
		$filemanPath=dirname($filemanPath);

		// провека наличия определённого ключа в настройках модуля 
		if (empty($inst->baseRFMUrls[$confKey]))
			throw new \yii\base\InvalidConfigException("Нет подходящего пути");
			
		$access=true;
		if (!empty($inst->baseRFMUrls[$confKey]['perms']))
			for($i=0;$i<count($inst->baseRFMUrls[$confKey]['perms']);$i++){
				$p=$inst->baseRFMUrls[$confKey]['perms'][$i];
				$access=$access && ($p=='@' && !Yii::$app->user->isGuest || Yii::$app->user->can($p));
			}

		if (!$access)
			return '';


		// назначение уникального идентификатора . для поля 
		$elid=md5('text-area-witch-tiny-'.$this->id);

		// объединение настроек редактора .... 
		$this->editorConfig=array_replace_recursive($inst->editorConfig,$this->editorConfig);
		
		if ($this->whithRfm){
			$ass=\AlexNet\TinyFileMan\assets\TinyMCEWhithRfmAsset::register($this->view);
			// https://www.codeinhouse.com/install-tinymce-laravel-and-responsive-file-manager/
			$this->editorConfig['plugins'][]='filemanager';
			$this->editorConfig['external_filemanager_path']=$filemanPath.'/';
			$this->editorConfig['filemanager_title']=$this->fileManWindowTitle;
		}
		else
			\AlexNet\TinyFileMan\assets\TinyMCEStartAsset::register($this->view);

		// настройки храним в сессии ..
		$confArr=Yii::$app->session->get('file-man-rfm',[]);
		$confArr[$elid]=$this->editorConfig;
		Yii::$app->session->set('file-man-rfm',$confArr);

		return \yii\helpers\Html::tag('textarea','',[
			'data-confurl'=>\yii\helpers\Url::to(array_merge([$inst->id.'/file-man/config','elid'=>$elid],$params)),
			'class'=>'textarea-with-tiny '.$this->id,
			'rows'=>$this->textareaHeigt,
		]);
	}
}