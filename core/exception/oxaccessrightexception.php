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
 * @copyright © OXID eSales AG 2003-2008
 * $Id: oxaccessrightexception.php, v 1.0 2007.7.31 09.54.24 mathiasf Exp
 */


/**
 * Exception class thrown when a violation of access rights is commited, e.g.:
 * - no rights to view area
 */
class oxAccessRightException extends oxException
{
    /**
     * To what object the access was denied
     *
     * @var string
     */
    protected  $_sObjectName = null;

    /**
     * Class name of the object which caused the RR exception
     *
     * @param string $sObjectName Class name of the object
     *
     * @return  null
     */
    public function setObjectName( $sObjectName )
    {
        $this->_sObjectName = $sObjectName;
    }

    /**
     * Class name of the object that caused the RR exception
     *
     * @return string
     */
    public function getObjectName()
    {
        return $this->_sObjectName;
    }

    /**
     * Get string dump
     * Overrides oxException::getString()
     *
     * @return string
     */
    public function getString()
    {
        return __CLASS__ .'-'.parent::getString()." Faulty Object --> ".$this->_sObjectName."\n";
    }

    /**
     * Override of Exception::getValues()
     *
     * @return array
     */
    public function getValues()
    {
        $aRes = parent::getValues();
        $aRes['object'] = $this->getObjectName();
        return $aRes;
    }
}