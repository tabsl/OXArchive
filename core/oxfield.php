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
 * $Id: oxfield.php 13617 2008-10-24 09:38:46Z sarunas $
 */

/**
 * Database field description object.
 * @package core
 */
class oxField // extends oxSuperCfg
{
    const T_TEXT = 1;
    const T_RAW  = 2;

    /**
     * constructor
     * initial value assigment is coded here by not calling a function is for performance
     * because oxField is created MANY times and even a function call matters
     *
     * @param mixed $value
     * @param int $type
     */
    public function __construct($value = null, $type = self::T_TEXT)
    {
        // duplicate content here is needed for performance.
        // as this function is called *many* (a lot) times, it is crucial to be fast here!
        switch ($type) {
            case self::T_TEXT:
            default:
                $this->rawValue = $value;
                break;
            case self::T_RAW:
                $this->value = $value;
                break;
        }
    }

    /**
     * checks if $name is set
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        switch ($name) {
            case 'rawValue':
                return ($this->rawValue !== null);
            case 'value':
                return ($this->value !== null);
            //return true;
        }
        return false;
    }

    /**
     * magic getter
     *
     * @param string $name
     * @return string | null
     */
    public function __get($name)
    {
        switch ($name) {
            case 'rawValue':
                return $this->value;
            case 'value':
                if (is_string($this->rawValue)) {
                    $this->value = htmlspecialchars($this->rawValue);
                } else {
                    // TODO: call htmlentities for each (recursive ???)
                    $this->value = $this->rawValue;
                }
                if ($this->rawValue == $this->value) {
                    unset($this->rawValue);
                }
                return $this->value;
            default:
                return null;
        }
    }

    /**
     * TODO: remove this
     *
     * @return unknown
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * converts to formatted db date
     *
     * @return null
     */
    public function convertToFormattedDbDate()
    {
        $this->setValue(oxUtilsDate::getInstance()->formatDBDate( $this->rawValue ), self::T_RAW);
    }

    /**
     * converts to pseudo html - new lines to <br /> tags
     *
     * @return null
     */
    public function convertToPseudoHtml()
    {
        $this->setValue(str_replace("\r", '', nl2br(htmlentities($this->rawValue))), self::T_RAW);
    }

    protected function _initValue($value = null, $type = self::T_TEXT)
    {
        switch ($type) {
            case self::T_TEXT:
                $this->rawValue = $value;
                break;
            case self::T_RAW:
                $this->value = $value;
                break;
        }
    }

    /**
     * setter
     *
     * @return null
     */
    public function setValue($value = null, $type = self::T_TEXT)
    {
        unset($this->rawValue);
        unset($this->value);
        $this->_initValue($value, $type);
    }

    /**
     * return raw value
     *
     * @return string
     */
    public function getRawValue()
    {
        if (null === $this->rawValue) {
            return $this->value;
        };
        return $this->rawValue;
    }
}
