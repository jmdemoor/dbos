<?php

namespace app\models\member;

use Yii;

/**
 * This is the model class for table "MemberEmails".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $email
 *
 * @property Members $member
 */
class Email extends \yii\db\ActiveRecord
{
	/*
	 * Injected Member object, used for creating new entries
	 */
	public $member;
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberEmails';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'email'],
            [['email'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'email' => 'Email',
        ];
    }

    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert)) {
	    	if (!(isset($this->member) && ($this->member instanceof Member)))
	    		throw new \yii\base\InvalidConfigException('No member object injected');
    		if ($insert) 
    			$this->member_id = $this->member->member_id;
    		return true;
    	}
    	return false;
    }
    
}
