<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $modelReceipt app\models\accounting\ReceiptMember */
/* @var $modelMember app\models\accounting\AllocatedMember */

// The controller action that will render the list
$url = Url::to(['/member/member-list']);

$this->title = 'Create Member Receipt';
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="receipt-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
//    		'layout' => 'horizontal',
    		'id' => 'receipt-form',
    ]); 
    
    $full_nm = empty($modelMember->member_id) ? 'Search for a member...' : $modelMember->member->fullName;
    ?>
    
    <?= $form->field($modelMember, 'member_id')->widget(Select2::classname(), [
		'size' => Select2::SMALL,
    	'initValueText' => $full_nm,
    	'options' => ['placeholder' => 'Search for a member...'],
	    'pluginOptions' => [
	        'allowClear' => true,
	        'minimumInputLength' => 3,
	        'ajax' => [
	            'url' => $url,
	            'dataType' => 'json',
				'data' => new JsExpression('function(params) { return {search:params.term}; }'),
	        ],
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('function(member_id) { return member_id.text; }'),
			'templateSelection' => new JsExpression('function(member_id) { return member_id.text; }'),
	    ],
	]); ?>
    
    <?= $this->render('../receipt/_formfields', [
    	'form' => $form,
        'model' => $model,
    ]) ?>

    <?= $form->field($model, 'fee_types')->multiselect($model->getFeeOptions($model->lob_cd)) ?>

    <?= $form->field($model, 'other_local')
    		 ->textInput(['maxlength' => true, 'id' => 'otherlocal'])
    		 ->label('Previous Local', ['id' => 'otherlocallbl']) ?>
    
    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>

<?php 
$script = <<< JS

$(function() {
		$('#otherlocallbl').hide();
		$('#otherlocal').hide();
})

$('#receiptmember-fee_types').change(function() {
	var ccexists = false;
	$('#receiptmember-fee_types input:checked').each(function() {
    	if ($(this).attr('value') == 'CC') {
			ccexists = true;
		}
	});
	if (ccexists == true) {
		$('#otherlocallbl').show();
		$('#otherlocal').show();
	} else {
		$('#otherlocallbl').hide();
		$('#otherlocal').hide();
	};
});

JS;
$this->registerJs($script);
?>
