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
 * $Id: oxerptype_category.php 16303 2009-02-05 10:23:41Z rimvydas.paskevicius $
 */

require_once( 'oxerptype.php');

class oxERPType_Category extends oxERPType
{
    protected $_aFieldListVersions = array(
        '1' => array(
            'OXID'           => 'OXID',
            'OXPARENTID'     => 'OXPARENTID',
            'OXORDER'        => 'OXORDER',
            'OXACTIV'        => 'OXACTIV',
            'OXHIDDEN'       => 'OXHIDDEN',
            'OXSHOPID'       => 'OXSHOPID',
            'OXSHOPINCL'     => 'OXSHOPINCL',
            'OXSHOPEXCL'     => 'OXSHOPEXCL',
            'OXTITLE'        => 'OXTITLE',
            'OXDESC'         => 'OXDESC',
            'OXLONGDESC'     => 'OXLONGDESC',
            'OXTHUMB'        => 'OXTHUMB',
            'OXEXTLINK'      => 'OXEXTLINK',
            'OXTEMPLATE'     => 'OXTEMPLATE',
            'OXDEFSORT'      => 'OXDEFSORT',
            'OXDEFSORTMODE'  => 'OXDEFSORTMODE',
            'OXICON'         => 'OXICON',
            'OXSKIPDISCOUNTS'=> 'OXSKIPDISCOUNTS',
            'OXACTIV_1'      => 'OXACTIV_1',
            'OXTITLE_1'      => 'OXTITLE_1',
            'OXDESC_1'       => 'OXDESC_1',
            'OXLONGDESC_1'   => 'OXLONGDESC_1',
            'OXACTIV_2'      => 'OXACTIV_2',
            'OXTITLE_2'      => 'OXTITLE_2',
            'OXDESC_2'       => 'OXDESC_2',
            'OXLONGDESC_2'   => 'OXLONGDESC_2',
            'OXACTIV_3'      => 'OXACTIV_3',
            'OXTITLE_3'      => 'OXTITLE_3',
            'OXDESC_3'       => 'OXDESC_3',
            'OXLONGDESC_3'   => 'OXLONGDESC_3',
            'OXPRICEFROM'    => 'OXPRICEFROM',
            'OXPRICETO'      => 'OXPRICETO',
            'OXTYPE'         => 'OXTYPE',
            'OXSEOID'        => 'OXSEOID',
            'OXSEOID_1'      => 'OXSEOID_1',
            'OXSEOID_2'      => 'OXSEOID_2',
            'OXSEOID_3'      => 'OXSEOID_3'
        ),
        '2' => array(
            'OXID' => 'OXID',
            'OXPARENTID' => 'OXPARENTID',
            'OXLEFT' => 'OXLEFT',
            'OXRIGHT' => 'OXRIGHT',
            'OXROOTID' => 'OXROOTID',
            'OXSORT' => 'OXSORT',
            'OXACTIVE' => 'OXACTIVE',
            'OXHIDDEN' => 'OXHIDDEN',
            'OXSHOPID' => 'OXSHOPID',
            'OXSHOPINCL' => 'OXSHOPINCL',
            'OXSHOPEXCL' => 'OXSHOPEXCL',
            'OXTITLE' => 'OXTITLE',
            'OXDESC' => 'OXDESC',
            'OXLONGDESC' => 'OXLONGDESC',
            'OXTHUMB' => 'OXTHUMB',
            'OXEXTLINK' => 'OXEXTLINK',
            'OXTEMPLATE' => 'OXTEMPLATE',
            'OXDEFSORT' => 'OXDEFSORT',
            'OXDEFSORTMODE' => 'OXDEFSORTMODE',
            'OXPRICEFROM' => 'OXPRICEFROM',
            'OXPRICETO' => 'OXPRICETO',
            'OXACTIVE_1' => 'OXACTIVE_1',
            'OXTITLE_1' => 'OXTITLE_1',
            'OXDESC_1' => 'OXDESC_1',
            'OXLONGDESC_1' => 'OXLONGDESC_1',
            'OXACTIVE_2' => 'OXACTIVE_2',
            'OXTITLE_2' => 'OXTITLE_2',
            'OXDESC_2' => 'OXDESC_2',
            'OXLONGDESC_2' => 'OXLONGDESC_2',
            'OXACTIVE_3' => 'OXACTIVE_3',
            'OXTITLE_3' => 'OXTITLE_3',
            'OXDESC_3' => 'OXDESC_3',
            'OXLONGDESC_3' => 'OXLONGDESC_3',
            'OXICON' => 'OXICON',
            'OXVAT' => 'OXVAT',
            'OXSKIPDISCOUNTS' => 'OXSKIPDISCOUNTS',
            'OXSHOWSUFFIX' => 'OXSHOWSUFFIX',
        ),
    );

    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxcategories';
        $this->_sShopObjectName = 'oxcategory';
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
        if ('1' == oxERPBase::getUsedDbFieldsVersion()) {
            switch ($sField) {
                case 'OXTYPE':
                    return "'0' as $sField";
                case 'OXSEOID':
                case 'OXSEOID_1':
                case 'OXSEOID_2':
                case 'OXSEOID_3':
                    return "'' as $sField";
                case 'OXACTIV':
                    return "OXACTIVE as OXACTIV";
                case 'OXACTIV_1':
                    return "OXACTIVE_1 as OXACTIV_1";
                case 'OXACTIV_2':
                    return "OXACTIVE_2 as OXACTIV_2";
                case 'OXACTIV_3':
                    return "OXACTIVE_3 as OXACTIV_3";
                case 'OXORDER':
                    return "OXSORT as $sField";
            }
        }
        return parent::getSqlFieldName($sField, $iLanguage, $iShopID);
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
        if ('1' == oxERPBase::getUsedDbFieldsVersion()) {
            if (!$aData['OXPARENTID']) {
                $aData['OXPARENTID'] = 'oxrootid';
            }
            $aData['OXSORT'] = $aData['OXORDER'];
        }
        return $aData;
    }

}
