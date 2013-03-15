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
 * $Id: roles_begroups.inc.php 14035 2008-11-06 14:48:53Z arvydas $
 */

$aColumns = array( 'container1' => array(    // field , table,  visible, multilanguage, ident
                                        array( 'oxtitle',  'oxgroups', 1, 0, 0 ),
                                        array( 'oxid',     'oxgroups', 0, 0, 0 ),
                                        array( 'oxid',     'oxgroups', 0, 0, 1 ),
                                        ),
                     'container2' => array(
                                        array( 'oxtitle',  'oxgroups', 1, 0, 0 ),
                                        array( 'oxid',     'oxgroups', 0, 0, 0 ),
                                        array( 'oxid',     'oxobject2role', 0, 0, 1 ),
                                        )
                    );
/**
 * Class manages back-end user groups rights
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

        $sRoleId      = oxConfig::getParameter( 'oxid' );
        $sSynchRoleId = oxConfig::getParameter( 'synchoxid' );

        // category selected or not ?
        if ( !$sRoleId ) {
            $sQAdd  = " from $sGroupTable where 1 ";
        } else {
            $sQAdd  = " from oxobject2role, $sGroupTable where oxobject2role.oxtype = 'oxgroups' and ";
            $sQAdd .= " oxobject2role.oxroleid = '$sRoleId' and $sGroupTable.oxid=oxobject2role.oxobjectid ";
        }

        if ( $sSynchRoleId && $sSynchRoleId != $sRoleId ) {
            $sQAdd .= " and $sGroupTable.oxid not in ( ";
            $sQAdd .= " select oxobject2role.oxobjectid from oxobject2role where oxobject2role.oxtype = 'oxgroups' and ";
            $sQAdd .= " oxobject2role.oxroleid = '$sSynchRoleId' ) ";
        }

        return $sQAdd;
    }

    /**
     * Removes User group from R&R
     *
     * @return null
     */
    public function removegroupfromberoles()
    {
        $aRemoveGroups = $this->_getActionIds( 'oxobject2role.oxid' );
        if ( oxConfig::getParameter( 'all' ) ) {

            $sQ = $this->_addFilter( "delete oxobject2role.* ".$this->_getQuery() );
            oxDb::getDb()->Execute( $sQ );

        } elseif ( $aRemoveGroups && is_array( $aRemoveGroups ) ) {
            $sQ = "delete from oxobject2role where oxobject2role.oxid in ('" . implode( "', '", $aRemoveGroups ) . "') ";
            oxDb::getDb()->Execute( $sQ );
        }
    }

    /**
     * Adds User group to R&R
     *
     * @return null
     */
    public function addgrouptoberoles()
    {
        $aChosenCat = $this->_getActionIds( 'oxgroups.oxid' );
        $soxId       = oxConfig::getParameter( 'synchoxid' );
        if ( oxConfig::getParameter( 'all' ) ) {
            $sGroupTable = getViewName('oxgroups');
            $aChosenCat = $this->_getAll( $this->_addFilter( "select $sGroupTable.oxid ".$this->_getQuery() ) );
        }
        if ( $soxId && $soxId != "-1" && is_array( $aChosenCat ) ) {
            foreach ( $aChosenCat as $sChosenCat) {
                $oRiRo = oxNew( "oxbase" );
                $oRiRo->init( "oxobject2role" );
                $oRiRo->oxobject2role__oxobjectid = new oxField($sChosenCat);
                $oRiRo->oxobject2role__oxroleid   = new oxField($soxId);
                $oRiRo->oxobject2role__oxtype     = new oxField("oxgroups");
                $oRiRo->save();
            }
        }
    }
}
