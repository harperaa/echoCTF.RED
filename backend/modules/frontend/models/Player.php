<?php

namespace app\modules\frontend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use app\modules\activity\models\SpinQueue;
use app\modules\activity\models\SpinHistory;

/**
 * This is the model class for table "player".
 *
 * @property int $id
 * @property string $username
 * @property string $fullname
 * @property string $email
 * @property string $type
 * @property string $password
 * @property string $activkey
 * @property int $created
 * @property int $active
 * @property int $academic
 * @property int $status
 * @property string $ts
 *
 * @property PlayerBadge[] $playerBadges
 * @property Badge[] $badges
 * @property PlayerFinding[] $playerFindings
 * @property Finding[] $findings
 * @property PlayerHint[] $playerHints
 * @property Hint[] $hints
 * @property PlayerIp[] $playerIp
 * @property PlayerIp[] $playerIps
 * @property PlayerMac[] $playerMacs
 * @property PlayerQuestion[] $playerQuestions
 * @property PlayerTreasure[] $playerTreasures
 * @property Treasure[] $treasures
 * @property Report[] $reports
 * @property Sessions[] $sessions
 * @property Sshkey $sshkey
 * @property Stream[] $streams
 * @property Team[] $teams
 * @property TeamPlayer $teamPlayer
 * @property Team[] $teams0
 */
class Player extends \yii\db\ActiveRecord
{
  public $ovpn=null,$online=null,$last_seen=null;
  const STATUS_DELETED = 0;
  const STATUS_INACTIVE = 9;
  const STATUS_ACTIVE = 10;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'player';
    }
    public function behaviors()
    {
      return [
          [
              'class' => TimestampBehavior::className(),
              'createdAtAttribute' => 'created',
              'updatedAtAttribute' => 'ts',
              'value' => new Expression('NOW()'),
          ],
      ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username'], 'required'],
            [['type'], 'string'],
            [['active','status'], 'integer'],
            [['academic'], 'boolean'],
            [['email'], 'filter', 'filter'=>'strtolower'],
            [['activkey'], 'string', 'max' => 32],
            [['activkey'], 'default', 'value' => Yii::$app->security->generateRandomString()],
            [['username', 'fullname', 'email', 'password', 'activkey'], 'string', 'max' => 255],
            [['username'], 'unique'],
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
            'fullname' => 'Fullname',
            'email' => 'Email',
            'type' => 'Type',
            'password' => 'Password',
            'activkey' => 'Activkey',
            'created' => 'Created',
            'active' => 'Active',
            'academic' => 'Academic',
            'status'=>'Status',
            'ts' => 'Ts',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerBadges()
    {
        return $this->hasMany(PlayerBadge::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBadges()
    {
        return $this->hasMany(Badge::className(), ['id' => 'badge_id'])->viaTable('player_badge', ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerFindings()
    {
        return $this->hasMany(PlayerFinding::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFindings()
    {
        return $this->hasMany(Finding::className(), ['id' => 'finding_id'])->viaTable('player_finding', ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerHints()
    {
        return $this->hasMany(PlayerHint::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHints()
    {
        return $this->hasMany(Hint::className(), ['id' => 'hint_id'])->viaTable('player_hint', ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerIp()
    {
        return $this->hasOne(PlayerIp::className(), ['player_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerIps()
    {
        return $this->hasMany(PlayerIp::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerSsl()
    {
        return $this->hasOne(PlayerSsl::className(), ['player_id' => 'id']);
    }

    public function getPlayerSpin()
    {
        return $this->hasOne(PlayerSpin::className(), ['player_id' => 'id']);
    }

    public function getSpinQueue()
    {
        return $this->hasMany(SpinQueue::className(), ['player_id' => 'id']);
    }

    public function getSpinHistory()
    {
        return $this->hasMany(SpinHistory::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerMacs()
    {
        return $this->hasMany(PlayerMac::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerQuestions()
    {
        return $this->hasMany(PlayerQuestion::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerTreasures()
    {
        return $this->hasMany(PlayerTreasure::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTreasures()
    {
        return $this->hasMany(Treasure::className(), ['id' => 'treasure_id'])->viaTable('player_treasure', ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReports()
    {
        return $this->hasMany(Report::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSessions()
    {
        return $this->hasMany(\app\modules\activity\models\Sessions::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSshkey()
    {
        return $this->hasOne(Sshkey::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStreams()
    {
        return $this->hasMany(Stream::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeams()
    {
        return $this->hasMany(Team::className(), ['owner_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeamPlayer()
    {
        return $this->hasOne(TeamPlayer::className(), ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeams0()
    {
        return $this->hasOne(Team::className(), ['id' => 'team_id'])->viaTable('team_player', ['player_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLast()
    {
        return $this->hasOne(\app\modules\activity\models\PlayerLast::className(), ['id' => 'id']);
    }

  /*  public function getOvpn()
    {
      $ip=Yii::$app->cache->Memcache->get("ovpn:".$this->id);
      if($ip===false) return long2ip(0);
      return $ip;
    }

    public function getLast_seen()
    {
      $last_seen=Yii::$app->cache->Memcache->get("last_seen:".$this->id);
      if($last_seen===false) $last_seen=null;
      return $last_seen;
    }

    public function getOnPUI()
    {
      return Yii::$app->cache->Memcache->get("online:".$this->id);
    }*/
    public static function find()
    {
      return parent::find()->select(['player.*','ifnull(memc_get(concat("ovpn:",player.id)),0) as ovpn','ifnull(memc_get(concat("online:",player.id)),0) as online','memc_get(concat("last_seen:",player.id)) as last_seen']);
    }

    public function getHeadshots(){
      $QUERY="SELECT t.* FROM target AS t left join treasure as t2 on t2.target_id=t.id left join finding as t3 on t3.target_id=t.id LEFT JOIN player_treasure as t4 on t4.treasure_id=t2.id and t4.player_id=:player_id left join player_finding as t5 on t5.finding_id=t3.id and t5.player_id=:player_id GROUP BY t.id HAVING count(distinct t2.id)=count(distinct t4.treasure_id) AND count(distinct t3.id)=count(distinct t5.finding_id) ORDER BY t.ip,t.fqdn,t.name";
      $targets = Yii::$app->db->createCommand($QUERY, [':player_id'=>$this->id])->queryAll();
  		return $targets;
    }

    public function ban()
    {
      $ban=new \app\modules\frontend\models\BannedPlayer;
      $ban->old_id=$this->id;
      $ban->username=$this->username;
      $ban->email=$this->email;
      $ban->registered_at=$this->created;
      $ban->banned_at=new \yii\db\Expression('NOW()');
      if($ban->save() && $this->delete())
        return true;
      return false;

    }
}
