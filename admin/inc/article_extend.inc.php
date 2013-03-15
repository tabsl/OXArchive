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
 * @package inc
 * @copyright (C) OXID eSales AG 2003-2009
 * @version OXID eShop CE
 * $Id: article_extend.inc.php 18928 2009-05-11 12:22:51Z vilma $
 */

$aColumns = array( 'container1' => array(    // field , table,         visible, multilanguage, ident
                                        array( 'oxtitle', 'oxcategories', 1, 1, 0 ),
                                        array( 'oxdesc',  'oxcategories', 1, 1, 0 ),
                                        array( 'oxid',    'oxcategories', 0, 0, 1 )
                                        ),
                     'container2' => array(
                                        array( 'oxtitle', 'oxcategories', 1, 1, 0 ),
                                        array( 'oxdesc',  'oxcategories', 1, 1, 0 ),
                                        array( 'oxid',    'oxobject2category', 0, 0, 1 ),
                                        array( 'oxtime',  'oxobject2category', 0, 0, 1 ),
                                        array( 'oxid',    'oxcategories',      0, 0, 1 )
                                        ),
                   );
/**
 * Class controls article assignment to category
 */
class ajaxComponent extends ajaxListComponent
{
    /**
     * Returns SQL query for data to fetc
     *
     * @return string
     */
    protected function _getQuery()
    {
        $sCategoriesTable = getViewName( 'oxcategories' );
        $sO2CView = getViewName( 'oxobject2category' );

        $sOxid      = oxConfig::getParameter( 'oxid' );
        $sSynchOxid = oxConfig::getParameter( 'synchoxid' );

        if ( $sOxid ) {
            // all categories article is in
            $sQAdd  = " from $sO2CView left join $sCategoriesTable on $sCategoriesTable.oxid=$sO2CView.oxcatnid ";
            $sQAdd .= " where $sO2CView.oxobjectid = '$sOxid' and $sCategoriesTable.oxid is not null ";
        } else {
            $sQAdd  = " from $sCategoriesTable where $sCategoriesTable.oxid not in ( ";
            $sQAdd .= " select $sCategoriesTable.oxid from $sO2CView left join $sCategoriesTable on $sCategoriesTable.oxid=$sO2CView.oxcatnid ";
            $sQAdd .= " where $sO2CView.oxobjectid = '$sSynchOxid' and $sCategoriesTable.oxid is not null ) and $sCategoriesTable.oxpriceto = '0'";
        }

        return $sQAdd;
    }

    /**
     * Returns array with DB records
     *
     * @param string $sQ SQL query
     *
     * @return array
     */
    protected function _getDataFields( $sQ )
    {
        $aDataFields = parent::_getDataFields( $sQ );
        if ( oxConfig::getParameter( 'oxid' ) && is_array( $aDataFields ) && count( $aDataFields ) ) {

            // looking for smallest time value to mark record as main category ..
            $iMinPos = null;
            $iMinVal = null;
            reset( $aDataFields );
            while ( list( $iPos, $aField ) = each( $aDataFields ) ) {

                // allready set ?
                if ( $aField['_3'] == '0' ) {
                    $iMinPos = null;
                    break;
                }

                if ( !$iMinVal ) {
                    $iMinVal = $aField['_3'];
                    $iMinPos = $iPos;
                } elseif ( $iMinVal > $aField['_3'] ) {
                    $iMinPos = $iPos;
                }
            }

            // setting primary category
            if ( isset( $iMinPos ) ) {
                $aDataFields[$iMinPos]['_3'] = '0';
            }
        }

        return $aDataFields;
    }

    /**
     * Removes article from chosen category
     *
     * @return null
     */
    public function removecat()
    {
        $myConfig   = $this->getConfig();
        $aRemoveCat = $this->_getActionIds( 'oxobject2category.oxid' );
        $soxId      = oxConfig::getParameter( 'oxid' );
        $sShopID    = $myConfig->getShopId();
        $sO2CView = getViewName( 'oxobject2category' );
        $oDb = oxDb::getDb();

        // removing all
        if ( oxConfig::getParameter( 'all' ) ) {

            $sQ = $this->_addFilter( "delete $sO2CView.* ".$this->_getQuery() );
            $oDb->Execute( $sQ );

        } elseif ( is_array( $aRemoveCat ) && count( $aRemoveCat ) ) {

            $sQ = 'delete from oxobject2category where oxid in ("' . implode( '", "', $aRemoveCat ) . '")';
            $oDb->Execute( $sQ );

        }

        $this->resetContentCache();
    }

    /**
     * Adds article to chosen category
     *
     * @return null
     */
    public function addcat()
    {
        $myConfig = $this->getConfig();
        $aAddCat = $this->_getActionIds( 'oxcategories.oxid' );
        $soxId   = oxConfig::getParameter( 'synchoxid' );
        $sShopID = $myConfig->getShopId();
        $sO2CView = getViewName('oxobject2category');

        // adding
        if ( oxConfig::getParameter( 'all' ) ) {
            $sCategoriesTable = getViewName( 'oxcategories' );
            $aAddCat = $this->_getAll( $this->_addFilter( "select $sCategoriesTable.oxid ".$this->_getQuery() ) );
        }

        if ( isset( $aAddCat) && is_array($aAddCat)) {

            $oDb = oxDb::getDb();

            $oNew = oxNew( 'oxbase' );
            $oNew->init( 'oxobject2category' );
            $myUtilsObj = oxUtilsObject::getInstance();

            foreach ( $aAddCat as $sAdd ) {

                // check, if it's already in, then don't add it again
                $sSelect = 'select 1 from ' . $sO2CView . ' as oxobject2category where oxobject2category.oxcatnid="' . $sAdd . '" and oxobject2category.oxobjectid ="' . $soxId . '"';
                if ( $oDb->getOne( $sSelect ) )
                    continue;

                $oNew->setId( $myUtilsObj->generateUID() );
                $oNew->oxobject2category__oxobjectid = new oxField($soxId);
                $oNew->oxobject2category__oxcatnid   = new oxField($sAdd);
                $oNew->oxobject2category__oxtime     = new oxField(time());


                $oNew->save();
            }

            $this->resetContentCache();
        }
    }

    /**
     * Sets selected category as a default
     *
     * @return null
     */
    public function setAsDefault()
    {
        $myConfig = $this->getConfig();
        $sDefCat = oxConfig::getParameter( "defcat" );
        $soxId   = oxConfig::getParameter( "oxid" );
        $sShopId = $myConfig->getShopId();

        $sShopCheck = "";

        $sQ = "update oxobject2category set oxtime = oxtime + 10 where oxobjectid = '$soxId' $sShopCheck ";
        oxDb::getInstance()->getDb()->Execute($sQ);
        $sQ = "update oxobject2category set oxtime = 0 where oxobjectid = '$soxId' and oxcatnid = '".$sDefCat."' $sShopCheck ";
        oxDb::getInstance()->getDb()->Execute($sQ);
    }
}
