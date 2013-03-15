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
 * $Id: alist.php 14276 2008-11-19 13:57:45Z arvydas $
 */

/**
 * List of articles for a selected product group.
 * Collects list of articles, according to it generates links for list gallery,
 * metatags (for search engines). Result - "list.tpl" template.
 * OXID eShop -> (Any selected shop product category).
 */
class aList extends oxUBase
{
    /**
     * Count of all articles in list.
     * @var integer
     */
    protected $_iAllArtCnt = 0;

    /**
     * Number of possible pages.
     * @var integer
     */
    protected $_iCntPages = null;

    /**
     * Current class default template name.
     * @var string
     */
    protected $_sThisTemplate = 'list.tpl';

    /**
     * New layout list template
     * @var string
     */
    protected $_sThisMoreTemplate = 'list_more.tpl';

    /**
     * Category path string
     * @var string
     */
    protected $_sCatPathString = null;

    /**
     * Marked which defines if current view is sortable or not
     * @var bool
     */
    protected $_blShowSorting = true;

    /**
     * Category attributes.
     * @var array
     */
    protected $_aAttributes = null;

    /**
     * Category article list
     * @var array
     */
    protected $_aCatArtList = null;

    /**
     * Category tree html path
     * @var string
     */
    protected $_sCatTreeHtmlPath = null;

    /**
     * If category has subcategories
     * @var bool
     */
    protected $_blHasVisibleSubCats = null;

    /**
     * List of category's subcategories
     * @var array
     */
    protected $_aSubCatList = null;

    /**
     * Page navigation
     * @var object
     */
    protected $_oPageNavigation = null;

    /**
     * Active category object.
     * @var object
     */
    protected $_oCategory = null;

    /**
     * Active object is category.
     * @var bool
     */
    protected $_blIsCat = null;

    /**
     * Recomendation list
     * @var object
     */
    protected $_oRecommList = null;

    /**
     * Category title
     * @var string
     */
    protected $_sCatTitle = null;

    /**
     * Category seo url state
     * @var bool
     */
    protected $_blFixedUrl = null;

    /**
     * Generates (if not generated yet) and returns view ID (for
     * template engine caching).
     *
     * @return string   $this->_sViewId view id
     */
    public function getViewId()
    {
        if ( isset( $this->_sViewId ) ) {
            return $this->_sViewId;
        }

        $iActPage = $this->getActPage();

        // shorten it
            $_sViewId = md5( parent::getViewId().'|'.oxConfig::getParameter( 'cnid' ).'|'.$iActPage.'|'.oxConfig::getParameter( '_artperpage' ) );


        return $this->_sViewId = $_sViewId;
    }

    /**
     * Executes parent::render(), loads active category, prepares article
     * list sorting rules. According to category type loads list of
     * articles - regular (oxarticlelist::LoadCategoryArticles()) or price
     * dependent (oxarticlelist::LoadPriceArticles()). Generates page navigation data
     * such as previous/next window URL, number of available pages, generates
     * metatags info (oxview::_convertForMetaTags()) and returns name of
     * template to render.
     *
     * Template variables:
     * <b>articlelist</b>, <b>filterattributes</b>, <b>pageNavigation</b>,
     * <b>subcatlist</b>, <b>meta_keywords</b>, <b>meta_description</b>
     *
     * @return  string  $this->_sThisTemplate   current template file name
     */
    public function render()
    {
        $myConfig = $this->getConfig();

        $this->_oCategory  = null;
        $blContinue = true;
        $this->_blIsCat = false;

        // A. checking for fake "more" category
        if ( 'oxmore' == oxConfig::getParameter( 'cnid' ) && $myConfig->getConfigParam( 'blTopNaviLayout' ) ) {

            // overriding some standard value and parameters
            $this->_sThisTemplate = $this->_sThisMoreTemplate;
            $this->_oCategory = oxNew( 'oxcategory' );
            $this->_oCategory->oxcategories__oxactive = new oxField(1, oxField::T_RAW);
        } else {
            if( ( $this->_oActCategory = $this->getActCategory() ) ) {
                $this->_oCategory  = $this->_oActCategory;
                $blContinue = $this->_oCategory->oxcategories__oxactive->value;
                $this->_blIsCat = true;
            }
        }


        // category is inactive ?
        if ( !$blContinue || !$this->_oCategory ) {
            oxUtils::getInstance()->redirect( $myConfig->getShopURL().'index.php' );
        }

        $this->_aViewData['filterattributes'] = $this->getAttributes();

        $this->_aViewData['articlelist']       = $this->getArticleList();
        $this->_aViewData['similarrecommlist'] = $this->getSimilarRecommLists();

        // loading actions
        $this->_aViewData['articlebargainlist'] = $this->getBargainArticleList();
        $this->_aViewData['aTop5Articles']      = $this->getTop5ArticleList();

        $this->_aViewData['pageNavigation'] = $this->getPageNavigation();

        $this->_aViewData['actCatpath']        = $this->getCatTreePath();
        $this->_aViewData['template_location'] = $this->getTemplateLocation();

        // generating meta info
        $this->setMetaDescription( null );
        $this->setMetaKeywords( null );

        // add to the parent view
        $this->_aViewData['actCategory'] = $this->getActiveCategory();

        $oCat = $this->getActCategory();
        if ($oCat && is_array($myConfig->getConfigParam( 'aRssSelected' )) && in_array('oxrss_categories', $myConfig->getConfigParam( 'aRssSelected' ))) {
            $oRss = oxNew('oxrssfeed');
            $this->addRssFeed($oRss->getCategoryArticlesTitle($oCat), $oRss->getCategoryArticlesUrl($oCat), 'activeCategory');
        }

        //Gets subcategory tree from category tree
        $this->_aViewData['hasVisibleSubCats'] = $this->hasVisibleSubCats();
        $this->_aViewData['subcatlist']        = $this->getSubCatList();

        $this->_aViewData['title'] = $this->getTitle();

        parent::render();

        return $this->getTemplateName();
    }

    /**
     * Stores chosen category filter into session.
     *
     * Session variables:
     * <b>session_attrfilter</b>
     *
     * @return null
     */
    public function executefilter()
    {
        // store this into session
        $aFilter = oxConfig::getParameter( 'attrfilter', 1 );
        $sActCat = oxConfig::getParameter( 'cnid' );
        $aSessionFilter = oxSession::getVar( 'session_attrfilter' );
        $aSessionFilter[$sActCat] = $aFilter;
        oxSession::setVar( 'session_attrfilter', $aSessionFilter );
    }

    /**
     * Loads and returns article list of active category.
     *
     * @param string $oCategory category object
     *
     * @return array
     */
    protected function _loadArticles( $oCategory )
    {
        $myConfig = $this->getConfig();

        $iNrofCatArticles = (int) $myConfig->getConfigParam( 'iNrofCatArticles' );
        $iNrofCatArticles = $iNrofCatArticles?$iNrofCatArticles:1;

        // load only articles which we show on screen
        $oArtList = oxNew( 'oxarticlelist' );
        $oArtList->setSqlLimit( $iNrofCatArticles * $this->getActPage(), $iNrofCatArticles );

        if ( $this->_oActCategory ) {
            $oArtList->setCustomSorting( $this->getSortingSql( $this->_oActCategory->getId() ) );
        }

        if ( $oCategory->oxcategories__oxpricefrom->value || $oCategory->oxcategories__oxpriceto->value ) {
            $dPriceFrom = $oCategory->oxcategories__oxpricefrom->value;
            $dPriceTo   = $oCategory->oxcategories__oxpriceto->value;

            $this->_iAllArtCnt = $oArtList->loadPriceArticles( $dPriceFrom, $dPriceTo, $oCategory );
        } else {

            $aSessionFilter = null;
            $aSessionFilter = oxSession::getVar( 'session_attrfilter' );

            $sActCat = oxConfig::getParameter( 'cnid' );
            $this->_iAllArtCnt = $oArtList->loadCategoryArticles( $sActCat, $aSessionFilter );
        }

        $this->_iCntPages = round( $this->_iAllArtCnt/$iNrofCatArticles + 0.49 );

        return $oArtList;
    }

    /**
     * Returns active product id to load its seo meta info
     *
     * @return string
     */
    protected function _getSeoObjectId()
    {
        if ( ( $oCategory = $this->getActCategory() ) ) {
            return $oCategory->getId();
        }
    }

    /**
     * Returns string built from category titles
     *
     * @return string
     */
    protected function _getCatPathString()
    {
        if ( $this->_sCatPathString === null ) {

            // marking as allready set
            $this->_sCatPathString = false;

            //fetching category path
            if ( is_array( $aPath = $this->getCatTreePath() ) ) {

                $this->_sCatPathString = '';
                foreach ( $aPath as $oCat ) {
                    if ( $this->_sCatPathString ) {
                        $this->_sCatPathString .= ', ';
                    }
                    $this->_sCatPathString .= strtolower( $oCat->oxcategories__oxtitle->value );
                }
            }
        }
        return $this->_sCatPathString;
    }

    /**
     * Creates list view meta description which looks like:
     *
     * You are here: {Parent category title} - {Current category title}. {Shops start title}
     *
     * @param mixed $aCatPath  category path (not used)
     * @param int   $iLength   max length of result, -1 for no truncation (not used)
     * @param bool  $blDescTag if true - performs additional dublicate cleaning (not used)
     *
     * @return  string  $sString    converted string
     */
    protected function _prepareMetaDescription( $aCatPath, $iLength = 1024, $blDescTag = false )
    {
        // using language constant ..
        $sDescription = oxLang::getInstance()->translateString( 'INC_HEADER_YOUAREHERE' );

        // appending parent title
        if ( ( $oParent = $this->_oCategory->getParentCategory() ) ) {
            $sDescription .= " {$oParent->oxcategories__oxtitle->value} -";
        }

        // adding cateogry title
        $sDescription .= " {$this->_oCategory->oxcategories__oxtitle->value}.";

        // and final component ..
        if ( ( $sSuffix = $this->getConfig()->getActiveShop()->oxshops__oxstarttitle->value ) ) {
            $sDescription .= " {$sSuffix}";
        }

        // making safe for output
        $aRemoveChars = array( "\"", "'", "\n", "\r", "\t", "\x95", "\xA0" );
        $sDescription = str_replace( $aRemoveChars, ' ', $sDescription );

        return strip_tags( html_entity_decode( $sDescription ) );
    }

    /**
     * Metatags - description and keywords - generator for search
     * engines. Uses string passed by parameters, cleans HTML tags,
     * string dublicates, special chars. Also removes strings defined
     * in $myConfig->aSkipTags (Admin area).
     *
     * @param mixed $aCatPath  category path
     * @param int   $iLength   max length of result, -1 for no truncation
     * @param bool  $blDescTag if true - performs additional dublicate cleaning
     *
     * @return  string  $sString    converted string
     */
    protected function _collectMetaDescription( $aCatPath, $iLength = 1024, $blDescTag = false )
    {
        //formatting description tag
        $sAddText = $this->_oActCategory?trim( $this->_oActCategory->oxcategories__oxlongdesc->value ):'';
        if ( !$sAddText && count($this->_aArticleList)) {
            foreach ( $this->_aArticleList as $oArticle ) {
                if ( $sAddText ) {
                    $sAddText .= ', ';
                }
                $sAddText .= $oArticle->oxarticles__oxtitle->value;
            }
        }

        return parent::_prepareMetaDescription( $this->_getCatPathString().' - '.$sAddText, $iLength, $blDescTag );
    }

    /**
     * Creates string of keywords (category titles) seperated by comma
     *
     * @param mixed $aCatPath category path (not used)
     *
     * @return string
     */
    protected function _prepareMetaKeyword( $aCatPath )
    {
        $sKeywords = '';
        if ( ( $oParent = $this->_oCategory->getParentCategory() ) ) {
            $sKeywords = $oParent->oxcategories__oxtitle->value;
        }

        $sKeywords = ( $sKeywords ? $sKeywords . ', ' : '' ) . $this->_oCategory->oxcategories__oxtitle->value;
        $aSubCats  = $this->_oCategory->getSubCats();
        if ( is_array( $aSubCats ) ) {
            foreach ( $aSubCats as $oSubCat ) {
                $sKeywords .= ', '.$oSubCat->oxcategories__oxtitle->value;
            }
        }

        $sKeywords = parent::_prepareMetaDescription( $sKeywords, -1, true );
        $sKeywords = $this->_removeDuplicatedWords( $sKeywords );

        // removing in admin defined strings
        $aSkipTags = $this->getConfig()->getConfigParam( 'aSkipTags' );
        if ( is_array( $aSkipTags ) && $sKeywords ) {
            foreach ( $aSkipTags as $sSkip ) {
                $aPattern = array( '/\W'.$sSkip.'\W/i', '/^'.$sSkip.'\W/i', '/\"'.$sSkip.'$/i' );
                $sKeywords  = preg_replace( $aPattern, '', $sKeywords );
            }
        }

        return $sKeywords;
    }

    /**
     * creates a string of keyword filtered by the function prepareMetaDescription and without any duplicates
     * additional the admin defined strings are removed
     *
     * @param mixed $aCatPath category path
     *
     * @return string of keywords seperated by comma
     */
    protected function _collectMetaKeyword( $aCatPath )
    {
        $iMaxTextLenght = 60;
        $sText = '';

        if (count($this->_aArticleList)) {
            foreach ( $this->_aArticleList as $oProduct ) {
                $sDesc = strip_tags( trim( strtolower( $oProduct->oxarticles__oxlongdesc->value ) ) );
                if ( strlen( $sDesc ) > $iMaxTextLenght ) {
                    $sMidText = substr( $sDesc, 0, $iMaxTextLenght );
                    $sText   .= substr( $sMidText, 0, ( strlen( $sMidText ) - strpos( strrev( $sMidText ), ' ' ) ) );
                }
            }
        }
        return parent::_prepareMetaKeyword( $this->_getCatPathString() . ', ' . $sText );
    }

    /**
     * Assigns Template name ($this->_sThisTemplate) for article list
     * preview. Name of template can be defined in admin or passed by
     * URL ("tpl" variable).
     *
     * @return string
     */
    public function getTemplateName()
    {
        // assign template name
        if ( ( $sTplName = basename( oxConfig::getParameter( 'tpl' ) ) ) ) {
            $this->_sThisTemplate = $sTplName;
        } elseif ( $this->_oActCategory && $this->_oActCategory->oxcategories__oxtemplate->value ) {
            $this->_sThisTemplate = $this->_oActCategory->oxcategories__oxtemplate->value;
        }

        return $this->_sThisTemplate;
    }

    /**
     * Adds page number parameter to current Url and returns formatted url
     *
     * @param string $sUrl  url to append page numbers
     * @param int    $iPage current page number
     * @param int    $iLang requested language
     *
     * @return string
     */
    protected function _addPageNrParam( $sUrl, $iPage, $iLang = null)
    {
        if ( oxUtils::getInstance()->seoIsActive() && ( $oCategory = $this->getActCategory() ) ) {
            if ( $iPage ) { // only if page number > 0
                $sUrl = oxSeoEncoderCategory::getInstance()->getCategoryPageUrl( $oCategory, $iPage, $iLang, $this->_isFixedUrl( $oCategory ) );
            }
        } else {
            $sUrl = parent::_addPageNrParam( $sUrl, $iPage, $iLang );
        }
        return $sUrl;
    }

    /**
     * Returns category seo url status (fixed or not)
     *
     * @return bool
     */
    protected function _isFixedUrl( $oCategory )
    {
        if ( $this->_blFixedUrl == null ) {
            $sId = $oCategory->getId();
            $iLang = $oCategory->getLanguage();
            $sShopId = oxConfig::getInstance()->getShopId();
            $this->_blFixedUrl = oxDb::getDb()->getOne( "select oxfixed from oxseo where oxobjectid = '{$sId}' and oxshopid = '{$sShopId}' and oxlang = '{$iLang}' and oxparams = '' " );
        }
        return $this->_blFixedUrl;
    }

    /**
     * Template variable getter. Returns true if we have category
     *
     * @return bool
     */
    public function _isActCategory()
    {
        return $this->_blIsCat;
    }

    /**
     * Template variable getter. Returns active category
     *
     * @return bool
     */
    protected function _getCategory()
    {
        return $this->_oCategory;
    }

    /**
     * Generates Url for page navigation
     *
     * @return string
     */
    public function generatePageNavigationUrl( )
    {
        if ( ( oxUtils::getInstance()->seoIsActive() && ( $oCategory = $this->getActCategory() ) ) ) {
            return $oCategory->getLink();
        } else {
            return parent::generatePageNavigationUrl( );
        }
    }

    /**
     * Returns SQL sorting string with additional checking if category has its own sorting configuration
     *
     * @param string $sCnid sortable item id
     *
     * @return string
     */
    public function getSorting( $sCnid )
    {
        // category has own sorting
        $aSorting = parent::getSorting( $sCnid );
        if ( !$aSorting && $this->_oActCategory && $this->_oActCategory->oxcategories__oxdefsort->value ) {
            $sSortBy  = getViewName( 'oxarticles' ).'.'.$this->_oActCategory->oxcategories__oxdefsort->value;
            if ( $sSortBy) {
                $sSortDir = $this->_oActCategory->oxcategories__oxdefsortmode->value ? " desc " : null;
            }
            $sSortDir = $sSortDir?$sSortDir:null;

            $this->setItemSorting( $sCnid, $sSortBy, $sSortDir );
            $aSorting = array ( 'sortby' => $sSortBy, 'sortdir' => $sSortDir );
        }
        return $aSorting;
    }

    /**
     * Returns title suffix used in template
     *
     * @return string
     */
    public function getTitleSuffix()
    {
        if ( $this->getActCategory()->oxcategories__oxshowsuffix->value ) {
           return $this->getConfig()->getActiveShop()->oxshops__oxtitlesuffix->value;
        }
    }

    /**
     * returns object, assosiated with current view.
     * (the object that is shown in frontend)
     *
     * @return object
     */
    protected function getSubject()
    {
        return $this->getActCategory();
    }

    /**
     * Template variable getter. Returns array of attribute values
     * we do have here in this category
     *
     * @return array
     */
    public function getAttributes()
    {
        // #657 gather all attribute values we do have here in this category
        $this->_aAttributes = false;
        if ( $this->_getCategory() ) {
            $aAttributes = $this->_getCategory()->getAttributes();
            if ( count( $aAttributes ) ) {
                $this->_aAttributes = $aAttributes;
            }
        }
        return $this->_aAttributes;
    }

    /**
     * Template variable getter. Returns category's article list
     *
     * @return array
     */
    public function getArticleList()
    {
        if ( $this->_aArticleList === null ) {
            if ( ( $oCategory = $this->_getCategory() ) && $this->_isActCategory() ) {
                $aArticleList = $this->_loadArticles( $oCategory );
                if ( count( $aArticleList ) ) {
                    $this->_aArticleList = $aArticleList;
                }
            }
        }
        return $this->_aArticleList;
    }

    /**
     * Template variable getter. Returns recommendation list
     *
     * @return object
     */
    public function getSimilarRecommLists()
    {
        if ( $this->_oRecommList === null ) {
            $this->_oRecommList = false;
            if ( $aCatArtList = $this->getArticleList() ) {
                $oRecommList = oxNew('oxrecommlist');
                $this->_oRecommList = $oRecommList->getRecommListsByIds( $aCatArtList->arrayKeys());
            }
        }
        return $this->_oRecommList;
    }

    /**
     * Template variable getter. Returns category path
     *
     * @return string
     */
    public function getCatTreePath()
    {
        if ( $this->_sCatTreePath === null ) {
             $this->_sCatTreePath = false;
            // category path
            if ( $oCatTree = $this->getCategoryTree() ) {
                $this->_sCatTreePath = $oCatTree->getPath();
            }
        }
        return $this->_sCatTreePath;
    }

    /**
     * Template variable getter. Returns category html path
     *
     * @return string
     */
    public function getTemplateLocation()
    {
        if ( $this->_sCatTreeHtmlPath === null ) {
             $this->_sCatTreeHtmlPath = false;
            // category path
            if ( $oCatTree = $this->getCategoryTree() ) {
                $this->_sCatTreeHtmlPath = $oCatTree->getHtmlPath();
            }
        }
        return $this->_sCatTreeHtmlPath;
    }

    /**
     * Template variable getter. Returns true if category has active
     * subcategories.
     *
     * @return bool
     */
    public function hasVisibleSubCats()
    {
        if ( $this->_blHasVisibleSubCats === null ) {
            $this->_blHasVisibleSubCats = false;
            if ( $oClickCat = $this->getActCategory() ) {
                $this->_blHasVisibleSubCats = $oClickCat->getHasVisibleSubCats();
            }
        }
        return $this->_blHasVisibleSubCats;
    }

    /**
     * Template variable getter. Returns list of subategories.
     *
     * @return array
     */
    public function getSubCatList()
    {
        if ( $this->_aSubCatList === null ) {
            $this->_aSubCatList = array();
            if ( $oClickCat = $this->getActCategory() ) {
                $this->_aSubCatList = $oClickCat->getSubCats();
            }
        }
        return $this->_aSubCatList;
    }

    /**
     * Template variable getter. Returns page navigation
     *
     * @return object
     */
    public function getPageNavigation()
    {
        if ( $this->_oPageNavigation === null ) {
            $this->_oPageNavigation = false;
            $this->_oPageNavigation = $this->generatePageNavigation();
        }
        return $this->_oPageNavigation;
    }

    /**
     * Template variable getter. Returns category title.
     *
     * @return string
     */
    public function getTitle()
    {
        if ( $this->_sCatTitle === null ) {
            $this->_sCatTitle = false;
            if ( $oCategory = $this->_getCategory() ) {
                $this->_sCatTitle = $oCategory->oxcategories__oxtitle->value;
            }
        }
        return $this->_sCatTitle;
    }

    /**
     * Template variable getter. Returns Top 5 article list
     *
     * @return array
     */
    public function getTop5ArticleList()
    {
        if ( $this->_aTop5ArticleList === null ) {
            $this->_aTop5ArticleList = false;
            $myConfig = $this->getConfig();
            if ( $myConfig->getConfigParam( 'bl_perfLoadAktion' ) && $this->_isActCategory() ) {
                // top 5 articles
                $oArtList = oxNew( 'oxarticlelist' );
                $oArtList->loadTop5Articles();
                if ( $oArtList->count() ) {
                    $this->_aTop5ArticleList = $oArtList;
                }
            }
        }
        return $this->_aTop5ArticleList;
    }

    /**
     * Template variable getter. Returns bargain article list
     *
     * @return array
     */
    public function getBargainArticleList()
    {
        if ( $this->_aBargainArticleList === null ) {
            $this->_aBargainArticleList = array();
            if ( $this->getConfig()->getConfigParam( 'bl_perfLoadAktion' ) && $this->_isActCategory() ) {
                $oArtList = oxNew( 'oxarticlelist' );
                $oArtList->loadAktionArticles( 'OXBARGAIN' );
                if ( $oArtList->count() ) {
                    $this->_aBargainArticleList = $oArtList;
                }
            }
        }
        return $this->_aBargainArticleList;
    }

    /**
     * Template variable getter. Returns active search
     *
     * @return object
     */
    public function getActiveCategory()
    {
        return $this->getActCategory();
    }

}
