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
 * @copyright © OXID eSales AG 2003-2008
 * $Id: category_rights_buyable.inc.php 14035 2008-11-06 14:48:53Z arvydas $
 */

$aColumns = array( 'container1' => array(    // field , table,  visible, multilanguage, ident
                                        array( 'oxtitle', 'oxgroups', 1, 0, 0 ),
                                        array( 'oxid',    'oxgroups', 0, 0, 0 ),
                                        array( 'oxrrid',  'oxgroups', 0, 0, 1 ),
                                        ),
                     'container2' => array(
                                        array( 'oxtitle', 'oxgroups', 1, 0, 0 ),
                                        array( 'oxid',    'oxgroups', 0, 0, 0 ),
                                        array( 'oxrrid',  'oxgroups', 0, 0, 1 ),
                                        )
                    );
/**
 * Class manages category rights to buy
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
        // looking for table/view
        $sGroupTable = getViewName('oxgroups');
        $iAction = 2;

        $sArtId      = oxConfig::getParameter( 'oxid' );
        $sSynchArtId = oxConfig::getParameter( 'synchoxid' );

        // category selected or not ?
        if ( !$sArtId) {
            $sQAdd  = " from $sGroupTable where 1 ";
        } else {
            // fetching article RR view index
            $sQAdd   = " from $sGroupTable, oxobjectrights where ";
            $sQAdd  .= " oxobjectrights.oxobjectid = '$sArtId' and ";
            $sQAdd  .= " oxobjectrights.oxoffset = ( $sGroupTable.oxrrid div 31 ) and ";
            $sQAdd  .= " oxobjectrights.oxgroupidx & ( 1 << ( $sGroupTable.oxrrid mod 31 ) ) and oxobjectrights.oxaction = $iAction ";
        }

        if ( $sSynchArtId && $sSynchArtId != $sArtId ) {
            $sQAdd   = " from $sGroupTable left join oxobjectrights on ";
            $sQAdd  .= " oxobjectrights.oxoffset = ($sGroupTable.oxrrid div 31) and ";
            $sQAdd  .= " oxobjectrights.oxgroupidx & ( 1 << ( $sGroupTable.oxrrid mod 31 ) ) and oxobjectrights.oxobjectid='$sSynchArtId' and oxobjectrights.oxaction = $iAction ";
            $sQAdd  .= " where oxobjectrights.oxobjectid != '$sSynchArtId' or ( oxobjectid is null )";
        }

        return $sQAdd;
    }

    /**
     * Removing article from Buy Article group list
     *
     * @return null
     */
    public function removegroupfromcatbuy()
    {
        $myConfig = $this->getConfig();

        $aGroups = $this->_getActionIds( 'oxgroups.oxrrid' );
        $soxId   = oxConfig::getParameter( 'oxid');

        $iRange  = oxConfig::getParameter( 'oxrrapplyrange');
        $iAction = 2;

        // removing all
        if ( oxConfig::getParameter( 'all' ) ) {
            $sGroupTable = getViewName('oxgroups');
            $aGroups = $this->_getAll( $this->_addFilter( "select $sGroupTable.oxrrid ".$this->_getQuery() ) );
        }

        if ( $soxId != "-1" && isset( $soxId) && count($aGroups)) {
            $aIndexes = array();
            foreach ($aGroups as $iRRIdx) {
                $iOffset = ( int ) ( $iRRIdx / 31 );
                $iBitMap = 1 << ( $iRRIdx % 31 );

                // summing indexes
                if ( !isset( $aIndexes[ $iOffset ] ) )
                    $aIndexes[ $iOffset ] = $iBitMap;
                else
                    $aIndexes[ $iOffset ] = $aIndexes [ $iOffset ] | $iBitMap;
            }

            $oCat = oxNew( "oxcategory" );
            $oCat->load( $soxId);

            $sShopID  = $myConfig->getShopID();

            $iShopBit = oxUtils::getInstance()->getShopBit($sShopID);
            $sO2CView = getViewName('oxobject2category');

            // iterating through indexes and applying to (sub)categories R&R
            foreach ( $aIndexes as $iOffset => $sIdx ) {
                // processing category
                $sQ  = "update oxobjectrights set oxgroupidx = oxgroupidx & ~$sIdx where oxobjectid = '$soxId' and oxoffset = $iOffset and oxaction = $iAction ";
                oxDb::getDb()->Execute( $sQ );

                // processing articles
                $sQ  = "update oxobjectrights set oxgroupidx = oxgroupidx & ~$sIdx where oxaction = $iAction and oxoffset = $iOffset and oxobjectid in ( ";
                $sQ .= "select oxobject2category.oxobjectid from  $sO2CView as oxobject2category where oxobject2category.oxcatnid='$soxId' ) ";
                oxDb::getDb()->Execute( $sQ );

                if ($iRange) {
                    // processing subcategories
                    $sQ  = "update oxobjectrights, oxcategories set oxobjectrights.oxgroupidx = oxobjectrights.oxgroupidx & ~$sIdx where oxobjectrights.oxoffset = $iOffset and oxobjectrights.oxaction = $iAction ";
                    $sQ .= "and oxobjectrights.oxobjectid = oxcategories.oxid and ";
                    $sQ .= "( ( oxcategories.oxshopincl & $iShopBit ) > 0 and ( oxcategories.oxshopexcl & $iShopBit ) = 0 ) and ";
                    $sQ .= "oxcategories.oxleft > ".$oCat->oxcategories__oxleft->value." and oxcategories.oxright < ".$oCat->oxcategories__oxright->value." and ";
                    $sQ .= "oxcategories.oxrootid='".$oCat->oxcategories__oxrootid->value."' ";
                    oxDb::getDb()->Execute( $sQ );

                    // processing articles
                    $sQ  = "update oxobjectrights set oxobjectrights.oxgroupidx = oxobjectrights.oxgroupidx & ~$sIdx ";
                    $sQ .= "where oxobjectrights.oxaction = $iAction and oxobjectrights.oxobjectid in ( ";
                    $sQ .= "select oxobject2category.oxobjectid from $sO2CView as oxobject2category ";
                    $sQ .= "left join oxcategories on oxcategories.oxid = oxobject2category.oxcatnid ";
                    $sQ .= "where ( ( oxcategories.oxshopincl & $iShopBit ) > 0 and ( oxcategories.oxshopexcl & $iShopBit ) = 0 ) and ";
                    $sQ .= "oxcategories.oxrootid = '".$oCat->oxcategories__oxrootid->value."' and ";
                    $sQ .= "oxcategories.oxleft > ".$oCat->oxcategories__oxleft->value." and ";
                    $sQ .= "oxcategories.oxright < ".$oCat->oxcategories__oxright->value." ) ";
                    oxDb::getDb()->Execute( $sQ );
                }
            }

            // removing cleared
            $sQ = "delete from oxobjectrights where oxgroupidx = 0";
            oxDb::getDb()->Execute( $sQ );
        }
    }

    /**
     * Adding article to Buy Article group list
     *
     * @return null
     */
    public function addgrouptocatbuy()
    {
        $myConfig = $this->getConfig();

        $aGroups = $this->_getActionIds( 'oxgroups.oxrrid' );
        $soxId   = oxConfig::getParameter( 'synchoxid');

        $iRange  = oxConfig::getParameter( 'oxrrapplyrange');
        $iAction = 2;

        // adding
        if ( oxConfig::getParameter( 'all' ) ) {
            $sGroupTable = getViewName('oxgroups');
            $aGroups = $this->_getAll( $this->_addFilter( "select $sGroupTable.oxrrid ".$this->_getQuery() ) );
        }

        if ( $soxId != "-1" && isset( $soxId) && count($aGroups)) {
            $aIndexes = array();
            foreach ($aGroups as $iRRIdx) {
                $iOffset = ( int ) ( $iRRIdx / 31 );
                $iBitMap = 1 << ( $iRRIdx % 31 );

                // summing indexes
                if ( !isset( $aIndexes[ $iOffset ] ) )
                    $aIndexes[ $iOffset ] = $iBitMap;
                else
                    $aIndexes[ $iOffset ] = $aIndexes [ $iOffset ] | $iBitMap;
            }

            $oCat = oxNew( "oxcategory" );
            $oCat->load( $soxId);

            $sShopID  = $myConfig->getShopID();
            //$iShopBit = pow( 2, ( $sShopID - 1 ) );
            $iShopBit = oxUtils::getInstance()->getShopBit($sShopID);
            $sO2CView = getViewName('oxobject2category');

            // iterating through indexes and applying to (sub)categories R&R
            foreach ( $aIndexes as $iOffset => $sIdx ) {
                // processing category
                $sQ  = "insert into oxobjectrights (oxid, oxobjectid, oxgroupidx, oxoffset, oxaction) ";
                $sQ .= "values ('".oxUtilsObject::getInstance()->generateUID()."', '$soxId', $sIdx, $iOffset,  $iAction ) on duplicate key update oxgroupidx = (oxgroupidx | $sIdx ) ";
                oxDb::getDb()->Execute( $sQ );

                // processing articles
                $sQ  = "insert into oxobjectrights (oxid, oxobjectid, oxgroupidx, oxoffset, oxaction) ";
                $sQ .= "select md5( concat( a.oxobjectid, oxobject2category.oxid) ), oxobject2category.oxobjectid, a.oxgroupidx, a.oxoffset, a.oxaction ";
                $sQ .= "from $sO2CView as oxobject2category left join oxobjectrights a on oxobject2category.oxcatnid=a.oxobjectid where oxobject2category.oxcatnid='$soxId' and a.oxaction = $iAction ";
                $sQ .= "on duplicate key update oxobjectrights.oxgroupidx = (oxobjectrights.oxgroupidx | a.oxgroupidx ) ";
                oxDb::getDb()->Execute( $sQ );

                if ( $iRange ) {
                    // processing subcategories
                    $sQ  = "insert into oxobjectrights (oxid, oxobjectid, oxgroupidx, oxoffset, oxaction) ";
                    $sQ .= "select '".oxUtilsObject::getInstance()->generateUID()."', oxcategories.oxid, $sIdx, $iOffset, $iAction from oxcategories ";
                    $sQ .= "where ( ( oxcategories.oxshopincl & $iShopBit ) > 0 and ( oxcategories.oxshopexcl & $iShopBit ) = 0 ) and ";
                    $sQ .= "oxcategories.oxleft > ".$oCat->oxcategories__oxleft->value." and oxcategories.oxright < ".$oCat->oxcategories__oxright->value." and ";
                    $sQ .= "oxcategories.oxrootid='".$oCat->oxcategories__oxrootid->value."' ";
                    $sQ .= "on duplicate key update oxgroupidx = (oxgroupidx | $sIdx ) ";
                    oxDb::getDb()->Execute( $sQ );

                    // processing articles
                    $sQ  = "insert into oxobjectrights (oxid, oxobjectid, oxgroupidx, oxoffset, oxaction) ";
                    $sQ .= "select md5( concat( a.oxobjectid, oxobject2category.oxid, a.oxaction, a.oxoffset) ), oxobject2category.oxobjectid, a.oxgroupidx, a.oxoffset, a.oxaction ";
                    $sQ .= "from $sO2CView oxobject2category ";
                    $sQ .= "left join oxcategories on oxcategories.oxid = oxobject2category.oxcatnid ";
                    $sQ .= "left join oxobjectrights a on a.oxobjectid = '$soxId' ";
                    $sQ .= "where oxcategories.oxrootid = '".$oCat->oxcategories__oxrootid->value."' and ";
                    $sQ .= "oxcategories.oxleft > ".$oCat->oxcategories__oxleft->value." and  ";
                    $sQ .= "oxcategories.oxright < ".$oCat->oxcategories__oxright->value." and a.oxaction = $iAction ";
                    $sQ .= "on duplicate key update oxobjectrights.oxgroupidx = (oxobjectrights.oxgroupidx | a.oxgroupidx )";
                    oxDb::getDb()->Execute( $sQ );
                }
            }
        }
    }
}
