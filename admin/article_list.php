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
 * $Id: article_list.php 14018 2008-11-06 13:33:39Z arvydas $
 */

/**
 * Admin article list manager.
 * Collects base article information (according to filtering rules), performs sorting,
 * deletion of articles, etc.
 * Admin Menu: Manage Products -> Articles.
 * @package admin
 */
class Article_List extends oxAdminList
{
    /**
     * Name of chosen object class (default null).
     *
     * @var string
     */
    protected $_sListClass = 'oxarticle';

    /**
     * Type of list.
     *
     * @var string
     */
    protected $_sListType = 'oxarticlelist';

    /**
     * Sets SQL query parameters, executes parent
     * method parent::Init().
     *
     * @return null
     */
    public function init()
    {
        //loading article
        $soxId = oxConfig::getParameter( "oxid");
        if ($soxId && $soxId != -1) {
            $oArticle = oxNew( "oxarticle");
            $oArticle->load( $soxId);
            $oArticle->getAdminVariants();
        }

        parent::init();
    }

    /**
     * Collects articles base data and passes them according to filtering rules,
     * returns name of template file "article_list.tpl".
     *
     * @return string
     */
    public function render()
    {
        $myConfig = $this->getConfig();
        $sPwrSearchFld = oxConfig::getParameter( "pwrsearchfld");
        if( !isset( $sPwrSearchFld))
            $sPwrSearchFld  = "oxtitle";

        foreach ( $this->_oList as $key => $oArticle) {
            $sFieldName = "oxarticles__".strtolower($sPwrSearchFld);

            // formatting view
            if ( !$myConfig->getConfigParam( 'blSkipFormatConversion' ) ) {
                if ( $oArticle->$sFieldName->fldtype == "datetime")
                    oxDb::getInstance()->convertDBDateTime( $oArticle->$sFieldName );
                elseif ( $oArticle->$sFieldName->fldtype == "timestamp")
                    oxDb::getInstance()->convertDBTimestamp( $oArticle->$sFieldName );
                elseif ( $oArticle->$sFieldName->fldtype == "date")
                    oxDb::getInstance()->convertDBDate( $oArticle->$sFieldName );
            }

            $oArticle->pwrsearchval = $oArticle->$sFieldName->value;
            $this->_oList[$key] = $oArticle;
        }


        parent::render();

        // load fields
        $oArticle = oxNew("oxarticle");
        $this->_aViewData["pwrsearchfields"] = $oArticle->getSearchableFields();
        $this->_aViewData["pwrsearchfld"] =  strtoupper($sPwrSearchFld);

        if ( isset( $this->_aViewData["where"])) {
            $aWhere = &$this->_aViewData["where"];
            $sFieldName = "oxarticles__".strtoupper($sPwrSearchFld);
            if ( isset( $aWhere->$sFieldName))
                $this->_aViewData["pwrsearchinput"] = $aWhere->$sFieldName;
        }

        // parent categorie tree
        $oCatTree = oxNew( "oxCategoryList");
        $oCatTree->buildList($myConfig->getConfigParam( 'bl_perfLoadCatTree' ));

        $sChosenCat  = oxConfig::getParameter( "art_category");
        if ( isset( $aWhere ) && $aWhere ) {
            foreach ($oCatTree as $oCategory ) {
                if ( $oCategory->oxcategories__oxid->value == $sChosenCat ) {
                    $oCategory->selected = 1;
                }
            }

            /*
            while (list($key, $val) = each($oCatTree)) {
                echo " AAAAA ";
                if ( $val->oxcategories__oxid->value == $sChosenCat) {
                    $val->selected = 1;
                    $oCatTree[$key] = $val;
                }
            }
            */
        }
        $this->_aViewData["cattree"] =  $oCatTree;

        return "article_list.tpl";
    }

    /**
     * Sets articles sorting by category.
     *
     * @param string $sSQL sql string
     *
     * @return string
     */
    protected function _changeselect( $sSql )
    {
        // add category
        $sChosenCat  = oxConfig::getParameter( "art_category");
        if ( $sChosenCat ) {
            $sTable  = getViewName("oxarticles");
            $sO2CView = getViewName("oxobject2category");
            $sInsert = "from $sTable left join $sO2CView on $sTable.oxid = $sO2CView.oxobjectid where $sO2CView.oxcatnid = '$sChosenCat' and ";
            //$sSql = str_replace( "from\s+$sTable\s+where", $sInsert, $sSql);
            $sSql = preg_replace( "/from\s+$sTable\s+where/i", $sInsert, $sSql);
        }

        return $sSql;
    }

    /**
     * Builds and returns array of SQL WHERE conditions.
     *
     * @return array
     */
    public function buildWhere()
    {
        // we override this to select only parent articles
        $sViewName = getViewName( 'oxarticles' );
        $this->_aWhere = parent::buildWhere();
        if ( !is_array($this->_aWhere))
            $this->_aWhere = array();
        // adding folder check
        $sFolder = oxConfig::getParameter( 'folder' );
        if ( $sFolder && $sFolder != '-1' ) {
            $this->_aWhere["$sViewName.oxfolder"] = $sFolder;
        }
        return $this->_aWhere;
    }

    /**
     * Adding empty parent check
     *
     * @param array  $aWhere  SQL condition array
     * @param string $sqlFull SQL query string
     *
     * @return $sQ
     */
    protected function _prepareWhereQuery( $aWhere, $sqlFull )
    {
        $sQ = parent::_prepareWhereQuery( $aWhere, $sqlFull );
        $sViewName = getViewName( 'oxarticles' );
        //searchong for empty oxfolder fields
        $sQ .= " and $sViewName.oxparentid = ''";
        return $sQ;
    }

    /**
     * Deletes entry from the database
     *
     * @return null
     */
    public function deleteEntry()
    {
        $oArticle = oxNew( "oxarticle");
        if ( $oArticle->load( oxConfig::getParameter( "oxid") ) ) {
            parent::deleteEntry();
        }
    }

}
