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
 * $Id: oxutilsstring.php 13617 2008-10-24 09:38:46Z sarunas $
 */

/**
 * String manipulation class
 */
class oxUtilsString
{
    /**
     * oxUtils class instance.
     *
     * @var oxutils* instance
     */
    private static $_instance = null;

    /**
     * Returns string manipulation utility instance
     *
     * @return oxUtilsString
     */
    public static function getInstance()
    {
        // disable caching for test modules
        if ( defined( 'OXID_PHP_UNIT' ) ) {
            static $inst = array();
            self::$_instance = $inst[oxClassCacheKey()];
        }

        if ( !self::$_instance instanceof oxUtilsString ) {


            self::$_instance = oxNew( 'oxUtilsString' );

            if ( defined( 'OXID_PHP_UNIT' ) ) {
                $inst[oxClassCacheKey()] = self::$_instance;
            }
        }
        return self::$_instance;
    }


    /**
     * Prepares passed string for CSV format
     *
     * @param string $sInField String to prepare
     *
     * @return string
     */
    public function prepareCSVField($sInField)
    {
        if (strstr($sInField, '"'))
            return '"'.str_replace('"', '""', $sInField).'"';
        elseif (strstr($sInField, ';'))
            return '"'.$sInField.'"';
        return $sInField;
    }

     /**
     * shortens a string to a size $iLenght, whereby "," and multiple spaces are removed
     * "," is rerplaced with " " and leading and ending whitespaces are removed
     *
     * @param string $sString input string
     * @param int    $iLength maximum length of result string , -1 -> no truncation
     *
     * @return string a string of maximum length $iLength without multiple spaces and commas
     */
    public function minimizeTruncateString( $sString, $iLength )
    {
        $sString = str_replace( ",", " ", $sString );
        //leading and ending whitesapces
        $sString = ereg_replace( "^[ \t\n\r\v]+|[ \t\n\r\v]+$", "", $sString );
        //multiple whitespaces
        $sString = ereg_replace( "[ \t\n\r\v]+", " ", $sString );
        if ( strlen( $sString ) > $iLength && $iLength != -1 ) {
            $sString = substr( $sString, 0, $iLength );
        }
        return $sString;
    }

    /**
     * Prepares and returns string for search engines.
     *
     * @param string $sSearchStr String to prepare for search engines
     *
     * @return string
     */
    public function prepareStrForSearch($sSearchStr)
    {
        if ( preg_match( "/(ä|ö|ü|Ä|Ö|Ü|ß|(&amp;))/", $sSearchStr ) ) {
            return str_replace( array('ä',      'ö',      'ü',      'Ä',      'Ö',      'Ü',      'ß',       '&amp;' ),
                                array('&auml;', '&ouml;', '&uuml;', '&Auml;', '&Ouml;', '&Uuml;', '&szlig;', '&' ),
                                $sSearchStr);
        }

        return '';
    }
}
