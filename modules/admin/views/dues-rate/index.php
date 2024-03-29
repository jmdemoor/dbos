<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\accounting\DuesRateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $lobPicklist array */
/* @var $token bool|false|string */

$this->title = 'Dues Rates';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php if($token): ?>
    <div class="flash-error"><div class="pull-right"><?= Html::a('Refresh Fee Calendar Now', ["/admin/fee-calendar/refresh"], ['class' => 'btn btn-danger btn-embedded']) ?></div><div><?= $token ?></div></div>
<?php endif; ?>

<div class="dues-rate-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
    	'filterRowOptions'=>['class'=>'filter-row'],
    	'panel'=>[
    				'type'=>GridView::TYPE_WARNING,
    				'heading'=> $this->title,
    				'after' => false,
    	],
		'toolbar' => [
    						'content' => Html::button('Create Dues Rate', [
    						        'class' => 'btn btn-success btn-modal',
    						        'value' => Url::to(["create"]),
                                    'id' => 'duesRateCreateButton',
                                    'data-title' => 'Dues Rate',
                            ]),
		],
        'columns' => [
            [
                'class' => 'kartik\grid\ExpandRowColumn',
                'width' => '50px',
                'value' => function () {
                    return GridView::ROW_COLLAPSED;
                },
                'detailUrl' => Yii::$app->urlManager->createUrl([
                    '/admin/dues-rate/stripe-prod-json',
                ]),
                'headerOptions' => ['class' => 'kartik-sheet-style'],
                'expandOneOnly' => true,
            ],

            [
                'class' => 'kartik\grid\DataColumn',
        		'attribute' => 'lob_cd',
        		'filterType' => GridView::FILTER_SELECT2,
        		'filter' => $lobPicklist,
        		'filterWidgetOptions' => [
        						'size' => kartik\select2\Select2::SMALL,
        						'hideSearch' => true,
        						'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
        		],
    		],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'rate_class',
                'value' => 'rateClass.descrip',
                'contentOptions' => ['style' => 'white-space: nowrap;'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => array_merge(["" => ""], $searchModel->rateClassOptions),
                'filterWidgetOptions' => [
                    'size' => kartik\select2\Select2::SMALL,
                    'hideSearch' => true,
                    'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
                ],
            ],
            'effective_dt:date',
            'end_dt:date',
            [
            	'attribute' => 'rate',
            	'contentOptions' => ['style' => 'text-align:right',]
    		],

            ['class' => 'yii\grid\ActionColumn', 'template' => '{delete}',],
        ],
    ]); ?>

</div>
<?= $this->render('../partials/_modal') ?>


