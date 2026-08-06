<?php

require_once __DIR__ . '/config/google.php';

$authUrl = $client->createAuthUrl();



header("Location: " . $authUrl);


exit;
