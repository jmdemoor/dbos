<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\value\TradeSpecialty */

$this->title = 'Update Trade Specialty: ' . ' ' . $model->specialty;
$this->params['breadcrumbs'][] = ['label' => 'Trade Specialties', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->specialty, 'url' => ['view', 'id' => $model->specialty]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="trade-specialty-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
