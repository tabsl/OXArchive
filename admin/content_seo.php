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
 * $Id: content_seo.php 13619 2008-10-24 09:40:23Z sarunas $
 */

/**
 * Content seo config class
 */
class Content_Seo extends Object_Seo
{
    /**
     * Returns objects seo url
     * @param oxcontent $oContent active content object
     * @return string
     */
    protected function _getSeoUrl( $oContent )
    {
        $oEncoder = oxSeoEncoderContent::getInstance();
        $oEncoder->getContentUrl( $oContent );

        return parent::_getSeoUrl( $oContent );
    }

    /**
     * Returns seo object
     * @return oxcontent
     */
    protected function _getObject( $sOxid )
    {
        // load object
        $oContent = oxNew( 'oxcontent' );
        if ( $oContent->loadInLang( $this->_iEditLang, $sOxid ) ) {
            return $oContent;
        }
    }

    /**
     * Returns url type
     * @return string
     */
    protected function _getType()
    {
        return 'oxcontent';
    }

    /**
     * Returns objects std url
     * @return string
     */
    protected function _getStdUrl( $sOxid )
    {
        $oContent = oxNew( 'oxcontent' );
        $oContent->loadInLang( $this->_iEditLang, $sOxid );
        return $oContent->getLink();
    }
}