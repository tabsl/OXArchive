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
 * $Id: oxerpcsv.php 16303 2009-02-05 10:23:41Z rimvydas.paskevicius $
 */

/**
 * Class handeling csv import
 * @package core
 */
class oxErpCsv extends oxERPBase {

    protected $_aSupportedVersions = array("0.1", "1.0", "1.1", "2.0");
    protected $_aCsv2BaseVersionsMap = array("0.1" => "1", "1.0" => "1", "1.1"=>"1.1", "2.0" => "2");

    //version of the file which is imported right now
    protected $_sCurrVersion = "";

    protected $_aData = array();

    protected $_iRetryRows = 0;

    protected $_sReturn;

    protected $_sPath;

    protected $_aImportedActions2Article = array();
    protected $_aImportedObject2Category = array();
    protected $_aImportedAccessoire2Article = array();

    protected function _beforeExport($sType){}
    protected function _afterExport($sType) {}
    protected function _getImportType(& $aData) {}
    protected function _getImportMode($Data) {}
    protected function _modifyData($aData, $oType) {}
    public function loadSessionData( $sSessionID ) {}

    /**
     * parses and replaces special chars
     *
     * @param string $sText input text
     * @param bool $blMode 	true = Text2CSV, false = CSV2Text
     *
     * @return string
     */
    protected function _csvTextConvert($sText, $blMode)
    {
        $aSearch  = array(chr(13), chr(10), '\'',    '"');
        $aReplace = array('&#13;', '&#10;', '&#39;', '&#34;');

        if( $blMode)
            $sText = str_replace( $aSearch, $aReplace ,$sText);
        else
            $sText = str_replace( $aReplace, $aSearch ,$sText);

        return $sText;
    }

    public function Import()
    {
        $this->_beforeImport();

        do{
            while( $this->_importOne());
        }
        while ( !$this->_afterImport() );

    }

    protected function _beforeImport()
    {
        if(!$this->_iRetryRows){
            //convert all text
            foreach ($this->_aData as $key => $value) {
                $this->_aData[$key] = $this->_csvTextConvert($value, false);
            }
        }

    }
    protected function _afterImport()
    {
        //check if there have been no errors or failures
        $aStatistics = $this->getStatistics();
        $iRetryRows  = 0;

        foreach ($aStatistics as $key => $value) {
            if($value['r'] == false){
               $iRetryRows ++;
               $this->_sReturn.=  "File[".$this->_sPath."] - dataset number: $key - Error: ".$value['m']." ---<br> ".PHP_EOL;
            }
        }

        if($iRetryRows != $this->_iRetryRows && $iRetryRows>0){
            $this->_resetIdx();
            $this->_iRetryRows  = $iRetryRows;
            $this->_sReturn     = '';

            return false;
        }

        return true;
    }
    public function getImportData()
    {
        return $this->_aData[$this->_iIdx];
    }

    /**
     * due to compatibility reasons, the field list of V0.1
     *
     * @return array
     */
    private function getOldOrderArticleFieldList()
    {
        $aFieldList = array(
            'OXID'          => 'OXID',
            'OXORDERID'     => 'OXORDERID',
            'OXAMOUNT'      => 'OXAMOUNT',
            'OXARTID'       => 'OXARTID',
            'OXARTNUM'      => 'OXARTNUM',
            'OXTITLE'       => 'OXTITLE',
            'OXSELVARIANT'  => 'OXSELVARIANT',
            'OXNETPRICE'    => 'OXNETPRICE',
            'OXBRUTPRICE'   => 'OXBRUTPRICE',
            'OXVAT'         => 'OXVAT',
            'OXPERSPARAM'   => 'OXPERSPARAM',
            'OXPRICE'       => 'OXPRICE',
            'OXBPRICE'      => 'OXBPRICE',
            'OXTPRICE'      => 'OXTPRICE',
            'OXWRAPID'      => 'OXWRAPID',
            'OXSTOCK'       =>  'OXSTOCK',
            'OXORDERSHOPID' => 'OXORDERSHOPID',
            'OXTOTALVAT'    => 'OXTOTALVAT'
        );

        return $aFieldList;
    }

    /**
     * due to compatibility reasons, the field list of V0.1
     *
     * @return array
     */
    private function getOldOrderFielsList()
    {
         $aFieldList = array(
            'OXID'		     => 'OXID',
            'OXSHOPID'		 => 'OXSHOPID',
            'OXUSERID'		 => 'OXUSERID',
            'OXORDERDATE'	 => 'OXORDERDATE',
            'OXORDERNR'		 => 'OXORDERNR',
            'OXBILLCOMPANY'	 => 'OXBILLCOMPANY',
            'OXBILLEMAIL'	 => 'OXBILLEMAIL',
            'OXBILLFNAME'	 => 'OXBILLFNAME',
            'OXBILLLNAME'	 => 'OXBILLLNAME',
            'OXBILLSTREET'	 => 'OXBILLSTREET',
            'OXBILLSTREETNR' => 'OXBILLSTREETNR',
            'OXBILLADDINFO'	 => 'OXBILLADDINFO',
            'OXBILLUSTID'	 => 'OXBILLUSTID',
            'OXBILLCITY'	 => 'OXBILLCITY',
            'OXBILLCOUNTRY'	 => 'OXBILLCOUNTRY',
            'OXBILLZIP'		 => 'OXBILLZIP',
            'OXBILLFON'		 => 'OXBILLFON',
            'OXBILLFAX'		 => 'OXBILLFAX',
            'OXBILLSAL'		 => 'OXBILLSAL',
            'OXDELCOMPANY'	 => 'OXDELCOMPANY',
            'OXDELFNAME'	 => 'OXDELFNAME',
            'OXDELLNAME'	 => 'OXDELLNAME',
            'OXDELSTREET'	 => 'OXDELSTREET',
            'OXDELSTREETNR'	 => 'OXDELSTREETNR',
            'OXDELADDINFO'	 => 'OXDELADDINFO',
            'OXDELCITY'		 => 'OXDELCITY',
            'OXDELCOUNTRY'	 => 'OXDELCOUNTRY',
            'OXDELZIP'		 => 'OXDELZIP',
            'OXDELFON'		 => 'OXDELFON',
            'OXDELFAX'		 => 'OXDELFAX',
            'OXDELSAL'		 => 'OXDELSAL',
            'OXDELCOST'		 => 'OXDELCOST',
            'OXDELVAT'		 => 'OXDELVAT',
            'OXPAYCOST'		 => 'OXPAYCOST',
            'OXPAYVAT'		 => 'OXPAYVAT',
            'OXWRAPCOST'	 => 'OXWRAPCOST',
            'OXWRAPVAT'		 => 'OXWRAPVAT',
            'OXCARDID'		 => 'OXCARDID',
            'OXCARDTEXT'	 => 'OXCARDTEXT',
            'OXDISCOUNT'	 => 'OXDISCOUNT',
            'OXBILLNR'		 => 'OXBILLNR',
            'OXREMARK'		 => 'OXREMARK',
            'OXVOUCHERDISCOUNT'		 => 'OXVOUCHERDISCOUNT',
            'OXCURRENCY'	 => 'OXCURRENCY',
            'OXCURRATE'		 => 'OXCURRATE',
            'OXTRANSID'		 => 'OXTRANSID',
            'OXPAID'		 => 'OXPAID',
            'OXIP'		     => 'OXIP',
            'OXTRANSSTATUS'	 => 'OXTRANSSTATUS',
            'OXLANG'		 => 'OXLANG',
            'OXDELTYPE'		 => 'OXDELTYPE'
            );

            return $aFieldList;
    }

    protected  function _checkIDField( $sID)
    {
        if( !isset( $sID) || !$sID)
            throw new Exception("ERROR: Articlenumber/ID missing!");
        elseif( strlen( $sID) > 32)
            throw new Exception( "ERROR: Articlenumber/ID longer then allowed (32 chars max.)!");
    }

    /**
     * method overridden to allow olf Order and OrderArticle types
     *
     * @param string $sType
     *
     * @return object
     */
    protected function _getInstanceOfType( $sType)
    {
        //due to backward compatibility
        if($sType == 'oldOrder'){
            $oType = parent::_getInstanceOfType('order');
            $oType->setFieldList($this->getOldOrderFielsList());
            $oType->setFunctionSuffix('OldOrder');
        }elseif($sType == 'oldOrderArticle'){
            $oType = parent::_getInstanceOfType('orderarticle');
            $oType->setFieldList($this->getOldOrderArticleFieldList());
            $oType->setFunctionSuffix('OldOrderArticle');
        }elseif($sType == 'article2vendor'){
            $oType = parent::_getInstanceOfType('article');
            $oType->setFieldList(array("OXID", "OXVENDORID"));
        }elseif($sType == 'mainarticle2categroy') {
            $oType = parent::_getInstanceOfType('article2category');
            $oType->setFieldList(array("OXOBJECTID", "OXCATNID", "OXTIME"));
            $oType->setFunctionSuffix('mainarticle2category');
        }
        else{
            $oType = parent::_getInstanceOfType($sType);
        }

        return $oType;
    }

    // --------------------------------------------------------------------------
    //
    // Import Handler
    // One _Import* method needed for each object defined in /objects/ folder, all these objects  can be imported
    //
    // --------------------------------------------------------------------------




    protected function _ImportArticle( oxERPType & $oType, $aRow)
    {

        if($this->_sCurrVersion == "0.1")
        {
            $myConfig = oxConfig::getInstance();
            //to allow different shopid without consequences (ignored fields)
            $myConfig->setConfigParam('blMallCustomPrice', false);
        }


        if(isset($aRow['OXID'])){
            $this->_checkIDField($aRow['OXID']);
        }else{
            $this->_checkIDField($aRow['OXARTNUM']);
            $aRow['OXID'] = $aRow['OXARTNUM'];
        }

        $sResult = $this->_Save( $oType, $aRow, $this->_sCurrVersion == "0.1"); // V0.1 allowes the shopid to be set no matter which login
        return (boolean) $sResult;
    }

    protected function _ImportAccessoire( oxERPType & $oType, $aRow) {

        // deleting old relations before import in V0.1
        if ( $this->_sCurrVersion == "0.1" && !isset($this->_aImportedAccessoire2Article[$aRow['OXARTICLENID']] ) ) {
            $myConfig = oxConfig::getInstance();
            $sDeleteSQL = "delete from oxaccessoire2article where oxarticlenid = '{$aRow['OXARTICLENID']}'";
            oxDb::getDb()->Execute( $sDeleteSQL );
            $this->_aImportedAccessoire2Article[$aRow['OXARTICLENID']] = 1;
        }

        $sResult = $this->_Save( $oType, $aRow);
        return (boolean) $sResult;
    }

    protected function _ImportArticle2Action( oxERPType & $oType, $aRow)
    {

        if ( $this->_sCurrVersion == "0.1" && !isset( $this->_aImportedActions2Article[$aRow['OXARTID']] ) ) { //only in V0.1 and only once per import/article


            $myConfig = oxConfig::getInstance();
            $sDeleteSQL = "delete from oxactions2article where oxartid = '{$aRow['OXARTID']}'";
            oxDb::getDb()->Execute( $sDeleteSQL );
            $this->_aImportedActions2Article[$aRow['OXARTID']] = 1;
        }

        $sResult = $this->_Save( $oType, $aRow, $this->_sCurrVersion == "0.1");
        return (boolean) $sResult;
    }

    protected function _ImportArticle2Category( oxERPType & $oType, $aRow)
    {

        // deleting old relations before import in V0.1
        if ( $this->_sCurrVersion == "0.1" && !isset( $this->_aImportedObject2Category[$aRow['OXOBJECTID']] ) ) {
            $myConfig = oxConfig::getInstance();
            $sDeleteSQL = "delete from oxobject2category where oxobjectid = '{$aRow['OXOBJECTID']}'";
            oxDb::getDb()->Execute( $sDeleteSQL );
            $this->_aImportedObject2Category[$aRow['OXOBJECTID']] = 1;
        }

        $sResult = $this->_Save( $oType, $aRow);
        return (boolean) $sResult;
    }

    protected function _ImportMainArticle2Category( oxERPType & $oType, $aRow)
    {
        $aRow['OXTIME'] = 0;

        $myConfig = oxConfig::getInstance();
        $sSql = "select OXID from oxobject2category where oxobjectid = '".$aRow['OXOBJECTID']."' and OXCATNID = '".$aRow['OXCATNID']."'";
        $aRow['OXID'] = oxDb::getDb()->GetOne($sSql);

        $sResult = $this->_Save( $oType, $aRow);

        if ((boolean) $sResult) {

            $sSql = "Update oxobject2category set oxtime = oxtime+10 where oxobjectid = '" . $aRow['OXOBJECTID'] ."' and oxcatnid != '". $aRow['OXCATNID'] ."' and oxshopid = '".$myConfig->getShopId()."'";
            oxDb::getDb()->Execute($sSql);

        }

        return (boolean) $sResult;
    }

    protected function _ImportCategory( oxERPType & $oType, $aRow)
    {

        $sResult = $this->_Save( $oType, $aRow, $this->_sCurrVersion == "0.1");
        return (boolean) $sResult;
    }

    protected function _ImportCrossselling( oxERPType & $oType, $aRow)
    {


        // deleting old relations before import in V0.1
        if ( $this->_sCurrVersion == "0.1" && !isset($this->_aImportedObject2Article[$aRow['OXARTICLENID']] ) ) {
            $myConfig = oxConfig::getInstance();
            $sDeleteSQL = "delete from oxobject2article where oxarticlenid = '{$aRow['OXARTICLENID']}'";
            oxDb::getDb()->Execute( $sDeleteSQL );
            $this->aImportedObject2Article[$aRow['OXARTICLENID']] = 1;
        }

        $sResult = $this->_Save( $oType, $aRow);
        return (boolean) $sResult;
    }

    protected function _ImportScaleprice( oxERPType & $oType, $aRow)
    {

        $sResult = $this->_Save( $oType, $aRow, $this->_sCurrVersion == "0.1");
        return (boolean) $sResult;
    }

    protected function _ImportOrder( oxERPType & $oType, $aRow)
    {

        $sResult = $this->_Save( $oType, $aRow);
        return true; //MAFI a unavoidable hack as oxorder->update() does always return null !!! a hotfix is needed
        //return (boolean) $sResult;
    }

    protected function _ImportOrderArticle( oxERPType & $oType, $aRow)
    {

        $sResult = $this->_Save( $oType, $aRow);
        return (boolean) $sResult;
    }

    protected function _ImportOrderStatus( oxERPType & $oType, $aRow) {
        $oOrderArt = oxNew( "oxorderarticle", "core");
        $oOrderArt->Load( $aRow['OXID']);

        if( $oOrderArt->getId()) {

            try {
                if( $this->_sCurrVersion != "0.1")
                    $oType->checkWriteAccess($oOrderArt->getId());

                    // store status
                $aStatuses = unserialize( $oOrderArt->oxorderarticles__oxerpstatus->value );

                $oStatus = new stdClass();
                $oStatus->STATUS 		= $aRow['OXERPSTATUS_STATUS'];
                $oStatus->date 			= $aRow['OXERPSTATUS_TIME'];
                $oStatus->trackingid 	= $aRow['OXERPSTATUS_TRACKID'];

                $aStatuses[$aRow['OXERPSTATUS_TIME']] = $oStatus;
                $oOrderArt->oxorderarticles__oxerpstatus = new oxField(serialize( $aStatuses), oxField::T_RAW);
                $oOrderArt->Save();
                return true;
            } catch (Exception $ex) {
                return false;
            }
        }

        return false;
    }

    protected function _ImportUser( oxERPType & $oType, $aRow)
    {

        //Speciall check for user
        if(isset($aRow['OXUSERNAME']))
        {
            $sID = $aRow['OXID'];
            $sUserName = $aRow['OXUSERNAME'];

            $oUser = oxNew( "oxuser", "core");
            $oUser->oxuser__oxusername = new oxField($sUserName, oxField::T_RAW);

            //If user exists with and modifies OXID, throw an axception
            //throw new Exception( "USER {$sUserName} already exists!");
            if( $oUser->exists( $sID) && $sID != $oUser->getId() ) {
                throw new Exception( "USER $sUserName already exists!");
            }

        }

        $sResult  = $this->_Save( $oType, $aRow);
        return (boolean) $sResult;
    }

    protected function _ImportVendor( oxERPType & $oType, $aRow)
    {

        $sResult = $this->_Save( $oType, $aRow, $this->_sCurrVersion == "0.1");
        return (boolean) $sResult;
    }

    protected function _ImportArtextends( oxERPType & $oType, $aRow) {
        if (oxERPBase::getRequestedVersion() < 2) {
            return false;
        }
        $sResult = $this->_Save( $oType, $aRow);
        return (boolean) $sResult;
    }

    protected function _ImportCountry( oxERPType & $oType, $aRow)
    {
        $sResult = $this->_Save( $oType, $aRow);
        return (boolean) $sResult;
    }

    protected function _ImportArticleStock( oxERPType & $oType, $aRow) {
        $sResult = $this->_Save( $oType, $aRow);
        return (boolean) $sResult;
    }
}
