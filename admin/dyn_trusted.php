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
 * @copyright © OXID eSales AG 2003-2008
 * $Id: dyn_trusted.php 14020 2008-11-06 13:36:42Z arvydas $
 */


/**
 * Admin dyn trusted manager.
 * @package admin
 * @subpackage dyn
 */
class dyn_trusted extends Shop_Config
{
    /**
     * Creates shop object, passes shop data to Smarty engine and returns name of
     * template file "dyn_trusted.tpl".
     *
     * @return string
     */
    public function render()
    {
        parent::render();
        $this->_aViewData['oxid'] = $this->getConfig()->getShopId();

        $aIds = $this->_aViewData["confaarrs"]['iShopID_TrustedShops'];

        $this->_aViewData["aShopID_TrustedShops"] = $aIds;
        $this->_aViewData["alllang"] = oxLang::getInstance()->getLanguageNames();

        return "dyn_trusted.tpl";
    }

    /**
     * Saves changed shop configuration parameters.
     *
     * @return mixed
     */
    public function save()
    {
        $myConfig = $this->getConfig();
        $aConfStrs  = oxConfig::getParameter( "aShopID_TrustedShops");
        $blSave = true;
        foreach ( $aConfStrs as $sConfStrs) {
            if ( $sConfStrs ) {
                if ( strlen( $sConfStrs ) != 33 || substr( $sConfStrs, 0, 1 ) != 'X' ) {
                    $this->_aViewData["errorsaving"] = 1;
                    $blSave = false;
                }
            }
        }
        if ( $blSave ) {
            $myConfig->saveShopConfVar( "aarr", 'iShopID_TrustedShops', serialize($aConfStrs), $myConfig->getShopId() );
        }
    }

    /**
     *
     */
    public function getViewId()
    {
        return 'dyn_interface';
    }
}
