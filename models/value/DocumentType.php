<?php

namespace app\models\value;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "DocumentTypes".
 *
 * @property string $doc_type
 * @property string $catg
 */
class DocumentType extends ActiveRecord
{

    const CATG_MEMBER = 'Member';
    const CATG_TRAINING = 'Training';
    const CATG_EMPLOYMENT = 'Employment';
    const CATG_RECEIPT = 'Receipt';

    const TYPE_RECURRING_CCAUTH = 'Recurring Credit Card Authorization Form';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'DocumentTypes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['doc_type', 'catg'], 'required'],
            [['doc_type'], 'string', 'max' => 50],
            [['catg'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'doc_type' => 'Document Type',
            'catg' => 'Category',
        ];
    }

    public function getCatgOptions()
    {
        return [
            self::CATG_MEMBER => self::CATG_MEMBER,
            self::CATG_TRAINING => self::CATG_TRAINING,
            self::CATG_EMPLOYMENT => self::CATG_EMPLOYMENT,
            self::CATG_RECEIPT => self::CATG_RECEIPT,
        ];
    }
}
