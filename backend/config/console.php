<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'mailer' => [
          'class' => 'yii\swiftmailer\Mailer',
          'useFileTransport' => true,
          'viewPath' => '@app/mail/layouts',
          'transport' => [
              'class' => 'Swift_SmtpTransport',
              'host' => 'smtp.gmail.com',
              'username' => 'username',
              'password' => 'password',
              'port' => '587',
              'encryption' => 'tls',
              'streamOptions' => [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
              ],
          ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;