<?php 

namespace AlexNet\TinyFileMan\assets;

class TinyMCEStartAsset extends \yii\web\AssetBundle
{
	public $sourcePath='@app/vendor/alex-net/tiny-file-man/src/front';

	public $js=['tiny-mce-init.js'];

	public $depends=[
		'yii\web\YiiAsset',
		'AlexNet\TinyFileMan\assets\TinyMCEAsset',
		'AlexNet\TinyFileMan\assets\TinyMCELangPackAsset',
	];
}