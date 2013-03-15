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
 * @copyright © OXID eSales AG 2003-2009
 * $Id: oxadmindetails.php 14554 2008-12-08 15:37:05Z vilma $
 */

/**
 * Including AJAX wrapper class
 */
require_once "oxajax.php";

/**
 * Admin selectlist list manager.
 * @package admin
 */
class oxAdminDetails extends oxAdminView
{
    /**
     * Global editor object
     *
     * @var object
     */
    protected $_oEditor   = null;

    /**
     * Calls parent::render, sets admin help url
     *
     * @return string
     */
    public function render()
    {
        $sReturn = parent::render();

        // generate help link
        $myConfig = $this->getConfig();
        $sDir = $myConfig->getConfigParam( 'sShopDir' ) . '/documentation/admin';
        if ( is_dir( $sDir ) ) {
            $sDir = $myConfig->getConfigParam( 'sShopURL' ) . 'documentation/admin';
        } else {

                $oShop = oxNew( 'oxshop' );
                $oShop->load( oxSession::getVar( 'actshop' ) );
                //$sDir = "http://docu.oxid-esales.com/PE/{$oShop->oxshops__oxversion->value}/" . $myConfig->getConfigParam( 'iAdminLanguage' ) . '/admin';
                $sDir = "http://docu.oxid-esales.com/PE/{$oShop->oxshops__oxversion->value}/" . oxLang::getInstance()->getTplLanguage() . '/admin';
        }

        $this->_aViewData['sHelpURL'] = $sDir;

        return $sReturn;
    }

    /**
     * Generates Text editor and set values ( load CSS etc. )
     *
     * @param int    $iWidth      editor width
     * @param int    $iHeight     editor height
     * @param object $oObject     object passed to editor
     * @param string $sField      object field which content is passed to editor
     * @param string $sStylesheet stylesheet to use in editor
     *
     * @return string Editor output
     */
    protected function _generateTextEditor( $iWidth, $iHeight, $oObject, $sField, $sStylesheet = null )
    {
        $myConfig = $this->getConfig();

        // include the config file and editor class:
        $sEditorPath = 'wysiwigpro';
        $sEditorFile = getShopBasePath()."admin/".$sEditorPath . '/wysiwygPro.class.php';



        $sEditObjectValue = '';
        if ( $oObject ) {
            $sInitialValue = '';
            if ($oObject->$sField instanceof oxField) {
                $sInitialValue = $oObject->$sField->getRawValue();
            } else {
                $sInitialValue = $oObject->$sField->value;
            }
            $oObject->$sField = new oxField(str_replace( '[{$shop->currenthomedir}]', $myConfig->getCurrentShopURL(), $sInitialValue ), oxField::T_RAW);
            $sEditObjectValue = $oObject->$sField->value;
        }


        if (!$sEditorFile || !file_exists($sEditorFile)) {
            if (strpos($iWidth, '%') === false) {
                $iWidth .= 'px';
            }
            if (strpos($iHeight, '%') === false) {
                $iHeight .= 'px';
            }
            return "<textarea id='editor_{$sField}' style='width:{$iWidth}; height:{$iHeight};'>$sEditObjectValue</textarea>";
        }

        include_once ($sEditorFile);

        // create a new instance of the wysiwygPro class:
        $this->_oEditor = new wysiwygPro();

        // set language file name
        $sEditorUrl = $myConfig->getConfigParam( 'sShopURL' ).$myConfig->getConfigParam( 'sAdminDir' )."/{$sEditorPath}/";
        if ( $sAdminSSLURL = $myConfig->getConfigParam( 'sAdminSSLURL' ) ) {
            $sEditorUrl = "{$sAdminSSLURL}/{$sEditorPath}/";
        }

        $this->_oEditor->editorURL = $sEditorUrl;
        $this->_oEditor->urlFormat = 'absolute';

        // document & image directory:
        $this->_oEditor->documentDir = $this->_oEditor->imageDir = $myConfig->getPictureDir( false ).'wysiwigpro/';
        $this->_oEditor->documentURL = $this->_oEditor->imageURL = $myConfig->getPictureUrl( null, false ).'wysiwigpro/';
        
        // enabling upload
        $this->_oEditor->upload = true;

        //#M432 enabling deleting files and folders
        $this->_oEditor->deleteFiles = true;
        $this->_oEditor->deleteFolders = true;

        // allowed image extensions
        $this->_oEditor->allowedImageExtensions = '.jpg, .jpeg, .gif, .png';

        // allowed document extensions
        $this->_oEditor->allowedDocExtensions   = '.html, .htm, .pdf, .doc, .rtf, .txt, .xl, .xls, .ppt, .pps, .zip, .tar, .swf, .wmv, .rm, .mov, .jpg, .jpeg, .gif, .png';

        // set name
        $this->_oEditor->name = $sField;

        // set language file name
        $this->_oEditor->lang = oxLang::getInstance()->translateString( 'editor_language', oxLang::getInstance()->getTplLanguage() );

        // set contents
        if ( $sEditObjectValue ) {
            $this->_oEditor->value = $sEditObjectValue;
        }

        // parse for styles and add them
        $this->setAdminMode( false );
        $sCSSPath = $myConfig->getResourcePath("styles/{$sStylesheet}", false );
        $sCSSUrl  = $myConfig->getResourceUrl("styles/{$sStylesheet}", false );

        $aCSSPaths = array();

        // #1157C - in wysiwigpro editor font problem
        $aCSSPaths[] = $myConfig->getResourcePath("oxid.css", false );

        $this->setAdminMode( true );

        if (is_file($sCSSPath)) {

                $aCSSPaths[] = $sCSSUrl;

            if (is_readable($sCSSPath)) {
                $aCSS = @file( $sCSSPath);
                if ( isset( $aCSS) && $aCSS) {
                    $aClasses = array();
                    foreach ( $aCSS as $key => $sLine ) {
                        $sLine = trim($sLine);

                        if ( $sLine[0] == '.' && !strstr( $sLine, 'default' ) ) {
                            // found one tag
                            $sTag = substr( $sLine, 1);
                            $iEnd = strpos( $sTag, ' ' );
                            if ( !isset( $iEnd ) || !$iEnd ) {
                                $iEnd = strpos( $sTag, '\n' );
                        }

                            if ( $sTag = substr( $sTag, 0, $iEnd ) ) {
                                $aClasses["span class='{$sTag}'"] = $sTag;
                    }
                }
            }

                    $this->_oEditor->stylesMenu = $aClasses;
        }
            }
        }

        foreach ( $aCSSPaths as $sCssPath ) {
            $this->_oEditor->addStylesheet( $sCssPath );
        }

        //while there is a bug in editor template filter we cannot use this feature
        // loading template filter plugin
        $this->_oEditor->loadPlugin( 'templateFilter' );
        $this->_oEditor->plugins['templateFilter']->protect( '[{', '}]' );
        if ( $myConfig->getConfigParam( 'bl_perfParseLongDescinSmarty' ) ) {
            $this->_oEditor->plugins['templateFilter']->assign( '[{$shop->currenthomedir}]', $myConfig->getShopURL() );
            $this->_oEditor->plugins['templateFilter']->assign( '[{$shop->currenthomedir}]', $myConfig->getSSLShopURL() );
        }

        // generate and return editor code
        return $this->_oEditor->fetch( $iWidth, $iHeight );
    }

    /**
     * Resets number of articles in current shop categories
     *
     * @return null
     */
    public function resetNrOfCatArticles()
    {

            oxUtils::getInstance()->oxResetFileCache();
    }

    /**
     * Resets number of articles in current shop vendors
     *
     * @return null
     */
    public function resetNrOfVendorArticles()
    {

            oxUtils::getInstance()->oxResetFileCache();
    }

    /**
    * Function creates category tree for select list used in "Category main", "Article extend" etc.
    * Returns ID of selected category if available
    *
    * @param string $sTplVarName     name of template variable where is stored category tree
    * @param string $sSelectedCatId  ID of category witch was selected in select list
    * @param string $sEditCatId      ID of category witch we are editing
    * @param bool   $blForceNonCache Set to true to disable caching
    * @param int    $iTreeShopId     tree shop id
    *
    * @return string
    */
    protected function _getCategoryTree( $sTplVarName, $sSelectedCatId, $sEditCatId = '', $blForceNonCache = false, $iTreeShopId = null )
    {
        // caching category tree, to load it once, not many times
        if ( !isset( $this->oCatTree ) || $blForceNonCache ) {
            $this->oCatTree = oxNew( 'oxCategoryList' );
            $this->oCatTree->setShopID( $iTreeShopId );
            $this->oCatTree->buildList( $this->getConfig()->getConfigParam( 'bl_perfLoadCatTree' ) );
        }

        // copying tree
        $oCatTree = $this->oCatTree;
        //removing current category
        if ( $sEditCatId && isset( $oCatTree[$sEditCatId] ) ) {
            unset( $oCatTree[$sEditCatId] );
        }

        // add first fake category for not assigned articles
        $oRoot = oxNew( 'oxcategory' );
        $oRoot->oxcategories__oxtitle = new oxField('--');

        $oCatTree->assign( array_merge( array( '' => $oRoot ), $oCatTree->getArray() ) );

        // mark selected
        if ( $sSelectedCatId ) {
            // fixed parent category in select list
            foreach ($oCatTree as $oCategory) {
                if ($oCategory->getId() == $sSelectedCatId ) {
                    $oCategory->selected = 1;
                }
            }
        } else { // no category selected - opening first available
            $oCatTree->rewind();
            if ( $oCat = $oCatTree->current() ) {
                $oCat->selected = 1;
                $sSelectedCatId = $oCat->getId();
            }
        }

        // passing to view
        $this->_aViewData[$sTplVarName] =  $oCatTree;

        return $sSelectedCatId;
    }

    /**
     * Updates object folder parameters
     *
     * @return null
     */
    public function changeFolder()
    {
        $sFolder = oxConfig::getParameter( 'setfolder' );
        $sFolderClass = oxConfig::getParameter( 'folderclass' );

        if ( $sFolderClass == 'oxcontent' && $sFolder == 'CMSFOLDER_NONE' ) {
            $sFolder = '';
        }

        $oObject = oxNew( $sFolderClass );
        if ( $oObject->load( oxConfig::getParameter( 'oxid' ) ) ) {
            $oObject->{$oObject->getCoreTableName() . '__oxfolder'} = new oxField($sFolder);
            $oObject->save();
        }
    }

    /**
     * Sets-up navigation parameters
     *
     * @param string $sNode active view id
     *
     * @return null
     */
    protected function _setupNavigation( $sNode )
    {
        // navigation according to class
        if ( $sNode ) {

            $myAdminNavig = $this->getNavigation();

            // default tab
            $this->_aViewData['default_edit'] = $myAdminNavig->getActiveTab( $sNode, $this->_iDefEdit );

            // buttons
            $this->_aViewData['bottom_buttons'] = $myAdminNavig->getBtn( $sNode );
        }
    }
}
