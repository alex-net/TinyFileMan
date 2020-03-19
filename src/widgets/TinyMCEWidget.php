<?php 

namespace AlexNet\TinyFileMan\widgets;

use Yii;

class TinyMCEWidget extends \yii\base\Widget
{
	// уникальный id для обозначения  текстового редактора ..
	public $elid;
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





	public function run()
	{
		// экземпляр модуля ... 
		$inst=\AlexNet\TinyFileMan\FileManMod::getInstance();

		// назначение уникального идентификатора . для поля 
		if (!$this->elid)
			$this->elid='text-area-witch-tiny-'.$this->id;

		// объединение настроек редактора .... 
		//if (!empty($this->editorConfig))
		$this->editorConfig=array_merge($inst->editorConfig,$this->editorConfig);
		
		if ($this->whithRfm){
			$ass=\AlexNet\TinyFileMan\assets\TinyMCEWhithRfmAsset::register($this->view);
			// https://www.codeinhouse.com/install-tinymce-laravel-and-responsive-file-manager/
			$this->editorConfig['plugins'][]='filemanager';
			//$this->editorConfig['toolbar'].='|responsivefilemanager';
			$this->editorConfig['external_filemanager_path']='/'.$inst->baseRFMUrl.'/';
		}
		else
			\AlexNet\TinyFileMan\assets\TinyMCEStartAsset::register($this->view);

		// настройки храним в сессии ..
		$confArr=Yii::$app->session->get('file-man-rfm',[]);
		$confArr[$this->elid]=$this->editorConfig;
		Yii::$app->session->set('file-man-rfm',$confArr);

		return \yii\helpers\Html::tag('textarea','',[
			'id'=>$this->elid,
			'data-confurl'=>\yii\helpers\Url::to([$inst->id.'/file-man/config','elid'=>$this->elid]),
			'class'=>'textarea-with-tiny',
			'rows'=>$this->textareaHeigt,
		]);
	}
}