<?php 

namespace AlexNet\TinyFileMan\components;
use Yii;
class RFMAction extends \yii\base\Action
{
	public function run()
	{
		$file=Yii::getAlias($this->controller->module->RFMlink.'/filemanager/'.$this->id.'.php');
		// если файла нет - пишем ошибку .. 
		if (!file_exists($file))
			return 'Ошибка';
		// переходим в каталог  файлового менеджера .. 
		chdir(dirname($file));
		global $lang_vars, $config;
		$lang_vars=[];
		// читаем настройки
		$config=include dirname($file).'/config/config.php';
		$config=array_merge($config,$this->controller->module->fileManConfig);
		$config['upload_dir']=str_replace(Yii::getAlias('@webroot'), '', Yii::getAlias($this->controller->module->uploadPath));
		$config['default_language']=Yii::$app->language;
		$config['current_path']=Yii::getAlias($this->controller->module->uploadPath);//'../../web/imgs/';//
		$config['thumbs_base_path']= Yii::getAlias($this->controller->module->thumbsPath);//'../../web/thumbs/';
		
		return $this->controller->renderFile($file,[
			'config'=>$config,
			'version'=>$version,
		]);	
	}
}