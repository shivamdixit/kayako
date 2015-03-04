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
     * @var string $hashtag the tag to search for
     */
    private $hashtag;

    /**
     * Relative path of the API
     *
     * @var string
     */
    private $path;

    /**
     * Maximum records to be fetched at a time from twitter
     *
     * @var int $count Default value is 100
     */
    private $count;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->twitterOAuth = (new Authenticate())->getConnection();
        $this->hashtag = '#custserv';
        $this->path = 'search/tweets';
        $this->count = 100;
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
     * Function to fetch tweets based on the parameter specified
     *
     * @param int $maxId Max ID of the tweet
     * @return array of objects containing tweets
     */
    public function fetchTweets($maxId = null)
    {
        // Contains the final result of the call
        $result = array();

        // To remove redundancy, decrease one from maxId
        // if 64bit int is supported
        $maxId = $this->processMaxId($maxId);

        try {
            // Make the API call using given params
            $tweets = $this->twitterOAuth->get(
                $this->path,
                array(
                    'q' => $this->hashtag,
                    'count' => $this->count,
                    'max_id' => $maxId
                )
            );

            // status => true implies successfully fetched
            $result['status'] = true;
            $result['tweets'] = array();

            // Loop through all the tweets
            foreach ($tweets->statuses as $value ) {
                // Extract tweets having more than one RT
                if ($value->retweet_count >= 1) {
                    // Push the tweets in the final result
                    array_push($result['tweets'], $value);
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
