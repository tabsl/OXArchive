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
 * $Id: oxutilsfile.php 14388 2008-11-26 15:43:17Z vilma $
 */

/**
 * File manipulation utility class
 */
class oxUtilsFile extends oxSuperCfg
{
    /**
     * oxUtils class instance.
     *
     * @var oxutils* instance
     */
    private static $_instance = null;

    /**
     * Returns object instance
     *
     * @return oxUtilsFile
     */
    public static function getInstance()
    {
        // disable caching for test modules
        if ( defined( 'OXID_PHP_UNIT' ) ) {
            static $inst = array();
            self::$_instance = $inst[oxClassCacheKey()];
        }

        if ( !self::$_instance instanceof oxUtilsFile ) {

            self::$_instance = oxNew( 'oxUtilsFile' );
            if ( defined( 'OXID_PHP_UNIT' ) ) {
                $inst[oxClassCacheKey()] = self::$_instance;
            }
        }
        return self::$_instance;
    }

    /**
     * Normalizes dir by adding missing trailing slash
     *
     * @param string $sDir Directory
     *
     * @return string
     */
    public function normalizeDir( $sDir )
    {
        if ( isset($sDir) && substr($sDir, -1) !== '/' ) {
            $sDir .= "/";
        }

        return $sDir;
    }

    /**
     * Copies directory tree for creating a new shop.
     *
     * @param string $sSourceDir Source directory
     * @param string $sTargetDir Target directory
     *
     * @return null
     */
    public function copyDir( $sSourceDir, $sTargetDir )
    {
        $handle = opendir( $sSourceDir );
        while ( false !== ( $file = readdir( $handle ) ) ) {
            if ( $file != '.' && $file != '..' ) {
                if ( is_dir( $sSourceDir.'/'.$file ) ) {

                    // recursive
                    $sNewSourceDir = $sSourceDir.'/'.$file;
                    $sNewTargetDir = $sTargetDir.'/'.$file;
                    if ( strcasecmp( $file, 'CVS' ) &&  strcasecmp( $file, '.svn' )) {
                        @mkdir( $sNewTargetDir, 0777 );
                        $this->copyDir( $sNewSourceDir, $sNewTargetDir );
                    }
                } else {
                    $sSourceFile = $sSourceDir.'/'.$file;
                    $sTargetFile = $sTargetDir.'/'.$file;

                    //do not copy files within dyn_images
                    if ( !strstr( $sSourceDir, 'dyn_images' ) ||  $file == 'nopic.jpg' || $file == 'nopic_ico.jpg' ) {
                        @copy( $sSourceFile, $sTargetFile );
                    }
                }
            }
        }
        closedir($handle);
    }

    /**
     * Deletes directory tree.
     *
     * @param string $sSourceDir Path to directory
     *
     * @return null
     */
    public function deleteDir( $sSourceDir )
    {
        if ( is_dir( $sSourceDir ) ) {
            if ( $oDir = dir( $sSourceDir ) ) {

                while ( false !== $sFile = $oDir->read() ) {
                    if ( $sFile == '.' || $sFile == '..' ) {
                        continue;
                    }

                    if ( !$this->deleteDir( $oDir->path . DIRECTORY_SEPARATOR . $sFile ) ) {
                        $oDir->close();
                        return false;
                    }
                }

                $oDir->close();
                return rmdir( $sSourceDir );
            }
        } elseif ( file_exists( $sSourceDir ) ) {
            return unlink ( $sSourceDir );
        }
    }

    /**
     * Reads remote stored file. Returns contents of file.
     *
     * @param string $sPath Remote file path & name
     *
     * @return string
     */
    public function readRemoteFileAsString( $sPath )
    {
        $sRet  = '';
        $hFile = @fopen( $sPath, 'r' );
        if ( $hFile ) {
            socket_set_timeout( $hFile, 2 );
            while ( !feof( $hFile ) ) {
                $sLine = fgets( $hFile, 4096 );
                $sRet .= $sLine;
            }
            fclose( $hFile );
        }

        return $sRet;
    }

    /**
     * Uploaded file processor (filters, etc), sets configuration parameters to
     * passed object and returns it.
     *
     * @param object $oObject object, that parameters are modified according to passed files
     *
     * @return object
     */
    public function processFiles( $oObject = null )
    {
        global $_FILES;
        $myConfig = $this->getConfig();

        if ( isset( $_FILES['myfile']['name'])) {

            // A. protection for demoshops - strictly defining allowed file extensions
            $blDemo = false;
            if ( $myConfig->isDemoShop() ) {
                $blDemo = true;
                $aAllowedFiles = array( 'gif', 'jpg', 'png', 'pdf' );
            }

            // process all files
            while (list($key, $value) = each($_FILES['myfile']['name'])) {
                $aSource = $_FILES['myfile']['tmp_name'];
                $sSource = $aSource[$key];
                $aFiletype = explode( "@", $key);
                $key    = $aFiletype[1];
                $sType  = $aFiletype[0];
                $value = strtolower( $value);

                // no file ? - skip
                if (!$value)
                    continue;

                // add type to name
                $aFilename = explode( ".", $value);

                $sFileType = trim($aFilename[count($aFilename)-1]);

                //hack?
                $aBadFiles = array("php", "jsp", "cgi", "cmf", "exe");

                if ( in_array($sFileType, $aBadFiles) || ( $blDemo && !in_array( $sFileType, $aAllowedFiles ) ) ) {
                    oxUtils::getInstance()->showMessageAndExit( "We don't play this game, go away" );
                }

                if ( isset($sFileType)) {
                    // removing file type
                    if ( count($aFilename) > 0) {
                        unset($aFilename[count($aFilename)-1]);
                    }
                    $sFName = "";
                    if ( isset($aFilename[0])) {
                        $sFName = preg_replace('/[^a-zA-Z0-9_\.-]/', '', implode('.', $aFilename));
                    }
                    $value = $sFName . "_" .strtolower($sType).".".$sFileType;
                }
                // Directory
                $iPos = 0;
                switch( $sType) {
                    case 'ICO':
                    case 'CICO':
                        $iPos = "icon";
                        break;
                    case 'TH':
                    case 'TC':
                    default:
                        $iPos = 0;
                        break;
                    case 'P1':
                        $iPos = 1;
                        break;
                    case 'P2':
                        $iPos = 2;
                        break;
                    case 'P3':
                        $iPos = 3;
                        break;
                    case 'P4':
                        $iPos = 4;
                        break;
                    case 'P5':
                        $iPos = 5;
                        break;
                    case 'P6':
                        $iPos = 6;
                        break;
                    case 'P7':
                        $iPos = 7;
                        break;
                    case 'P8':
                        $iPos = 8;
                        break;
                    case 'P9':
                        $iPos = 9;
                        break;
                    case 'P10':
                        $iPos = 10;
                        break;
                    case 'P11':
                        $iPos = 11;
                        break;
                    case 'P12':
                        $iPos = 12;
                        break;
                    case 'Z1':
                        $iPos = 'z1';
                        break;
                    case 'Z2':
                        $iPos = 'z2';
                        break;
                    case 'Z3':
                        $iPos = 'z3';
                        break;
                    case 'Z4':
                        $iPos = 'z4';
                        break;
                    case 'Z5':
                        $iPos = 'z5';
                        break;
                    case 'Z6':
                        $iPos = 'z6';
                        break;
                    case 'Z7':
                        $iPos = 'z7';
                        break;
                    case 'Z8':
                        $iPos = 'z8';
                        break;
                    case 'Z9':
                        $iPos = 'z9';
                        break;
                    case 'Z10':
                        $iPos = 'z10';
                        break;
                    case 'Z11':
                        $iPos = 'z11';
                        break;
                    case 'Z12':
                        $iPos = 'z12';
                        break;

                }

                $sTarget = $myConfig->getAbsDynImageDir() . "/$iPos/$value";

                // add file process here
                $blCopy = false;
                switch ( $sType) {
                    case 'TH':
                        if ( $myConfig->getConfigParam( 'sThumbnailsize' )) {
                            // convert this file
                            $aSize = explode( "*", $myConfig->getConfigParam( 'sThumbnailsize' ));
                            $iX = $aSize[0];
                            $iY = $aSize[1];
                            $blCopy = oxUtilspic::getInstance()->resizeImage( $sSource, $sTarget, $iX, $iY );
                        }
                        break;
                    case 'TC':
                        if ( $myConfig->getConfigParam( 'sCatThumbnailsize' )) {
                            // convert this file
                            $aSize = explode( "*", $myConfig->getConfigParam( 'sCatThumbnailsize' ));
                            $iX = $aSize[0];
                            $iY = $aSize[1];
                            $blCopy = oxUtilspic::getInstance()->resizeImage( $sSource, $sTarget, $iX, $iY );
                        }
                        break;
                    case 'CICO':
                    case 'ICO':
                        if ( $myConfig->getConfigParam( 'sIconsize' ) ) {
                            // convert this file
                            $aSize = explode( "*", $myConfig->getConfigParam( 'sIconsize' ) );
                            $iX = $aSize[0];
                            $iY = $aSize[1];
                            $blCopy = oxUtilspic::getInstance()->resizeImage( $sSource, $sTarget, $iX, $iY );

                        }
                        break;
                    case 'P1':
                    case 'P2':
                    case 'P3':
                    case 'P4':
                    case 'P5':
                    case 'P6':
                    case 'P7':
                    case 'P8':
                    case 'P9':
                    case 'P10':
                    case 'P11':
                    case 'P12':
                        //
                        $aPType = explode("P", $sType);
                        $iPic = intval($aPType[1]) - 1;

                        // #840A + compatibility with prev. versions
                        $aDetailImageSizes = $myConfig->getConfigParam( 'aDetailImageSizes' );
                        $sDetailImageSize = $myConfig->getConfigParam( 'sDetailImageSize' );
                        if ( isset($aDetailImageSizes["oxpic".intval($aPType[1])])) {
                            $sDetailImageSize = $aDetailImageSizes["oxpic".intval($aPType[1])];
                        }

                        if ( $sDetailImageSize ) {
                            // convert this file
                            $aSize = explode( "*", $sDetailImageSize);
                            $iX = $aSize[0];
                            $iY = $aSize[1];
                            $blCopy = oxUtilspic::getInstance()->resizeImage( $sSource, $sTarget, $iX, $iY );

                            //make an icon
                            $sIconName = oxUtilsPic::getInstance()->iconName($sTarget);
                            $aSize = explode( "*", $myConfig->getConfigParam( 'sIconsize' ) );
                            $iX = $aSize[0];
                            $iY = $aSize[1];
                            $blCopy = oxUtilspic::getInstance()->resizeImage( $sSource, $sIconName, $iX, $iY );

                        }
                        break;

                    case 'Z1':
                    case 'Z2':
                    case 'Z3':
                    case 'Z4':
                    case 'Z5':
                    case 'Z6':
                    case 'Z7':
                    case 'Z8':
                    case 'Z9':
                    case 'Z10':
                    case 'Z11':
                    case 'Z12':

                        //
                        $aPType = explode("Z", $sType);
                        $iPic = intval($aPType[1]) - 1;

                        // #840A + compatibility with prev. versions
                        $aZoomImageSizes = $myConfig->getConfigParam( 'aZoomImageSizes' );
                        $sZoomImageSize  = $myConfig->getConfigParam( 'sZoomImageSize' );
                        if ( isset($aZoomImageSizes["oxzoom".intval($aPType[1])])) {
                            $sZoomImageSize = $aZoomImageSizes["oxzoom".intval($aPType[1])];
                        }

                        //
                        if ( $sZoomImageSize) {
                            // convert this file
                            $aSize = explode( "*", $sZoomImageSize);
                            $iX = $aSize[0];
                            $iY = $aSize[1];
                            $blCopy = oxUtilspic::getInstance()->resizeImage( $sSource, $sTarget, $iX, $iY );
                        }
                        break;

                    default:
                        break;
                    }

                if ( !$blCopy && $sSource) {
                    move_uploaded_file( $sSource, $sTarget);
                    chmod( $sTarget, 0644);
                }
                // assign the name
                if ( isset( $value) && $value) {
                    $oObject->$key->setValue($value);
                }
            }
        }

        return $oObject;
    }

    /**
     * Checks if passed file exists and may be opened for reading. Returns true
     * on success.
     *
     * @param string $sFile Name of file to check
     *
     * @return bool
     */
    function checkFile( $sFile )
    {
        $mySession = $this->getSession();

        $aCheckCache = oxSession::getVar("checkcache");

        if ( isset( $aCheckCache[$sFile])) {
            return $aCheckCache[$sFile];
        }

        $blRet = false;

        //if (@fclose(@fopen( $sFile, "r")))
        if (is_readable( $sFile)) {
            $blRet = true;
        } else {
            // try again via socket
            $blRet = $this->urlValidate( $sFile );
        }

        $aCheckCache[$sFile] = $blRet;
        oxSession::setVar("checkcache", $aCheckCache);

        return $blRet;
    }

    /**
     * Checks if given URL is accessible (HTTP-Code: 200)
     *
     * @param string $sLink given link
     *
     * @return boolean
     */
    function urlValidate( $sLink )
    {
        $aUrlParts = @parse_url( $sLink );

        if ( empty( $aUrlParts["host"] ) ) {
            return( false );
        }

        if ( !empty( $aUrlParts["path"] ) ) {
            $sDocumentPath = $aUrlParts["path"];
        } else {
            $sDocumentPath = "/";
        }

        if ( !empty( $aUrlParts["query"] ) ) {
            $sDocumentPath .= "?" . $aUrlParts["query"];
        }

        $sHost = $aUrlParts["host"];
        $sPort = $aUrlParts["port"];

        // Now (HTTP-)GET $documentpath at $host";
        if (empty( $sPort ) ) {
            $sPort = "80";
        }
        $socket = @fsockopen( $sHost, $sPort, $errno, $errstr, 30 );
        if (!$socket) {
            return(false);
        } else {
            fwrite ($socket, "HEAD ".$sDocumentPath." HTTP/1.0\r\nHost: $sHost\r\n\r\n");
            $http_response = fgets( $socket, 22 );

            if ( ereg("200 OK", $http_response, $regs ) ) {
                return(true);
                fclose( $socket );
            } else {
                return(false);
            }
        }
    }

    /**
     * Handles uploaded path. Returns new URL to the file
     *
     * @param array  $aFileInfo   Global $_FILE parameter info
     * @param string $sUploadPath RELATIVE (to config sShopDir parameter) path for uploaded file to be copied
     *
     * @throws oxException if file is not valid
     *
     * @return string
     */
    public function handleUploadedFile($aFileInfo, $sUploadPath)
    {
        $sBasePath = $this->getConfig()->getConfigParam('sShopDir');

        //checking params
        if ( !isset( $aFileInfo['name'] ) || !isset( $aFileInfo['tmp_name'] ) ) {
            throw new oxException( 'EXCEPTION_NOFILE' );
        }

        //wrong chars in file name?
        if ( !eregi('^[_a-z0-3\.]+$', $aFileInfo['name'] ) ) {
            throw new oxException( 'EXCEPTION_FILENAMEINVALIDCHARS' );
        }

        // error uploading file ?
        if ( isset( $aFileInfo['error'] ) && $aFileInfo['error'] ) {
            throw new oxException( 'EXCEPTION_FILEUPLOADERROR_'.( (int) $aFileInfo['error'] ) );
        }

        $aPathInfo = pathinfo($aFileInfo['name']);

        $sExt = $aPathInfo['extension'];
        $sFileName = $aPathInfo['filename'];

        if ( !in_array( $sExt, $this->getConfig()->getConfigParam( 'aAllowedUploadTypes' ) ) ) {
            throw new oxException( 'EXCEPTION_NOTALLOWEDTYPE' );
        }

        //file exists ?
        while (file_exists($sBasePath . "/" .$sUploadPath . "/" . $sFileName . "." . $sExt)) {
            $sFileName .= "(1)";
        }

        move_uploaded_file($aFileInfo['tmp_name'], $sBasePath . "/" .$sUploadPath . "/" . $sFileName . "." . $sExt);

        $sUrl = $this->getConfig()->getShopUrl() . "/" . $sUploadPath . "/" . $sFileName . "." . $sExt;

        //removing dublicate slashes
        $sUrl = str_replace('//', '/', $sUrl);
        $sUrl = str_replace('http:/', 'http://', $sUrl);

        return $sUrl;
    }
}
