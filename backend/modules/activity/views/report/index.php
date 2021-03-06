<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\activity\models\ReportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ucfirst(Yii::$app->controller->module->id).' / '.ucfirst(Yii::$app->controller->id);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Report', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            'id',
            'title',
            'player_id',
            [
                'attribute' => 'player',
                'label'=>'Player',
                'value'=> function($model) {return sprintf("id:%d %s",$model->player_id,$model->player->username);},
            ],
            'body:ntext',
            [
              'attribute'=>'status',
              'filter'=>['pending'=>'Pending', 'approved'=>'Approved','invalid'=>'Invalid'],
            ],
            //'points',
            //'modcomment:ntext',
            //'pubtitle',
            //'pubbody:ntext',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
