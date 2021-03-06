<?php

namespace app\models\accounting;

use \app\models\contractor\Contractor;

/**
 * This is the model class for table "ResponsibleEmployers".
 *
 * @property integer $receipt_id
 * @property string $license_nbr
 *
 * @property Receipt $receipt
 * @property Contractor $employer
 */
class ResponsibleEmployer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ResponsibleEmployers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receipt_id'], 'integer'],
            [['license_nbr'],  'exist', 'targetClass' => '\app\models\contractor\Contractor', 'targetAttribute' => 'license_nbr']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'receipt_id' => 'Receipt ID',
            'license_nbr' => 'Employer',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (isset($changedAttributes['license_nbr'])) {
            // Change payor on receipt
            if (!$insert) {
                $receipt = $this->receipt;
                $employer = $this->employer;
                $receipt->payor_nm = $employer->contractor;
                $receipt->save();
            }
        }

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceipt()
    {
        return $this->hasOne(Receipt::className(), ['id' => 'receipt_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployer()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'license_nbr']);
    }
    
    
}
