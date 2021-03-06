<?php

namespace app\models\employment;

/**
 * This model uses the VIEW CurrentEmployment and is for current employment entries only
 */
class CurrentEmployment extends Employment
{
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CurrentEmployment';
    }
    
}