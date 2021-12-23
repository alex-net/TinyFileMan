<?php 

namespace AlexNet\TinyFileMan\widgets;

use Yii;

class RfmIframeWidget extends RfmBaseWidget
{

	public function run()
	{

		$iframeurl=\yii\helpers\Url::to($this->createUrlToManager());

		//$this->saveConfigToSessi();

		return $this->render('iframe-widget',['src'=>$iframeurl]);
	}

}

