<?php

error_reporting(E_ALL);
date_default_timezone_set('Europe/Paris');

header("Access-Control-Allow-Origin: *");

# http://xisbn.worldcat.org/webservices/xid/isbn/9782754101967?method=getMetadata&format=json&fl=*

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_sqlite',
        'path'   => __DIR__.'/../db/livres.db',
        'memory' => false
)));

$app->get('/', function() use($app) {
    $livres = array();
    try {
        $livres = $app['db']->fetchAll('select * from livres');
    } catch (Exception $e) {
        $app->json(array('status' => $e), 500);
    }
    return $app['twig']->render('index.twig', array('livres' => $livres));
});

$app->get('/api/books/initdb/', function() use($app) {
    $sql = <<<EOL
CREATE TABLE IF NOT EXISTS livres (
id INTEGER PRIMARY KEY DESC
, title
, author
, publisher
, year
, isbn
);
EOL;
    try {
        $app['db']->query($sql);
        return $app->json(array('status' => 'ok'));
    } catch (Exception $e) {
        return $app->json(array('status' => $e), 500);
    }
});

$app->get('/api/books/new/', function(Request $request) use($app) {
    $isbn = $request->get('isbn');
    $title = $request->get('title');
    $author = $request->get('author');
    $publisher = $request->get('publisher');
    $year = $request->get('year');

    if(empty($author)
       || empty($isbn)
       || empty($publisher)
       || empty($title)
       || empty($year))
        return $app->json(array('status' => 'missing params'), 400);

    try {
        $app['db']->insert('livres'
                           , array('author'        => $author
                                   , 'isbn'        => $isbn
                                   , 'publisher' => $publisher
                                   , 'title'       => $title
                                   , 'year'        => $year));
        return $app->json(array('status' => 'insert'));
    } catch (Exception $e) {
        return $app->json(array('status' => $e));
    }
});

$app->run();