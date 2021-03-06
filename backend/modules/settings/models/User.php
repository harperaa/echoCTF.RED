<?php

namespace app\modules\settings\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property string $verification_token
 * @property int $admin
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }



    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password_hash', 'email', ], 'required'],
            [['status', 'created_at', 'updated_at', 'admin'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'verification_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['auth_key'], 'default', 'value' => Yii::$app->security->generateRandomString()],
            [['password_reset_token'], 'default', 'value' => Yii::$app->security->generateRandomString() . '_' . time()],
            [['verification_token'], 'default', 'value' => Yii::$app->security->generateRandomString() . '_' . time()],
            [['status'], 'default', 'value'=>10],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'verification_token' => 'Verification Token',
            'admin' => 'Admin',
        ];
    }
    public function beforeSave($insert)
    {
      if (parent::beforeSave($insert) && $insert) {
          $this->password_hash = Yii::$app->security->generatePasswordHash($this->password_hash);
          return true;
      } else {
          return false;
      }
    }
}
