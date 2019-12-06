<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "player_finding".
 *
 * @property int $player_id
 * @property int $finding_id
 * @property string $ts
 *
 * @property Player $player
 * @property Finding $finding
 */
class PlayerFinding extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'player_finding';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['player_id', 'finding_id'], 'required'],
            [['player_id', 'finding_id'], 'integer'],
            [['ts'], 'safe'],
            [['player_id', 'finding_id'], 'unique', 'targetAttribute' => ['player_id', 'finding_id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::className(), 'targetAttribute' => ['player_id' => 'id']],
            [['finding_id'], 'exist', 'skipOnError' => true, 'targetClass' => Finding::className(), 'targetAttribute' => ['finding_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'player_id' => 'Player ID',
            'finding_id' => 'Finding ID',
            'ts' => 'Ts',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayer()
    {
        return $this->hasOne(Player::className(), ['id' => 'player_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFinding()
    {
        return $this->hasOne(Finding::className(), ['id' => 'finding_id']);
    }
}
