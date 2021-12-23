<?php 

namespace AlexNet\TinyFileMan\assets;

class TinyMCEStartAsset extends \yii\web\AssetBundle
{
	public $sourcePath='@AlexNet/TinyFileMan/front';

	public $js=['tiny-mce-init.js'];

	public $depends=[
		'yii\web\YiiAsset',
		'AlexNet\TinyFileMan\assets\TinyMCEAsset',
		'AlexNet\TinyFileMan\assets\TinyMCELangPackAsset',
	];
}