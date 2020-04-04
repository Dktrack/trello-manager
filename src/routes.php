<?php

use Slim\Http\Request;
use Slim\Http\Response;

use App\TrelloManager;
use App\TrelloTools;
use App\Actions\UI\UI_Home;
// Routes

$app->get('/cron', TrelloManager::class . ':doCron');

$app->get('/', UI_Home::class );


