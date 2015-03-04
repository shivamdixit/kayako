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

use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Class Authenticate
 *
 * Initializes the connection for making requests
 * to twitter API
 *
 * @package Kayako
 */
class Authenticate
{
    /**
     * Contains the sensitive API information
     *
     * @var mixed Array containing API secrets
     */
    private $config;

    /**
     * Constructor
     *
     * Initialize the $config variable
     */
    public function __construct()
    {
        $this->config = include(dirname(__FILE__). '/../config/config.php');
    }

    /**
     * Creates object to interact with twitter API
     *
     * @return TwitterOAuth connection object
     */
    public function getConnection()
    {
        return new TwitterOAuth(
            $this->config['customerKey'],
            $this->config['customerSecret'],
            $this->config['accessToken'],
            $this->config['accessTokenSecret']
        );
    }
}
