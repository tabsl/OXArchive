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
 * $Id: main.php 13619 2008-10-24 09:40:23Z sarunas $
 */

/**
 * Admin main manager.
 * Starting administrator windows template.
 * @package admin
 */
class Main extends oxAdminView
{
    /**
     * Executes parent method parent::render(), creates shop object, sets template parameters
     * and returns name of template file "main.tpl".
     *
     * @return string
     */
    public function render()
    {
        $myConfig = $this->getConfig();

        parent::render();

        $this->_aViewData['sVersion'] = $myConfig->getConfigParam( 'sVersion' );

        $iDynInterfaceLanguage = $myConfig->getConfigParam( 'dynInterfaceLanguage' );
        if ( isset( $iDynInterfaceLanguage ) )
            $iLang = $iDynInterfaceLanguage;
        else
            //$iLang = $myConfig->getConfigParam( 'iAdminLanguage' );
            $iLang = oxLang::getInstance()->getTplLanguage();

        $this->_aViewData['dynLanguage'] = $iLang;

        // #661 execute stuff we run each time when we start admin once
        $this->_aViewData['sMessage'] = $this->_doStartUpChecks();

        // read RSS from forum
        $this->_aViewData['rssfeed'] = array_merge( $this->_readNews("23,21,24"), $this->_readNews("32"));

        return "main.tpl";
    }

    /**
     * Every Time Admin starts we perform these checks
     * returns some messages if there is something to display
     *
     * @return string
     */
    protected function _doStartUpChecks()
    {   // #661
        $sMessage = "";

            // check if there are any links in oxobject2category which are outdated or old
            $sSQL = "select oxobject2category.oxid from oxcategories, oxobject2category left join oxarticles on oxarticles.oxid = oxobject2category.oxobjectid  where oxcategories.oxid = oxobject2category.oxcatnid and oxarticles.oxid is null";
            $iCnt = 0;
            $sDel = "";
            $rs = oxDb::getDb()->Execute( $sSQL);
            if ($rs != false && $rs->recordCount() > 0) {
                while (!$rs->EOF) {
                    if ( $iCnt)
                        $sDel .= ",";
                    $sDel .= "'".$rs->fields[0]."'";
                    $iCnt++;
                    $rs->moveNext();
                }
                // delete it now
                oxDb::getDb()->Execute("delete from oxobject2category where oxid in ($sDel)");
                $sMessage = "- Deleted $iCnt old/outdated entries in table oxobject2category.<br>";
            }

        // do nothing, checks do need too much time in EE and huge databases

        return $sMessage;
    }

    /**
     * Read news from forum under www.oxid-esales.com via RSS
     * returns the threads to display as array
     *
     * @param string $sForumIDs forum topic id
     *
     * @return array
     */
    protected function _readNews( $sForumIDs)
    {
        $aNews = array();
        $blReadRSS = $this->getConfig()->getConfigParam( 'blReadRSS' );
        if ( isset( $blReadRSS ) && !$blReadRSS )
           return $aNews;

        $url    = "http://www.oxid-esales.com/de/forum/external.php?forumids=".$sForumIDs;
        $sData  = '';

        $fp = @fopen( $url, "rb");
        if ( $fp) {
            while ( !feof($fp))
                $sData .= fread( $fp, 8192);
            fclose( $fp);
        }

        $aIndexes = array();
        if ( isset( $sData) && $sData) {
            $oParser = @xml_parser_create();
            $aValues = array();
            if ( $oParser) {
                xml_parser_set_option($oParser, XML_OPTION_CASE_FOLDING, 0);
                xml_parser_set_option($oParser, XML_OPTION_SKIP_WHITE, 1);
                xml_parser_set_option($oParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
                xml_parse_into_struct($oParser, $sData, $aValues, $aIndexes);
                $iErr = xml_get_error_code( $oParser);
                if ( $iErr != false) {
                    echo( "Error ! XML : ". xml_error_string( $iErr));
                    echo( "\ndata:".$sData);
                    die();
                }
                xml_parser_free($oParser);
            }

            // build lines for parsing
            foreach ( $aValues as $key => $aContent) {
                if ( $aContent['tag'] == "item" && $aContent['type'] == "open") {
                    $oLine = new stdClass();
                    continue;
                } elseif ( $aContent['tag'] == "item" && $aContent['type'] == "close") {
                    //MK: fixed problem when admin logs in he cannot see oxid forum messages on start page
                    //$oLine = new stdClass();
                    $aTmp = explode( "Erstellt", $oLine->description);
                    $oLine->description = $aTmp[1];
                    $oLine->description = str_replace("von:", "Erstellt von:", $oLine->description);
                    $aTmp2 = explode( "Geschrieben", $oLine->description);
                    $oLine->description = $aTmp2[0];
                    $oLine->date        = $aTmp2[1];
                    $oLine->date = str_replace("am ", "", $oLine->date);
                    $oLine->date = str_replace(" um ", ",", $oLine->date);
                    $oLine->date .= " Uhr";

                    if ( count( $aNews[$aTmp[0]]) < 3 )
                        $aNews[$aTmp[0]][] = $oLine;

                    $oLine = null;
                    continue;
                }

                if ($oLine) {
                    // store values
                    $sVar = $aContent['tag'];
                    if ( isset( $aContent['value']))
                        $oLine->$sVar = $aContent['value'];
                }
            }
        }

        return $aNews;
    }


}
