<?php

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$client = new Google_Client();

$client->setClientId('294705059456-mv28is0muib181j507o51nn4u3uopour.apps.googleusercontent.com');
$client->setClientSecret('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');

$client->setRedirectUri(
    'https://puebla-conalep.net/callback.php'
);

$client->addScope('openid');
$client->addScope('email');
$client->addScope('profile');

$client->setAccessType('online');
$client->setPrompt('select_account');
