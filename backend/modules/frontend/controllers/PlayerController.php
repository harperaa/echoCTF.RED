<?php

namespace app\modules\frontend\controllers;

use Yii;
use app\modules\frontend\models\ImportPlayerForm;
use app\modules\frontend\models\Player;
use app\modules\frontend\models\Team;
use app\modules\frontend\models\TeamPlayer;
use app\modules\frontend\models\PlayerSsl;
use app\modules\frontend\models\PlayerSearch;
use app\modules\settings\models\Sysconfig;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * PlayerController implements the CRUD actions for Player model.
 */
class PlayerController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
          'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['index','create','update','view'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'ban' => ['POST'],
                    'reset-playdata' => ['POST'],
                    'reset-player-progress' => ['POST'],
                    'toggle-academic' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Player models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PlayerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionGraphs()
    {
        $dataProvider=Yii::$app->db->createCommand("select count(*) as registrations,count(if(active=1,1,0)) as activations,date(created) as dateindex from player group by date(created) ORDER BY date(created) ASC")->queryAll();
        $treasures=Yii::$app->db->createCommand("select count(*) as claims,date(ts) as dateindex from player_treasure group by date(ts) ORDER BY date(ts) ASC")->queryAll();
        $categories=[];
        $registrations=[];
        $activations=[];
        foreach($dataProvider as $key => $rec)
        {
          $categories[]=$rec['dateindex'];
          $registrations[]=intval(@$rec['registrations']);
          $activations[]=intval(@$rec['activations']);
        }
        foreach($treasures as $key => $rec)
        {
          $dates[]=$rec['dateindex'];
          $claims[]=intval(@$rec['claims']);
        }
        return $this->render('graphs', [
            'registrations' => $registrations,
            'categories'=>$categories,
            'claims'=>$claims,
            'claimDates'=>$dates
        ]);
    }

    /**
     * Displays a single Player model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Player model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Player();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Imports Players from uploaded CSV file.
     * If import is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionImport()
    {
      $model = new ImportPlayerForm();

      if (Yii::$app->request->isPost) {
          $model->attributes=Yii::$app->request->post()["ImportPlayerForm"];
          $model->csvFile = UploadedFile::getInstance($model, 'csvFile');
          if ($model->upload() && $model->parseCSV())
          {
              $trans=Yii::$app->db->beginTransaction();
              try
              {
                $records=0;
                foreach($model->csvRecords as $rec)
                {
                  $p=new Player;
                  $p->username=$rec[0];
                  $p->fullname=$rec[0];
                  $p->email=$rec[0];
                  $p->academic=$rec[2] == 'no' ? false : true;
                  $p->save(false);
                  if(Team::find()->where( [ 'name' => $rec[1] ] )->exists())
                  {
                    $team=Team::find()->where( [ 'name' => $rec[1] ] )->one();
                    $tp=new TeamPlayer;
                    $tp->team_id=$team->id;
                    $tp->player_id=$p->id;
                    $tp->save();
                  }
                  else
                  {
                    $team=new Team;
                    $team->name=$rec[1];
                    $team->owner_id=$p->id;
                    $team->academic=$rec[2] == 'no' ? 0 : 1;
                    $team->save();
                  }
                  if($model->player_ssl=='1')
                  {
                    Yii::$app->params['dn']['commonName'] = $p->id;
                    // Generate a new private (and public) key pair
                    $privkey = openssl_pkey_new(Yii::$app->params['pkey_config']);

                    // Generate a certificate signing request
                    $csr = openssl_csr_new(Yii::$app->params['dn'], $privkey, array('digest_alg' => 'sha256', 'config'=>__DIR__ . '/../../../config/CA.cnf','encrypt_key'=>false));

                    // Generate a self-signed cert, valid for 365 days
                    $CAcert = "file:///tmp/echoCTF-OVPN-CA.crt";
                    $CAprivkey = array("file:///tmp/echoCTF-OVPN-CA.key",null);
                    file_put_contents('/tmp/echoCTF-OVPN-CA.key',Sysconfig::findOne('CA.key')->val);
                    file_put_contents('/tmp/echoCTF-OVPN-CA.crt',Sysconfig::findOne('CA.crt')->val);
                    $x509 = openssl_csr_sign($csr, $CAcert, $CAprivkey, 365, array('digest_alg'=>'sha256','config'=>__DIR__ . '/../../../config/CA.cnf','x509_extensions'=>'usr_cert'), time() );
                    openssl_csr_export($csr, $csrout);
                    openssl_x509_export($x509, $certout,false);
                    openssl_x509_export($x509, $crtout);
                    openssl_pkey_export($privkey, $pkeyout);
                    unlink('/tmp/echoCTF-OVPN-CA.key');
                    unlink('/tmp/echoCTF-OVPN-CA.crt');

                    if($p->playerSsl!==NULL)
                      $ps=$p->playerSsl;
                    else {
                      $ps=new PlayerSsl;
                      $ps->player_id=$p->id;
                    }
                    $ps->subject=serialize(Yii::$app->params['dn']);
                    $ps->csr=$csrout;
                    $ps->crt=$crtout;
                    $ps->txtcrt=$certout;
                    $ps->privkey=$pkeyout;
                    $ps->save();
                  }
                }
                $trans->commit();
                \Yii::$app->getSession()->setFlash('success', 'successful import of csv records');
              }
              catch (\Exception $e)
              {
                $trans->rollBack();
                if (isset($e->errorInfo) && $e->errorInfo[0]=="23000" && $e->errorInfo[1]==1062)
                  Yii::$app->session->setFlash('error', 'Failed to import file with error message ['.$e->errorInfo[2].']');
                else
                {
                  var_dump($e);
                  Yii::$app->session->setFlash('error', 'Failed to import file');
                }
              }
          }
      }

      return $this->render('import', ['model' => $model]);
    }

    public function actionResetPlayerProgress()
    {
      try {
        $result=\Yii::$app->db->createCommand("CALL reset_player_progress()")->execute();
        Yii::$app->session->setFlash('success', 'Successfully reseted all player progress');
      }
      catch (\Exception $e)
      {
        Yii::$app->session->setFlash('error', 'Failed to reset player progress');
      }
      return $this->redirect(['index']);

    }

    public function actionResetPlaydata()
    {
      try {
        $result=\Yii::$app->db->createCommand("CALL reset_playdata()")->execute();
        Yii::$app->session->setFlash('success', 'Successfully removed all player data');
      }
      catch (\Exception $e)
      {
        Yii::$app->session->setFlash('error', 'Failed to remove player data');
      }
      return $this->redirect(['index']);

    }

    /**
     * Updates an existing Player model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Player model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {

        if(($model = Player::findOne($id)) !== null && $model->delete())
          Yii::$app->session->setFlash('success',sprintf('Player [%s] deleted.',$model->username));
        else
          Yii::$app->session->setFlash('error','Player deletion failed.');
        return $this->redirect(['index']);
    }

    /**
     * Ban an existing Player model
     * If ban is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionBan($id)
    {
      $trans=Yii::$app->db->beginTransaction();
      try
      {
        if($this->findModel($id)->ban()) {
          $trans->commit();
          Yii::$app->session->setFlash('success','Player deleted and placed on banned table.');
        }
        else {
           new Exception('Faled to delete and ban player.');
        }
      }
      catch (Exception $e)
      {
        $trans->rollBack();
        Yii::$app->session->setFlash('error','Failed to ban player.');
      }
      if(Yii::$app->request->referrer){
        return $this->redirect(Yii::$app->request->referrer);
      }else{
        return $this->redirect(['index']);
      }
    }

    /**
     * Toggles an existing Player academic flag model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionToggleAcademic($id)
    {
        $model=$this->findModel($id);
        $model->updateAttributes(['academic' => !$model->academic]);

        return $this->redirect(['index']);
    }

    public function actionGenerateSsl($id) {
        $player=$this->findModel($id);
        Yii::$app->params['dn']['commonName'] = $player->id;
        Yii::$app->params['dn']['emailAddress']=$player->email;
        // Generate a new private (and public) key pair
        $privkey = openssl_pkey_new(Yii::$app->params['pkey_config']);

        // Generate a certificate signing request
        $csr = openssl_csr_new(Yii::$app->params['dn'], $privkey, array('digest_alg' => 'sha256', 'config'=>__DIR__ . '/../../../config/CA.cnf','encrypt_key'=>false));

        // Generate a self-signed cert, valid for 365 days
        $CAcert = "file:///tmp/echoCTF-OVPN-CA.crt";
        $CAprivkey = array("file:///tmp/echoCTF-OVPN-CA.key",null);
        file_put_contents('/tmp/echoCTF-OVPN-CA.key',Sysconfig::findOne('CA.key')->val);
        file_put_contents('/tmp/echoCTF-OVPN-CA.crt',Sysconfig::findOne('CA.crt')->val);
        $x509 = openssl_csr_sign($csr, $CAcert, $CAprivkey, 365, array('digest_alg'=>'sha256','config'=>__DIR__ . '/../../../config/CA.cnf','x509_extensions'=>'usr_cert'), time() );
        var_dump(openssl_error_string());
        openssl_csr_export($csr, $csrout);
        openssl_x509_export($x509, $certout,false);
        openssl_x509_export($x509, $crtout);
        openssl_pkey_export($privkey, $pkeyout);
        unlink('/tmp/echoCTF-OVPN-CA.key');
        unlink('/tmp/echoCTF-OVPN-CA.crt');

        if($player->playerSsl!==NULL)
          $ps=$player->playerSsl;
        else {
          $ps=new PlayerSsl;
          $ps->player_id=$player->id;
        }
        $ps->subject=serialize(Yii::$app->params['dn']);
        $ps->csr=$csrout;
        $ps->crt=$crtout;
        $ps->txtcrt=$certout;
        $ps->privkey=$pkeyout;
        if($ps->save())
        {
          Yii::$app->session->setFlash('success', "SSL Keys regenerated.");
          return $this->redirect(['/frontend/player/index']);
        }
        Yii::$app->session->setFlash('error', "Something went wrong with the SSL keys regeneration.");
        return $this->redirect(['/frontend/player/index']);
    }

    public function actionBanFiltered()
    {
      $searchModel = new PlayerSearch();
      $query = $searchModel->searchBan(['PlayerSearch'=>Yii::$app->request->post()]);
      $trans=Yii::$app->db->beginTransaction();
      try
      {
        $counter=$query->count();
        foreach($query->all() as $q)
          $q->ban();
        $trans->commit();
        Yii::$app->session->setFlash('success','[<code><b>'.intval($counter).'</b></code>] Users banned');

      }
      catch (Exception $e)
      {
        $trans->rollBack();
        Yii::$app->session->setFlash('error','Failed to ban users');
      }
      return $this->redirect(['index']);
    }
    /**
     * Finds the Player model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Player the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Player::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


      /*
        Mail Users their activation URL
      */
      public function actionMail(int $id, $baseURL="https://echoctf.red/activate/")
      {
        // Get innactive players
        $failedSend=$okSend=[];
        $player=$this->findModel($id);
        $event_name=Sysconfig::findOne('event_name')->val;
        // Generate activation URL
        $activationURL=sprintf("%s%s",$baseURL,$player->activkey);
        $content =   $this->renderPartial('_account_activation_email', ['player' => $player,'activationURL'=>$activationURL,'event_name'=>$event_name], true);
        if($this->mailPlayer($content,$player,'echoCTF RED re-sending of account activation URL'))
          Yii::$app->session->setFlash('success',"The user has been mailed.");
        else
          Yii::$app->session->setFlash('notice',"Failed to send mail to user.");

        return $this->goBack(Yii::$app->request->referrer);
      }

      private function mailPlayer($content,$player,$subject)
    	{
    	// Get mailer
        try {
          \Yii::$app->mailer->compose()
            ->setFrom([Sysconfig::findOne('mail_from')->val => Sysconfig::findOne('mail_fromName')->val])
            ->setTo([$player->email=>$player->username])
            ->setSubject($subject)
            ->setTextBody($content)
            ->send();
          }
      		catch(Exception $e)
      		{
      			return false;
      		}
      		return true;
    	}

}
