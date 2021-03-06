Kayako
======
[![Build Status](https://travis-ci.org/shivamdixit/kayako.svg?branch=master)](https://travis-ci.org/shivamdixit/kayako)

Fetch and display tweets which are re-tweeted at-least once and contains hashtag #custserv.

### Features

* View older tweets through infinite scroll
* Redis cache support for faster loading
* All the previous viewed tweets are cached in Redis database. If requested tweets are newer or older than cached tweets then only fetch the tweets from the API. Otherwise request is served from cache.

### Dependencies

* Redis server is MUST
* PHP must be running on 64bit machine

### Instructions

* Extract the downloaded file
* Move it to your document root
* Run ``composer update`` from command line
* Add your API secrets in ``app/config/config.sample.php``
* Rename the file to ``config.php``
* Run redis-server with default config
* Open browser and navigate to localhost
* All the tweets will be displayed

### Contact

For any queries contact shivamd001 [at] gmail [dot] com
