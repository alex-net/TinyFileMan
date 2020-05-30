<?php 
use \yii\helpers\Html;
use \yii\bootstrap\Modal;
?>

<div <?=Html::renderTagAttributes($wrapperOptions);?>>
	<?php if (!empty($model) && !empty($attribute)):?>
		<?=Html::activeInput($inputType,$model,$attribute,$inputOptions);?>
	<?php else:?>
		<?=Html::Input($inputType,'text-field-'.$wid,'',$inputOptions);?>
	<?php endif;?>
	<span class="input-group-btn" style="font-size: initial;">
		<?php Modal::begin($modalOption);?>
		<?=$this->render('iframe-widget',['src'=>$iframeUrl]);?>
		<?php Modal::end();?>
	</span>
</div>