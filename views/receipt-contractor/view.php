<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\receipt */
/* @var $membProvider ActiveDataProvider */
/* @var $searchMemb AllocatedMemberSearch */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="receipt-view">

    <h1><?= Html::encode('Receipt: ' . $this->title) ?></h1>

	<?= $this->render('../receipt/_viewtoolbar', ['model' => $model, 'class' => 'contractor']); ?>
	<?= $this->render('../receipt/_detail', ['modelReceipt' => $model]); ?>
	
    <?= GridView::widget([
    		'id' => 'member-grid',
    		'dataProvider' => $membProvider,
        	'filterModel' => $searchMemb,
 			'filterRowOptions'=>['class'=>'filter-row'],
			'panel'=>[
		        'type'=>GridView::TYPE_DEFAULT,
		        'heading'=>'<i class="glyphicon glyphicon-user"></i>&nbsp;Allocated to Members',
		        'before' => false,
		        'after' => false,
		    ],
			'columns' => [
					[
							'class'=>'kartik\grid\ExpandRowColumn',
							'width'=>'50px',
							'value'=>function ($model, $key, $index, $column) {
										return GridView::ROW_COLLAPSED;
									 },
							'detailUrl'=> Yii::$app->urlManager->createUrl(['allocation/summary-ajax']),
							'headerOptions'=>['class'=>'kartik-sheet-style'],
    						'expandOneOnly'=>true,
				],
				[ 
						'attribute' => 'fullName',
						'format' => 'raw',
						'value' => function ($data) {
							return Html::a ( Html::encode ( $data->member->fullName ), [ 
									'member/view',
									'id' => $data->member_id 
							] );
						} 
				],
        		[
        				'attribute' => 'reportId', 
        				'value' => 'member.report_id',
        				
    			],
				[
						'attribute' => 'totalAllocation',
						'format' => ['decimal', 2],
						'hAlign' => 'right',
    			],
				
			],
	]); ?>
										
	
	
</div>