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
namespace Kayako;

/**
 * Class Redis
 * Contains all the functions related to database CRUD operations
 * along with helper functions
 *
 * @package Kayako
 */
class Redis
{
    /**
     * Object to interact with Redis server
     *
     * @var \Predis\Client
     */
    private $client;

    /**
     * Key for 'set' while storing tweets in Redis
     *
     * @var string
     */
    private $tweetKey;

    /**
     * Constructor
     *
     * @param string $key Key for Redis 'set'
     */
    public function __construct($key = null)
    {
        if ($key === null) {
            $key = 'tweet';
        }
        $this->client = new \Predis\Client();
        $this->tweetKey = $key;
    }

    /**
     * Function to add tweet to the database
     *
     * @param int $tweetId id of the tweet to be stored
     * @param array $data Array containing following data
     *      - id_str  id of the tweet in string
     *      - retweet_count  number of time it has been RT
     *      - screen_name  name of tweet owner
     *      - text  tweet content
     * @return array
     */
    public function addTweet($tweetId = null, $data = null)
    {
        if ($tweetId === null || $data === null) {
            return array(
                'status' => false,
                'error' => 'Required parameter missing'
            );
        }

        $this->client->zadd($this->tweetKey, array($tweetId => $tweetId));
        $this->client->hmset($this->tweetKey . ':' . $tweetId, $data);

        return array('status' => true, 'message' => 'Successfully added');
    }

    /**
     * Function to get least tweet id from the database
     *
     * @return int minimum tweet id
     */
    public function getMinTweetId()
    {
        if ($this->getTotalTweetCount() === 0) {
            return 0;
        } else {
            return $this->client->zrange($this->tweetKey, 0, 0)[0];
        }
    }

    /**
     * Function to get max tweet id from the database
     *
     * @return int maximum tweet id
     */
    public function getMaxTweetId()
    {
        if ($this->getTotalTweetCount() === 0) {
            return 0;
        } else {
            return $this->client->zrevrange($this->tweetKey, 0, 0)[0];
        }
    }

    /**
     * Function to get total count of tweets
     *
     * @return int total count
     */
    public function getTotalTweetCount()
    {
        return $this->client->zcard($this->tweetKey);
    }

    /**
     * Function to get tweets from the database whose id
     * is less than given tweet id
     *
     * @param int $maxId to search tweets less than $maxId
     * @param int $count limit the records by $count
     * @return array containing tweets having id less than $maxId
     */
    public function getTweetsLessThan($maxId = null, $count = null)
    {
        $result = array();
        $maxId = $maxId ? $maxId - 1: '+inf';
        $tweets = $this->client->zrevrangebyscore(
            $this->tweetKey,
            $maxId,
            '-inf',
            array('limit' => array('offset' => 0, 'count' => $count))
        );

        $i = 0;
        foreach ($tweets as $tweet) {
            $result[$i++] = $this->client->hgetall($this->tweetKey . ':' . $tweet);
        }

        return $result;
    }

    /**
     * Function to flush the database
     */
    public function clear()
    {
        $tweets = $this->client->zrangebyscore($this->tweetKey, '-inf', '+inf');
        foreach ($tweets as $tweet) {
            $this->client->del($this->tweetKey . ':' . $tweet);

        }

        $this->client->del($this->tweetKey);
    }

    /**
     * Function to delete a given tweet
     *
     * @param int $tweetId id of tweet which is to be deleted
     */
    public function deleteTweet($tweetId)
    {
        $this->client->zrem($this->tweetKey, $tweetId);
        $this->client->del($this->tweetKey . ':' . $tweetId);
    }
}
