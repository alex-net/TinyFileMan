<?php 

namespace AlexNet\TinyFileMan\assets;

class RfmWrapperWidgetAsset extends \yii\web\AssetBundle
{
	public $sourcePath='@AlexNet/TinyFileMan/front';
	public $js=['rfm-wrapper-widget.js'];
	public $depends=['yii\web\YiiAsset'];
}