<?php 

namespace AlexNet\TinyFileMan\assets;

use Yii;

class TinyMCEAsset extends \yii\web\AssetBundle
{
	public $sourcePath='@npm/tinymce';

	public $js=['tinymce.min.js','jquery.tinymce.js'];
	
	public $depends=[
		'\yii\web\YiiAsset',
	];


}