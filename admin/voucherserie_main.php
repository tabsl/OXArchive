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
 * $Id: voucherserie_main.php 14014 2008-11-06 13:26:22Z arvydas $
 */

/**
 * Admin article main voucherserie manager.
 * There is possibility to change voucherserie name, description, valid terms
 * and etc.
 * Admin Menu: Shop Settings -> Vouchers -> Main.
 * @package admin
 */
class VoucherSerie_Main extends oxAdminDetails
{
    /**
     * Executes parent method parent::render(), creates oxvoucherserie object,
     * passes data to Smarty engine and returns name of template file
     * "voucherserie_list.tpl".
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $soxId = oxConfig::getParameter( "oxid");

        // check if we right now saved a new entry
        $sSavedID = oxConfig::getParameter( "saved_oxid");
        if ( ($soxId == "-1" || !isset( $soxId)) && isset( $sSavedID) ) {
            $soxId = $sSavedID;
            oxSession::deleteVar( "saved_oxid");
            $this->_aViewData["oxid"] =  $soxId;
            // for reloading upper frame
            $this->_aViewData["updatelist"] =  "1";
        }

        if ( $soxId != "-1" && isset( $soxId)) {
            // load object
            $oVoucherSerie = oxNew( "oxvoucherserie" );
            $oVoucherSerie->load( $soxId);
            $this->_aViewData["edit"] =  $oVoucherSerie;
            $this->_aViewData["status"] = $oVoucherSerie->countVouchers();
        }

        return "voucherserie_main.tpl";
    }

    /**
     * Saves main Voucherserie parameters changes.
     *
     * @return mixed
     */
    public function save()
    {

        // Parameter Processing

        $soxId          = oxConfig::getParameter("oxid");
        $aSerieParams   = oxConfig::getParameter("editval");
        $dVoucherAmount = oxConfig::getParameter("voucherAmount");
        if (!is_numeric($dVoucherAmount) || $dVoucherAmount < 0)
            $dVoucherAmount = 0;

        // Voucher Serie Processing

        $oVoucherSerie = oxNew( "oxvoucherserie" );
        // if serie already exist use it
        if ($soxId != "-1")
            $oVoucherSerie->load($soxId);
        else
            $aSerieParams["oxvoucherseries__oxid"] = null;


        // select random nr if chosen
        //if(oxConfig::getParameter("randomNr"))
           // $aSerieParams["oxvoucherseries__oxserienr"] = uniqid($aSerieParams["oxvoucherseries__oxserienr"]);

        // update serie object
        //$aSerieParams = $oVoucherSerie->ConvertNameArray2Idx($aSerieParams);
        $oVoucherSerie->assign($aSerieParams);
        $oVoucherSerie->save();

        // #614A
        if ($soxId == "-1") {
            // all usergroups
            $oGroups = oxNew( "oxlist" );
            $oGroups->init( "oxgroups" );
            $oGroups->selectString( "select * from oxgroups" );
            foreach ($oGroups as $sGroupID => $oGroup) {
                $oNewGroup = oxNew( "oxobject2group" );
                $oNewGroup->oxobject2group__oxobjectid = new oxField($oVoucherSerie->oxvoucherseries__oxid->value);
                $oNewGroup->oxobject2group__oxgroupsid = new oxField($oGroup->oxgroups__oxid->value);
                $oNewGroup->save();
            }
        }

        // Voucher processing

        $oNewVoucher = oxNew( "oxvoucher" );
        //$aVoucherParams = $oNewVoucher->ConvertNameArray2Idx($aVoucherParams);

        // first we update already existing and not used vouchers

        $oExistingVoucherList = $oVoucherSerie->getVoucherList();
        // prepare voucher params
        foreach ($oExistingVoucherList as $oVoucher) {
            $oVoucher->assign($aVoucherParams);
            $oVoucher->save();
        }

        // second we create new vouchers that are defined in the entry

        for ($i = 0; $i < $dVoucherAmount; $i++) {
            $oNewVoucher->assign($aVoucherParams);
            $oNewVoucher->oxvouchers__oxvoucherserieid = new oxField($oVoucherSerie->oxvoucherseries__oxid->value);
            $oNewVoucher->oxvouchers__oxvouchernr = new oxField(oxConfig::getParameter("voucherNr"));
            if (oxConfig::getParameter("randomVoucherNr"))
                $oNewVoucher->oxvouchers__oxvouchernr = new oxField(uniqid($oNewVoucher->oxvouchers__oxvouchernr->value));
            $oNewVoucher->save();
            $oNewVoucher = oxNew( "oxvoucher" );
        }

        // release all chekbox states
        oxSession::deleteVar("randomVoucherNr");
        oxSession::deleteVar("randomNr");
        $this->_aViewData["updatelist"] = "1";

        // set oxid if inserted
        if ($soxId == "-1")
            oxSession::setVar("saved_oxid", $oVoucherSerie->oxvoucherseries__oxid->value);

        return $this->autosave();
    }
}
