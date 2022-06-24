<h1>Произошла ошибка</h1>
<?php 

echo \yii\widgets\DetailView::widget([
    'model' => $errData,
    'attributes' => [
        'file', 'code', 'mess', 'trace:html'
    ],
]);

