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
 * @package smartyPlugins
 * @copyright © OXID eSales AG 2003-2009
 * $Id: modifier.oxmultilangassign.php 13723 2008-10-26 22:59:58Z alfonsas $
 */

/*
* Smarty function
* -------------------------------------------------------------
* Purpose: Output multilang string for admin
* add [{ oxmultilang ident="..." }] where you want to display content
* -------------------------------------------------------------
*/
function smarty_modifier_oxmultilangassign($sIdent)
{    $myConfig = oxConfig::getInstance();

    static $aLangCache = null;

    $iLang = oxLang::getInstance()->getTplLanguage();

    if ( !isset( $iLang ) ) {
        $iLang = oxLang::getInstance()->getBaseLanguage();
        if ( !isset( $iLang ) )
            $iLang = 0;
    }

    if ( !isset( $sIdent ) )
        $sIdent = "IDENT MISSING";

    $sSourceFile  = $myConfig->getLanguagePath("lang.php",isAdmin(),$iLang);
    $sSourceFile2 = $myConfig->getLanguagePath("cust_lang.php",isAdmin(),$iLang);

    $sText = "<b>ERROR : Translation for $sIdent not found in $sSourceFile or $sSourceFile2!</b>";

    if( !$aLangCache[$iLang])
    {   require( $sSourceFile);
        $aLangCache[$iLang] = $aLang;

        if(is_file($sSourceFile2)){
            require( $sSourceFile2);
            $aLangCache[$iLang] = array_merge($aLangCache[$iLang], $aLang);
        }
    }

    if( isset( $aLangCache[$iLang][$sIdent])){
        $sText = $aLangCache[$iLang][$sIdent];
    }

    return $sText;
}
