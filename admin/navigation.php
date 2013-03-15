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
 * @package admin
 * @copyright © OXID eSales AG 2003-2009
 * $Id: navigation.php 14757 2008-12-16 09:00:48Z arvydas $
 */

/**
 * Administrator GUI navigation manager class.
 * @package admin
 */
class Navigation extends oxAdminView
{
    /**
     * Executes parent method parent::render(), generates menu HTML code,
     * passes data to Smarty engine, returns name of template file "nav_frame.tpl".
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $sItem = oxConfig::getParameter( "item");
        if ( !isset( $sItem) || !$sItem ) {
            $sItem = "nav_frame.tpl";

            $aFavorites = oxConfig::getParameter( "favorites");
            if(is_array($aFavorites)) {
                oxUtilsServer::getInstance()->setOxCookie('oxidadminfavorites',implode('|',$aFavorites));
            }

        } else {
            // set menu structure
            $this->_aViewData["menustructure"] =  $this->getNavigation()->getDomXml()->documentElement->childNodes;

            // version patch strin
            $sVersion = str_replace( array("EE.","PE."), "", $this->_sShopVersion);
            $this->_aViewData["sVersion"] =  trim($sVersion);

            // favorite navigation
            $aFavorites = explode('|',oxUtilsServer::getInstance()->getOxCookie('oxidadminfavorites'));

            if(is_array($aFavorites) && count($aFavorites)) {
                 $this->_aViewData["menufavorites"] = $this->getNavigation()->getListNodes($aFavorites);
                 $this->_aViewData["aFavorites"]    = $aFavorites;
            }

            // history navigation
            $aHistory = explode('|',oxUtilsServer::getInstance()->getOxCookie('oxidadminhistory'));
            if(is_array($aHistory) && count($aHistory)) {
                $this->_aViewData["menuhistory"] = $this->getNavigation()->getListNodes($aHistory);
            }

            // open history node ?
            $this->_aViewData["blOpenHistory"] = oxConfig::getParameter( 'openHistory' );
        }

        $oShoplist = oxNew( 'oxshoplist' );
        $oBaseShop = $oShoplist->getBaseObject();

        $sWhere = '';
        $blisMallAdmin = oxSession::getVar( 'malladmin' );
        if ( !$blisMallAdmin) {
            // we only allow to see our shop
            $sShopID = oxSession::getVar( "actshop" );
            $sWhere = "where oxshops.oxid = '$sShopID'";
        }

        $oShoplist->selectString( "select ".$oBaseShop->getSelectFields()." from " . $oBaseShop->getViewName() . " $sWhere" );
        $this->_aViewData['shoplist'] = $oShoplist;

        return $sItem;
    }

    /**
     * destroy session, redirects to admin login
     *
     * @return null
     */
    public function logout()
    {
        $mySession = $this->getSession();
        $myConfig  = $this->getConfig();

        $oUser = oxNew( "oxuser" );
        $oUser->logout();

        // dodger - Task #1364 — Logout-Button
        // store
        $sSID = $mySession->getId();

        // kill session
        $mySession->destroy();

        // delete also, this is usually not needed but for security reasons we execute still
        if ( $myConfig->getConfigParam( 'blAdodbSessionHandler' ) ) {
            $sSQL = "delete from oxsessions where SessionID = '$sSID'";
            oxDb::getDb()->Execute( $sSQL);
        }

        oxUtils::getInstance()->redirect( 'index.php' );
    }
}
