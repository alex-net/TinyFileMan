<?php 

namespace AlexNet\TinyFileMan\assets;

class RfmInputWidgetAsset extends \yii\web\AssetBundle
{
	public $sourcePath='@AlexNet/TinyFileMan/front';
	public $js=['rfm-input-widget.js'];
	public $depends=['yii\bootstrap\BootstrapPluginAsset'];	
}