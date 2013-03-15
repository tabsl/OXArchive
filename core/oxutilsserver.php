<?php
/**
 *    This file is part of OXID eShop Community Edition.
 *
 *    OXID eShop Community Edition is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    OXID eShop Community Edition is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.oxid-esales.com
 * @package core
 * @copyright © OXID eSales AG 2003-2008
 * $Id: oxutilsserver.php 13617 2008-10-24 09:38:46Z sarunas $
 */

/**
 * Server data manipulation class
 */
class oxUtilsServer
{
    /**
     * oxUtils class instance.
     *
     * @var oxutils* instance
     */
    private static $_instance = null;

    /**
     * Returns server utils instance
     *
     * @return oxUtilsServer
     */
    public static function getInstance()
    {
        // disable caching for test modules
        if ( defined( 'OXID_PHP_UNIT' ) ) {
            static $inst = array();
            self::$_instance = $inst[oxClassCacheKey()];
        }

        if ( !self::$_instance instanceof oxUtilsServer ) {
            self::$_instance = oxNew( 'oxUtilsServer');
            if ( defined( 'OXID_PHP_UNIT' ) ) {
                $inst[oxClassCacheKey()] = self::$_instance;
            }
        }
        return self::$_instance;
    }

    /**
     * sets cookie
     *
     * @param string $sName   cookie name
     * @param string $sValue  value
     * @param int    $iExpire expire time
     * @param string $sPath   The path on the server in which the cookie will be available on
     *
     * @return bool
     */
    public function setOxCookie( $sName, $sValue = "", $iExpire = 0, $sPath = '/' )
    {
        //TODO: since setcookie takes more than just 4 params..
        // would be nice to have it sending through https only, if in https mode
        // or allowing only http access to cookie [no JS access - reduces XSS attack possibility]
        // ref: http://lt.php.net/manual/en/function.setcookie.php

        if ( defined('OXID_PHP_UNIT')) {
            // do NOT set cookies in php unit.
            return;
        }
        if ( $sPath !== null ) {
            return setcookie( $sName, $sValue, $iExpire, $sPath );
        } else {
            return setcookie( $sName, $sValue, $iExpire );
        }
    }

    /**
     * Returns cookie $sName value.
     * If optional parameter $sName is not set then getCookie() returns whole cookie array
     *
     * @param string $sName cookie param name
     *
     * @return string
     */
    public function getOxCookie( $sName = null )
    {
        if ( $sName && isset( $_COOKIE[$sName] ) ) {
            return oxConfig::checkSpecialChars($_COOKIE[$sName]);
        } elseif ( $sName && !isset( $_COOKIE[$sName] ) ) {
            return null;
        } elseif ( !$sName && isset( $_COOKIE ) ) {
            return $_COOKIE;
        }
        return null;
    }

    /**
     * Returns remote IP address
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        if ( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
            $sIP = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif ( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
            $sIP = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $sIP = $_SERVER["REMOTE_ADDR"];
        }
        return $sIP;
    }

    /**
     * returns a server constant
     *
     * @param string $sServVar optional - which server var should be returned, if null returns whole $_SERVER
     *
     * @return mixed
     */
    public function getServerVar( $sServVar = null )
    {
        if ( isset( $_SERVER ) ) {
            if ( $sServVar && isset( $_SERVER[$sServVar] ) ) {
                return $_SERVER[$sServVar];
            } elseif (!$sServVar) {
                return $_SERVER;
            }
        }
        return null;
    }

}
