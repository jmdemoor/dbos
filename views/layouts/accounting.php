<?php

use yii\widgets\Breadcrumbs;
use kartik\widgets\SideNav;

?>

<?php $this->beginContent('@app/views/layouts/main.php'); ?>

			
	<div class="container">
		<div class="col-sm-3">
		<?php
			$type = SideNav::TYPE_DEFAULT;
			echo SideNav::widget([
				'type' => $type,
				'heading' => 'Operations',
				'items' => [
						[
							'label' => 'Member Assessment', 'icon' => '',
						],
						[
							'label' => 'Member Payment', 'icon' => '',
						],
						[
							'label' => 'Employer Invoices', 
							'icon' => '',
							'items' => [
								['label' => 'All Employers'],		
								['label' => 'Choose Employer'],		
							],
						],
						[
							'label' => 'Employer Remittance', 'icon' => '',
						],
						[
							'label' => 'Reports',
							'icon' => 'book',
							'items' => [
								['label' => 'Receipt Book Balance', 'url'=>'#'],
								['label' => 'Member Statuses', 'url'=>'#'],
								['label' => 'Arrears Report', 'url'=>'#'],
								['label' => 'Generate PAC Export', 'url'=>'#'],
							],
						],
					],
			]);
			
			?>
		</div>
		<div class="col-sm-9">
	        <?= Breadcrumbs::widget([
	            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
	        ]) ?>
			<?= $content; ?>
		</div>
	
	


<?php $this->endContent(); ?>