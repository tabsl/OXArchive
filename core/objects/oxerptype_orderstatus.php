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
 * @copyright (C) OXID eSales AG 2003-2009
 * @version OXID eShop CE
 * $Id: oxerptype_orderstatus.php 16303 2009-02-05 10:23:41Z rimvydas.paskevicius $
 */

require_once( 'oxerptype.php');

class oxERPType_OrderStatus extends oxERPType
{
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxorderarticles';

        $this->_aFieldList = array(
            'OXID'          => 'OXID',
            'OXERPSTATUS_STATUS'   => 'OXERPSTATUS_STATUS',
            'OXERPSTATUS_TIME'     => 'OXERPSTATUS_TIME',
            'OXERPSTATUS_TRACKID'  => 'OXERPSTATUS_TRACKID'
        );
    }

    public function getSQL( $sWhere, $iLanguage = 0, $iShopID = 1)
    {
         if( strstr( $sWhere, 'where'))
            $sWhere .= ' and ';
        else
            $sWhere .= ' where ';

        $sWhere .= 'oxordershopid = \''.$iShopID.'\'';

        return parent::getSQL($sWhere, $iLanguage, $iShopID);
    }

    public function checkWriteAccess($sOxid)
    {
        $myConfig = oxConfig::getInstance();

        $oDB = oxDb::getDb();

        $sSql = "select oxordershopid from ". $this->getTableName($myConfig->getShopId()) ." where oxid = '". $sOxid ."'";
        $sRes = $oDB->getOne($sSql);

        if($sRes && $sRes != $myConfig->getShopId()){
            throw new Exception( oxERPBase::$ERROR_USER_NO_RIGHTS);
        }
    }

    /**
     * issued before saving an object. can modify aData for saving
     *
     * @param oxBase $oShopObject
     * @param array  $aData
     * @return array
     */
    protected function _preAssignObject($oShopObject, $aData, $blAllowCustomShopId)
    {
        $aData = parent::_preAssignObject($oShopObject, $aData, $blAllowCustomShopId);
        if (isset($aData['OXERPSTATUS_STATUS'])
            && isset($aData['OXERPSTATUS_TIME'])
            && isset($aData['OXERPSTATUS_TRACKID']))
            {
            $oStatus = new stdClass();
            $oStatus->STATUS        = $aData['OXERPSTATUS_STATUS'];
            $oStatus->date          = $aData['OXERPSTATUS_TIME'];
            $oStatus->trackingid    = $aData['OXERPSTATUS_TRACKID'];
            $aData['OXERPSTATUS']   = serialize( $oStatus );
        }
        return $aData;
    }

    /**
     * We have the possibility to add some data
     *
     * @param array $aFields
     */
    public function addExportData( $aFields)
    {
        if ($aFields['OXERPSTATUS']) {
            $oStatus = @unserialize($aFields['OXERPSTATUS']);
            unset($aFields['OXERPSTATUS']);
            if (is_object($oStatus)) {
                $aFields['OXERPSTATUS_STATUS']  = $oStatus->STATUS;
                $aFields['OXERPSTATUS_TIME']    = $oStatus->date;
                $aFields['OXERPSTATUS_TRACKID'] = $oStatus->trackingid;
            }
        }
        return $aFields;
    }


    /**
     * return sql column name of given table column
     *
     * @param string $sField
     * @param int    $iLanguage
     *
     * @return string
     */
    protected function getSqlFieldName($sField, $iLanguage = 0, $iShopID = 1)
    {
        switch ($sField) {
            case 'OXERPSTATUS_STATUS':
                return "(OXERPSTATUS) as OXERPSTATUS";
            case 'OXERPSTATUS_TIME':
            case 'OXERPSTATUS_TRACKID':
                return "'' as $sField";
        }

        return parent::getSqlFieldName($sField, $iLanguage, $iShopID);
    }


}
