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

require __DIR__ . '/../../vendor/autoload.php';

/**
 * Class RedisTest
 * Contains tests for \Kayako\Redis class
 */
class RedisTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object of Redis class
     *
     * @var \Kayako\Twitter
     */
    protected $redisObject;

    /**
     * Setup the environment before running tests
     */
    public function setUp()
    {
        $this->redisObject = new \Kayako\Redis('tweet-test');

        // Add some dummy tweets
        $tweetId = 100;
        $data = array('id_str' => '100', 'text' => 'Foo', 'screen_name' => 'Bar', 'retweet_count' => '1');
        $this->redisObject->addTweet($tweetId, $data);

        $tweetId = 101;
        $data = array('id_str' => '101', 'text' => 'Baz', 'screen_name' => 'Foo', 'retweet_count' => '1');
        $this->redisObject->addTweet($tweetId, $data);

        $tweetId = 102;
        $data = array('id_str' => '102', 'text' => 'Bar', 'screen_name' => 'Baz', 'retweet_count' => '1');
        $this->redisObject->addTweet($tweetId, $data);
    }

    /**
     * Test minimum tweet id
     */
    public function testMinimumTweetId()
    {
        $this->assertEquals('100', $this->redisObject->getMinTweetId());
    }

    /**
     * Test maximum tweet id
     */
    public function testMaximumTweetId()
    {
        $this->assertEquals('102', $this->redisObject->getMaxTweetId());
    }

    /**
     * Test tweets less than a given id
     */
    public function testGetTweetsLesserThan()
    {
        $expected = array(
            array('id_str' => '100', 'text' => 'Foo', 'screen_name' => 'Bar', 'retweet_count' => '1'),
            array('id_str' => '101', 'text' => 'Baz', 'screen_name' => 'Foo', 'retweet_count' => '1')
        );

        $this->assertSame($expected, $this->redisObject->getTweetsLessThan(102));
    }

    /**
     * Test total tweet count
     */
    public function testTotalTweetCount()
    {
        $this->assertEquals(3, $this->redisObject->getTotalTweetCount());
    }

    /**
     * Clean up the database
     */
    public function tearDown()
    {
        $this->redisObject->deleteTweet(100);
        $this->redisObject->deleteTweet(101);
        $this->redisObject->deleteTweet(102);
    }
}
