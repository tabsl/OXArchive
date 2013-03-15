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
 * $Id: roles_fegroups.inc.php 14035 2008-11-06 14:48:53Z arvydas $
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
 * Class manages front-end user groups rights
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

        $sId      = oxConfig::getParameter( 'oxid' );
        $sSynchId = oxConfig::getParameter( 'synchoxid' );

        $iAction = 1;
        // category selected or not ?
        if ( !$sId) {
            $sQAdd  = " from $sGroupTable where 1 ";
        } else {
            $sQAdd   = " from $sGroupTable, oxobjectrights where ";
            $sQAdd  .= " oxobjectrights.oxobjectid = '$sId' and ";
            $sQAdd  .= " oxobjectrights.oxoffset = ($sGroupTable.oxrrid div 31) and ";
            $sQAdd  .= " oxobjectrights.oxgroupidx & (1 << ($sGroupTable.oxrrid mod 31)) and oxobjectrights.oxaction = $iAction ";
        }

        if ( $sSynchId && $sSynchId != $sId) {
            $sQAdd   = " from $sGroupTable left join oxobjectrights on ";
            $sQAdd  .= " oxobjectrights.oxoffset = ($sGroupTable.oxrrid div 31) and ";
            $sQAdd  .= " oxobjectrights.oxgroupidx & (1 << ($sGroupTable.oxrrid mod 31)) and oxobjectrights.oxobjectid='$sSynchId' and oxobjectrights.oxaction = $iAction ";
            $sQAdd  .= " where oxobjectrights.oxobjectid != '$sSynchId' or (oxobjectid is null) ";
        }

        return $sQAdd;
    }

    /**
     * Removes chosen user group (groups) from delivery list
     *
     * @return null
     */
    public function removegroupfromferoles()
    {
        $aChosenGrp = $this->_getActionIds( 'oxgroups.oxrrid' );
        $soxId      = oxConfig::getParameter( 'oxid');

        $iAction    = 1;

        // removing all
        if ( oxConfig::getParameter( 'all' ) ) {
            $sGroupTable = getViewName('oxgroups');
            $aChosenGrp = $this->_getAll( $this->_addFilter( "select $sGroupTable.oxrrid ".$this->_getQuery() ) );
        }

        if ( isset( $soxId) && $soxId != "-1" && is_array( $aChosenGrp ) && $aChosenGrp ) {
            $aIndexes = array();
            foreach ($aChosenGrp as $iRRIdx) {
                $iOffset = ( int ) ( $iRRIdx / 31 );
                $iBitMap = 1 << ( $iRRIdx % 31 );

                // summing indexes
                if ( !isset( $aIndexes[ $iOffset ] ) )
                    $aIndexes[ $iOffset ] = $iBitMap;
                else
                    $aIndexes[ $iOffset ] = $aIndexes [ $iOffset ] | $iBitMap;
            }

            // iterating through indexes and applying to (sub)categories R&R
            foreach ( $aIndexes as $iOffset => $sIdx ) {
                // processing category
                $sQ  = "update oxobjectrights set oxgroupidx = oxgroupidx & ~$sIdx where oxobjectid = '$soxId' and oxoffset = $iOffset and oxaction = $iAction ";
                oxDb::getDb()->Execute( $sQ );
            }

            // removing cleared
            $sQ = "delete from oxobjectrights where oxgroupidx = 0";
            oxDb::getDb()->Execute( $sQ );

        }
    }

    /**
     * Adds chosen user group (groups) to R&R list
     *
     * @return null
     */
    public function addgrouptoferoles()
    {
        $aChosenCat = $this->_getActionIds( 'oxgroups.oxrrid' );
        $soxId      = oxConfig::getParameter( 'synchoxid' );

        $iAction    = 1;
        if ( oxConfig::getParameter( 'all' ) ) {
            $sGroupTable = getViewName('oxgroups');
            $aChosenCat = $this->_getAll( $this->_addFilter( "select $sGroupTable.oxrrid ".$this->_getQuery() ) );
        }

        if ( isset( $soxId) && $soxId != "-1" && isset( $aChosenCat) && $aChosenCat) {
            $aIndexes = array();
            foreach ( $aChosenCat as $iRRIdx) {
                $iOffset = ( int ) ( $iRRIdx / 31 );
                $iBitMap = 1 << ( $iRRIdx % 31 );

                // summing indexes
                if ( !isset( $aIndexes[ $iOffset ] ) )
                    $aIndexes[ $iOffset ] = $iBitMap;
                else
                    $aIndexes[ $iOffset ] = $aIndexes [ $iOffset ] | $iBitMap;
            }

            // iterating through indexes and applying to (sub)categories R&R
            foreach ( $aIndexes as $iOffset => $sIdx ) {
                // processing category
                $sQ  = "insert into oxobjectrights (oxid, oxobjectid, oxgroupidx, oxoffset, oxaction) ";
                $sQ .= "values ('".oxUtilsObject::getInstance()->generateUID()."', '$soxId', $sIdx, $iOffset,  $iAction ) on duplicate key update oxgroupidx = (oxgroupidx | $sIdx ) ";
                oxDb::getDb()->Execute( $sQ );
            }
        }
    }
}
