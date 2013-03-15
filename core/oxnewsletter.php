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
 * $Id: oxnewsletter.php 14378 2008-11-26 13:59:41Z vilma $
 */

/**
 * Newsletter manager.
 * Performs creation of newsletter text, assign newsletter to user groups,
 * deletes and etc.
 * @package core
 */
class oxNewsletter extends oxBase
{
    /**
     * Smarty template engine object (default null).
     *
     * @var object
     */
    protected $_oSmarty = null;

    /**
     * Newsletter HTML format text (default null).
     *
     * @var string
     */
    protected $_sHtmlText = null;

    /**
     * Newsletter plaintext format text (default null).
     *
     * @var string
     */
    protected $_sPlainText = null;

    /**
     * User groups object (default null).
     *
     * @var object
     */
    protected $_oGroups = null;

    /**
     * User session object (default null).
     *
     * @var object
     */
    protected $_oUser = null;

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'oxnewsletter';

    /**
     * Class constructor, initiates Smarty engine object, parent constructor
     * (parent::oxBase()).
     */
    public function __construct()
    {
        parent::__construct();

        $this->init( 'oxnewsletter' );

        $this->_oSmarty = oxUtilsView::getInstance()->getSmarty();
        $this->_oSmarty->force_compile = true;
    }

    /**
     * Deletes object information from DB, returns true on success.
     *
     * @param string $sOxId object ID (default null)
     *
     * @return bool
     */
    public function delete( $sOxId = null )
    {
        if( !$sOxId) {
            $sOxId = $this->getId();
        }
        if( !$sOxId) {
            return false;
        }

        $blDeleted = parent::delete( $sOxId );

        if ( $blDeleted ) {
            $sDelete = "delete from oxobject2group where oxobject2group.oxshopid = '".$this->getShopId()."' and oxobject2group.oxobjectid = '$sOxId' ";
            oxDb::getDb()->execute( $sDelete );
        }

        return $blDeleted;
    }

    /**
     * Returns assigned user groups list object
     *
     * @return object $_oGroups
     */
    public function getGroups()
    {
        if ( isset( $this->_oGroups ) ) {
            return $this->_oGroups;
        }

        // usergroups
        $this->_oGroups = oxNew( "oxList", "oxgroups" );

        // performance
        $sSelect  = 'select oxgroups.* from oxgroups, oxobject2group ';
        $sSelect .= 'where oxobject2group.oxobjectid="'.$this->getId().'" ';
        $sSelect .= 'and oxobject2group.oxgroupsid=oxgroups.oxid ';
        $this->_oGroups->selectString( $sSelect );

        return $this->_oGroups;
    }

    /**
     * Returns assigned HTML text
     *
     * @return string
     */
    public function getHtmlText()
    {
        return $this->_sHtmlText;
    }

    /**
     * Returns assigned plain text
     *
     * @return string
     */
    public function getPlainText()
    {
        return $this->_sPlainText;
    }

    /**
     * Creates oxshop object and sets base parameters (such as currency and
     * language).
     *
     * @param string $sUserid          User ID or OBJECT
     * @param bool   $blPerfLoadAktion perform option load actions
     *
     * @return null
     */
    public function prepare( $sUserid, $blPerfLoadAktion = false )
    {
        // switching off admin
        $blAdmin = $this->isAdmin();
        $this->setAdminMode( false );

        $myConfig = $this->getConfig();

        $oShop = oxNew( 'oxshop' );
        $oShop->load( $myConfig->getShopId() );
        $oView = $myConfig->getActiveView();
        $oShop = $oView->addGlobalParams( $oShop );

        // add currency
        $this->_setUser( $sUserid );

        $this->_setParams( $oShop, $myConfig->getActShopCurrencyObject(), $blPerfLoadAktion );

        $this->setAdminMode( $blAdmin );
    }

    /**
     * Creates oxemail object, calls mail sending function (oxEMail::sendNewsletterMail()),
     * returns true on success (if mailing function was unable to complete, sets emailing to
     * user failure status in DB).
     *
     * @return bool
     */
    public function send()
    {
        $oxEMail = oxNew( 'oxemail' );
        $blSend = $oxEMail->sendNewsletterMail( $this, $this->_oUser );
        //print_r($oxEMail);
        // store failed info
        if ( !$blSend ) {
            oxDb::getDb()->Execute( "update oxnewssubscribed set oxemailfailed = '1' where oxemail = '".$this->_oUser->oxuser__oxusername->value."'");
        }

        return $blSend;
    }

    /**
     * Assigns to Smarty oxuser object, add newsletter products,
     * adds products which fit to the last order of
     * this user, generates HTML and plaintext format newsletters.
     *
     * @param object $oShop            Shop object
     * @param object $oCurrency        Currency object
     * @param bool   $blPerfLoadAktion perform option load actions
     *
     * @return null
     */
    protected function _setParams( $oShop, $oCurrency, $blPerfLoadAktion = false )
    {
        $this->_oSmarty->assign( 'myshop', $oShop );
        $this->_oSmarty->assign( 'shop', $oShop );
        $this->_oSmarty->assign( 'mycurrency', $oCurrency );
        $this->_oSmarty->assign( 'myuser', $this->_oUser );

        $this->_assignProducts( $blPerfLoadAktion );

        // set for smarty
        // actually I think this should solve our problems with newsletter
        // AFAIK the problem was that I always used the same name for fetching so smarty caches this template then
        $this->_oSmarty->oxidcache = clone $this->oxnewsletter__oxtemplate;
        $this->_sHtmlText  = $this->_oSmarty->fetch( 'ox:'.$this->oxnewsletter__oxid->value.'oxnewsletter__oxtemplate' );

        $this->_oSmarty->oxidcache = clone $this->oxnewsletter__oxplaintemplate;
        $this->_sPlainText = $this->_oSmarty->fetch( 'ox:'.$this->oxnewsletter__oxid->value.'oxnewsletter__oxplaintemplate' );
    }

    /**
     * Creates oxuser object (user ID passed to method),
     *
     * @param string $sUserid User ID or OBJECT
     *
     * @return null
     */
    protected function _setUser( $sUserid )
    {
        if ( is_string( $sUserid )) {
            $oUser = oxNew( 'oxuser' );
            if ( $oUser->load( $sUserid ) ) {
                $this->_oUser = $oUser;
            }
        } else
            $this->_oUser = $sUserid;   // we expect a full and valid user object
    }

    /**
     * Add newsletter products (#559 only if we have user we can assign this info),
     * adds products which fit to the last order of assigned user.
     *
     * @param bool $blPerfLoadAktion perform option load actions
     *
     * @return null
     */
    protected function _assignProducts( $blPerfLoadAktion = false )
    {
        if ( $blPerfLoadAktion ) {
            $oArtList = oxNew( 'oxarticlelist' );
            $oArtList->loadAktionArticles( 'OXNEWSLETTER' );
            $this->_oSmarty->assign( 'articlelist', $oArtList );
        }

        if ( $this->_oUser->getId() ) {
            $oArticle = oxNew( 'oxarticle' );
            $sArticleTable = $oArticle->getViewName();

            // add products which fit to the last order of this user
            $sSelect  = "select $sArticleTable.* from oxorder left join oxorderarticles on oxorderarticles.oxorderid = oxorder.oxid";
            $sSelect .= " left join $sArticleTable on oxorderarticles.oxartid = $sArticleTable.oxid";
            $sSelect .= " where ".$oArticle->getSqlActiveSnippet();
            $sSelect .= " and oxorder.oxuserid = '".$this->_oUser->oxuser__oxid->value."' order by oxorder.oxorderdate desc";

            $oArtList = oxNew( 'oxarticlelist' );
            $oArtList->selectString( $sSelect );

            $oOneArt   = null;
            $oOneOrder = null;
            $aSimList  = array();

            if ( $oArtList->count() ) {
                $oOneArt = $oArtList->current();
            }

            if ( $oOneArt ) {
                $oSimList = $oOneArt->getSimilarProducts();
            }

            if ( $oSimList && $oSimList->count() ) {
                $this->_oSmarty->assign( 'simlist', $oSimList );
                $iCnt = 0;
                foreach ( $oSimList as $oArt ) {
                    $sName = "simarticle$iCnt";
                    $this->_oSmarty->assign( $sName, $oArt );
                    $iCnt++;
                }
            }
        }
    }

    /**
     * Sets data field value
     *
     * @param string $sFieldName index OR name (eg. 'oxarticles__oxtitle') of a data field to set
     * @param string $sValue     value of data field
     * @param int    $iDataType  field type
     *
     * @return null
     */
    protected function _setFieldData( $sFieldName, $sValue, $iDataType = oxField::T_TEXT )
    {
        if ( 'oxtemplate' === $sFieldName || 'oxplaintemplate' === $sFieldName ) {
            $iDataType = oxField::T_RAW;
        }
        return parent::_setFieldData($sFieldName, $sValue, $iDataType);
    }
}
