<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $payorPicklist array */
/* @var $searchModel \yii\data\ActiveDataProvider */

?>
<div id="receipt-popup">

<?php
// 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
Pjax::begin(['id' => 'receipt-grid', 'enablePushState' => false]);

/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
        'id' => 'receipt-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'filterRowOptions' => ['class' => 'filter-row'],
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => 'Member Receipts',
            'before' => false,
            'after' => false,
        ],
        'columns' => [
            [
                'class' => 'kartik\grid\ExpandRowColumn',
                'width' => '50px',
                'value' => function ($model, $key, $index, $column) {
                    return GridView::ROW_COLLAPSED;
                },
                'detailUrl' => Yii::$app->urlManager->createUrl(['allocation/summary-ajax']),
                'headerOptions' => ['class' => 'kartik-sheet-style'],
                'expandOneOnly' => true,
            ],
            [
                'attribute' => 'receipt_id',
                'label' => 'Nbr',
            ],
            [
                'attribute' => 'received_dt',
                'value' => 'receipt.received_dt',
                'format' => 'date',
                'label' => 'Received',
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'payor_type_filter',
                'width' => '140px',
                'value' => 'receipt.payorText',
                'label' => 'Payor',
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => array_merge(["" => ""], $payorPicklist),
                'filterWidgetOptions' => [
                    'size' => \kartik\widgets\Select2::SMALL,
                    'hideSearch' => true,
                    'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
                ],
            ],
            [
                'attribute' => 'feeTypes',
                'value' => 'receipt.feeTypeTexts',
                'format' => 'ntext',
                'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'totalAllocation',
                'label' => 'Paid',
                'format' => ['decimal', 2],
                'hAlign' => 'right',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'white-space: nowrap;'],
                'template' => '{view}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['title' => 'View']);
                    },
                ],
                'urlCreator' => function ($action, $model, $key, $index) {
                    if ($action === 'view') {
                        $route = ($model->receipt->payor_type == 'C') ? '/receipt-contractor' : '/receipt-member';
                        $url = Yii::$app->urlManager->createUrl([$route . '/view', 'id' => $model->receipt_id]);
                        return $url;
                    }
                },
            ],
        ],
        //		'showPageSummary' => true,
    ]);


?>
</div>
<?php

Pjax::end();
