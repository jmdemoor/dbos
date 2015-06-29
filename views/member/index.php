<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\helpers\OptionHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\member\MemberSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $statusPickList array */

$this->title = 'Members';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
 		'filterRowOptions'=>['class'=>'filter-row'],
		'panel'=>[
	        'type'=>GridView::TYPE_PRIMARY,
	        'heading'=> $this->title,
		    'after' => false,
		],
		'toolbar' => [
			'content' => Html::a('Create Member', ['create'], ['class' => 'btn btn-success']),
		],
		'rowOptions' => function($model) {
							$css = ['verticalAlign' => 'middle'];
        					if(!isset($model->currentStatus) || ($model->currentStatus->member_status == 'I')) {
//        						return ['class' => 'text-muted'];
								$css['class'] = 'text-muted';
        					}
        					return $css;
    					},
        'columns' => [

    		[
    			'attribute' => 'lob_cd',
    			'value' => 'currentStatus.lob_cd',
    			'width' => '80px',
    			'label' => 'Local',
    		],
    		
    		[
				'attribute' => 'status',
    			'width' => '140px',
    			'value' => 'currentStatus.status.descrip',
            	'filterType' => GridView::FILTER_SELECT2,
            	'filter' => array_merge(["" => ""], $statusPicklist),
            	'filterWidgetOptions' => [
            			'size' => \kartik\widgets\Select2::SMALL,
            			'hideSearch' => true,
            			'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
            	],
    		],
        	[
        		'attribute' => 'class',
        		'value' => 'currentClass.mClassDescrip',
        		'label' => 'Class',
        	],
			'report_id',
            [
            	'attribute' => 'fullName',
            	'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
        	[
            		'attribute' => 'specialties',
        			'value' => 'specialtyTexts',
        			'format' => 'ntext',
        			'contentOptions' => ['style' => 'white-space: nowrap;'],
        	],
        	[
        			'attribute' => 'employer', 
        			'value' => 'employer.descrip',
        			'contentOptions' => ['style' => 'white-space: nowrap;'],
        	],
            [
            	'class' => 'yii\grid\ActionColumn',
            	'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
        ],
    ]); ?>

</div>
