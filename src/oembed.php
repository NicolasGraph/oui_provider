<?php

/*
 * This file is part of oui_player_provider,
 * an extendable plugin to easily embed
 * customizable players in Textpattern CMS.
 *
 * https://github.com/NicolasGraph/oui_player_provider
 *
 * Copyright (C) 2018 Nicolas Morand
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA..
 */

/**
 * Provider
 *
 * @package Oui\Player
 */

namespace Oui\Player;

abstract class Oembed extends Provider
{
    /**
     * JSON related API endpoint.
     * It should just need the URL to be appended.
     *
     * @var string
     */

    protected static $endPoint = 'https://vimeo.com/api/oembed.json?url=';

    /**
     * Provider URL
     *
     * @var string
     */

    protected static $URLBase = 'http://vimeo.com/';

    /**
     * OEmbed data
     *
     * @var string
     */

    protected $data;

    /**
     * $endPoint getter.
     */

    protected static function getEndPoint()
    {
        return static::$endPoint;
    }

    /**
     * $URLBase getter.
     */

    protected static function getURLBase()
    {
        return static::$URLBase;
    }

    /**
     * Build media URL.
     */

    protected function getMediaURL()
    {
        return self::getURLBase() . $this->getMediaInfos()[$this->getMedia()]['uri'];
    }

    /**
     * $data setter
     */

    protected function setData()
    {
        $this->data = json_decode(file_get_contents(self::getEndPoint() . $this->getMediaURL()));
    }

    /**
     * $data unsetter
     */

    protected function unsetData()
    {
        $this->data = null;
    }

    /**
     * $data getter
     */

    protected function getData($name)
    {
        $this->data or $this->setData();

        return $this->data->$name;
    }
}
