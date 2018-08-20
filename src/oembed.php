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
    protected static $endPoint = 'https://vimeo.com/api/oembed.json?url=';

    protected static $URLBase = 'http://vimeo.com/';

    protected $data;

    protected static function getEndPoint()
    {
        return static::$endPoint;
    }

    protected static function getURLBase()
    {
        return static::$URLBase;
    }

    protected function getMediaURL()
    {
        return self::getURLBase() . $this->getMediaInfos()[$this->getMedia()]['uri'];
    }

    protected function setData()
    {
        $this->data = json_decode(file_get_contents(self::getEndPoint() . $this->getMediaURL()));
    }

    protected function unsetData()
    {
        $this->data = null;
    }

    protected function getData($name)
    {
        $this->data or $this->setData();

        return $this->data->$name;
    }
}
