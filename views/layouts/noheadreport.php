<?php
/**
 * @var string $content
 */
use yii\helpers\Html;


app\assets\ApplicationUiAssetBundle::register($this);
?>
<?php $this->beginPage(); ?>
	<!DOCTYPE html>
	<html lang="<?= Yii::$app->language ?>">
	<head>
		<meta charset="<?= Yii::$app->charset ?>" />
		<title>Printable</title>
    	<?php $this->head()?>
    	<?= Html::csrfMetaTags()?>
	</head>
	<body>
	<?php $this->beginBody()?>
	    <div class="wrap"> 
	    		<?=
	    			Html::button('Print This', [
	    					'class' => 'btn btn-default btn-print no-rept',
	    			]);
	    		?>
		<hr>
			<div class="container">
 				<?= $content; ?>
			</div>
			<footer class="clearfix">
			    <div class="container">
 			    </div>
			</footer>
		</div>
	<?php $this->endBody()?>
	</body>
	</html>
<?php $this->endPage()?>
