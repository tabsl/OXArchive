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
 * $Id: oxsearch.php 13914 2008-10-30 11:12:55Z arvydas $l
 */

/**
 * Implements search
 * @package core
 */
class oxSearch extends oxSuperCfg
{
    /**
     * Active language id
     *
     * @var int
     */
    protected $_iLanguage = 0;

    /**
     * Class constructor. Executes search lenguage setter
     *
     * @return null
     */
    public function __construct()
    {
        $this->setLanguage();
    }

    /**
     * Search language setter. If no param is passed, will be taken default shop language
     *
     * @param string $iLanguage string (default null)
     *
     * @return null;
     */
    public function setLanguage( $iLanguage = null )
    {
        if ( !isset( $iLanguage ) ) {
            $this->_iLanguage = oxLang::getInstance()->getBaseLanguage();
        } else {
            $this->_iLanguage = $iLanguage;
        }
    }

    /**
     * Returns a list of articles according to search parameters. Returns matched
     *
     * @param string $sSearchParamForQuery query parameter
     * @param string $sInitialSearchCat    initial category to seearch in
     * @param string $sInitialSearchVendor initial vendor to seearch for
     * @param string $sSortBy              sort by
     *
     * @return oxarticlelist
     */
    public function getSearchArticles( $sSearchParamForQuery = false, $sInitialSearchCat = false, $sInitialSearchVendor = false, $sSortBy = false )
    {
        // sets active page
        $this->iActPage = (int) oxConfig::getParameter( 'pgNr' );
        $this->iActPage = ($this->iActPage < 0)?0:$this->iActPage;

        // load only articles which we show on screen
        //setting default values to avoid possible errors showing article list
        $iNrofCatArticles = $this->getConfig()->getConfigParam( 'iNrofCatArticles' );
        $iNrofCatArticles = $iNrofCatArticles?$iNrofCatArticles:10;

        $oArtList = oxNew( 'oxarticlelist' );
        $oArtList->setSqlLimit( $iNrofCatArticles * $this->iActPage, $iNrofCatArticles );

        $sSelect = $this->_getSearchSelect( $sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sSortBy );
        if ( $sSelect ) {
            $oArtList->selectString( $sSelect );
        }
        return $oArtList;
    }

    /**
     * Returns the amount of articles according to search parameters.
     *
     * @param string $sSearchParamForQuery query parameter
     * @param string $sInitialSearchCat    initial category to seearch in
     * @param string $sInitialSearchVendor initial vendor to seearch for
     *
     * @return int
     */
    public function getSearchArticleCount( $sSearchParamForQuery = false, $sInitialSearchCat = false, $sInitialSearchVendor = false )
    {
        $iCnt = 0;
        $sSelect = $this->_getSearchSelect( $sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, false );
        if ( $sSelect ) {

            $sPartial = substr( $sSelect, strpos( $sSelect, ' from ' ) );
            $sSelect  = "select count( ".getViewName( 'oxarticles' ).".oxid ) $sPartial ";

            $iCnt = oxDb::getDb()->getOne( $sSelect );
        }
        return $iCnt;
    }

    /**
     * Returns the appropriate SQL select for a search according to search parameters
     *
     * @param string $sSearchParamForQuery query parameter
     * @param string $sInitialSearchCat    initial category to seearch in
     * @param string $sInitialSearchVendor initial vendor to seearch for
     * @param string $sSortBy              sort by
     *
     * @return string
     */
    protected function _getSearchSelect( $sSearchParamForQuery = false, $sInitialSearchCat = false, $sInitialSearchVendor = false, $sSortBy = false)
    {
        // performance
        if ( $sInitialSearchCat ) {
            // lets search this category - is no such category - skip all other code
            $oCategory = oxNew( 'oxcategory' );
            $sCatTable = $oCategory->getViewName();

            $sQ  = "select 1 from $sCatTable where $sCatTable.oxid = ".oxDb::getDb()->quote( $sInitialSearchCat )." ";
            $sQ .= "and ".$oCategory->getSqlActiveSnippet();
            if ( !oxDb::getDb()->getOne( $sQ ) )
                return;
        }

        // performance:
        if ( $sInitialSearchVendor ) {
            // lets search this vendor - if no such vendor - skip all other code
            $oVendor   = oxNew( 'oxvendor' );
            $sVndTable = $oVendor->getViewName();

            $sQ  = "select 1 from $sVndTable where $sVndTable.oxid = ".oxDb::getDb()->quote( $sInitialSearchVendor )." ";
            $sQ .= "and ".$oVendor->getSqlActiveSnippet();
            if ( !oxDb::getDb()->getOne( $sQ ) )
                return;
        }

        $sWhere = null;

        if ( $sSearchParamForQuery ) {
            $sWhere = $this->_getWhere( $sSearchParamForQuery );
        } elseif ( !$sInitialSearchCat && !$sInitialSearchVendor ) {
            //no search string
            return null;
        }

        $oArticle = oxNew( 'oxarticle' );
        $sArticleTable = $oArticle->getViewName();
        $sO2CView      = getViewName( 'oxobject2category' );

        $sSelectFields = $oArticle->getSelectFields();

        // longdesc field now is kept on different table
        $sDescTable = '';
        $sDescJoin  = '';
        if ( is_array( $aSearchCols = oxConfig::getInstance()->getConfigParam( 'aSearchCols' ) ) ) {
            if ( in_array( 'oxlongdesc', $aSearchCols ) || in_array( 'oxtags', $aSearchCols ) ) {
                $sDescView  = getViewName( 'oxartextends' );
                $sDescTable = ", {$sDescView} ";
                $sDescJoin  = " {$sDescView}.oxid={$sArticleTable}.oxid and ";
            }
        }

        //select articles
        $sSelect = "select {$sSelectFields} from {$sArticleTable} {$sDescTable} where {$sDescJoin} ";

        // must be additional conditions in select if searching in category
        if ( $sInitialSearchCat ) {
            $sSelect = "select {$sSelectFields} from {$sArticleTable}, {$sO2CView} as
                        oxobject2category {$sDescTable} where oxobject2category.oxcatnid='{$sInitialSearchCat}' and
                        oxobject2category.oxobjectid={$sArticleTable}.oxid and {$sDescJoin} ";
        }

        $sSelect .= $oArticle->getSqlActiveSnippet();
        $sSelect .= " and {$sArticleTable}.oxparentid = '' and {$sArticleTable}.oxissearch = 1 ";

        if ( $sInitialSearchVendor ) {
            $sSelect .= " and {$sArticleTable}.oxvendorid = '{$sInitialSearchVendor}' ";
        }

        $sSelect .= $sWhere;

        if ( $sSortBy ) {
            $sSelect .= " order by {$sSortBy} ";
        }

        return $sSelect;
    }

    /**
     * Forms and returns SQL query string for search in DB.
     *
     * @param string $sSearchString searching string
     *
     * @return string
     */
    protected function _getWhere( $sSearchString )
    {
        $myConfig = $this->getConfig();
        $blSep    = false;
        $sArticleTable = getViewName( 'oxarticles' );

        $aSearchCols = $myConfig->getConfigParam( 'aSearchCols' );
        if ( !(is_array( $aSearchCols ) && count( $aSearchCols ) ) ) {
            return '';
        }

        $oTempArticle = oxNew( 'oxarticle' );
        $sSearchSep   = $myConfig->getConfigParam( 'blSearchUseAND' )?'and ':'or ';
        $aSearch  = explode( ' ', $sSearchString );
        $sSearch  = ' and ( ';

        foreach ( $aSearch as $sSearchString ) {

            if ( !strlen( $sSearchString ) ) {
                continue;
            }

            if ( $blSep ) {
                $sSearch .= $sSearchSep;
            }

            $blSep2 = false;
            $sSearch  .= '( ';

            foreach ( $aSearchCols as $sField ) {

                if ( $blSep2 ) {
                    $sSearch  .= ' or ';
                }

                $sLanguage = '';
                if ( $this->_iLanguage && $oTempArticle->isMultilingualField( $sField ) ) {
                    $sLanguage = oxLang::getInstance()->getLanguageTag( $this->_iLanguage );
                }

                // as long description now is on different table table must differ
                if ( $sField == 'oxlongdesc' || $sField == 'oxtags' ) {
                    $sSearchField = getViewName( 'oxartextends' ).".{$sField}{$sLanguage}";
                } else {
                    $sSearchField = "{$sArticleTable}.{$sField}{$sLanguage}";
                }

                $sSearch .= " {$sSearchField} like ".oxDb::getDb()->quote( "%$sSearchString%" );

                // special chars ?
                if ( ( $sUml = oxUtilsString::getInstance()->prepareStrForSearch( $sSearchString ) ) ) {
                    $sSearch  .= " or {$sSearchField} like ".oxDb::getDb()->quote( "%$sUml%" );
                }

                $blSep2 = true;
            }
            $sSearch  .= ' ) ';

            $blSep = true;
        }

        $sSearch .= ' ) ';

        return $sSearch;
    }
}

