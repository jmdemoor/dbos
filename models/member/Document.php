<?php

namespace app\models\member;

use app\models\base\BaseDocument;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MemberDocuments".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $doc_type
 * @property string $doc_id
 * 
 */
class Document extends BaseDocument
{
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberDocuments';
    }

    public function getTypeOptions()
    {
    	return ArrayHelper::map($this->member->unfiledDocs, 'doc_type', 'doc_type');
    }
    
    
}
