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
 * $Id: oxlang.php 14378 2008-11-26 13:59:41Z vilma $
 */

/**
 * Language related utility class
 */
class oxLang extends oxSuperCfg
{
    /**
     * oxUtilsCount instance.
     *
     * @var oxlang
     */
    private static $_instance = null;

    /**
     * Current shop base language Id
     *
     * @var int
     */
    protected $_iBaseLanguageId = null;

    /**
     * Templates language Id
     *
     * @var int
     */
    protected $_iTplLanguageId = null;

    /**
     * Editing object language Id
     *
     * @var int
     */
    protected $_iEditLanguageId = null;

    /**
     * Language translations array
     *
     * @var array
     */
    protected $_aLangCache = null;

    /**
     * Admin language translations array
     *
     * @var array
     */
    protected $_aAdminLangCache = null;

    /**
     * resturns a single instance of this class
     *
     * @return oxLang
     */
    public static function getInstance()
    {
        if ( defined('OXID_PHP_UNIT')) {
            if ( ($oClassMod = modInstances::getMod(__CLASS__))  && is_object($oClassMod) ) {
                return $oClassMod;
            } else {
                $inst = oxNew( 'oxLang' );
                 modInstances::addMod( __CLASS__, $inst );
                 return $inst;
            }
        }

        if ( !self::$_instance instanceof oxLang ) {

            self::$_instance = oxNew( 'oxLang');
        }
        return self::$_instance;
    }

    /**
     * resetBaseLanguage resets base language id cache
     *
     * @access public
     * @return void
     */
    public function resetBaseLanguage()
    {
        $this->_iBaseLanguageId = null;
    }

    /**
     * Returns active shop language id
     *
     * @return string
     */
    public function getBaseLanguage()
    {
        $myConfig = $this->getConfig();
        //$this->_iBaseLanguageId = null;

        if ( $this->_iBaseLanguageId !== null ) {
            return $this->_iBaseLanguageId;
        }

        $blAdmin = $this->isAdmin();

        // languages and search engines
        if ( $blAdmin && ( ( $iSeLang = oxConfig::getParameter( 'changelang' ) ) !== null ) ) {
            $this->_iBaseLanguageId = $iSeLang;
        }

        if ( is_null( $this->_iBaseLanguageId ) ) {
            $this->_iBaseLanguageId = oxConfig::getParameter( 'lang' );
        }

        //or determining by domain
        $aLanguageUrls = $myConfig->getConfigParam( 'aLanguageURLs' );

        if ( !$blAdmin && is_array( $aLanguageUrls ) ) {
            foreach ( $aLanguageUrls as $iId => $sUrl ) {
                if ( $myConfig->isCurrentUrl( $sUrl ) ) {
                    $this->_iBaseLanguageId = $iId;
                    break;
                }
            }
        }

        if ( is_null( $this->_iBaseLanguageId ) ) {
            $this->_iBaseLanguageId = oxConfig::getParameter( 'language' );
        }

        if ( is_null( $this->_iBaseLanguageId ) ) {
            $this->_iBaseLanguageId = $myConfig->getConfigParam( 'sDefaultLang' );
        }

        $this->_iBaseLanguageId = (int) $this->_iBaseLanguageId;

        // validating language
        $this->_iBaseLanguageId = $this->validateLanguage( $this->_iBaseLanguageId );

        return $this->_iBaseLanguageId;
    }

    /**
     * Returns active shop templates language id
     * If it is not an admin area, template language id is same
     * as base shop language id
     *
     * @return string
     */
    public function getTplLanguage()
    {
        if ( $this->_iTplLanguageId !== null ) {
            return $this->_iTplLanguageId;
        }

        if ( !$this->isAdmin() ) {
            $this->_iTplLanguageId = $this->getBaseLanguage();
        } else {
            //admin area

            if ( is_null( $this->_iTplLanguageId ) ) {
                //$this->_iTplLanguageId = oxConfig::getParameter( 'tpllanguage' );
                $this->_iTplLanguageId = oxSession::getVar( 'tpllanguage' );
            }

            if ( is_null( $this->_iTplLanguageId ) ) {
                $this->_iTplLanguageId = $this->getBaseLanguage();
            }
        }

        // validating language
        $this->_iTplLanguageId = $this->validateLanguage( $this->_iTplLanguageId );

        return $this->_iTplLanguageId;
    }

    /**
     * Returns editing object working language id
     *
     * @return string
     */
    public function getEditLanguage()
    {
        if ( $this->_iEditLanguageId !== null ) {
            return $this->_iEditLanguageId;
        }

        if ( !$this->isAdmin() ) {
            $this->_iEditLanguageId = $this->getBaseLanguage();
        } else {
            $this->_iEditLanguageId = oxConfig::getParameter( 'editlanguage' );

            // check if we really need to set the new language
            if ( "saveinnlang" == $this->getConfig()->getActiveView()->getFncName() ) {
                $iNewLanguage = oxConfig::getParameter( "new_lang");
            }

            if ( isset( $iNewLanguage ) ) {
                if ( isset( $iNewLanguage ) ) {
                    $this->_iEditLanguageId = $iNewLanguage;
                    oxSession::deleteVar( "new_lang" );
                }
            }

            if ( is_null( $this->_iEditLanguageId ) ) {
                $this->_iEditLanguageId = $this->getBaseLanguage();
            }
        }

        // validating language
        $this->_iEditLanguageId = $this->validateLanguage( $this->_iEditLanguageId );

        return $this->_iEditLanguageId;
    }

    /**
     * Returns array of available languages.
     *
     * @param integer $iLanguage Number if current language (default null)
     *
     * @return array
     */
    public function getLanguageArray( $iLanguage = null )
    {
        $myConfig = oxConfig::getInstance();

        if ( is_null($iLanguage) ) {
            $iLanguage = $this->_iBaseLanguageId;
        }

        $aLanguages = array();
        $aConfLanguages = $myConfig->getConfigParam( 'aLanguages' );
        if ( is_array( $aConfLanguages ) ) {

            $i = 0;
            reset( $aConfLanguages );
            while ( list( $key, $val ) = each( $aConfLanguages ) ) {
                if ( $val) {
                    $oLang = new oxStdClass();
                    $oLang->id   = $i;
                    $oLang->abbr = $key;
                    $oLang->name = $val;
                    if ( isset( $iLanguage ) && $i == $iLanguage ) {
                        $oLang->selected = 1;
                    } else {
                        $oLang->selected = 0;
                    }
                    $aLanguages[$i] = $oLang;
                }
                ++$i;
            }
        }
        return $aLanguages;
    }

    /**
     * getLanguageNames returns array of language names e.g. array('Deutch', 'English')
     *
     * @param int $iLanguage language number
     *
     * @access public
     * @return string
     */
    public function getLanguageAbbr( $iLanguage = null)
    {
        $myConfig = oxConfig::getInstance();

        if ( !isset($iLanguage) ) {
            $iLanguage = $this->_iBaseLanguageId;
        }

        $aLangAbbr = $this->getLanguageIds();

        if ( isset($iLanguage,$aLangAbbr[$iLanguage]) ) {
            return $aLangAbbr[$iLanguage];
        }

        return $iLanguage;
    }

    /**
     * getLanguageNames returns array of language names e.g. array('Deutch', 'English')
     *
     * @access public
     * @return array
     */
    public function getLanguageNames()
    {
        return array_values(oxConfig::getInstance()->getConfigParam( 'aLanguages' ));
    }

    /**
     * Returns available language IDs
     *
     * @return array
     */
    public function getLanguageIds()
    {
        return array_keys(oxConfig::getInstance()->getConfigParam( 'aLanguages' ));
    }

    /**
     * Searches for translation string in file and on success returns translation,
     * otherwise returns initial string.
     *
     * @param string $sStringToTranslate Initial string
     * @param int    $iLang              optional language number
     * @param bool   $blAdminMode        on special case you can force mode, to load language constant from admin/shops language file
     *
     * @throws oxLanguageException in debug mode
     *
     * @return string
     */
    public function translateString( $sStringToTranslate, $iLang = null, $blAdminMode = null )
    {
        $aLangCache = $this->_getLangTranslationArray( $iLang, $blAdminMode );
        if ( isset( $aLangCache[$sStringToTranslate] ) ) {
            $sText = $aLangCache[$sStringToTranslate];
        } else {
            $sText = $sStringToTranslate;
        }

            $blIsAdmin = isset( $blAdminMode ) ? $blAdminMode : $this->isAdmin();
            if ( !$blIsAdmin && $sText === $sStringToTranslate ) {
                $sText = $this->_readTranslateStrFromTextFile( $sStringToTranslate, $iLang, $blIsAdmin );
            }

        return $sText;
    }

    /**
     * Returns formatted currency string, according to formatting standards.
     *
     * @param double $dValue  Plain price
     * @param object $oActCur Object of active currency
     *
     * @return string
     */
    public function formatCurrency( $dValue, $oActCur = null )
    {
        if ( !$oActCur ) {
            $oActCur = $this->getConfig()->getActShopCurrencyObject();
        }
        return number_format( $dValue, $oActCur->decimal, $oActCur->dec, $oActCur->thousand );
    }

    /**
     * Returns formatted vat value, according to formatting standards.
     *
     * @param double $dValue  Plain price
     * @param object $oActCur Object of active currency
     *
     * @return string
     */
    public function formatVat( $dValue, $oActCur = null )
    {   
        $iDecPos = 0;
        $sValue  = ( string ) $dValue;
        if ( ( $iDotPos = strpos( $sValue, '.' ) ) !== false ) {
            $iDecPos = strlen( substr( $sValue, $iDotPos + 1 ) );
        }

        $oActCur = $oActCur ? $oActCur : $this->getConfig()->getActShopCurrencyObject();
        $iDecPos = ( $iDecPos < $oActCur->decimal ) ? $iDecPos : $oActCur->decimal;          
        return number_format( $dValue, $iDecPos, $oActCur->dec, $oActCur->thousand );
    }

    /**
     * According to user configuration forms and return language prefix.
     *
     * @param integer $iLanguage User selected language (default null)
     *
     * @return string
     */
    public function getLanguageTag( $iLanguage = null)
    {
        if ( !isset( $iLanguage ) ) {
            $iLanguage = $this->getBaseLanguage();
        }

        $iLanguage = (int) $iLanguage;

        return ( ( $iLanguage )?"_$iLanguage":"" );
    }

    /**
     * get language array from lang translation file
     *
     * @param int  $iLang       optional language
     * @param bool $blAdminMode admin mode switch
     *
     * @return array
     */
    protected function _getLangTranslationArray( $iLang = null, $blAdminMode = null )
    {
        startProfile("_getLangTranslationArray");
        $myConfig = $this->getConfig();
        $sFileName = '';
        $sCustFileName = '';

        $blAdminMode = isset( $blAdminMode ) ? $blAdminMode : $this->isAdmin();

        $iLang  = ( $iLang === null )?oxSession::getVar( 'blAdminTemplateLanguage' ):$iLang;
        if ( !isset( $iLang ) ) {
            $iLang = $this->getBaseLanguage();
            if ( !isset( $iLang ) ) {
                $iLang = 0;
            }
        }

        if ( $blAdminMode ) {
            $aLangCache = $this->_aAdminLangCache;
        } else {
            $aLangCache = $this->_aLangCache;
        }

        // casting for security reasons
        $iLang = (int) $iLang;
        if ( !$aLangCache[$iLang] ) {

            /*
            $sFileName     = $myConfig->getLanguagePath('lang.php', $blAdminMode,$iLang);
            $sCustFileName = $myConfig->getLanguagePath('cust_lang.php', $blAdminMode,$iLang);
            */
            $sCacheName = "languagefiles_".$blAdminMode."_".$iLang."_".$this->getConfig()->getShopId();
            $aLangFiles = oxUtils::getInstance()->fromFileCache($sCacheName);
            if (!$aLangFiles) {
                $sDir = dirname($myConfig->getLanguagePath('lang.php', $blAdminMode, $iLang));

                //get all lang files
                $aLangFiles = glob($sDir."/*lang.php");

                //save to cache
                oxUtils::getInstance()->toFileCache($sCacheName, $aLangFiles);
            }

            $aLangCache[$iLang] = array();
            foreach ($aLangFiles as $sLangFile) {
                require $sLangFile;
                $aLangCache[$iLang] = array_merge( $aLangCache[$iLang], $aLang);
            }


            //build lang array
            /*
            if ( is_file( $sFileName ) ) {
                require $sFileName;
                $aLangCache[$iLang] = $aLang;
            }

            if ( is_file( $sCustFileName ) ) {
                require $sCustFileName;
                $aLangCache[$iLang] = array_merge( $aLangCache[$iLang], $aLang);
            }*/

            if ( $blAdminMode ) {
                $this->_aAdminLangCache = $aLangCache;
            } else {
                $this->_aLangCache = $aLangCache;
            }
        }


        stopProfile("_getLangTranslationArray");

        // if language array exists ..
        if ( isset( $aLangCache[$iLang] ) ) {
            return $aLangCache[$iLang];
        } else {
            return array();
        }
    }

    /**
     * translates a given string
     *
     * @param string $sStringToTranslate string that should be translated
     * @param int    $iLang              language id (optional)
     * @param bool   $blIsAdmin          admin mode switch (default null)
     *
     * @return string translation
     */
    protected function _readTranslateStrFromTextFile( $sStringToTranslate, $iLang = null, $blIsAdmin = null )
    {
        $iLang  = ( $iLang === null )?oxSession::getVar( 'blAdminTemplateLanguage' ):$iLang;
        if ( !isset( $iLang ) ) {
            $iLang = $this->getBaseLanguage();
            if ( !isset( $iLang ) ) {
                $iLang = 0;
            }
        }

        $blIsAdmin = isset( $blIsAdmin ) ? $blIsAdmin : $this->isAdmin();
        $sFileName = $this->getConfig()->getLanguagePath('lang.txt', $blIsAdmin, $iLang);

        if ( is_file ( $sFileName ) ) {

            static $aUserLangCache = array();

            if ( !isset( $aUserLangCache[$sFileName] ) ) {
                $handle = @fopen( $sFileName, "r" );
                if ( $handle === false ) {
                    return $sStringToTranslate;
                }

                $contents = fread( $handle, filesize ( $sFileName ) );
                fclose( $handle );
                $fileArray = explode( "\n", $contents );
                $aUserLangCache[$sFileName] = array();
                $aLang = &$aUserLangCache[$sFileName];

                while ( list( $nr,$line ) = each( $fileArray ) ) {
                    $line = ltrim( $line );
                    if ( $line[0]!="#" and strpos( $line, "=" ) > 0 ) {
                        $index = trim( substr( $line, 0, strpos($line, "=" ) ) );
                        $value = trim( substr( $line, strpos( $line, "=" ) + 1, strlen( $line ) ) );
                        $aLang[trim($index)] = trim($value);
                    }
                }
            }

            if ( !isset( $aLang ) && isset( $aUserLangCache[$sFileName] ) ) {
                $aLang = &$aUserLangCache[$sFileName];
            }

            if ( isset( $aLang[$sStringToTranslate] ) ) {
                return $aLang[$sStringToTranslate];
            }
        }

        return $sStringToTranslate;
    }

    /**
     * Validate language id. If not valid id, returns default value
     *
     * @param int $iLang Language id
     *
     * @return int
     */
    public function validateLanguage( $iLang = null )
    {
        $iLang = (int) $iLang;

        // checking if this language is valid
        $aLanguages = $this->getLanguageArray();

        if ( !isset( $aLanguages[$iLang] ) && is_array( $aLanguages ) ) {
            $oLang = current( $aLanguages );
            $iLang = $oLang->id;
        }

        return $iLang;
    }

    /**
     * Set base shop language
     *
     * @param int $iLang Language id
     *
     * @return null
     */
    public function setBaseLanguage( $iLang = null )
    {
        if ( is_null($iLang) ) {
            $iLang = $this->getBaseLanguage();
        } else {
            $this->_iBaseLanguageId = (int) $iLang;
        }

        if ( defined( 'OXID_PHP_UNIT' ) ) {
            modSession::getInstance();
        }

        oxSession::setVar( 'language', $iLang );
    }

    /**
     * Set templates language id
     *
     * @param int $iLang Language id
     *
     * @return null
     */
    public function setTplLanguage( $iLang = null )
    {
        if ( is_null($iLang) ) {
            $iLang = $this->getTplLanguage();
        } else {
            $this->_iTplLanguageId = (int) $iLang;
        }

        if ( defined( 'OXID_PHP_UNIT' ) ) {
            modSession::getInstance();
        }

        oxSession::setVar( 'tpllanguage', $iLang );
    }

}
