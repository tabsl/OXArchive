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
 * @copyright © OXID eSales AG 2003-2009
 * $Id: article_rights_buyable.inc.php 14035 2008-11-06 14:48:53Z arvydas $
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
 * Class manages article rights to buy
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
        $sGroupTable = getViewName('oxgroups');

        $sRRId = null;
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
            $sQAdd  .= " oxobjectrights.oxoffset = ($sGroupTable.oxrrid div 31) and ";
            $sQAdd  .= " oxobjectrights.oxgroupidx & (1 << ($sGroupTable.oxrrid mod 31)) and oxobjectrights.oxaction = $iAction ";
        }

        if ( $sSynchArtId && $sSynchArtId != $sArtId) {
            $sQAdd  = " from $sGroupTable left join oxobjectrights on oxobjectrights.oxoffset = ( $sGroupTable.oxrrid div 31 ) and ";
            $sQAdd .= " oxobjectrights.oxgroupidx & (1 << ( $sGroupTable.oxrrid mod 31 ) ) and oxobjectrights.oxobjectid='$sSynchArtId' and oxobjectrights.oxaction = $iAction ";
            $sQAdd .= " where oxobjectrights.oxobjectid != '$sSynchArtId' or ( oxobjectid is null ) ";
        }

        return $sQAdd;
    }

    /**
     * Removing article from View Article group
     *
     * @return null
     */
    public function removegroupfromview()
    {
        $aGroups = $this->_getActionIds( 'oxgroups.oxrrid' );
        $soxId   = oxConfig::getParameter( 'oxid');
        $iAction = 2;

        // removing all
        if ( oxConfig::getParameter( 'all' ) ) {
            $sGroupTable = getViewName('oxgroups');
            $aGroups = $this->_getAll( $this->_addFilter( "select $sGroupTable.oxrrid ".$this->_getQuery() ) );
        }

        if ( $soxId != "-1" && isset( $soxId) && is_array($aGroups) && count($aGroups)) {
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

            // iterating through indexes and applying to (sub)categories R&R
            foreach ( $aIndexes as $iOffset => $sIdx ) {
                // processing article
                $sQ  = "update oxobjectrights set oxgroupidx = oxgroupidx & ~$sIdx where oxobjectid='$soxId' and oxoffset = $iOffset and oxaction = $iAction ";
                oxDb::getDb()->Execute( $sQ );
            }

            // removing cleared
            $sQ = "delete from oxobjectrights where oxgroupidx = 0";
            oxDb::getDb()->Execute( $sQ );
        }
    }

    /**
     * Adding article to View Article group list
     *
     * @return null
     */
    public function addgrouptoview()
    {
        $aGroups = $this->_getActionIds( 'oxgroups.oxrrid' );
        $soxId   = oxConfig::getParameter( 'synchoxid');
        $iAction = 2;

        // adding
        if ( oxConfig::getParameter( 'all' ) ) {
            $sGroupTable = getViewName('oxgroups');
            $aGroups = $this->_getAll( $this->_addFilter( "select $sGroupTable.oxrrid ".$this->_getQuery() ) );
        }

        if ( $soxId != "-1" && isset( $soxId) && is_array($aGroups) && count($aGroups)) {
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
