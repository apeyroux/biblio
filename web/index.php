<?php

error_reporting(E_ALL);
date_default_timezone_set('Europe/Paris');

header("Access-Control-Allow-Origin: *");

# http://xisbn.worldcat.org/webservices/xid/isbn/9782754101967?method=getMetadata&format=json&fl=*

require_once __DIR__.'/../vendor/autoload.php';
$app['debug'] = true;

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_sqlite',
        'path'   => './db/livres.db',
        'memory' => false
)));

$app->get('/', function() use($app) {
    return $app['twig']->render('index.twig');
});

$app->get('/api/books/new/{isbn}/', function($isbn) use($app) {
    return $app->json(array('isbn' => $isbn));
});

$app->run();