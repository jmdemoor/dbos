<?php

/* @var $this yii\web\View */
/* @var $model app\models\accounting\AllocatedMember */

?>

<div class="allocation-reassign">

    <?= $this->render('../partials/_empllookup', [
        'model' => $model,
    	'label' => 'Reassign',
    ]) ?>

    
    
</div>
