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
 * @copyright © OXID eSales AG 2003-2008
 * $Id: function.oxgetseourl.php 14410 2008-11-28 16:02:31Z arvydas $
 */


/*
* Smarty function
* -------------------------------------------------------------
* Purpose: output SEO style url
* add [{ oxgetseourl ident="..." }] where you want to display content
* -------------------------------------------------------------
*/
function smarty_function_oxgetseourl( $params, &$smarty )
{
    $sOxid = isset( $params['oxid'] ) ? $params['oxid'] : null;
    $sType = isset( $params['type'] ) ? $params['type'] : null;
    $sUrl  = isset( $params['ident'] ) ? $params['ident'] : null;

    // requesting specified object SEO url
    if ( $sOxid && $sType ) {
        $oObject = oxNew( $sType );
        if ( $oObject->load( $sOxid ) ) {
            $sUrl = $oObject->getLink();
        }
    } elseif ( $sUrl && oxUtils::getInstance()->seoIsActive() ) {
        // if SEO is on ..
        $oEncoder = oxSeoEncoder::getInstance();
        if ( ( $sStaticUrl = $oEncoder->getStaticUrl( $sUrl ) ) ) {
            $sUrl = $sStaticUrl;
        }
    }

    $sDynParams = isset( $params['params'] )?$params['params']:false;
    if ( $sDynParams ) {
        require_once $smarty->_get_plugin_filepath( 'modifier','oxaddparams' );
        $sUrl = smarty_modifier_oxaddparams( $sUrl, $sDynParams );
    }

    return $sUrl;
}
