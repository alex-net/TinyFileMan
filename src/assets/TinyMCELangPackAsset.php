<?php 

namespace AlexNet\TinyFileMan\assets;

use Yii;
class TinyMCELangPackAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@npm/tinymce-i18n/langs5';

    public function init()
    {
        parent::init();
        $lang = explode('-', Yii::$app->language);
        $this->js[] = reset($lang) . '.js';
    }
}