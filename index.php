<?php

/**
 * Copyright (c) 2014 Shivam Dixit <shivamd001@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * or (at your option) any later version, as published by the Free
 * Software Foundation
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details
 *
 * You should have received a copy of the GNU General Public
 * License along with this program; if not, write to the
 * Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

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
    'debug' => true,
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
    echo json_encode((array)$twitter->getTweets($maxId));
});

/**
 * Run the application
 */
$app->run();
