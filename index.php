<?php

/**
 * Include the composer autloader
 */
require 'vendor/autoload.php';

use \Slim\Slim;

/**
 * Register the slim auto loader classes
 */
\Slim\Slim::registerAutoloader();

/**
 * Create and config a new Slim object
 */
$app = new Slim(array(
    'debug' => false,
    'templates.path' => 'app/views'
));

/**
 * Route for home page
 */
$app->get('/', function() use ($app) {
    // Render the index.php view
    $app->render('index.php');
});

/**
 * Route for API request
 */
$app->get('/fetch', function() use($app) {
    // Extract the get parameters from request
    $maxId = $app->request()->get('max_id');

    $twitter = new \Kayako\Twitter();

    // Return tweets in json format on success
    echo json_encode((array)$twitter->fetchTweets($maxId));
});

/**
 * Run the application
 */
$app->run();
