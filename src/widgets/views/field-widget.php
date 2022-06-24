<?php 
use \yii\helpers\Html;
?>

<div <?= Html::renderTagAttributes($wrapperOptions); ?>>
	<?php if (!empty($model) && !empty($attribute)): ?>
		<?= Html::activeInput($inputType, $model, $attribute, $inputOptions); ?>
	<?php else: ?>
		<?= Html::Input($inputType, 'text-field-' . $wid, '', $inputOptions); ?>
	<?php endif; ?>
	<span class="input-group-btn">
		<?= \yii\helpers\Html::button($btnText, $btnOptions); ?>
	</span>	
	<?php 
		$BModalClass::begin($modalOption);
		$BModalClass::end();
	?>
</div>