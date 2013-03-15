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
 * $Id: details.php 14135 2008-11-11 13:54:45Z arvydas $
 */

/**
 * Article details information page.
 * Collects detailed article information, possible variants, such information
 * as crosselling, similarlist, picture gallery list, etc.
 * OXID eShop -> (Any chosen product).
 * @package main
 */
class Details extends oxUBase
{
    /**
     * List of article variants.
     *
     * @var array
     */
    protected $_aVariantList = array();

    /**
     * Current class default template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'details.tpl';

    /**
     * Current product parent article object
     *
     * @var oxarticle
     */
    protected $_oParent = null;

    /**
     * Marker if user can rate current product
     *
     * @var bool
     */
    protected $_blCanRate = null;

    /**
     * Marked which defines if current view is sortable or not
     * @var bool
     */
    protected $_blShowSorting = true;

    /**
     * If tags will be changed
     * @var bool
     */
    protected $_blEditTags = null;

    /**
     * All tags
     * @var array
     */
    protected $_aTags = null;

    /**
     * Tag cloud
     * @var array
     */
    protected $_sTagCloud = null;

    /**
     * Returns user recommlist
     * @var array
     */
    protected $_aUserRecommList = null;

    /**
     * Returns login form anchor
     * @var string
     */
    protected $_sLoginFormAnchor = null;

    /**
     * Class handling CAPTCHA image.
     * @var object
     */
    protected $_oCaptcha = null;

    /**
     * Media files
     * @var array
     */
    protected $_aMediaFiles = null;

    /**
     * History (last seen) products
     * @var array
     */
    protected $_aLastProducts = null;

    /**
     * Current product's vendor
     * @var object
     */
    protected $_oVendor = null;

    /**
     * Current product's category
     * @var object
     */
    protected $_oCategory = null;

    /**
     * Current product's attributes
     * @var object
     */
    protected $_aAttributes = null;

    /**
     * Parent article name
     * @var string
     */
    protected $_sParentName = null;

    /**
     * Parent article url
     * @var string
     */
    protected $_sParentUrl = null;

    /**
     * Picture gallery
     * @var array
     */
    protected $_aPicGallery = null;

    /**
     * Select lists
     * @var array
     */
    protected $_aSelectLists = null;

    /**
     * Reviews of current article
     * @var array
     */
    protected $_aReviews = null;

    /**
     * CrossSelling articlelist
     * @var object
     */
    protected $_oCrossSelling = null;

    /**
     * Similar products articlelist
     * @var object
     */
    protected $_oSimilarProducts = null;

    /**
     * Similar recommlists
     * @var object
     */
    protected $_oRecommList = null;

    /**
     * Accessoires of current article
     * @var object
     */
    protected $_oAccessoires = null;

    /**
     * List of customer also bought thies products
     * @var object
     */
    protected $_aAlsoBoughtArts = null;

    /**
     * Search title
     * @var string
     */
    protected $_sSearchTitle = null;

    /**
     * Returns current product parent article object if it is available
     *
     * @param string $sParentId parent product id
     *
     * @return oxarticle
     */
    protected function _getParentProduct( $sParentId )
    {
        if ( $sParentId && $this->_oParent === null ) {
            if ( ( $oParent = oxNewArticle( $sParentId ) ) ) {
                $this->_oParent = $oParent;
            } else {
                $this->_oParent = false;
            }
        }
        return $this->_oParent;
    }

    /**
     * loading full list of variants,
     * if we are child and do not have any variants then please load all parent variants as ours
     *
     * @return null
     */
    public function loadVariantInformation()
    {
        //loading full list of variants
        $this->_aVariantList = $this->_oProduct->getVariants();

        //if we are child and do not have any variants then please load all parent variants as ours
        if ( ( $oParent = $this->_getParentProduct( $this->_oProduct->oxarticles__oxparentid->value ) ) && count( $this->_aVariantList ) == 0 ) {
            $myConfig = $this->getConfig();

            $this->_aVariantList = $oParent->getVariants();

            //in variant list parent may be NOT buyable
            if ( $oParent->blNotBuyableParent ) {
                $oParent->blNotBuyable = true;
            }

            //lets additionally add parent article if it is sellable
            if ( $myConfig->getConfigParam( 'blVariantParentBuyable' ) ) {
                //#1104S if parent is buyable load selectlists too
                $oParent->aSelectlist = $oParent->getSelectLists();
                $this->_aVariantList = array_merge( array( $oParent ), $this->_aVariantList );
            }

            //..and skip myself from the list
            if ( isset( $this->_aVariantList[$this->_oProduct->getId()] ) ) {
                unset( $this->_aVariantList[$this->_oProduct->getId()] );
            }
        }

    }

    /**
     * Returns prefix ID used by template engine.
     *
     * @return  string  $this->_sViewID view id
     */
    public function getViewId()
    {
        if ( isset( $this->_sViewId )) {
            return $this->_sViewId;
        }

            $_sViewId = parent::getViewId().'|'.oxConfig::getParameter( 'anid' ).'|'.count( $this->_aVariantList ).'|';


        return $this->_sViewId = $_sViewId;
    }

    /**
     * Returns view reset id
     *
     * @return string
     */
    public function getViewResetId()
    {
        $sId  = parent::GetViewResetID();
        $sId .= '|anid='.$this->_oProduct->getId();
        if ( $this->_oProduct->oxarticles__oxparentid->value ) {
            $sId .= '|anid='.$this->_oProduct->oxarticles__oxparentid->value;
        }

        return $sId;
    }

    /**
     * Executes parent method parent::init() and newly loads article
     * object if users language was changed.
     *
     * @return null
     */
    public function init()
    {
        parent::init();

        $myConfig = $this->getConfig();
        $myUtils  = oxUtils::getInstance();

        //this option is only for lists and we must reset value
        //as blLoadVariants = false affect "ab price" functionality
        $myConfig->setConfigParam( 'blLoadVariants', true );

        $sOxid = oxConfig::getParameter( 'anid' );

        // object is not yet loaded
        if ( !$this->_oProduct || ($this->_oProduct && $this->_oProduct->getId() == $sOxid  ) ) {
            $this->_oProduct = oxNew( 'oxarticle' );

            /*
            //skipping Ab price?
            $blForceSkipAbPrice = true; //#1795
            if ( !$myConfig->getConfigParam( 'blVariantParentBuyable' )) {
                $blForceSkipAbPrice = false; // #870A.
            }*/

            $this->_oProduct->setSkipAbPrice( true);

            if ( !$this->_oProduct->load( $sOxid ) ) {
                $myUtils->redirect( $myConfig->getShopHomeURL() );
            }

        }


        // assign template name
        if ( $this->_oProduct->oxarticles__oxtemplate->value ) {
            $this->_sThisTemplate = $this->_oProduct->oxarticles__oxtemplate->value;
        }

        if ( ( $sTplName = oxConfig::getParameter( 'tpl' ) ) ) {
            $this->_sThisTemplate = basename ( $sTplName );
        }

        $this->loadVariantInformation();

        if (oxConfig::getParameter( 'listtype' ) == 'vendor') {
            $this->_oProduct->setLinkType(1);
        }
    }

    /**
     * If possible loads additional article info (oxarticle::getCrossSelling(),
     * oxarticle::getAccessoires(), oxarticle::getReviews(), oxarticle::GetSimilarProducts(),
     * oxarticle::GetCustomerAlsoBoughtThisProducts()), forms variants details
     * navigation URLs
     * loads selectlists (oxarticle::GetSelectLists()), prerares HTML meta data
     * (details::_convertForMetaTags()). Returns name of template file
     * details::_sThisTemplate
     *
     * Template variables:
     * <b>product</b>, <b>ispricealarm</b>, <b>reviews</b>, <b>crossselllist</b>,
     * <b>accessoirelist</b>, <b>similarlist</b>, <b>customerwho</b>,
     * <b>variants</b>, <b>amountprice</b>, <b>selectlist</b>, <b>sugsucc</b>,
     * <b>meta_description</b>, <b>meta_keywords</b>, <b>blMorePic</b>,
     * <b>parent_url</b>, <b>draw_parent_url</b>, <b>parentname</b>, <b>sBackUrl</b>,
     * <b>allartattr</b>, <b>sSearchTitle</b>
     *
     * @return  string  $this->_sThisTemplate   current template file name
     */
    public function render()
    {
        $myConfig = $this->getConfig();

        //loading amount price list
        $this->_oProduct->loadAmountPriceInfo();

        // Passing to view. Left for compatibility reasons for a while. Will be removed in future
        $this->_aViewData['product'] = $this->getProduct();

        $this->_aViewData["aTags"]      = $this->getTags();
        $this->_aViewData["blEditTags"] = $this->getEditTags();
        startProfile("tagCloud");
        $this->_aViewData['tagCloud']   = $this->getTagCloud();
        stopProfile("tagCloud");

        $this->_aViewData['loginformanchor'] = $this->getLoginFormAnchor();

        $this->_aViewData['ispricealarm'] = $this->isPriceAlarm();

        $this->_aViewData['customerwho']       = $this->getAlsoBoughtThiesProducts();
        $this->_aViewData['accessoirelist']    = $this->getAccessoires();
        //$this->_aViewData['similarrecommlist'] = $this->getSimilarRecommLists();
        $this->_aViewData['similarlist']       = $this->getSimilarProducts();
        $this->_aViewData['crossselllist']     = $this->getCrossSelling();

        $this->_aViewData['variants'] = $this->getVariantList();

        $this->_aViewData['reviews'] = $this->getReviews();

        $this->_aViewData['selectlist'] = $this->getSelectLists();

        $sSource = strtolower($this->_oProduct->oxarticles__oxlongdesc->value);
        $sSource = str_replace( array( '<br>', '<br />', '<br/>' ), "\n", $sSource );
        $sSource = strip_tags( $sSource );

        $this->setMetaDescription( $this->_oProduct->oxarticles__oxtitle->value.' - '.$sSource, 200, true );
        if ( strlen( trim( $this->_oProduct->oxarticles__oxsearchkeys->value ) ) > 0 ) {
            $sSource = $this->_oProduct->oxarticles__oxsearchkeys->value.' '.$sSource;
        }
        $this->setMetaKeywords( $sSource );

        $this->_aViewData["actpicid"]  = $this->getActPictureId();
        $this->_aViewData["actpic"]    = $this->getActPicture();
        $this->_aViewData['blMorePic'] = $this->morePics();
        $this->_aViewData['ArtPics']   = $this->getPictures();
        $this->_aViewData['ArtIcons']  = $this->getIcons();
        $this->_aViewData['blZoomPic'] = $this->showZoomPics();
        $this->_aViewData['aZoomPics'] = $this->getZoomPics();
        $this->_aViewData['iZoomPic']  = $this->getActZoomPic();

        $this->_aViewData['parentname']      = $this->getParentName();
        $this->_aViewData['parent_url']      = $this->getParentUrl();
        $this->_aViewData['draw_parent_url'] = $this->drawParentUrl();

        $this->_aViewData['pgNr'] = $this->getActPage();

        parent::render();

        $this->_aViewData['allartattr'] = $this->getAttributes();

        // #1102C
        $this->_aViewData['oCategory'] = $this->getCategory();

        $this->_aViewData['oVendor'] = $this->getVendor();

        $this->_aViewData['aLastProducts'] = $this->getLastProducts();

        // #785A loads and sets locator data
        $oLocator = oxNew( 'oxlocator', $this->getListType() );
        $oLocator->setLocatorData( $this->_oProduct, $this );

        //media files
        $this->_aViewData['aMediaUrls'] = $this->getMediaFiles();

        if (in_array('oxrss_recommlists', $myConfig->getConfigParam( 'aRssSelected' )) && $this->getSimilarRecommLists()) {
            $oRss = oxNew('oxrssfeed');
            $this->addRssFeed($oRss->getRecommListsTitle($this->_oProduct), $oRss->getRecommListsUrl($this->_oProduct), 'recommlists');
        }


        //antispam
        $this->_aViewData['oCaptcha']     = $this->getCaptcha();
        $this->_aViewData['sSearchTitle'] = $this->getSearchTitle();
        $this->_aViewData['actCatpath']   = $this->getCatTreePath();

        return $this->_sThisTemplate;
    }

    /**
     * Checks if rating runctionality is on and allwed to user
     *
     * @return bool
     */
    public function canRate()
    {
        if ( $this->_blCanRate === null ) {

            $this->_blCanRate = false;
            $myConfig = $this->getConfig();

            if ( $myConfig->getConfigParam( 'bl_perfLoadReviews' ) &&
                 $oUser = $this->getUser() ) {

                $oRating = oxNew( 'oxrating' );
                $this->_blCanRate = $oRating->allowRating( $oUser->getId(), 'oxarticle', $this->_oProduct->getId() );
            }
        }
        return $this->_blCanRate;
    }

    /**
     * Saves user ratings and review text (oxreview object)
     *
     * @return null
     */
    public function saveReview()
    {
        $sReviewText = trim( ( string ) oxConfig::getParameter( 'rvw_txt' , true ) );
        $dRating     = oxConfig::getParameter( 'artrating' );
        if ($dRating < 0 || $dRating > 5) {
            $dRating = null;
        }

        $sArtId  = oxConfig::getParameter( 'anid' );
        $sUserId = oxSession::getVar( 'usr' );

        //save rating
        if ( $dRating ) {
            $oRating = oxNew( 'oxrating' );
            $blRate = $oRating->allowRating( $sUserId, 'oxarticle', $this->_oProduct->getId());
            if ( $blRate) {
                $oRating->oxratings__oxuserid = new oxField($sUserId);
                $oRating->oxratings__oxtype   = new oxField('oxarticle', oxField::T_RAW);
                $oRating->oxratings__oxobjectid = new oxField($sArtId);
                $oRating->oxratings__oxrating = new oxField($dRating);
                $oRating->save();
                $this->_oProduct->addToRatingAverage( $dRating);
            } else {
                $dRating = null;
            }
        }

        if ( $sReviewText ) {
            $oReview = oxNew( 'oxreview' );
            $oReview->oxreviews__oxobjectid = new oxField($sArtId);
            $oReview->oxreviews__oxtype = new oxField('oxarticle', oxField::T_RAW);
            $oReview->oxreviews__oxtext = new oxField($sReviewText, oxField::T_RAW);
            $oReview->oxreviews__oxlang = new oxField(oxLang::getInstance()->getBaseLanguage());
            $oReview->oxreviews__oxuserid = new oxField($sUserId);
            $oReview->oxreviews__oxrating = new oxField(( $dRating) ? $dRating : null);
            $oReview->save();
        }
    }

    /**
     * Show login template
     *
     * @return null
     */
    public function showLogin()
    {
        $this->_sThisTemplate = 'account_login.tpl';

        $sAnchor = oxConfig::getInstance()->getParameter("anchor");
        if ($sAnchor)
            $this->_sLoginFormAnchor = $sAnchor;
    }

    /**
     * Adds article to selected recommlist
     *
     * @return null
     */
    public function addToRecomm()
    {
        $sRecommText = trim( ( string ) oxConfig::getParameter( 'recomm_txt' ) );
        $sRecommList = oxConfig::getParameter( 'recomm' );
        $sArtId      = oxConfig::getParameter( 'anid' );

        if ( $sArtId ) {
            $oRecomm = oxNew( 'oxrecommlist' );
            $oRecomm->load( $sRecommList);
            $oRecomm->addArticle( $sArtId, $sRecommText );
        }
    }

    /**
     * Adds tag from parameter
     *
     * @return null;
     */
    public function addTags()
    {
        $sTag = $this->getConfig()->getParameter('newTags', 1);
        $sTag .= " ".html_entity_decode($this->getConfig()->getParameter('highTags', 1));

        $this->_oProduct->addTag($sTag);

        //refresh
        $oTagHandler = oxNew('oxTagCloud');
        $this->_sTagCloud = $oTagHandler->getTagCloud($this->_oProduct->getId());
    }

    public function editTags()
    {
        $oTagCloud = oxNew("oxTagCloud");
        $this->_aTags = $oTagCloud->getTags($this->_oProduct->getId());
        $this->_blEditTags = true;
    }

    /**
     * Returns active product id to load its seo meta info
     *
     * @return string
     */
    protected function _getSeoObjectId()
    {
        if ( isset( $this->_oProduct ) ) {
            return $this->_oProduct->getId();
        }
    }

    /**
     * loading full list of attributes
     *
     * @return array $_aAttributes
     */
    public function getAttributes()
    {
        if ( $this->_aAttributes === null ) {
            // all attributes this article has
            $aArtAttributes = $this->_oProduct->getAttributes();

            //making a new array for backward compatibility
            $this->_aAttributes = array();
            if ( count( $aArtAttributes ) ) {
                foreach ( $aArtAttributes as $sKey => $oAttribute ) {
                    $this->_aAttributes[$sKey] = new stdClass();
                    $this->_aAttributes[$sKey]->title = $oAttribute->oxattribute__oxtitle->value;
                    $this->_aAttributes[$sKey]->value = $oAttribute->oxattribute__oxvalue->value;
                }
            }
        }

        return $this->_aAttributes;
    }


    /**
     * Returns if tags will be edit
     *
     * @return bool
     */
    public function getEditTags()
    {
        return $this->_blEditTags;
    }

    /**
     * Returns all tags
     *
     * @return array
     */
    public function getTags()
    {
        return $this->_aTags;
    }

    /**
     * Returns tag cloud
     *
     * @return string
     */
    public function getTagCloud()
    {
        if ( $this->_sTagCloud === null ) {
            $this->_sTagCloud = false;
            $oTagHandler = oxNew('oxTagCloud');
            $this->_sTagCloud = $oTagHandler->getTagCloud($this->getProduct()->getId());
        }
        return $this->_sTagCloud;
    }


    /**
     * Returns login form anchor
     *
     * @return array
     */
    public function getLoginFormAnchor()
    {
        return $this->_sLoginFormAnchor;
    }

    /**
     * Returns current product
     *
     * @return object
     */
    public function getProduct()
    {
        return $this->_oProduct;
    }

    /**
     * Returns variant lists of current product
     *
     * @return array
     */
    public function getVariantList()
    {
        return $this->_aVariantList;
    }

    /**
     * Template variable getter. Returns object of handling CAPTCHA image
     *
     * @return object
     */
    public function getCaptcha()
    {
        if ( $this->_oCaptcha === null ) {
            $this->_oCaptcha = oxNew('oxCaptcha');
        }
        return $this->_oCaptcha;
    }

    /**
     * Template variable getter. Returns media files of current product
     *
     * @return array
     */
    public function getMediaFiles()
    {
        if ( $this->_aMediaFiles === null ) {
            $aMediaFiles = $this->getProduct()->getMediaUrls();
            $this->_aMediaFiles = count($aMediaFiles) ? $aMediaFiles : false;
        }
        return $this->_aMediaFiles;
    }

    /**
     * Template variable getter. Returns last seen products
     *
     * @return array
     */
    public function getLastProducts()
    {
        if ( $this->_aLastProducts === null ) {
            //last seen products for #768CA
            $sArtId = $this->_oProduct->oxarticles__oxparentid->value?$this->_oProduct->oxarticles__oxparentid->value:$this->_oProduct->getId();

            $oHistoryArtList = oxNew( 'oxarticlelist' );
            $oHistoryArtList->loadHistoryArticles( $sArtId );
            $this->_aLastProducts = $oHistoryArtList;
        }
        return $this->_aLastProducts;
    }

    /**
     * Template variable getter. Returns product's vendor
     *
     * @return object
     */
    public function getVendor()
    {
        if ( $this->_oVendor === null ) {
            $this->_oVendor = false;
            // #671
            if ( $this->getConfig()->getConfigParam( 'bl_perfLoadVendorTree' ) && ( $oVendor = $this->_oProduct->getVendor( false ) ) ) {
                $this->_oVendor = $oVendor;
            }
        }
        return $this->_oVendor;
    }

    /**
     * Template variable getter. Returns product's root category
     *
     * @return object
     */
    public function getCategory()
    {
        if ( $this->_oCategory === null ) {
            $this->_oCategory = false;
            $this->_oCategory = $this->_oProduct->getCategory();
        }
        return $this->_oCategory;
    }

    /**
     * Template variable getter. Returns if draw parent url
     *
     * @return bool
     */
    public function drawParentUrl()
    {
        if ( ( $oParent = $this->_getParentProduct( $this->_oProduct->oxarticles__oxparentid->value ) ) ) {
            if ( $this->getConfig()->getConfigParam( 'blVariantParentBuyable' ) || count( $this->_aVariantList ) > 0 ) {
                return true;
            }
        }
    }

    /**
     * Template variable getter. Returns parent article name
     *
     * @return string
     */
    public function getParentName()
    {
        if ( $this->_sParentName === null ) {
            $this->_sParentName = false;
            if ( ( $oParent = $this->_getParentProduct( $this->_oProduct->oxarticles__oxparentid->value ) ) ) {
                $this->_sParentName = $oParent->oxarticles__oxtitle->value;
            }
        }
        return $this->_sParentName;
    }

    /**
     * Template variable getter. Returns parent article name
     *
     * @return string
     */
    public function getParentUrl()
    {
        if ( $this->_sParentUrl === null ) {
            $this->_sParentUrl = false;
            if ( ( $oParent = $this->_getParentProduct( $this->_oProduct->oxarticles__oxparentid->value ) ) ) {
                $this->_sParentUrl = $oParent->oxdetaillink;
            }
        }
        return $this->_sParentUrl;
    }

    /**
     * Template variable getter. Returns picture galery of current article
     *
     * @return array
     */
    public function getPictureGallery()
    {
        if ( $this->_aPicGallery === null ) {
            $this->_aPicGallery = false;
            //get picture gallery
            $this->_aPicGallery = $this->_oProduct->getPictureGallery();
        }
        return $this->_aPicGallery;
    }

    /**
     * Template variable getter. Returns id of active picture
     *
     * @return string
     */
    public function getActPictureId()
    {
        $aPicGallery = $this->getPictureGallery();
        return $aPicGallery['ActPicID'];
    }

    /**
     * Template variable getter. Returns active picture
     *
     * @return object
     */
    public function getActPicture()
    {
        $aPicGallery = $this->getPictureGallery();
        return $aPicGallery['ActPic'];
    }

    /**
     * Template variable getter. Returns true if there more pictures
     *
     * @return bool
     */
    public function morePics()
    {
        $aPicGallery = $this->getPictureGallery();
        return $aPicGallery['MorePics'];
    }

    /**
     * Template variable getter. Returns pictures of current article
     *
     * @return array
     */
    public function getPictures()
    {
        $aPicGallery = $this->getPictureGallery();
        return $aPicGallery['Pics'];
    }

    /**
     * Template variable getter. Returns selected picture
     *
     * @param string $sPicNr
     *
     * @return string
     */
    public function getArtPic( $sPicNr )
    {
        $aPicGallery = $this->getPictureGallery();
        return $aPicGallery['Pics'][$sPicNr];
    }

    /**
     * Template variable getter. Returns icons of current article
     *
     * @return array
     */
    public function getIcons()
    {
        $aPicGallery = $this->getPictureGallery();
        return $aPicGallery['Icons'];
    }

    /**
     * Template variable getter. Returns if to show zoom pictures
     *
     * @return bool
     */
    public function showZoomPics()
    {
        $aPicGallery = $this->getPictureGallery();
        return $aPicGallery['ZoomPic'];
    }

    /**
     * Template variable getter. Returns zoom pictures
     *
     * @return array
     */
    public function getZoomPics()
    {
        $aPicGallery = $this->getPictureGallery();
        return $aPicGallery['ZoomPics'];
    }

    /**
     * Template variable getter. Returns active zoom picture id
     *
     * @return array
     */
    public function getActZoomPic()
    {
        return 1;
    }

    /**
     * Template variable getter. Returns selectlists of current article
     *
     * @return array
     */
    public function getSelectLists()
    {
        if ( $this->_aSelectLists === null ) {
            $this->_aSelectLists = false;
            if ( $this->getConfig()->getConfigParam( 'bl_perfLoadSelectLists' ) ) {
                $this->_aSelectLists = $this->_oProduct->getSelectLists();
            }
        }
        return $this->_aSelectLists;
    }

    /**
     * Template variable getter. Returns reviews of current article
     *
     * @return array
     */
    public function getReviews()
    {
        if ( $this->_aReviews === null ) {
            $this->_aReviews = false;
            $myConfig = $this->getConfig();
            if ( $myConfig->getConfigParam( 'bl_perfLoadReviews' ) ) {
                $this->_aReviews = $this->_oProduct->getReviews();
            }
        }
        return $this->_aReviews;
    }

    /**
     * Template variable getter. Returns crosssellings
     *
     * @return object
     */
    public function getCrossSelling()
    {
        if ( $this->_oCrossSelling === null ) {
            $this->_oCrossSelling = false;
            if ( $this->_oProduct ) {
                $this->_oCrossSelling = $this->_oProduct->getCrossSelling();
            }
        }
        return $this->_oCrossSelling;
    }

    /**
     * Template variable getter. Returns similar article list
     *
     * @return object
     */
    public function getSimilarProducts()
    {
        if ( $this->_oSimilarProducts === null ) {
            $this->_oSimilarProducts = false;
            if ( $this->_oProduct ) {
                $this->_oSimilarProducts = $this->_oProduct->getSimilarProducts();
            }
        }
        return $this->_oSimilarProducts;
    }

    /**
     * Template variable getter. Returns recommlists
     *
     * @return object
     */
    public function getSimilarRecommLists()
    {
        if ( $this->_oRecommList === null ) {
            $this->_oRecommList = false;
            if ( $this->_oProduct ) {
                $oRecommList = oxNew('oxrecommlist');
                $this->_oRecommList = $oRecommList->getRecommListsByIds( array($this->_oProduct->getId()));
            }
        }
        return $this->_oRecommList;
    }

    /**
     * Template variable getter. Returns accessoires of article
     *
     * @return object
     */
    public function getAccessoires()
    {
        if ( $this->_oAccessoires === null ) {
            $this->_oAccessoires = false;
            if ( $this->_oProduct ) {
                $this->_oAccessoires = $this->_oProduct->getAccessoires();
            }
        }
        return $this->_oAccessoires;
    }

    /**
     * Template variable getter. Returns list of customer also bought thies products
     *
     * @return object
     */
    public function getAlsoBoughtThiesProducts()
    {
        if ( $this->_aAlsoBoughtArts === null ) {
            $this->_aAlsoBoughtArts = false;
            if ( $this->_oProduct ) {
                $this->_aAlsoBoughtArts = $this->_oProduct->getCustomerAlsoBoughtThisProducts();
            }
        }
        return $this->_aAlsoBoughtArts;
    }

    /**
     * Template variable getter. Returns if pricealarm is disabled
     *
     * @return object
     */
    public function isPriceAlarm()
    {
        // #419 disabling pricealarm if article has fixed price
        if ( isset($this->_oProduct->oxarticles__oxblfixedprice->value) && $this->_oProduct->oxarticles__oxblfixedprice->value) {
            return 0;
        }
        return 1;
    }

    /**
     * returns object, assosiated with current view.
     * (the object that is shown in frontend)
     *
     * @return object
     */
    protected function getSubject()
    {
        return $this->_oProduct;
    }

    /**
     * Returns search title. It will be setted in oxlocator
     *
     * @return string
     */
    public function getSearchTitle()
    {
        return $this->_sSearchTitle;
    }

    /**
     * Returns search title setter
     *
     * @param string $sTitle
     *
     * @return null
     */
    public function setSearchTitle( $sTitle )
    {
        $this->_sSearchTitle = $sTitle;
    }

    /**
     * active category path setter
     *
     * @param string $sActCatPath
     *
     * @return string
     */
    public function setCatTreePath( $sActCatPath )
    {
        $this->_sCatTreePath = $sActCatPath;
    }

    /**
     * If product details are accessed by vendor url
     * view must not be indexable
     *
     * @return int
     */
    public function noIndex()
    {
        if (oxConfig::getParameter( 'listtype' ) == 'vendor') {
            return $this->_iViewIndexState = 2;
        }
        return parent::noIndex();
    }

    /**
     * Returns current view title. Default is null
     *
     * @return null
     */
    public function getTitle()
    {
        if ( $this->_oProduct ) {
            return $this->_oProduct->oxarticles__oxtitle->value . ( $this->_oProduct->oxarticles__oxvarselect->value ? ' ' . $this->_oProduct->oxarticles__oxvarselect->value : '' );
        }
    }
}
