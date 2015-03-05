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
 * Class Twitter
 *
 * Endpoint for fetching the tweets using the twitter API
 *
 * @package Kayako
 */
class Twitter
{
    /**
     * Object to interact with Twitter API
     *
     * @var \Abraham\TwitterOAuth\TwitterOAuth
     */
    private $twitterOAuth;

    /**
     * Contains the hashtag
     *
     * @var string $hashtag
     */
    private $hashtag;

    /**
     * Relative path of the API
     *
     * @var string
     */
    private $path;

    /**
     * Max number of records to be fetched from Twitter at a time
     *
     * @var int $count default value is 100
     */
    private $fetchCount;

    /**
     * Object to interact with Redis cache
     *
     * @var Redis
     */
    private $cache;

    /**
     * Max number of records to be fetched from cache at a time
     *
     * @var int default is 20
     */
    private $cacheCount;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->twitterOAuth = (new Authenticate())->getConnection();
        $this->hashtag = '#custserv';
        $this->path = 'search/tweets';
        $this->fetchCount = 100;
        $this->cache = new Redis();
        $this->cacheCount = 20;
    }

    /**
     * Processes parameter $maxId to reduce redundant tweets
     *
     * If the size of int on the machine is 8 bytes then
     * subtract one from it. Since maxId is 64bit, performing
     * subtraction on 32bit machine may lead to undefined behaviour.
     *
     * @param string $maxId The ID to be processed
     * @return mixed int/string depending upon the int size
     */
    protected function processMaxId($maxId)
    {
        return (PHP_INT_SIZE === 8 && !empty($maxId)) ? ($maxId - 1) : $maxId;
    }

    /**
     * Function to get tweets either from cache or using API
     * depending upon the parameters
     *
     * @param int $maxId to fetch all tweets below $maxId
     * @return array
     */
    public function getTweets($maxId = null)
    {
        $result = array();  // contains the final result of request

        // To remove redundancy, decrease one from maxId
        // if 64bit int is supported
        $maxId = $this->processMaxId($maxId);

        // Search the cache for tweets
        $tweets = $this->cache->getTweetsLessThan($maxId, $this->cacheCount);

        if ($maxId !== null) {
            if (count($tweets) === $this->cacheCount) {
                $result['status'] = true;
                $result['tweets'] = $tweets;
                $result['from'] = 'cache';
                return $result;
            } else {
                return $this->getResult(0, $maxId);
            }
        } else {
            return $this->getResult(1, $maxId);
        }
    }

    /**
     * Function to calculate result of request
     *
     * @param int $flag can be 0 or 1
     *      - 0     Fetch tweets which are newer than maximum id stored
     *      - 1     Fetch tweets which are older than minimum id stored
     *
     * @param int $maxId search tweets below it _i.e_ min id stored
     * @return array of result
     */
    private function getResult($flag, $maxId)
    {
        if ($flag === 1) {
            $fetchedTweets = $this->fetchAndCache(null, $this->cache->getMaxTweetId());
        } else {
            $fetchedTweets = $this->fetchAndCache($this->cache->getMinTweetId());
        }

        if ($fetchedTweets['status']) {
            $tweets = $this->cache->getTweetsLessThan($maxId, $this->cacheCount);
            $result['status'] = true;
            $result['tweets'] = $tweets;
        } else {
            $result['status'] = false;
            $result['error'] = $fetchedTweets['error'];
        }

        return $result;
    }

    /**
     * Function to fetch tweets based on the parameter specified
     *
     * @param int $maxId Max ID of the tweet
     * @param int $sinceId Request start from
     * @return array of tweets containing tweets
     */
    public function fetchAndCache($maxId = null, $sinceId = null)
    {
        // Contains the final result of the call
        $result = array();

        try {
            // Make the API call using given params
            $tweets = $this->twitterOAuth->get(
                $this->path,
                array(
                    'q' => $this->hashtag,
                    'count' => $this->fetchCount,
                    'max_id' => $maxId,
                    'since_id' => $sinceId
                )
            );

            // status => true implies successfully fetched
            $result['status'] = true;
            $result['tweets'] = array();

            // Loop through all the tweets
            foreach ($tweets->statuses as $value) {
                // Extract tweets having more than one RT
                if ($value->retweet_count >= 1) {
                    // Push the tweets in the final result
                    $tweetData = array(
                        'id_str' => $value->id_str,
                        'text' => $value->text,
                        'screen_name' => $value->user->screen_name,
                        'retweet_count' => $value->retweet_count
                    );
                    $this->cache->addTweet($value->id, $tweetData);
                    array_push($result['tweets'], $tweetData);
                }
            }

        } catch (\Exception $e) {
            // status => false implies unable to fetch
            $result['status'] = false;
            $result['error'] = $e->getMessage();
        }

        return $result;
    }
}
