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
	public $wrapperOptions=['class'=>'input-group'];
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

	public $modalHead='выбор файла';
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
		if (!empty($this->btnText))
			$btnOptions['label']=$this->btnText;
		// проброс настроек модельного окна .. 
		$modalOption=$this->modalOption;
		if (empty($modalOption['toggleButton']))
			$modalOption['toggleButton']=$btnOptions;
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
		
		$this->saveConfigToSessi();
		

		return $this->render('field-widget',[
			'model'=>$this->model,
			'attribute'=>$this->attribute,
			'inputType'=>$this->inputType,
			'inputOptions'=>$this->inputOptions,
			'wrapperOptions'=>$this->wrapperOptions,
			'wid'=>$this->id,
			'btnOptions'=>$btnOptions,
			'inputid'=>$inputid,
			'modalOption'=>$modalOption,
			'iframeUrl'=>$iframeurl,
		]);
	}
}