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
 * @package views
 * @copyright © OXID eSales AG 2003-2009
 * $Id: oxcmp_shop.php 13676 2008-10-25 13:12:52Z vilma $
 */

/**
 * Translarent shop manager (executed automatically), sets
 * registration information and current shop object.
 * @subpackage oxcmp
 */
class oxcmp_shop extends oxView
{
    /**
     * Marking object as component
     * @var bool
     */
    protected $_blIsComponent = true;

    /**
     * Executes parent::render() and returns active shop object.
     *
     * @return  object  $this->oActShop active shop object
     */
    public function render()
    {
        parent::render();

        $myConfig = $this->getConfig();
            $sShopLogo = $myConfig->getConfigParam( 'sShopLogo' );
            if ( $sShopLogo && file_exists( $myConfig->getAbsImageDir().'/'.$sShopLogo ) ) {
                $this->_oParent->setShopLogo($sShopLogo);
                // Passing to view. Left for compatibility reasons for a while. Will be removed in future
                $this->_oParent->addTplParam( 'shoplogo', $this->_oParent->getShopLogo() );
            }

        // is shop active?
        $oShop = $myConfig->getActiveShop();
        if ( !$oShop->oxshops__oxactive->value && 'oxstart' != $myConfig->getActiveView()->getClassName() && !$this->isAdmin() ) {
            $oEx = oxNew( 'oxShopException' );
            $oEx->setMessage( 'EXCEPTION_SHOP_NOTACTIVE' );
            throw $oEx;
        }

        return $oShop;
    }
}
