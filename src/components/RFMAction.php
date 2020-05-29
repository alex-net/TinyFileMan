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
		$params=Yii::$app->request->resolve();
		$params=end($params);
		// преобразование путей .. 
		foreach(['uploadPath','thumbsPath'] as $key)
			if (preg_match('#<([^>]+)(?:\:[^>]+)?>#',$conf[$key],$finds) && isset($params[$finds[1]])) {
				$conf[$key]=str_replace($finds[0], $params[$finds[1]], $conf[$key]);
				// проверка наличия путей .. 
				$path=Yii::getAlias($conf[$key]);
				if (!file_exists($path))
					\yii\helpers\FileHelper::createDirectory($path);
			}
	
		// если файла нет - пишем ошибку .. 
		// переходим в каталог  файлового менеджера .. 
		chdir(dirname($file));
		global $lang_vars, $config;
		$lang_vars=[];
		// читаем настройки
		$config=include dirname($file).'/config/config.php';
		$config=array_merge($config,$this->controller->module->fileManConfig);
		$config['upload_dir']=str_replace(Yii::getAlias('@webroot'), '', Yii::getAlias($conf['uploadPath']));
		$config['default_language']=Yii::$app->language;
		$config['current_path']=Yii::getAlias($conf['uploadPath']);//'../../web/imgs/';//
		$config['thumbs_base_path']= Yii::getAlias($conf['thumbsPath']);//'../../web/thumbs/';
		
		return $this->controller->renderFile($file,[
			'config'=>$config,
			'version'=>$version,
		]);	
	}
}