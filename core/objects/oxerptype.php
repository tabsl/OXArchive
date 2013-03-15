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
 * $Id: oxerptype.php 16303 2009-02-05 10:23:41Z rimvydas.paskevicius $
 */

class oxERPType
{
    public static $ERROR_WRONG_SHOPID = "Wrong shop id, operation not allowed!";

    protected   $_sTableName        = null;
    protected   $_sFunctionSuffix   = null;
    protected   $_aFieldList        = null;
    protected   $_aKeyFieldList     = null;
    protected   $_sShopObjectName   = null;

    /**
     * If true a export will be restricted vias th oxshopid column of the table
     *
     * @var unknown_type
     */
    protected $_blRestrictedByShopId = false;

    /**
     * versioning support for db layers
     *
     * @var array
     */
    protected $_aFieldListVersions = null;

    /**
     * getter for _sFunctionSuffix
     *
     * @return string
     */
    public function getFunctionSuffix()     {   return $this->_sFunctionSuffix;     }

    /**
     * getter for _sShopObjectName
     *
     * @return string
     */
    public function getShopObjectName()     {   return $this->_sShopObjectName;     }

    /**
     * getter for _sTableName
     *
     * @return string
     */
    public function getBaseTableName()      {   return $this->_sTableName;          }

    public function __construct()
    {
        $this->_sFunctionSuffix = str_replace( "oxERPType_", "", get_class( $this));
        if (isset($this->_aFieldListVersions)) {
            $this->_aFieldList = $this->_aFieldListVersions[oxERPBase::getUsedDbFieldsVersion()];
        }
    }

    /**
     * setter for the function prefix
     *
     * @param string $sNew
     */
    public function setFunctionSuffix($sNew)
    {
        $this->_sFunctionSuffix = $sNew;
    }

    /**
     * setter for field list
     *
     * @param array $aFieldList
     */
    public function setFieldList($aFieldList)
    {
        $this->_aFieldList = $aFieldList;
    }

    /**
     * Returns table or Viewname
     *
     * @return string
     */
    public function getTableName($iShopID=1)
    {
        return getViewName($this->_sTableName,$iShopID);
    }

    /**
     * Creates Array with [iLanguage][sFieldName]
     *
     * @return array
     */
    private function _getMultilangualFields()
    {
        $aRet = array();

        $aData = oxDb::getInstance()->getTableDescription( $this->_sTableName);

        foreach( $aData as $key => $oADODBField) {
            $iLang = substr( $oADODBField->name, strlen( $oADODBField->name) - 1, 1);
            if( is_numeric( $iLang) &&  substr( $oADODBField->name, strlen( $oADODBField->name) - 2, 1) == '_') {
                // multilangual field
                $sMainFld = str_replace( '_'.$iLang, "", $oADODBField->name);
                $aRet[$iLang][$sMainFld] = $oADODBField->name.' as '.$sMainFld;
            }
        }

        return $aRet;
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
        if( $iLanguage) {
            $aMultiLang = $this->_getMultilangualFields();
            // we need to load different fields
            if( isset( $aMultiLang[$iLanguage][$sField]))
                $sField = $aMultiLang[$iLanguage][$sField];
        }
        return $sField;
    }

    /**
     * returns SQL string for this type
     *
     * @param string $sWhere
     * @param integer $iLanguage
     * @return string
     */
    public function getSQL( $sWhere, $iLanguage = 0, $iShopID = 1)
    {
        if( !$this->_aFieldList)
            return;

        $sSQL    = 'select ';
        $blSep = false;

        foreach( $this->_aFieldList as $sField) {
            if( $blSep)
                $sSQL .= ',';

            $sSQL .= $this->getSqlFieldName($sField, $iLanguage, $iShopID);
            $blSep = true;
        }

        if($this->_blRestrictedByShopId){
            if( strstr( $sWhere, 'where'))
                $sWhere .= ' and ';
            else
                $sWhere .= ' where ';

            $sWhere .= 'oxshopid = \''.$iShopID.'\'';
        }

        $sSQL .= ' from '.$this->getTableName($iShopID).' '.$sWhere;

        return $sSQL;
    }

    /**
     * returns the "order by " string for  a sql query
     *
     * @param string $sFieldName order by that field
     * @param string $sType allowed values ASC and DESC
     * @return string
     */
    public function getSortString($sFieldName = null, $sType = null)
    {
        $sRes = " order by ";
        if ($sFieldName) {
            $sRes .= $sFieldName;
        } else {
            $sRes .= "oxid";
        }
        if ($sType && ($sType == "ASC" || $sType == "DESC")) {
            $sRes .= " ". $sType;
        }
        return $sRes;
    }

    /**
     * Basic access check for writing data, checks for same shopid, should be overridden if field oxshopid does not exist
     *
     * @param string $sOxid the oxid of the object
     *
     * @throws Exception
     */
    public function checkWriteAccess($sOxid)
    {
        $oObj = oxNew("oxbase");
        $oObj->init($this->_sTableName);
        if ($oObj->load($sOxid)) {
            $sFld = $this->_sTableName.'__oxshopid';
            if (isset($oObj->$sFld)) {
                $sRes = $oObj->$sFld->value;
                if($sRes && $sRes != oxConfig::getInstance()->getShopId()) {
                    throw new Exception( oxERPBase::$ERROR_USER_NO_RIGHTS);
                }
            }
        }
    }

    /**
     * checks done to make sure deletion is possible and allowed
     *
     * @param string $sId id of object
     *
     * @throws Exception
     *
     * @return object of given type
     */
    public function getObjectForDeletion( $sId)
    {

        $myConfig = oxConfig::getInstance();

        if( !isset($sId))
            throw new Exception( "Missing ID!");

        $oObj = oxNew( $this->getShopObjectName(), "core");

        if(!$oObj->exists($sId)){
            throw new Exception( $this->getShopObjectName(). " " . $sId. " does not exists!");
        }

        //We must load the object here, to check shopid and return it for further checks
        if(!$oObj->Load($sId)){
            //its possible that access is restricted allready
            throw new Exception( "No right to delete object {$sId} !");
        }

        if(!$this->_isAllowedToEdit($oObj->getShopId())) {
            throw new Exception( "No right to delete object {$sId} !");
        }

        return $oObj;
    }

    protected function _isAllowedToEdit($iShopId)
    {
        if ($oUsr = oxUser::getAdminUser()) {
            if ($oUsr->oxuser__oxrights->value == "malladmin") {
                return true;
            } elseif ($oUsr->oxuser__oxrights->value == (int) $iShopId) {
                return true;
            }
        }
        return false;
    }

    /**
     * direct sql check if it is allowed to delete the OXID of the current table
     *
     * @param string $sId
     */
    protected function _directSqlCheckForDeletion($sId)
    {
        $sSql = "select oxshopid from ".$this->_sTableName." where oxid = '" . $sId . "'";
        try {
            $iShopId = oxDb::getDb()->getOne($sSql);
        } catch (Exception $e) {
            // no shopid was found
            return;
        }
        if(!$this->_isAllowedToEdit($iShopId)) {
            throw new Exception( "No right to delete object {$sId} !");
        }
    }

    /**
     * default check if it is allowed to delete the OXID of the current table
     *
     * @param string $sId
     */
    public function checkForDeletion($sId)
    {

        if( !isset($sId)) {
            throw new Exception( "Missing ID!");
        }
        // malladmin can do it
        if ($oUsr = oxUser::getAdminUser()) {
            if ($oUsr->oxuser__oxrights->value == "malladmin") {
                return;
            }
        }
        try {
            $this->getObjectForDeletion($sId);
        } catch (oxSystemComponentException $e) {
            if ($e->getMessage() == 'EXCEPTION_SYSTEMCOMPONENT_CLASSNOTFOUND') {
                $this->_directSqlCheckForDeletion($sId);
            } else {
                throw $e;
            }
        }
    }

    /**
     * default deletion of the given OXID in the current table
     *
     * @param string  $sID
     * @return bool
     */
    public function delete($sID)
    {
        $myConfig = oxConfig::getInstance();
        $sSql = "delete from ".$this->_sTableName." where oxid = '" . $sID . "'";

        return oxDb::getDb()->Execute($sSql);
    }

    /**
     * default delete call to the given object
     *
     * @param object $oObj
     * @param string $sID
     * @return bool
     */
    public function deleteObject($oObj, $sID)
    {

        return $oObj->delete($sID);
    }

    /**
     * We have the possibility to add some data
     *
     * @param array $aFields
     */
    public function addExportData( $aFields)
    {
        return $aFields;
    }

    /**
     * allows to modify data before import
     *
     * @param array $aFields
     *
     * @deprecated
     * @see _preAssignObject
     *
     * @return array
     */
    public function addImportData($aFields)
    {
        return $aFields;
    }

    /**
     * used for the RR implementation, right now not really used
     *
     * @return array
     */
    public function GetRightFields()
    {
        $aRParams = array();

        foreach($this->_aFieldList as $sField) {
            $aRParams[] = strtolower($this->_sTableName.'__'.$sField);
        }
        return $aRParams;
    }

    /**
     * returns the predefined field list
     *
     * @return array
     */
    public function getFieldList() {
        return $this->_aFieldList;
    }

  /*  public function GetTable() {
        return $this->_sTableName;
    }*/

    /**
     * returns the keylist array
     *
     * @return array
     */
    public function getKeyFields() {
        return $this->_aKeyFieldList;
    }

    /**
     * returns try if type has key fields array
     *
     * @return bool
     */
    public function hasKeyFields() {
        if(isset($this->_aKeyFieldList) && is_array($this->_aKeyFieldList)){
            return true;
        }
        return false;
    }

    /**
     * issued before saving an object. can modify aData for saving
     *
     * @param oxBase $oShopObject
     * @param array  $aData
     * @param bool   $blAllowCustomShopId
     *
     * @return array
     */
    protected function _preAssignObject($oShopObject, $aData, $blAllowCustomShopId)
    {
        if( !isset( $aData['OXID'])) {
            throw new Exception( "OXID missing, seems to be wrong Format!");
        }
        if(!$oShopObject->exists( $aData['OXID'] )) {
            //$aData['OXSHOPID'] = $this->_iShopID;
            if (!$blAllowCustomShopId) {
                if (isset($aData['OXSHOPID'])) {
                    $aData['OXSHOPID'] = oxConfig::getInstance()->getShopId();
                }
            }
            if(!array_key_exists('OXSHOPINCL',$aData)) {
                $aData['OXSHOPINCL'] = oxUtils::getInstance()->getShopBit($aData['OXSHOPID']);
            }
            if(!array_key_exists('OXSHOPEXCL',$aData)) {
                $aData['OXSHOPEXCL'] = 0;
            }
        }
        if (isset($aData['OXACTIV'])) {
            $aData['OXACTIVE'] = $aData['OXACTIV'];
        }
        if (isset($aData['OXACTIVFROM'])) {
            $aData['OXACTIVEFROM'] = $aData['OXACTIVFROM'];
        }
        if (isset($aData['OXACTIVTO'])) {
            $aData['OXACTIVETO'] = $aData['OXACTIVTO'];
        }
        for ($i=1;$i<4;$i++) {
            if (isset($aData['OXACTIV_'.$i])) {
                $aData['OXACTIVE_'.$i] = $aData['OXACTIV_'.$i];
            }
        }
        // null values support
        foreach ($aData as $key => $val) {
            if (!strlen((string)$val)) {
                // oxbase whill quote it as string if db does not support null for this field
                $aData[$key] = null;
            }
        }
        return $aData;
    }

    /**
     * prepares object for saving in shop
     * returns true if save can proceed further
     *
     * @param $oShopObject
     * @param $aData
     *
     * @return boolean
     */
    protected function _preSaveObject($oShopObject, $aData)
    {
        return true;
    }

    /**
     * saves data by calling object saving
     *
     * @param array $aData
     * @param bool   $blAllowCustomShopId
     *
     * @return string | false
     */
    public function saveObject($aData, $blAllowCustomShopId)
    {
        $sObjectName = $this->getShopObjectName();
        if( $sObjectName){
            $oShopObject = oxNew( $sObjectName, 'core');
            if ($oShopObject instanceof oxI18n) {
                $oShopObject->SetLanguage(0);
                $oShopObject->setEnableMultilang(false);
            }
        } else {
            $oShopObject = oxNew( 'oxbase', 'core');
            $oShopObject->init($this->getBaseTableName());
        }

        $aData = $this->_preAssignObject($oShopObject, $aData, $blAllowCustomShopId);


        $oShopObject->Load( $aData['OXID']);

        $oShopObject->Assign( $aData );

        if ($blAllowCustomShopId) {
            $oShopObject->setIsDerived(false);
        }

        if ($this->_preSaveObject($oShopObject, $aData)) {
            // store
            if( $oShopObject->save()) {
                return $this->_postSaveObject($oShopObject, $aData);
            }
        }

        return false;
    }

    /**
     * post saving hook. can finish transactions if needed or ajust related data
     *
     * @param oxBase $oShopObject
     * @param data   $aData
     * @return mixed data to return
     */
    protected function _postSaveObject($oShopObject, $aData)
    {
        // returning ID on success
        return $oShopObject->getId();
    }
}

