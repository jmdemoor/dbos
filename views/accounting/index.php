<?php

use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\helpers\OptionHelper;
use app\models\accounting\Receipt;

/* @var $this yii\web\View */
/* @var $searchModel app\models\accounting\ReceiptSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $mine_only boolean  */
/* @var $payorPicklist array */

$this->title = 'Receipts';
$this->params['breadcrumbs'][] = $this->title;

$show_class = $mine_only ? 'glyphicon glyphicon-expand' : 'glyphicon glyphicon-user';
$show_label = $mine_only ? 'All' : 'Mine Only';
$toggle_mine_only = !$mine_only;


?>

<div class="receipt-index">

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
 		'filterRowOptions'=>['class'=>'filter-row'],
		'panel'=>[
	        'type'=>GridView::TYPE_PRIMARY,
	        'heading'=> $this->title,
			// workaround to prevent 1 in the before section
			'before' => (Yii::$app->user->can('createReceipt')) ? '' : false,
		    'after' => false,
		],
		'toolbar' => [
			'content' => 
				Html::a(Html::tag('span', '', ['class' => $show_class]) . '&nbsp;Show ' . $show_label, 
							['index', 'mine_only' => $toggle_mine_only],
							['class' => 'btn btn-default'])
				.
				Html::button('Create Receipt', 
					[
							'class' => 'btn btn-success btn-modal',
							'id' => 'receiptCreateButton',
							'value' => Url::to(["create-receipt"]),
							'data-title' => 'Receipt',
					]),
		],
    	'rowOptions' => function(Receipt $model) {
    		$css = ['verticalAlign' => 'middle'];
    		if ($model->void == OptionHelper::TF_TRUE)
    			$css['class'] = 'text-muted';
    		elseif ($model->isUpdating())
                $css['class'] = 'warning';
    		
    		return $css;
    		},
    	'columns' => [
    		[
    				'attribute' => 'id',
    				'label' => 'Nbr',
                    'value' => function(Receipt $model) { return ($model->isUpdating()) ? $model->id . ' [** NOT POSTED **]' : $model->id; },
    		],
    		'lob_cd',
    		[
    				'attribute' => 'received_dt',
    				'format' => 'date',
    				'label' => 'Received',
    		],
    		[
                'class' => 'kartik\grid\DataColumn',
				'attribute' => 'payor_type_filter',
    			'width' => '140px',
    			'value' => 'payorText',
    			'label' => 'Type',
            	'filterType' => GridView::FILTER_SELECT2,
            	'filter' => array_merge(["" => ""], $payorPicklist),
            	'filterWidgetOptions' => [
            			'size' => Select2::SMALL,
            			'hideSearch' => true,
            			'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
            	],
       		],
        	[
        			'attribute' => 'payor_nm',
        			'contentOptions' => ['style' => 'white-space: nowrap;'],
        			'value' => function($model) { return ($model->void == OptionHelper::TF_TRUE) ? '** VOID **' : $model->payor_nm; },
			],
            [
            		'attribute' => 'received_amt',
            		'contentOptions' => ['class' => 'right'],
        			'value' => function($model) { return ($model->void == OptionHelper::TF_TRUE) ? '** VOID **' : $model->received_amt; },
            ],
            /*  Remove for performance reasons (does not use an index with eager load)
    		[
    				'attribute' => 'feeTypes',
    				'value' => 'feeTypeTexts',
    				'format'  => 'ntext',
    				'contentOptions' => ['style' => 'white-space: nowrap;'],
        	],
        	*/
    		[
    			'class' => 'yii\grid\ActionColumn',
    			'template' => '{view}',
    			'buttons' => [
    				'view' => function(/** @noinspection PhpUnusedParameterInspection */
    				                    $url, $model, $key) {
    							return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
    							        'title' => 'View',
                                        'target' => '_blank',
                                ]);
    				},
    			],
    			'urlCreator' => function (/** @noinspection PhpUnusedParameterInspection */
    			                        $action, $model, $key, $index) {
					    			if ($action === 'view') {
					    				$route = '/receipt-' . $model->urlQual;
					    				return Yii::$app->urlManager->createUrl([$route . '/view', 'id' => $model->id]);
					    			}
					    			return null;
				},
    			 
    			'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
    	],
    ]); ?>



</div>
<?= $this->render('../partials/_modal') ?>