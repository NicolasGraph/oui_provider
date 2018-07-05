<?php

/*
 * This file is part of oui_provider,
 * an extendable plugin to easily embed
 * customizable players in Textpattern CMS.
 *
 * https://github.com/NicolasGraph/oui_provider
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

namespace Oui {

    abstract class Provider
    {
        /**
         * The provider name set from the class name.
         *
         * @var string
         * @see setProvider(), getProvider().
         */

        protected static $provider;

        /**
         * The value provided through the play attribute.
         *
         * @var string
         * @see setPlay(), getPlay().
         */

        protected $play;

        /**
         * @var array
         * @see setInfos(), getInfos().
         */

        protected $infos;

        /**
         * Attributes and their values.
         *
         * @var array
         * @see setConfig(), getConfig().
         */

        protected $config;

        /**
         * Associative array of different media types related values.
         * scheme: regex to check against a media URL/filename;
         * id: index of the media ID in the matches;
         * glue: an optional string to append to the first ID if multiple ID's can be macthed in the same URL;
         * prefix: an optional string to prepend to the current ID.
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
         * @see getPatterns(), setInfos().
         */

        protected static $patterns = array();

        /**
         * The player base path.
         *
         * @var string
         * @example '//www.youtube-nocookie.com/'
         * @see getSrc().
         */

        protected static $src;

        /**
         * URL of a script to embed.
         *
         * @var string
         * @example 'https://platform.vine.co/static/scripts/embed.js'
         * @see getScript(), embedScript(), $scriptEmbedded.
         */

        protected static $script;

        /**
         * Whether the script is already embed or not.
         *
         * @var bool
         * @see embedScript(), getScriptEmbedded().
         */

        protected static $scriptEmbedded = false;

        /**
         * Initial player size.
         *
         * @var array
         * @see getDims(), getSize().
         */

        protected static $dims = array(
            'width'  => '640',
            'height' => '',
            'ratio'  => '16:9',
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
         * @see getParams(), getPlayerParams().
         */

        protected static $params = array();

        /**
         * Strings sticking different player URL parts.
         *
         * @var array
         * @see setGlue(), getGlue(), resetGlue(), getPlaySrc().
         */

        protected static $glue = array('/', '?', '&amp;');

        /**
         * Constructor.
         * Set the $provider property.
         */

        public function __construct()
        {
            self::setProvider();
        }

        /**
         * $responsive property getter.
         *
         * @return bool
         */

        protected function getResponsive() {
            $att = $this->getConfig('responsive');

            return $att ? $att === 'true' ? true : false : get_pref('oui_player_responsive') === 'true';
        }

        /**
         * $play property setter.
         *
         * @return object $this.
         */

        public function setPlay($value, $fallback = false)
        {
            $this->play = $value;
            $this->getInfos($fallback);

            return $this;
        }

        /**
         * $play property getter.
         *
         * @return array
         */

        protected function getPlay()
        {
            return explode(', ', $this->play);
        }

        /**
         * $config property setter.
         *
         * @return object $this
         */

        public function setConfig($value)
        {
            $this->config = $value;

            return $this;
        }

        /**
         * $config property getter.
         *
         * @param  string $att Attribute name.
         * @return mixed       Attribute value or the $config full array.
         */

        protected function getConfig($att = null)
        {
            return $att ? $this->config[$att] : $this->config;
        }

        /**
         * $provider property setter.
         */

        protected static function setProvider()
        {
            static::$provider = strtolower(substr(strrchr(get_called_class(), '\\'), 1));
        }

        /**
         * $provider property getter.
         *
         * @return array
         */

        public static function getProvider()
        {
            self::setProvider();

            return static::$provider;
        }

        /**
         * $script property getter.
         *
         * @param  bool  $wrap Whether to wrap to embed the script URL in a script tag or not;
         * @return mixed       URL or HTML script tag.
         */

        protected static function getScript($wrap = false)
        {
            if (isset(static::$script)) {
                return $wrap ? '<script src="' . static::$script . '"></script>' : static::$script;
            }

            return false;
        }

        /**
         * $scriptEmbedded property getter.
         *
         * @return bool
         */

        protected static function getScriptEmbedded()
        {
            return static::$scriptEmbedded;
        }

        /**
         * $dims property getter.
         *
         * @return array
         */

        protected static function getDims()
        {
            return static::$dims;
        }

        /**
         * $params property getter.
         *
         * @return array
         */

        protected static function getParams()
        {
            return static::$params;
        }

        /**
         * $patterns property getter.
         *
         * @return array
         */

        protected static function getPatterns()
        {
            return array_key_exists('scheme', static::$patterns) ? array('undefined' => static::$patterns) : static::$patterns;
        }

        /**
         * $src property getter.
         *
         * @return string
         */

        protected static function getSrc()
        {
            return static::$src;
        }

        /**
         * $glue property getter.
         *
         * @param  integer $i     Index of the $glue value to get;
         * @return mixed          Value of the $glue item as string, or the $glue array.
         */

        protected static function getGlue($i = null)
        {
            return $i ? static::$glue[$i] : static::$glue;
        }

        /**
         * $glue property setter.
         *
         * @param integer $i     Index of the $glue value to set;
         * @param string  $value Value of the $glue item.
         */

        protected static function setGlue($i, $value)
        {
            static::$glue[$i] = $value;
        }

        /**
         * Embed the provider script.
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
         * Collect provider prefs.
         *
         * @param  array $prefs Prefs collected provider after provider.
         * @return array Collected prefs merged with ones already provided.
         */

        public static function getPrefs($prefs)
        {
            $merge_prefs = array_merge(self::getDims(), self::getParams());

            foreach ($merge_prefs as $pref => $options) {
                is_array($options) ?: $options = array('default' => $options);
                $options['group'] = Player::getPlugin() . '_' . self::getProvider();
                $prefs[$options['group'] . '_' . $pref] = $options;
            }

            return $prefs;
        }

        /**
         * Get a tag attributes.
         *
         * @param  string $tag      The plugin tag.
         * @param  array  $get_atts Stores attributes provider after provider.
         * @return array
         */

        public static function getAtts($tag, $get_atts)
        {
            $atts = array_merge(self::getDims(), self::getParams());

            // Replace any underscore with an hyphen.
            foreach ($atts as $att => $options) {
                $get_atts[str_replace('-', '_', $att)] = '';
            }

            return $get_atts;
        }

        /**
         * Set the current media(s) infos.
         *
         * @param  bool  $fallback Whether to set fallback $infos values or not.
         * @return array
         */

        public function setInfos($fallback = false)
        {
            $this->infos = array();

            foreach ($this->getPlay() as $play) {
                $notId = preg_match('/([.][a-z]+)/', $play); // URL or filename.

                if ($notId) {
                    $glue = null;

                    // Check the URL or filename against defined $patterns property values.
                    foreach (self::getPatterns() as $pattern => $options) {
                        if (preg_match($options['scheme'], $play, $matches)) {
                            $prefix = isset($options['prefix']) ? $options['prefix'] : '';

                            if (!array_key_exists($play, $this->infos)) {
                                $this->infos[$play] = array(
                                    'play' => $prefix . $matches[$options['id']],
                                    'type' => $pattern,
                                );

                                // Bandcamp and Youtube, et least, accept multiple matches.
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
         * Get the infos property.
         *
         * @return array
         */

        public function getInfos($fallback = false)
        {
            if (!$this->infos || !array_key_exists($this->getPlay()[0], $this->infos)) {
                $this->setInfos($fallback);
            }

            return $this->infos;
        }

        /**
         * Get the modified player parameters
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
                $pref = get_pref(Player::getPlugin() . '_' . self::getProvider() . '_' . $param);
                $default = is_array($infos) ? $infos['default'] : $infos;
                $att = str_replace('-', '_', $param);
                $value = isset($config[$att]) ? $config[$att] : '';

                // Add defined attribute values or modified preference values as player parameters.
                if ($value === '' && ($pref !== $default || isset($infos['force']))) {
                    $params[] = $param . '=' . str_replace('#', '', $pref); // Remove the hash from the color pref as a color type is used for the pref input.
                } elseif ($value !== '') {
                    $validArray = isset($infos['valid']) && is_array($infos['valid']) ? $infos['valid'] : '';

                    if (!$validArray || $validArray && in_array($value, $validArray)) {
                        $params[] = $param . '=' . str_replace('#', '', $value); // Remove the hash in the color attribute just in case…
                    } else {
                        trigger_error('Unknown attribute value for "' . $att . '". Valid values are: "' . implode('", "', $validArray) . '".');
                    }
                }
            }

            return $params;
        }

        /**
         * Get the player size.
         *
         * @return array Width and height keys and their values — Height could be not set (i.e. HTML5 audio player).
         */

        protected function getSize()
        {
            // Get dimensions from attributes, or fallback to preferences.
            $config = $this->getConfig();

            foreach (self::getDims() as $dim => $value) {
                $pref = get_pref(strtolower(str_replace('\\', '_', get_class($this))) . '_' . $dim);
                $att = isset($config[$dim]) ? $config[$dim] : '';

                if ($att === true || $att === 'false') {
                    $$dim = 0;
                } elseif ($att) {
                    $$dim = preg_replace('/\s+/', '', $att);
                } else {
                    $$dim = preg_replace('/\s+/', '', $pref);
                }
            }

            // Separate values and units.
            preg_match("/(\D+)/", $width, $wUnit);
            $wUnit ? $wUnit = $wUnit[0] : '';
            $width = (int) $width;

            if (isset($height)) {
                preg_match("/(\D+)/", $height, $hUnit);
                $hUnit ? $hUnit = $hUnit[0] : '';
                $height = (int) $height;
            }

            // Work out the provided ratio.
            if (!empty($ratio)) {
                preg_match("/(\d+):(\d+)/", $ratio, $matches);

                if ($matches && $matches[1] != 0 && $matches[2] != 0) {
                    $aspect = $matches[1] / $matches[2]; // Get the ratio as a decimal.
                } else {
                    trigger_error(gtxt('invalid_player_ratio'));
                }
            }

            // Calculate palyer width and/or height.
            $responsive = $this->getResponsive();

            if ($responsive) {
                if (!empty($ratio)) {
                    $height = 1 / $aspect * 100 . '%';
                    $width = '100%';
                } elseif (isset($height)) {
                    if ($width && $height) {
                        if ($wUnit && $hUnit && $wUnit === $hUnit || !$wUnit && !$hUnit) {
                            $height = $height / $width * 100 . '%';
                            $width = '100%';
                        }
                    } else {
                        trigger_error(gtxt('undefined_player_size'));
                    }
                } else {
                    $width = '100%';
                }
            } else {
                if (isset($height) && (!$width || !$height)) {
                    if ($ratio) {
                        if ($width) {
                            $height = $width / $aspect;
                            $wUnit ? $height .= $wUnit : '';
                        } else {
                            $width = $height * $aspect;
                            $hUnit ? $width .= $hUnit : '';
                        }
                    } else {
                        trigger_error(gtxt('undefined_player_size'));
                    }
                }
            }

            // Re-append unit if needed.
            is_int($width) && $wUnit && $wUnit !== 'px' ? $width .= $wUnit : '';

            if (isset($height)) {
                $responsive && !$hUnit ? $hUnit = 'px' : '';
                is_int($height) && $hUnit && ($responsive || $hUnit !== 'px') ? $height .= $hUnit : '';
            }

            return compact('width', 'height');
        }

        /**
         * Whether the $play property value is a provider related URL or not.
         *
         * @return bool
         */

        public function isValid()
        {
            return $this->getInfos();
        }

        /**
         * Build the player src value.
         *
         * @return string
         */

        protected function getPlaySrc()
        {
            if ($this->getPlay()[0]) {
                $play = $this->getInfos(true)[$this->getPlay()[0]]['play'];
                $glue = self::getGlue();
                $src = self::getSrc() . $glue[0] . $play; // Stick player URL and ID.

                // Stick defined player parameters.
                $params = $this->getPlayerParams();

                if (!empty($params)) {
                    $joint = strpos($src, $glue[1]) ? $glue[2] : $glue[1]; // Avoid repeated glue elements (interrogation marks).
                    $src .= $joint . implode($glue[2], $params); // Stick.
                }

                return $src;
            }

            trigger_error('Nothing to play');

            return false;
        }

        /**
         * Generate the player.
         *
         * @param  string $wraptag HTML wraptag name;
         * @param  string $class   Class name to apply to the wraptag.
         * @return HTML
         */

        public function getPlayer($wraptag = null, $class = null)
        {
            // Embed the provider related $script if needed.
            if (self::getScript() && !self::getScriptEmbedded()) {
                register_callback(array($this, 'embedScript'), 'textpattern_end');
                static::$scriptEmbedded = true;
            }

            $src = $this->getPlaySrc();

            if ($src) {
                $dims = $this->getSize();

                extract($dims);

                // Define responsive related styles.
                $responsive = $this->getResponsive();
                $style = 'style="border: none';
                $wrapstyle = '';

                if ($this->getResponsive()) {
                    $style .= '; position: absolute; top: 0; left: 0; width: 100%; height: 100%';
                    $wrapstyle .= 'style="position: relative; padding-bottom:' . $height . '; height: 0; overflow: hidden"';
                    $width = $height = false;
                    $wraptag or $wraptag = 'div';
                } else {
                    if (is_string($width)) {
                        $style .= '; width:' . $width;
                        $width = false;
                    }

                    if (is_string($height)) {
                        $style .= '; height:' . $height;
                        $height = false;
                    }
                }

                $style .= '"';

                // Build the player code.
                $player = sprintf(
                    '<iframe src="%s"%s%s %s %s></iframe>',
                    $src,
                    !$width ? '' : ' width="' . $width . '"',
                    !$height ? '' : ' height="' . $height . '"',
                    $style,
                    'allowfullscreen'
                );

                return ($wraptag) ? doTag($player, $wraptag, $class, $wrapstyle) : $player;
            }
        }

        public static function renderPlayer($atts)
        {
            return Player::renderPlayer(array_merge(array('provider' => self::getProvider()), $atts));
        }

        public static function renderIfPlayer($atts, $thing)
        {
            return Player::renderIfPlayer(array_merge(array('provider' => self::getProvider()), $atts), $thing);
        }
    }
}
