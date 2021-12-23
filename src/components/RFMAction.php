<?php 

namespace AlexNet\TinyFileMan\components;
use Yii;
class RFMAction extends \yii\base\Action
{
	public function run()
	{
		// запрашиваемый файл у файлового менеджера .. 
		$file=Yii::getAlias($this->controller->module->RFMlink.'/'.$this->id.'.php');
		// нет файла .. - вылет 
		if (!file_exists($file))
			throw new \yii\web\NotFoundHttpException("Нет такой страницы!");
		// забираем настройки  
		$elid=Yii::$app->request->get('elid');
		$sessi=Yii::$app->session->get('file-man-rfm',[]);
		if (empty($sessi[$elid]['filemanKey']) || empty($this->controller->module->baseRFMUrls[$sessi[$elid]['filemanKey']]))
			throw new \yii\web\NotFoundHttpException("Нет такой страницы!");

		$conf=$this->controller->module->baseRFMUrls[$sessi[$elid]['filemanKey']];
		$fileManWidgetConfig=$sessi[$elid]['fileManConf']??[];
		$params=Yii::$app->request->queryParams;
		// преобразование путей .. 
		foreach(['uploadPath','thumbsPath'] as $key){
			if (preg_match('#<([^>]+)(?:\:[^>]+)?>#',$conf[$key],$finds) && isset($params[$finds[1]])) 
				$conf[$key]=str_replace($finds[0], $params[$finds[1]], $conf[$key]);
			// проверка наличия путей .. 
			$path=Yii::getAlias($conf[$key]);
			if (!file_exists($path))
				\yii\helpers\FileHelper::createDirectory($path);
			$webroot=realpath(Yii::getAlias('@webroot'));
			if ($key=='thumbsPath' && strpos($path, $webroot)===false){
				$thumbsAsset=new \yii\web\AssetBundle([
					'sourcePath'=>$path,
				]);
				$thumbsAsset->publish(Yii::$app->assetManager);
			}
		}
	
		// если файла нет - пишем ошибку .. 
		// переходим в каталог  файлового менеджера .. 
		$mfrDir=dirname($file);
		chdir($mfrDir);
		global $lang_vars, $config;
		$lang_vars=[];
		// читаем настройки
		$config=include $mfrDir.'/config/config.php';
		$config=array_merge($config,$this->controller->module->fileManConfig,$fileManWidgetConfig);
		$config['upload_dir']=str_replace(Yii::getAlias('@webroot'), '', Yii::getAlias($conf['uploadPath']));
		// разборки с ззыком
		$lang=str_replace('-', '_', Yii::$app->language);
		$langs=[$lang];
		$lang=explode('_', $lang);
		$lang=reset($lang);
		$langs[]=strtolower($lang);

		foreach($langs as $l)
			if (file_exists($mfrDir.'/lang/'.$l.'.php')){
				$config['default_language']=$l;
				break;
			}
		
		//$config['default_language']=reset($lang);
		$config['current_path']=Yii::getAlias($conf['uploadPath']);//'../../web/imgs/';//
		$config['thumbs_base_path']= Yii::getAlias($conf['thumbsPath']);//'../../web/thumbs/';

		try{
			return $this->controller->renderFile($file,[
				'config'=>$config,
				'version'=>$version,
				'thumbsAsset'=>$thumbsAsset??null,
			]);	
		}
		catch(\Exception $e){
			return $this->controller->render('error',[
				'errData'=>[
					'mess'=>$e->getMessage(),
					'code'=>$e->getCode(),
					'file'=>$e->getFile().':'.$e->getLine(),
					'trace'=>nl2br($e->getTraceAsString()),
				],
			]);
		}
	}
}