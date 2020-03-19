<?php 

namespace AlexNet\TinyFileMan\assets;

use Yii;

class TinyMCEWhithRfmAsset extends \yii\web\AssetBundle
{
	public $depends=['\AlexNet\TinyFileMan\assets\TinyMCEStartAsset'];

	public function init()
	{
		parent::init();
		$inst=\AlexNet\TinyFileMan\FileManMod::getInstance();
		if(!empty($inst->RFMlink) && file_exists(Yii::getAlias($inst->RFMlink))){
			$this->sourcePath=$inst->RFMlink.'/tinymce';
			$this->js[]='plugin.min.js';
		}

	}

}