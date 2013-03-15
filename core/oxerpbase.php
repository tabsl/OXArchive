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
 * $Id: oxerpbase.php 16303 2009-02-05 10:23:41Z rimvydas.paskevicius $
 */

require_once( 'oxerpcompatability.php');

/**
 * oxERPBase
 *
 * @package
 * @author Lars Jankowfsky
 * @copyright Copyright (c) 2006
 * @version $Id: oxerpbase.php 16303 2009-02-05 10:23:41Z rimvydas.paskevicius $
 * @access public
 **/
abstract class oxERPBase
{

    static $ERROR_USER_WRONG = "ERROR: Could not login";
    static $ERROR_USER_NO_RIGHTS =  "Not sufficient rights to perform operation!";
    static $ERROR_USER_EXISTS = "ERROR: User already exists";
    static $ERROR_NO_INIT = "Init not executed, Access denied!";
    static $ERROR_DELETE_NO_EMPTY_CATEGORY = "Only empty category can be deleated";
    static $ERROR_OBJECT_NOT_EXISTING = "Object does not exist";

    static $MODE_IMPORT = "Import";
    static $MODE_DELETE = "Delete";

    protected   $_blInit        = false;
    protected   $_iLanguage     = null;
    protected   $_sUserID       = null;
    //session id
    protected   $_sSID          = null;

    protected static $_sRequestedVersion = '';

    /**
     * describes what db layer versions are implemented and usable with shop db version
     * 1st entry is default version (if none requested)
     *
     * @var array
     */
    protected static $_aDbLayer2ShopDbVersions = array(
        '1' => '1', '1.1' => '1', '2' => '2',
    );

    public $_aStatistics = array();
    public $_iIdx        = 0;

    // inline
    public function getStatistics()     {   return $this->_aStatistics; }
    public function getSessionID()      {   return $this->_sSID;        }

    protected abstract function _beforeExport($sType);
    protected abstract function _afterExport($sType);
    protected abstract function _beforeImport();
    protected abstract function _afterImport();
    public abstract function getImportData();
    protected abstract function _getImportType(& $aData);
    protected abstract function _getImportMode($Data);
    protected abstract function _modifyData($aData, $oType);

    /**
     * default fallback if some handler is missing
     *
     * @param string $sMethod
     * @param array $aArguments
     */
    public function __call( $sMethod, $aArguments)
    {
        throw new Exception( "ERROR: Handler for Object '$sMethod' not implemented!");
    }


    // -------------------------------------------------------------------------
    //
    // public interface
    //
    // -------------------------------------------------------------------------


    /**
     * oxERPBase::Init()
     * Init ERP Framework
     * Creates Objects, checks Rights etc.
     *
     * @param mixed $sUserName
     * @param mixed $sPassword
     * @param integer $iShopID
     * @param integer $iLanguage
     *
     * @return boolean
     **/
    public function init( $sUserName, $sPassword, $iShopID = 1, $iLanguage = 0)
    {
        $_COOKIE = array('admin_sid' => false);
        $myConfig = oxConfig::getInstance();
        $myConfig->setConfigParam( 'blAdmin', 1 );
        $myConfig->setAdminMode( true );

        $mySession = oxSession::getInstance();
        $myConfig->oActView = new FakeView();

        // hotfix #2429, #2430 MAFI
        if ($iShopID != 1) {
            $myConfig->setConfigParam('blMallUsers', false);
        }
        $myConfig->setShopId($iShopID);

        $mySession->setVar( "lang", $iLanguage);
        $mySession->setVar( "language", $iLanguage);

        $oUser = oxNew('oxuser');
        try {
            if (!$oUser->login($sUserName, $sPassword)) {
                $oUser = null;
            }
        }catch(oxUserException $e) {
            $oUser = null;
        }

        if ( !$oUser || ( isset($oUser->iError) && $oUser->iError == -1000)) {
            // authorization error
            throw new Exception( self::$ERROR_USER_WRONG );
        }
        elseif( ( $oUser->oxuser__oxrights->value == "malladmin" || $oUser->oxuser__oxrights->value == $myConfig->GetShopID()) )
        {
            $this->_sSID        = $mySession->getId();
            $this->_blInit      = true;
            $this->_iLanguage   = $iLanguage;
            $this->_sUserID     = $oUser->getId();
            //$mySession->freeze();
        } else {

            //user does not have sufficient rights for shop
            throw new Exception( self::$ERROR_USER_NO_RIGHTS);
        }

        $this->_resetIdx();

        return $this->_blInit;
    }

    /**
     * oxERPBase::loadSessionData()
     *
     * different handeling for SOAP request and CSV usage (V0.1)
     *
     * @param string $sSessionID
     **/
    public abstract function loadSessionData( $sSessionID );

    /**
     * Export one object type
     *
     * @param string $sType
     * @param string $sWhere
     */
    public function exportType( $sType, $sWhere = null,$iStart = null, $iCount = null, $sSortFieldName = null, $sSortType = null)
    {
        $this->_beforeExport($sType);
        $this->_export( $sType, $sWhere, $iStart, $iCount, $sSortFieldName, $sSortType);
        $this->_afterExport($sType);
    }

    /**
     * imports all data set up before
     *
     */
    public function Import()
    {
        $this->_beforeImport();
        while( $this->_importOne());
        $this->_afterImport();
    }

    /**
     * Factory for ERP types
     *
     * @param string $sType
     * @return object
     */
    protected function _getInstanceOfType( $sType)
    {
        $sClassName = 'oxerptype_'.$sType;
        $sFullPath  = dirname(__FILE__).'/objects/'.$sClassName.'.php';

        if( !file_exists( $sFullPath))
            throw new Exception( "Type $sType not supported in ERP interface!");

        require_once( $sFullPath);

        //return new $sClassName;
        return oxNew ($sClassName);
    }

    /**
     * Exports one type
     *
     * @param string $sType
     * @param string $sWhere
     */
    protected function _export( $sType, $sWhere, $iStart = null, $iCount = null, $sSortFieldName = null, $sSortType = null)
    {
        global $ADODB_FETCH_MODE;

        $myConfig = oxConfig::getInstance();
        // prepare
        $oType   = $this->_getInstanceOfType( $sType);
        //$sSQL    = $oType->getSQL( $sWhere, $this->_iLanguage,$this->_iShopID);
        $sSQL    = $oType->getSQL( $sWhere, $this->_iLanguage,$myConfig->getShopId());
        $sSQL    .= $oType->getSortString($sSortFieldName, $sSortType);
        $sFnc    = '_Export'.$oType->getFunctionSuffix();

        $save = $ADODB_FETCH_MODE;

        if(isset($iCount) || isset($iStart)){
            $rs = oxDb::getDb(true)->SelectLimit( $sSQL,$iCount,$iStart);
        } else {
            $rs = oxDb::getDb(true)->Execute( $sSQL);
        }

        if ($rs != false && $rs->RecordCount() > 0) {
            while (!$rs->EOF) {
                $blExport = false;
                $sMessage = '';

                $rs->fields = $oType->addExportData( $rs->fields);

                // check rights
                $this->_checkAccess( $oType, false);

                // export now
                try{
                    $blExport = $this->$sFnc( $rs->fields );
                } catch (Exception $e) {
                    $sMessage = $e->getMessage();

                }

                $this->_aStatistics[$this->_iIdx] = array('r'=>$blExport,'m'=>$sMessage);
                //#2428 MAFI
                $this->_nextIdx();

                $rs->MoveNext();
            }
        }
        $ADODB_FETCH_MODE       = $save;
    }

    /**
     * Just used for developing
     *
     * @param array $aData
     */
    protected function _OutputMappingArray( $sTable)
    {
        $aData = GetTableDescription( $sTable);

        $iIdx = 0;
        foreach( $aData as $key => $oADODBField) {
            if( !(is_numeric( substr( $oADODBField->name, strlen( $oADODBField->name) - 1, 1)) &&  substr( $oADODBField->name, strlen( $oADODBField->name) - 2, 1) == '_')) {
                echo( "'".$oADODBField->name."'\t\t => '".$oADODBField->name."',\n");
                $iIdx++;
            }
        }
    }

    protected function _getKeyID($oType, $aData)
    {
        $myConfig = oxConfig::getInstance();
        $aKeyFields = $oType->getKeyFields();

        if(!is_array($aKeyFields)) {
            return false;
        }

        $oDB = oxDb::getDb();
        //$aKeys = array_intersect_key($aData,$aKeyFields);

        $aWhere = array();
        $blAllKeys = true;
        foreach($aKeyFields as $sKey) {
            if(array_key_exists($sKey,$aData)){
                $aWhere[] = $sKey.'='.$oDB->qstr($aData[$sKey]);
            }else{
                $blAllKeys = false;
            }
        }

        if($blAllKeys) {
            $sSelect = 'SELECT OXID FROM '.$oType->getTableName().' WHERE '.implode(' AND ',$aWhere);
            $sOXID = $oDB->GetOne($sSelect);

            if(isset($sOXID)){
                return $sOXID;
            }
        }

        return oxUtilsObject::getInstance()->generateUID();
    }

    /**
     * Reset import counter, if retry is detected, only failed imports are repeated
     */
    protected function _resetIdx() {

        $this->_iIdx = 0;

        if(count($this->_aStatistics) && isset($this->_aStatistics[$this->_iIdx])){
            while( isset($this->_aStatistics[$this->_iIdx]) && $this->_aStatistics[$this->_iIdx]['r'] ) {
                $this->_iIdx ++;
            }
        }
    }

    /**
     * Increase import counter, if retry is detected, only failed imports are repeated
     */
    protected function _nextIdx() {
        $this->_iIdx ++;

        if(count($this->_aStatistics) && isset($this->_aStatistics[$this->_iIdx])){
            while( isset($this->_aStatistics[$this->_iIdx]) && $this->_aStatistics[$this->_iIdx]['r'] ) {
                $this->_iIdx ++;
            }
        }
    }

    /**
     * Checks if user as sufficient rights
     *
     * @param string $sTable
     * @param boolean $blWrite
     * @param integer $sShopID
     *
     */
    protected function _checkAccess( $oType, $blWrite, $sOxid = null) {
        $myConfig = oxConfig::getInstance();
        static $aAccessCache;

        if( !$this->_blInit)
            throw new Exception(self::$ERROR_NO_INIT);

        if($blWrite){
            //check against Shop id if it exists
            $oType->checkWriteAccess($sOxid);
        }

        // TODO
        // add R&R check for access
        if($myConfig->blUseRightsRoles)
        {
            static $aAccessCache;

            $sAccessMode = ((boolean)$blWrite)?'2':'1';
            $sTypeClass  = get_class($oType);

            if(!isset($aAccessCache[$sTypeClass][$sAccessMode]))
            {

                $oDB = oxDb::getDb();

                //create list of user/group id's
                $aIDs = array( $oDB->qstr($this->_sUserID) );
                $sQUserGroups = 'SELECT oxgroupsid ' .
                                'FROM oxobject2group '.
                                //"WHERE oxshopid = '{$this->_iShopID}' ".
                                "WHERE oxshopid = '{$myConfig->getShopId()}' ".
                                "AND oxobjectid ='{$this->_sUserID}'";

                $rs = $oDB->Execute( $sQUserGroups);
                if ($rs != false && $rs->RecordCount() > 0) {
                    while (!$rs->EOF) {
                        $aIDs[] = $oDB->qstr($rs->fields[0]);
                        $rs->MoveNext();
                    }
                }

                $aRParams = $oType->GetRightFields();
                foreach ($aRParams as $sKey => $sParam) {
                    $aRParams[$sKey] = $oDB->qstr($sParam);
                }

                //check user rights...
                $sSelect = 'SELECT count(*) '.
                           'FROM oxfield2role as rr , oxrolefields as rf, oxobject2role as ro, oxroles as rt '.
                           "WHERE rr.OXIDX < {$sAccessMode} ".
                           'AND rr.oxroleid = ro.oxroleid  '.
                           'AND rt.oxid = ro.oxroleid '.
                           'AND rt.oxactive = 1 '.
                           //"AND rt.oxshopid = '{$this->_iShopID}'".
                           "AND rt.oxshopid = '{$myConfig->getShopId()}'".
                           'AND rf.oxparam IN ('.implode(',',$aRParams).') '.
                           'AND ro.oxobjectid IN ('.implode(',',$aIDs).') '.
                           'AND rr.oxfieldid=rf.oxid';

                $iNoAccess = $oDB->GetOne($sSelect);
                $aAccessCache[$sTypeClass][$sAccessMode] = $iNoAccess;
           } else {
               $iNoAccess = $aAccessCache[$sTypeClass][$sAccessMode];
           }

           if($iNoAccess) {
               throw new Exception( self::$ERROR_USER_NO_RIGHTS);
           }
       }
    }

    /**
     * Main Import Handler, imports one row/call/object...
     *
     * @return boolean
     */
    protected function _importOne()
    {
        $blRet = false;

        // import one row/call/object...
        $aData = $this->getImportData();

        if( $aData) {
            $blRet = true;
            $blImport = false;
            $sMessage = '';

            $sType  = $this->_getImportType( $aData);
            $sMode = $this->_getImportMode($aData);
            $oType  = $this->_getInstanceOfType( $sType);
            $aData = $this->_modifyData($aData, $oType);

            // import now
            $sFnc   = '_' . $sMode . $oType->getFunctionSuffix();

            if ($sMode == oxERPBase::$MODE_IMPORT) {
               $aData = $oType->addImportData( $aData);
            }

            try{
                $blImport = $this->$sFnc( $oType, $aData);
                $sMessage = '';
            }
            catch (Exception $e) {
                $sMessage = $e->getMessage();
            }

            $this->_aStatistics[$this->_iIdx] = array('r'=>$blImport,'m'=>$sMessage);

        }
        //hotfix #2428 MAFI
        $this->_nextIdx();

        return $blRet;
    }


    /**
     * Insert or Update a Row into database
     *
     * @param oxERPType $oType
     * @param array $aData  assoc. Array with fieldnames, values what should be stored in this table
     * @return string | false
     */
    protected function _Save( oxERPType & $oType, $aData, $blAllowCustomShopId = false)
    {
        $myConfig = oxConfig::getInstance();

        // check rights
        $this->_checkAccess( $oType,  true, $aData['OXID']);

        if($oType->hasKeyFields() && !isset($aData['OXID'])){
            $sOXID = $this->_getKeyID($oType, $aData);
            if($sOXID){
                $aData['OXID'] = $sOXID;
            } else {
                $aData['OXID'] = oxUtilsObject::getInstance()->generateUID();
            }
        }

        return $oType->saveObject($aData, $blAllowCustomShopId);
    }

    /**
     * gets requested db layer version
     *
     * @return string
     */
    public static function getRequestedVersion()
    {
        if (!self::$_sRequestedVersion && isset($_GET['version'])) {
            self::$_sRequestedVersion = $_GET['version'];
        }
        if (!isset(self::$_aDbLayer2ShopDbVersions[self::$_sRequestedVersion])) {
            self::$_sRequestedVersion = '';
        }
        if (!self::$_sRequestedVersion) {
            reset(self::$_aDbLayer2ShopDbVersions);
            self::$_sRequestedVersion = key(self::$_aDbLayer2ShopDbVersions);
        }
        return self::$_sRequestedVersion;
    }

    /**
     * gets requested version for db fields used
     *
     * @return string
     */
    public static function getUsedDbFieldsVersion()
    {
        return self::$_aDbLayer2ShopDbVersions[self::getRequestedVersion()];
    }

    /**
     * gets requested db layer version
     *
     * @param  string $sDbLayerVersion
     */
    public static function setVersion($sDbLayerVersion = '')
    {
        self::$_sRequestedVersion = $sDbLayerVersion;
    }

}


// the following statements and class is just for pretending some error messages in oxconfig
if(!class_exists('FakeView')){
    class FakeView { public function AddGlobalParams() {     }}
}


