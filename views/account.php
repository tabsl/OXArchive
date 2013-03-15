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
 * $Id: account.php 14012 2008-11-06 13:23:45Z arvydas $
 */

/**
 * Current user "My account" window.
 * When user is logged in arranges "My account" window, by creating
 * links to user details, order review, notice list, wish list. There
 * is a link for logging out. Template includes Topoffer , bargain
 * boxes. OXID eShop -> MY ACCOUNT.
 */
class Account extends oxUBase
{
    /**
     * Number of user's orders.
     * @var integer
     */
    protected $_iOrderCnt = null;

    /**
     * Current article id.
     * @var string
     */
    protected $_sArticleId = null;

    /**
     * Search parameter for Html
     * @var string
     */
    protected $_sSearchParamForHtml = null;

    /**
     * Search parameter
     * @var string
     */
    protected $_sSearchParam = null;

    /**
     * Searched category
     * @var string
     */
    protected $_sSearchCatId = null;

    /**
     * Searched vendor
     * @var string
     */
    protected $_sSearchVendor = null;

    /**
     * List type
     * @var string
     */
    protected $_sListType = null;

    /**
     * Current class template name.
     * @var string
     */
    protected $_sThisTemplate = 'account_main.tpl';

    /**
     * Current class login template name.
     * @var string
     */
    protected $_sThisLoginTemplate = 'account_login.tpl';

    /**
     * Current view search engine indexing state:
     *     0 - index without limitations
     *     1 - no index / no follow
     *     2 - no index / follow
     */
    protected $_iViewIndexState = 1;

    /**
     * Loads action articles. If user is logged and returns name of
     * template to render account::_sThisTemplate
     *
     * Template variables:
     * <b>searchparam</b>, <b>searchparamforhtml</b>,
     * <b>searchcnid</b>, <b>searchvendor</b>, <b>listtype</b>
     *
     * @return  string  $_sThisTemplate current template file name
     */
    public function render()
    {
        parent::render();

        // loading actions
        $this->_loadActions();

        //
        if ( $sArtID = oxConfig::getParameter('aid') ) {
            $this->_aViewData['aid'] = $this->getArticleId();
            // #1834M - specialchar search
            $this->_aViewData['searchparam']        = $this->getSearchParam();
            $this->_aViewData['searchparamforhtml'] = $this->getSearchParamForHtml();
            $this->_aViewData['searchcnid']         = $this->getSearchCatId();
            $this->_aViewData['searchvendor']       = $this->getSearchVendor();
            $this->_aViewData['listtype']           = $this->getListType();
        }

        // is logged in ?
        $oUser = $this->getUser();
        if ( !$oUser || $oUser->oxuser__oxpassword->value == '' ) {
            return $this->_sThisTemplate = $this->_sThisLoginTemplate;
        }

        // calculating amount of orders made by user
        $this->_aViewData['iordersmade'] = $this->getOrderCnt();

        return $this->_sThisTemplate;
    }

    /**
     * Builds string with parameters for noredirect.
     *
     * @return string $sReturn parameter string for url
     */
    public function login_noredirect()
    {
        $sReturn = $this->_oaComponents['oxcmp_user']->login_noredirect();

        $sArtID = oxConfig::getParameter( 'aid' );
        if ( $sArtID ) {
            $sReturn = "details?anid=$sArtID";

            $sCatId = oxConfig::getParameter( 'cnid' );
            if ( $sCatId ) {
                $sReturn .= "&cnid=$sCatId";
            }
            // #1834M - specialchar search
            $sSearchParamForLink = rawurlencode( oxConfig::getParameter( 'searchparam', true ) );
            if ( $sSearchParamForLink ) {
                $sReturn .= "&searchparam=$sSearchParamForLink";
            }

            $sSearchCatId = oxConfig::getParameter( 'searchcnid' );
            if ( $sSearchCatId ) {
                $sReturn .= "&searchcnid=$sSearchCatId";
            }

            $sSearchVendor = oxConfig::getParameter( 'searchvendor' );
            if ( $sSearchVendor ) {
                $sReturn .= "&searchvendor=$sSearchVendor";
            }

            $sListType = oxConfig::getParameter( 'listtype' );
            if ( $sListType ) {
                $sReturn .= "&listtype=$sListType";
            }
        }

        return $sReturn;
    }

    /**
     * changes default template for compare in popup
     *
     * @return null
     */
    public function getOrderCnt()
    {
        if ( $this->_iOrderCnt === null ) {
            $this->_iOrderCnt = 0;
            if ( $oUser = $this->getUser() ) {
                $this->_iOrderCnt = $oUser->getOrderCount();
            }
        }
        return $this->_iOrderCnt;
    }

    /**
     * Return the active article id
     *
     * @return string | bool
     */
    public function getArticleId()
    {
        if ( $this->_sArticleId === null) {
            // passing wishlist information
            if ( $sArticleId = oxConfig::getParameter('aid') ) {
                $this->_sArticleId = $sArticleId;
            }
        }
        return $this->_sArticleId;
    }

    /**
     * Template variable getter. Returns search parameter for Html
     *
     * @return string
     */
    public function getSearchParamForHtml()
    {
        if ( $this->_sSearchParamForHtml === null ) {
            $this->_sSearchParamForHtml = false;
            if ( $this->getArticleId() ) {
                $this->_sSearchParamForHtml = oxConfig::getParameter( 'searchparam' );
            }
        }
        return $this->_sSearchParamForHtml;
    }

    /**
     * Template variable getter. Returns search parameter
     *
     * @return string
     */
    public function getSearchParam()
    {
        if ( $this->_sSearchParam === null ) {
            $this->_sSearchParam = false;
            if ( $this->getArticleId() ) {
                $this->_sSearchParam = rawurlencode( oxConfig::getParameter( 'searchparam', true ) );
            }
        }
        return $this->_sSearchParam;
    }

    /**
     * Template variable getter. Returns searched category id
     *
     * @return string
     */
    public function getSearchCatId()
    {
        if ( $this->_sSearchCatId === null ) {
            $this->_sSearchCatId = false;
            if ( $this->getArticleId() ) {
                $this->_sSearchCatId = rawurldecode( oxConfig::getParameter( 'searchcnid' ) );
            }
        }
        return $this->_sSearchCatId;
    }

    /**
     * Template variable getter. Returns searched vendor id
     *
     * @return string
     */
    public function getSearchVendor()
    {
        if ( $this->_sSearchVendor === null ) {
            $this->_sSearchVendor = false;
            if ( $this->getArticleId() ) {
                // searching in vendor #671
                $this->_sSearchVendor = rawurldecode( oxConfig::getParameter( 'searchvendor' ) );
            }
        }
        return $this->_sSearchVendor;
    }

    /**
     * Template variable getter. Returns list type
     *
     * @return string
     */
    public function getListType()
    {
        if ( $this->_sListType === null ) {
            $this->_sListType = false;
            if ( $this->getArticleId() ) {
                // searching in vendor #671
                $this->_sListType = oxConfig::getParameter( 'listtype' );
            }
        }
        return $this->_sListType;
    }
}
