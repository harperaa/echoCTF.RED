<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hint".
 *
 * @property int $id
 * @property string $title
 * @property string|null $player_type
 * @property string|null $message
 * @property string $category
 * @property int|null $badge_id Display this record after the user received the badge_id
 * @property int|null $finding_id Display this record after the user received the finding_id
 * @property int|null $treasure_id Display this record after the user received the treasure_id
 * @property int|null $question_id Display this record after the user answered the question_id
 * @property int|null $points_user Display this record after the user reaches these many points
 * @property int|null $points_team Display this record after the team reaches these many points
 * @property int|null $timeafter Display this hint after X seconds have been passed since the Start of the event
 * @property int|null $active Set this hint as active or innactive
 * @property string $ts
 *
 * @property Badge $badge
 * @property Finding $finding
 * @property Treasure $treasure
 * @property PlayerHint[] $playerHints
 * @property Player[] $players
 */
class Hint extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hint';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['player_type', 'message'], 'string'],
            [['badge_id', 'finding_id', 'treasure_id', 'question_id', 'points_user', 'points_team', 'timeafter', 'active'], 'integer'],
            [['ts'], 'safe'],
            [['title', 'category'], 'string', 'max' => 255],
            [['badge_id', 'finding_id', 'treasure_id', 'question_id', 'player_type'], 'unique', 'targetAttribute' => ['badge_id', 'finding_id', 'treasure_id', 'question_id', 'player_type']],
            [['badge_id'], 'exist', 'skipOnError' => true, 'targetClass' => Badge::className(), 'targetAttribute' => ['badge_id' => 'id']],
            [['finding_id'], 'exist', 'skipOnError' => true, 'targetClass' => Finding::className(), 'targetAttribute' => ['finding_id' => 'id']],
            [['treasure_id'], 'exist', 'skipOnError' => true, 'targetClass' => Treasure::className(), 'targetAttribute' => ['treasure_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'player_type' => 'Player Type',
            'message' => 'Message',
            'category' => 'Category',
            'badge_id' => 'Badge ID',
            'finding_id' => 'Finding ID',
            'treasure_id' => 'Treasure ID',
            'question_id' => 'Question ID',
            'points_user' => 'Points User',
            'points_team' => 'Points Team',
            'timeafter' => 'Timeafter',
            'active' => 'Active',
            'ts' => 'Ts',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBadge()
    {
        return $this->hasOne(Badge::className(), ['id' => 'badge_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFinding()
    {
        return $this->hasOne(Finding::className(), ['id' => 'finding_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTreasure()
    {
        return $this->hasOne(Treasure::className(), ['id' => 'treasure_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerHints()
    {
        return $this->hasMany(PlayerHint::className(), ['hint_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers()
    {
        return $this->hasMany(Player::className(), ['id' => 'player_id'])->viaTable('player_hint', ['hint_id' => 'id']);
    }
}
