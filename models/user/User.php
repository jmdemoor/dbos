<?php

namespace app\models\user;

use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "Users".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $last_login
 */
class User extends \yii\db\ActiveRecord
				 implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Users';
    }

	public function behaviors()
	{
		return [
				'timestamp' => \yii\behaviors\TimestampBehavior::className(),
		];
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password_hash', 'email'], 'required'],
            [['role', 'status'], 'integer'],
            [['username', 'email', 'last_login'], 'string', 'max' => 255],
            [['username'], 'unique'],
        	[['email'], 'email'],
        	[['auth_key'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'role' => 'Role',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function beforeSave($insert)
    {
        $return = parent::beforeSave($insert);

        if ($this->isAttributeChanged('password_hash'))
        	$this->password_hash = Yii::$app->security->generatePasswordHash($this->password_hash);

        if ($this->isNewRecord)
            $this->auth_key = Yii::$app->security->generateRandomKey($length = 255);

        return $return;
    }

    // 5 methods that need to be implemented by IdentityInterface and used internally by Yii
    
    public function getId()
    {
    	return $this->id;
    }
    
    public static function findIdentity($id)
    {
    	return static::findOne($id);
    }
    
    public function getAuthKey()
    {
    	return $this->auth_key;
    }
    
    public function validateAuthKey($authKey)
    {
    	return $this->getAuthKey() === $authKey;
    }
    
    public static function findIdentityByAccessToken($token, $type = null)
    {
    	throw new NotSupportedException('You can only login by username/password pair for now.');
    }
    
    
}