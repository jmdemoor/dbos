<?php

use kartik\grid\GridView;
use yii\widgets\Pjax;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $credential_id string */

?>

<div class="leftside forty-pct">

    <?php
    // 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
    Pjax::begin(['id' => "history{$credential_id}-grid", 'enablePushState' => false]);

    /** @noinspection PhpUnhandledExceptionInspection */
    echo  GridView::widget([
        'id' => "history{$credential_id}-grid",
        'dataProvider' => $dataProvider,
        'pjax' => false,
        'summary' => '',
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => 'History',
            'before' => false,
            'after' => false,
            'footer' => false,
        ],
        'columns' => [
            'complete_dt:date',
        ],
    ]);

    Pjax::end();
?>

</div>


