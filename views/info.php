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
 * @copyright © OXID eSales AG 2003-2008
 * $Id: info.php 13614 2008-10-24 09:36:52Z sarunas $
 */

/**
 * Shop information manager.
 * Renders and displays default or passed by URL template.
 */
class Info extends oxUBase
{
    /**
     * Delivery list
     * @var object
     */
    protected $_oDelList = null;

    /**
     * Delivery set list
     * @var object
     */
    protected $_oDelSetList = null;

    /**
     * Current view search engine indexing state:
     *     0 - index without limitations
     *     1 - no index / no follow
     *     2 - no index / follow
     */
    protected $_iViewIndexState = 1;

    /**
     * Class constructor, assigns template file name passed by URL
     * or stored in session ("tpl", "infotpl"), or sets default
     * ("impressum.tpl").
     *
     * Template variables:
     * <b>tpl</b>
     *
     * Session variables:
     * <b>infotpl</b>
     */
    public function info()
    {
        // assign template name
        $sTplName = oxConfig::getParameter( 'tpl');
        $sTplName = $sTplName?$sTplName:oxSession::getVar( 'infotpl' );

        if ( $sTplName ) {
            // security fix so that you cant access files from outside template dir
            $sTplName = basename( $sTplName );
            oxSession::setVar( 'infotpl', $sTplName );
        } else {
            $sTplName = 'impressum.tpl';
        }

        $this->_sThisTemplate = $sTplName;
    }


    /**
     * Loads delivery, deliveryset list info and returns name of template file
     * to render info::_sThisTemplate.
     *
     * Template variables:
     * <b>deliverylist</b>, <b>deliverysetlist</b>
     *
     * @return  string  $this->_sThisTemplate   current template file name
     */
    public function render()
    {
        parent::render();

        $this->_aViewData['tpl'] = $this->getTemplateName();

        $this->_aViewData['deliverylist']    = $this->getDeliveryList();
        $this->_aViewData['deliverysetlist'] = $this->getDeliverySetList();;

        return $this->_sThisTemplate;
    }

    /**
     * Template variable getter. Returns this template name
     *
     * @return string
     */
    public function getTemplateName()
    {
        return $this->_sThisTemplate;
    }

    /**
     * Template variable getter. Returns delivery list
     *
     * @return object
     */
    public function getDeliveryList()
    {
        if ( $this->_oDelList === null ) {
            $this->_oDelList = oxNew( 'oxdeliverylist' );
            $this->_oDelList->getList();
        }
        return $this->_oDelList;
    }

    /**
     * Template variable getter. Returns delivery set list
     *
     * @return object
     */
    public function getDeliverySetList()
    {
        if ( $this->_oDelSetList === null ) {
            $this->_oDelSetList = oxNew( 'oxdeliverysetlist' );
            $this->_oDelSetList->getList();
        }
        return $this->_oDelSetList;
    }
}
