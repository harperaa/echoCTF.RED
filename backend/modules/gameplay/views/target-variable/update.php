<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\gameplay\models\TargetVariable */

$this->title = 'Update Target Variable: ' . $model->target_id;
$this->params['breadcrumbs'][] = ucfirst(Yii::$app->controller->module->id);
$this->params['breadcrumbs'][] = ['label' => 'Target Variables', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->target_id, 'url' => ['view', 'target_id' => $model->target_id, 'key' => $model->key]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="target-variable-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
