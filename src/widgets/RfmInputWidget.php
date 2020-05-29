<?php 

namespace AlexNet\TinyFileMan\widgets;

use Yii;

class RfmInputWidget extends \yii\base\Widget
{
	/**
	 * настройки зависящие от адреса расположения файлового манагера ... (один элемент (ключ) из baseRFMUrls  модуля.. с подстановкой параметров если надо .. Аналогично  \yii\helpers\Url::to()
	 * @var array
	 */
	public $for;

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
		// проверяем наличие включённого модуля .. 
		$inst=\AlexNet\TinyFileMan\FileManMod::getInstance();
		if (!$inst || empty($this->for) || !is_array($this->for))
			throw new \yii\base\InvalidConfigException("Ошибки в настройках");

		$confKey=reset($this->for);
		// если нет настроек в модуле.. 
		if (empty($inst->baseRFMUrls[$confKey]))
			throw new \yii\base\InvalidConfigException("Ошибки в настройках");

		
		// проверка прав доступа 
		if (!empty($inst->baseRFMUrls[$confKey]['perms'])){
			$access=true;
			for($i=0;$i<count($inst->baseRFMUrls[$confKey]['perms']);$i++){
				$p=$inst->baseRFMUrls[$confKey]['perms'][$i];
				$access=$access && ($p=='@' && !Yii::$app->user->isGuest || Yii::$app->user->can($p));
			}
			if (!$access)
				return '';
		}

		$inputOptions=$this->inputOptions;
		$elid=$inputid='text-field-'.$this->id;
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


		$iframeurl=$this->for;
		$iframeurl[0]='/'.$inst->id.'/file-man/dialog';
		
		$elid=md5($elid);
		$iframeurl['elid']=$elid;
		$iframeurl['field_id']=$inputid;
		if ($this->relativeUrl)
			$iframeurl['relative_url']=1;
		$iframeurl=\yii\helpers\Url::to($iframeurl);
		Yii::info($iframeurl,'$iframeurl');

		$confArr=Yii::$app->session->get('file-man-rfm',[]);
		$confArr[$elid]=[
			'filemanKey'=>empty($confKey)?'':$confKey,
		];
		Yii::$app->session->set('file-man-rfm',$confArr);

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