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

namespace Oui\Player {

    abstract class Provider
    {
        /**
         * The value provided through the 'play'
         * attribute value of the plugin tag.
         *
         * @var string
         */

        protected static $provider;

        /**
         * The value provided through the 'play'
         * attribute value of the plugin tag.
         *
         * @var string
         */

        protected $play;

        /**
         * Infos.
         *
         * @var array
         */

        protected $infos;

        /**
         * Associative array attributes in use and their values.
         *
         * @var array
         */

        protected $config;

        /**
         * Associative array of 'play' value(s) and their.
         *
         * @var array
         * @example
         * protected static $patterns = array(
         *     'video' => array(
         *         'scheme' => '#^(http|https)://(www\.)?(youtube\.com/(watch\?v=|embed/|v/)|youtu\.be/)(([^&?/]+)?)#i',
         *         'id'     => '5',
         *         'glue'   => '&amp;',
         *     ),
         *     'list'  => array(
         *         'scheme' => '#^(http|https)://(www\.)?(youtube\.com/(watch\?v=|embed/|v/)|youtu\.be/)[\S]+list=([^&?/]+)?#i',
         *         'id'     => '5',
         *         'prefix' => 'list=',
         *     ),
         * );
         *
         * Where 'video' and 'list' are used to define the 'type' key of the $infos property
         * when an URL match the regular expression defined as the 'scheme'.
         * 'id' stores the index of the string to get from the matches.
         * If set, the 'glue' key allows to test multiple schemes and stick ID's with its value.
         * 'prefix' can defines an ID prefix.
         */

        protected static $patterns = array();

        /**
         * The player base path.
         *
         * @var string
         */

        protected static $src;

        /**
         * URL of a script to embed.
         *
         * @var string
         */

        protected static $script;

        /**
         * Whether the script is already embed or not.
         *
         * @var bool
         */

        protected static $scriptEmbedded = false;

        /**
         * Default player size.
         *
         * @var array
         */

        protected static $dims = array(
            'width'    => array(
                'default' => '640',
            ),
            'height'   => array(
                'default' => '',
            ),
            'ratio'    => array(
                'default' => '16:9',
            ),
        );

        /**
         * Player parameters and related options/values.
         *
         * @var array
         * @example
         * protected static $params = array(
         *     'size'  => array(
         *         'default' => 'large',
         *         'force'   => true,
         *         'valid'   => array('large', 'small'),
         *     ),
         * );
         *
         * Where 'size' is a player parameter and 'large' is its default value.
         * 'force' allows to set the parameter even if its value is the default one.
         * The 'valid' key accept an array of values or a type of values as an HTML input type.
         */

        protected static $params = array();

        /**
         * Strings sticking different player URL parts.
         *
         * @var array
         */

        protected static $glue = array('/', '?', '&amp;');

        /**
         * Caches the class instance.
         *
         * @var object
         */

        private static $instance = null;

        /**
         * Singleton.
         */

        public static function getInstance()
        {
            $class = get_called_class();

            if (!isset(self::$instance[$class])) {
                self::$instance[$class] = new static();
            }

            return self::$instance[$class];
        }

        /**
         * Constructor.
         */

        protected function __construct()
        {
            self::setProvider();
        }

        protected function getResponsive() {
            $att = $this->getConfig('responsive');

            return $att ? $att === 'true' ? true : false : get_pref('oui_player_responsive') === 'true';
        }

        /**
         * Gets the play property.
         *
         * @throws \Exception
         */

        public function setPlay($value, $fallback = false)
        {
            $this->play = $value;
            $infos = $this->getInfos();

            if (!$infos || !array_key_exists($value, $infos)) {
                $this->setInfos($fallback);
            }

            return $this;
        }

        /**
         * Gets the play property.
         */

        protected function getPlay()
        {
            return explode(', ', $this->play);
        }

        /**
         * Gets the play property.
         */

        public function setConfig($value)
        {
            $this->config = $value;

            return $this;
        }

        protected function getConfig($att = null)
        {
            return $att ? $this->config[$att] : $this->config;
        }

        protected static function setProvider()
        {
            static::$provider = substr(strrchr(get_called_class(), '\\'), 1);
        }

        /**
         * Gets the class name as the provider name.
         */

        public static function getProvider()
        {
            self::setProvider();

            return array(static::$provider);
        }

        protected static function getScript($wrap = false)
        {
            if (isset(static::$script)) {
                return $wrap ? '<script src="' . static::$script . '"></script>' : static::$script;
            }

            return false;
        }

        protected static function getScriptEmbedded()
        {
            return static::$scriptEmbedded;
        }

        protected static function getDims()
        {
            return static::$dims;
        }

        protected static function getParams()
        {
            return static::$params;
        }

        protected static function getPatterns()
        {
            return static::$patterns;
        }

        protected static function getSrc()
        {
            return static::$src;
        }

        protected static function getGlue($i = null)
        {
            return $i ? static::$glue[$i] : static::$glue;
        }

        protected static function setGlue($i, $value)
        {
            static::$glue[$i] = $value;
        }

        /**
         * Embeds the provider script.
         */

        public function embedScript()
        {
            if ($ob = ob_get_contents()) {
                ob_clean();

                echo str_replace(
                    '</body>',
                    self::getScript(true) . n . '</body>',
                    $ob
                );
            }
        }

        /**
         * Collects provider prefs.
         *
         * @param  array $prefs Prefs collected provider after provider.
         * @return array Collected prefs merged with ones already provided.
         */

        public static function getPrefs($prefs)
        {
            $merge_prefs = array_merge(self::getDims(), self::getParams());

            foreach ($merge_prefs as $pref => $options) {
                $options['group'] = strtolower(str_replace('\\', '_', get_called_class()));
                $pref = $options['group'] . '_' . $pref;
                $prefs[$pref] = $options;
            }

            return $prefs;
        }

        /**
         * Get tag attributes.
         *
         * @param  string $tag      The plugin tag.
         * @param  array  $get_atts Stores attributes provider after provider.
         * @return array  Attributes
         */

        public static function getAtts($tag, $get_atts)
        {
            $atts = array_merge(self::getDims(), self::getParams());

            foreach ($atts as $att => $options) {
                $att = str_replace('-', '_', $att);
                $get_atts[$att] = '';
            }

            return $get_atts;
        }

        /**
         * Sets the current media(s) infos.
         *
         * @return array The current media(s) infos.
         */

        public function setInfos($fallback = false)
        {
            $this->infos = array();

            foreach ($this->getPlay() as $play) {
                $notId = preg_match('/([.][a-z]+)/', $play); // URL or filename.

                if ($notId) {
                    $glue = null;

                    foreach (self::getPatterns() as $pattern => $options) {
                        if (preg_match($options['scheme'], $play, $matches)) {
                            $prefix = isset($options['prefix']) ? $options['prefix'] : '';

                            if (!array_key_exists($play, $this->infos)) {
                                $this->infos[$play] = array(
                                    'play' => $prefix . $matches[$options['id']],
                                    'type' => $pattern,
                                );

                                // Bandcamp and Youtube accept multiple matches.
                                if (!isset($options['glue'])) {
                                    break;
                                } else {
                                    $glue = $options['glue'];
                                }
                            } else {
                                $this->infos[$play]['play'] .= $glue . $prefix . $matches[$options['id']];
                                $this->infos[$play]['type'] = $pattern;
                            }
                        }
                    }
                } elseif ($fallback) {
                    $this->infos[$play] = array(
                        'play' => $play,
                        'type' => 'id',
                    );
                }

                if (method_exists($this, 'resetGlue') && array_key_exists($play, $this->infos)) {
                    $this->resetGlue($play);
                }
            }


            return $this;
        }

        /**
         * Gets the infos property; set it if necessary.
         *
         * @return array An associative array of
         */

        public function getInfos()
        {
            return $this->infos;
        }

        /**
         * Gets player parameters in in use
         * from the plugin tag attributes
         * or from the plugin prefs.
         *
         * @return array Parameters and their values.
         */

        protected function getPlayerParams()
        {
            $config = $this->getConfig();
            $params = array();

            foreach (self::getParams() as $param => $infos) {
                $pref = get_pref(strtolower(str_replace('\\', '_', get_class($this))) . '_' . $param);
                $default = $infos['default'];
                $att = str_replace('-', '_', $param);
                $value = isset($config[$att]) ? $config[$att] : '';

                // Adds attributes values in use or modified prefs values as player parameters.
                if ($value === '' && ($pref !== $default || isset($infos['force']))) {
                    // Removes # from the color pref as a color type is used for the pref input.
                    $params[] = $param . '=' . str_replace('#', '', $pref);
                } elseif ($value !== '') {
                    // Removes the # in the color attribute just in case…
                    $params[] = $param . '=' . str_replace('#', '', $value);
                }
            }

            return $params;
        }

        /**
         * Gets the player size
         * from the plugin tag attributes
         * or from the plugin prefs.
         *
         * @return array Player size.
         */

        protected function getSize()
        {
            $config = $this->getConfig();

            foreach (self::getDims() as $dim => $infos) {
                $pref = get_pref(strtolower(str_replace('\\', '_', get_class($this))) . '_' . $dim);
                $att = isset($config[$dim]) ? $config[$dim] : '';

                if ($att === true || $att === 'false') {
                    $$dim = 0;
                } elseif ($att) {
                    $$dim = $att;
                } else {
                    $$dim = $pref;
                }
            }

            if (!empty($ratio)) {
                // Works out the aspect ratio.
                preg_match("/(\d+):(\d+)/", $ratio, $matches);

                if ($matches && $matches[1]!=0 && $matches[2]!=0) {
                    $aspect = $matches[1] / $matches[2];
                    $pourcent = 1 / $aspect * 100 . '%';
                } else {
                    trigger_error(gtxt('invalid_player_ratio'));
                }
            }

            $responsive = $this->getResponsive();

            if ($responsive) {
                if (!empty($ratio)) {
                    $width = $height = '100%';
                } elseif ($width) {
                    if (!empty($height)) {
                        preg_match("/(\D+)/", $width, $widthUnit);
                        preg_match("/(\D+)/", $height, $heightUnit);

                        if ($widthUnit && $heightUnit && $widthUnit === $heightUnit || !$widthUnit && !$heightUnit) {
                            $pourcent = $height / $width * 100 . '%';
                            $width = $height = '100%';
                        } elseif ($width === '100%' && !$heightUnit) {
                            $pourcent = $height . 'px';
                        }
                    } else {
                        $width = '100%';
                    }
                }
            } else {
                if (!$width && empty($height)) {
                    if ($responsive && !empty($ratio)) {
                        $width = '100%';
                        $aspect = $width / $aspect;
                    } else {
                        trigger_error(gtxt('undefined_player_size'));
                    }
                } elseif (!$width || isset($height) && !$height) {
                    if ($ratio) {
                        // Calculates the new width/height.
                        if ($width) {
                            $height = $width / $aspect;
                            // Has unit?
                            preg_match("/(\D+)/", $width, $unit);
                            // Adds unit if it exists.
                            isset($unit[0]) ? $height .= $unit[0] : '';
                        } else {
                            $width = $height * $aspect;
                            preg_match("/(\D+)/", $height, $unit);
                            isset($unit[0]) ? $width .= $unit[0] : '';
                        }
                    } else {
                        trigger_error(gtxt('undefined_player_size'));
                    }
                }
            }

            return compact('width', 'height', 'pourcent');
        }

        /**
         * Whether a provided URL to play matches a provider URL scheme or not.
         *
         * @return bool
         */

        public function isValid()
        {
            return $this->getInfos();
        }

        protected function getPlaySrc()
        {
            $play = $this->getInfos()[$this->getPlay()[0]]['play'];
            $glue = self::getGlue();
            $src = self::getSrc() . $glue[0] . $play;
            $params = $this->getPlayerParams();

            if (!empty($params)) {
                $joint = strpos($src, $glue[1]) ? $glue[2] : $glue[1];
                $src .= $joint . implode($glue[2], $params);
            }

            return $src;
        }

        /**
         * Generates the player.
         *
         * @return string HTML
         */

        public function getPlayer($wraptag = null, $class = null)
        {
            if (self::getScript() && !self::getScriptEmbedded()) {
                register_callback(array($this, 'embedScript'), 'textpattern_end');
                static::$scriptEmbedded = true;
            }

            $src = $this->getPlaySrc();
            $dims = $this->getSize();

            extract($dims);

            $style = '';
            $wrapstyle = '';

            if ($this->getResponsive()) {
                $style .= ' style="position: absolute; top: 0; left: 0" ';
                $wrapstyle .= 'style="position: relative; padding-bottom:' . $pourcent . '; height: 0; overflow: hidden"';
                $wraptag or $wraptag = 'div';
            }

            $player = sprintf(
                '<iframe src="%s" width="%s" height="%s"%s%s></iframe>',
                $this->getPlaySrc(),
                $width,
                $height,
                $style,
                ' frameborder="0" allowfullscreen'
            );

            return ($wraptag) ? doTag($player, $wraptag, $class, $wrapstyle) : $player;
        }
    }
}
