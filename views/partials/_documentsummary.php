<?php

use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $id mixed|string */
/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */
/* @var $controller string */
/* @var $permission string */

?>

<div id="document-panel">

<?php
// 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
Pjax::begin(['id' => 'document-grid', 'enablePushState' => false]);

/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
        'id' => 'document-grid',
		'dataProvider' => $dataProvider,
		'pjax' => false,
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'<i class="glyphicon glyphicon-folder-close"></i>&nbsp;Documents',
				'class' => 'text-primary',
				'before' => false,
				'after' => false,
				// 'footer' => false,
		],
		'columns' => [
				'doc_type',
				[
				'attribute' => 'showPdf',
				'label' => 'Doc',
				'format' => 'raw',
				'value' => function($model) {
					return (isset($model->doc_id)) ?
						Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-paperclip', 'title' => 'Show document']),
							$model->imageUrl, ['target' => '_blank', 'data-pjax'=>"0"]) : '';
						},
				],
				[
						'class' => 	'kartik\grid\ActionColumn',
						'visible' => Yii::$app->user->can($permission),
						'controller' => $controller,
						'template' => '{delete}',
						'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
							['value' => Url::to(["/{$controller}/create", 'relation_id'  => $id]),
													'id' => 'documentCreateButton',
							'class' => 'btn btn-default btn-modal btn-embedded',
							'data-title' => 'Document',
						]),
				],
		],
]);
?>

</div>
<?php

Pjax::end();

