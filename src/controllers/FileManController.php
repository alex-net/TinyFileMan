<?php 

namespace AlexNet\TinyFileMan\controllers;

use Yii;

class FileManController extends \yii\web\Controller
{
	public $enableCsrfValidation=false;

	public $conf=[];

	

	public  function behaviors()
	{
		$bh=[];
		if (!empty($this->conf['perms']))
			$bh[]=[
				'class'=>'\yii\filters\AccessControl',
				'rules'=>[
					['allow'=>true,'roles'=>$this->conf['perms']],
				],
			];
		return $bh;
	}



	public function actions()
	{
		$path=Yii::getAlias($this->module->RFMlink.'/');
		$list=\yii\helpers\FileHelper::findFiles($path,[
			'recursive'=>false,
			'only'=>['*.php'],
		]);
		

		$actions=[];
		for($i=0;$i<count($list);$i++)
			$actions[basename($list[$i],'.php')]=\AlexNet\TinyFileMan\components\RFMAction::className();

		return $actions;
	}

	/**
	 * загрузка js скриптов css и картинок из менеджера .
	 * @param  string $cssscripts путь к картинке ...
	 * @return [type]             [description]
	 */
	public function actionCssScripts($cssscripts)
	{
		$path=Yii::getAlias($this->module->RFMlink.'/'.$cssscripts);
		if (file_exists($path)){
			$hand=fopen($path, 'rb');

			Yii::$app->response->sendStreamAsFile($hand,basename($path),[
				'mimeType'=>\yii\helpers\FileHelper::getMimeTypeByExtension($path),
				'inline'=>true,
				'fileSize'=>filesize($path)
			]);
			//->data=file_get_contents($path);
			Yii::$app->end();
		}
		return $cssscripts;
	}

	/**
	 * получить настройки для жлемента ... 
	 */
	public function actionConfig($elid)
	{
		
		$arr=Yii::$app->session->get('file-man-rfm',[]);
		$conf=$arr[$elid]??[];
		if (!$conf)
			throw new \yii\web\NotFoundHttpException("Error Processing Request");

		Yii::$app->response->format=\yii\web\Response::FORMAT_JSON;
		return ['conf'=>$conf['editor']];
		//return [];
	}
}