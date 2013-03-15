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
 * $Id: function.oxmultilang.php 13723 2008-10-26 22:59:58Z alfonsas $
 */

/*
* Smarty function
* -------------------------------------------------------------
* Purpose: Output multilang string
* add [{ oxmultilang ident="..." }] where you want to display content
* -------------------------------------------------------------
*/
function smarty_function_oxmultilang($params, &$smarty)
{    $myConfig = oxConfig::getInstance();

    if ( isAdmin() )
    {
        static $aLangCache = array();

        $iLang = oxLang::getInstance()->getTplLanguage();

        if ( !isset( $iLang ) )
            $iLang = 0;

        if( isset( $params['ident']))
            $sIdent = $params['ident'];
        else
            $sIdent = "IDENT MISSING";

        $sSourceFile  = $myConfig->getLanguagePath('lang.php', true, $iLang);
		$sSourceFile2 = $myConfig->getLanguagePath('cust_lang.php', true, $iLang);
		 
        if (!isset($aLangCache[$iLang])) {   
        	require( $sSourceFile);
            $aLangCache[$iLang] = $aLang;
            require( $sSourceFile2);
            $aLangCache[$iLang] = array_merge($aLangCache[$iLang], $aLang);
        }

        $aCurrCache = &$aLangCache[$iLang];
        if ( isset( $aCurrCache[$sIdent]))
            return $aCurrCache[$sIdent];

        if ( !isset( $params['noerror'] ) && $params['noerror'] ) {
            return '<b>ERROR : Translation for '.$sIdent.' not found in '.$sSourceFile.' !</b>';
        } else {
            return $sIdent;
        }
    }
    else
    {
        if( isset( $params['ident']))
            $sIdent = $params['ident'];
        else
            $sIdent = "IDENT MISSING";
        try{
            return oxLang::getInstance()->translateString($sIdent);
        }catch(oxLanguageException $oEx){
            // is thrown in debug mode and has to be caught here, as smarty hangs otherwise!
            return $sIdent;
        }
    }
    return "";
}
