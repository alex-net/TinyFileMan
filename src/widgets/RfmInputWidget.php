<?php 

namespace AlexNet\TinyFileMan\widgets;

use Yii;

class RfmInputWidget extends RfmBaseWidget
{


	public $model;
	public $attribute;

	/**
	 * Тип поля input (text hidden )
	 * @var string
	 */
	public $inputType='text';
	/**
	 * настройки поля input 
	 * @var array
	 */
	public $inputOptions=['class'=>'form-control'];
	/**
	 * Настройки обёртки 
	 * @var array
	 */
	public $wrapperOptions=['class'=>'input-group form-group'];
	/**
	 * Текст на кнопке
	 * @var string
	 */
	public $btnText='Выбрать файл';

	/**
	 * Настройки кнопки 
	 * @var array
	 */
	public $btnOptions=['class'=>'btn btn-default'];

	public $modalHead='Выбор файла';
	/**
	 * Настройки модального окна
	 * @var array
	 */
	public $modalOption=[
		'size'=>\yii\bootstrap\Modal::SIZE_LARGE,
	];
	
	/**
	 * использовать относительные ссылки 
	 * @var bool
	 */
	public $relativeUrl=false;

	public function run()
	{

		$inputOptions=$this->inputOptions;
		
		$inputid=$this->elid;
		if ($this->model && $this->attribute)
			$inputid=\yii\helpers\Html::getInputId($this->model,$this->attribute);
		else
			$this->inputOptions['id']=$inputid;

		// проброс настроек кнопки 
		$btnOptions=$this->btnOptions;
		// проброс настроек модельного окна .. 
		$modalOption=$this->modalOption;
		$modalOption['toggleButton']=false;
		if (!empty($this->modalHead))
			$modalOption['header']=$this->modalHead;



		$iframeurl=$this->createUrlToManager();
		if ($this->relativeUrl)
			$iframeurl['relative_url']=1;
		$iframeurl['field_id']=$inputid;
		if ($this->model && $this->attribute){
			$fldr=dirname($this->model->{$this->attribute});
			if (preg_match('#^'.$this->placeholderConfig['uploadPath'].'#i',$fldr)){
				$fldr=preg_replace('#^'.$this->placeholderConfig['uploadPath'].'#i','',$fldr);
				$iframeurl['fldr']=$fldr;
			}
		}
		$iframeurl = \yii\helpers\Url::to($iframeurl);
		//Yii::info($iframeurl,'$iframeurl');
		
		//$this->saveConfigToSessi();

		\AlexNet\TinyFileMan\assets\RfmInputWidgetAsset::register($this->view);
		// дописываем обёртку для элемента чтобы  можно было прицепиться  jsом
		$wrappClass='rfm-input-widget';
		if(empty($this->wrapperOptions['class']))
			$this->wrapperOptions['class']=$wrappClass;
		else
			$this->wrapperOptions['class'].=' '.$wrappClass;
		$this->wrapperOptions['data-fm-url']=$iframeurl;
		

		return $this->render('field-widget',[
			'model'=>$this->model,
			'attribute'=>$this->attribute,
			'inputType'=>$this->inputType,
			'inputOptions'=>$this->inputOptions,
			'wrapperOptions'=>$this->wrapperOptions,
			'wid'=>$this->id,
			'btnText'=>$this->btnText,
			'btnOptions'=>$btnOptions,
			'inputid'=>$inputid,
			'modalOption'=>$modalOption,
			'BModalClass'=>$this->bootstrapModelClass,
		]);
	}
}