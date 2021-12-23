<?php 

namespace AlexNet\TinyFileMan\widgets;

use Yii;
use \yii\helpers\Html;

class RfmWrapperWidget extends RfmBaseWidget
{
	// тег обёртки ..
	public $wrapTag='div';
	// пааметры обёртки .. 
	public $wrapOpts=[];
	// параметры модального окна bootstrap ... 
	public $modalOption=[];


	public function init()
	{
		parent::init();
		$wrapOpts=$this->wrapOpts;
		$wrapOpts['class'][]='rfm-wrapper-widget';

		// подцепить скрипт для управления всплыванием ..  окна ..
		\AlexNet\TinyFileMan\assets\RfmWrapperWidgetAsset::register($this->view);
		

		// обёртка 
		echo Html::beginTag($this->wrapTag,$wrapOpts);
		// id поля приёмника ...
		$inputId='input-file-receiver-'.$this->id;

		// url для Iframe
		$iframeurl=$this->createUrlToManager();
		$iframeurl['field_id']=$inputId;

		// input приёмник ... данных от ФМ .. 
		echo Html::hiddenInput($inputId,'',[
			'class'=>['input-file-receiver'],
			//'disabled'=>'disabled',
			'id'=>$inputId,
			'data-fm-url'=> \yii\helpers\Url::to($iframeurl),
		]);


		// всплывашка .. 
		$opt=$this->modalOption;
		$modal=$this->bootstrapModelClass?:'yii\bootstrap\Modal';
		$opt['options']['class'][]='rfm-wrapp-modal-container';
		$this->bootstrapModelClass::begin($opt);
		$modal::end();
	}

	public function run()
	{
		//  заккрывабщий тег ....
		return Html::endTag($this->wrapTag);
	}
}