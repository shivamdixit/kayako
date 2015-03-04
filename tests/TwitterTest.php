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

class TwitterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object of Twitter class
     *
     * @var \Kayako\Twitter
     */
    protected $twitterObject;

    /**
     * Setup the environment before running tests
     */
    public function setUp()
    {
        $this->twitterObject = new \Kayako\Twitter();
    }

    /**
     * To test the protected method using reflection method
     *
     * @param string $name Name of the class
     * @return ReflectionMethod
     */
    protected static function getMethod($name)
    {
        $class = new ReflectionClass('\\Kayako\\Twitter');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Test if the maxId is processed as per int size
     */
    public function testMaxIdIsProcessed()
    {
        $processMaxId = self::getMethod('processMaxId');
        $newId = $processMaxId->invokeArgs(new \Kayako\Twitter(), array(1));
        if (PHP_INT_SIZE === 8) {
            $this->assertEquals(0, $newId);
        } else {
            $this->assertEquals('1', $newId);
        }
    }

    /**
     * Test if the tweets are fetched
     */
//    public function testTweetsAreFetched()
//    {
//        $result = $this->twitterObject->fetchTweets();
//        $this->assertTrue($result['status']);
//    }
}
