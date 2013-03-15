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
 * $Id: oxtagcloud.php 13707 2008-10-26 15:01:21Z sarunas $
 */

if (!defined('OXTAGCLOUD_MINFONT')) {
    define('OXTAGCLOUD_MINFONT', 100);
    define('OXTAGCLOUD_MAXFONT', 400);
    define('OXTAGCLOUD_MINOCCURENCETOSHOW', 2);
    //depends on mysql server configuration
    define('OXTAGCLOUD_MINTAGLENGTH', 4);
    define('OXTAGCLOUD_STARTPAGECOUNT', 20);
    define('OXTAGCLOUD_EXTENDEDCOUNT', 200);
}

/**
 * Class dedicateg to tag cloud handling
 *
 */
class oxTagCloud extends oxSuperCfg
{
    /**
     * Cache key
     *
     * @var unknown_type
     */
    protected $_sCacheKey = "tagcloud_";

    /**
     * This method generates test tags data and probably should be deleted for the release. Or if you need tags you can generate them by: oxTagCloud::generateTagsFromLongDescription(); We used this method for demo data only
     */
    public static function generateTagsFromLongDescription($iLang = 0)
    {
        $sQ = "select oxid, oxlongdesc".oxLang::getLanguageTag($iLang)." from oxartextends";
        $rs = oxDB::getDb(true)->Execute($sQ);
        while(!$rs->EOF && $i++ < 100)
        {
            $sLD = '';
            try{
            $sLD = strip_tags($rs->fields["oxlongdesc"]);
            $sLD = substr($sLD, 0, strpos($sLD, ' ', 80));
            $sLD = str_replace(array("!","?",".",":","*", "«", "»", ",", "'", '"',"(",")", "&nbsp;", "\n", "\r"), "", $sLD);
            $sLD = trim(strtolower($sLD));
            $aLD = explode(" ", $sLD);

            $sLD = '';
            $aStopWords = array("die", "das", "die", "dieses", "aus", "in", "das", "den", "und", "mit", "ist", "so", "dem", "de", "die", "el","für"
            ,"das","is","im","hat","nur","du","sie","ihr","diese","als","da","kann","wenn","ein","eine","dein","der","nicht","viel","jede","zum","sich","ja","bis"
            ,"auf","oder","von","des","ab","dieser","vor","dir","von","einige","nach", "bei", "deiner","wie","also","sind","ins","einem", "wird", "am", "-", "22cm", "man", "24x19x4cm", "of", "es", "auch", "einen", "noch", "war", "waren", "wir", "aber", "haben");
            foreach ($aLD as $ld)
                if ($ld && !in_array($ld, $aStopWords) && !is_numeric($ld))
                    $sLD .= $ld." ";


            } catch (Exception $e) {}

            $sLD = self::prepareTags($sLD);

            echo $sLD."<br><br>\n";

            $sQ = "update oxartextends set oxtags".oxLang::getLanguageTag($iLang)." = '$sLD' where oxid = '".$rs->fields['oxid']."'";
            oxDb::getDb(true)->Execute($sQ);
            $rs->MoveNext();
        }
    }

    /**
     * Returns tag array
     *
     * @return array
     */
    public function getTags($sArtId = null, $blExtended = false)
    {
        if ($blExtended)
            $iAmount = OXTAGCLOUD_EXTENDEDCOUNT;
        else
            $iAmount = OXTAGCLOUD_STARTPAGECOUNT;

        //$oArticle = oxNew("oxarticle");
        //$sQ = "select oxtags from oxarticles where " . $oArticle->getSqlActiveSnippet();
        $sArticleSelect = " 1 ";
        if ($sArtId)
        {
            $sArtId = mysql_real_escape_string($sArtId);
            $sArticleSelect = " oxarticles.oxid = '$sArtId' ";
            $iAmount = 0;
        }

        $sField = "oxartextends.oxtags".oxLang::getInstance()->getLanguageTag();

        $sArtView = getViewName('oxarticles');
        $sQ = "select $sField as oxtags from $sArtView as oxarticles left join oxartextends on oxarticles.oxid=oxartextends.oxid where $sArticleSelect";
        //$sQ = "select $sField from oxartextends where $sArticleSelect";
        $rs = oxDb::getDb(true)->execute($sQ);
        $aTags = array();
        while ($rs && $rs->RecordCount() && !$rs->EOF) {
            $sTags = $this->trimTags($rs->fields['oxtags']);
            $aArticleTags = explode(' ', $sTags);
            foreach ($aArticleTags as $sTag) {
                if (trim($sTag))
                    ++$aTags[$sTag];
            }
            $rs->moveNext();
        }

        //taking only top tags
        if ($iAmount) {
            arsort($aTags);
            $aTags = array_slice($aTags, 0, $iAmount, true );
        }

        ksort($aTags);

        return $aTags;
    }

    /**
     * Returns HTML formated Tag Cloud
     *
     */
    public function getTagCloud($sArtId = null, $blExtended = false)
    {
        $sTagCloud = "";
        $sCacheKey = $this->_getCacheKey($blExtended);
        if ($this->_sCacheKey && !$sArtId) {
            $sTagCloud = oxUtils::getInstance()->fromFileCache($sCacheKey);
        }

        if ($sTagCloud)
            return $sTagCloud;

        startProfile('trimTags');
        $aTags = $this->getTags($sArtId, $blExtended);
        stopProfile('trimTags');
        if (!count($aTags))
            return $sTagCloud;

        $iMaxHit = max( $aTags);
        $blSeoIsActive = oxUtils::getInstance()->seoIsActive();
        if ( $blSeoIsActive) {
            $oSeoEncoder = oxSeoEncoder::getInstance();
        }

        $iLang = oxLang::getInstance()->getBaseLanguage();
        $sUrl = $this->getConfig()->getShopUrl();

        foreach ($aTags as $sTag => $sRelevance)
        {
            $sLink = $sUrl."index.php?cl=tag&amp;searchtag=".rawurlencode($sTag)."&amp;lang=".$iLang;
            if ( $blSeoIsActive) {
                $sLink = $oSeoEncoder->getDynamicUrl( "index.php?cl=tag&amp;searchtag=".rawurlencode($sTag), "tag/$sTag", $iLang );
            }
            $sTagCloud .= "<a style='font-size:". $this->_getFontSize($sRelevance, $iMaxHit) ."%;' href='$sLink'>".htmlentities($sTag)."</a> ";
        }

        if ($this->_sCacheKey && !$sArtId)
            oxUtils::getInstance()->toFileCache($sCacheKey, $sTagCloud);

        return $sTagCloud;
    }

    /**
     * Assigns article oxsearchkeys field value to article tags
     *
     * @return bool
     */
    /*
    public function assignTagsFromSearchKeys()
    {
        $sArticleTable = getViewName('oxarticles');
        $sQ = "select oxid, oxsearchkeys from $sArticleTable where oxsearchkeys <> '' " ;
        $rs = oxDb::getDb(true)->execute($sQ);
        while ($rs && $rs->RecordCount()>0 && !$rs->EOF)
        {
            $sOxid = $rs->fields['OXID'];
            $sSearchkeys = $rs->fields['OXSEARCHKEYS'];
            $sUpdate = "update oxartextends set oxtags = '$sSearchkeys' where not oxtags and oxid = '$sOxid'";
            oxDb::getDb()->execute($sUpdate);
            $rs->moveNext();
        }

        return true;
    }*/

    /**
     * Returns font size value for current occurence depending on max occurence.
     *
     * @param int $iHit
     * @param int $iMaxHit
     *
     * @return int
     */
    protected function _getFontSize($iHit, $iMaxHit)
    {
        //handling special case
        if ($iMaxHit <= OXTAGCLOUD_MINOCCURENCETOSHOW || !$iMaxHit)
            return OXTAGCLOUD_MINFONT;

        $iFontDiff = OXTAGCLOUD_MAXFONT - OXTAGCLOUD_MINFONT;
        $iMaxHitDiff = $iMaxHit - OXTAGCLOUD_MINOCCURENCETOSHOW;
        $iHitDiff = $iHit - OXTAGCLOUD_MINOCCURENCETOSHOW;

        if ($iHitDiff < 0)
            $iHitDiff = 0;

        $iSize = round($iHitDiff * $iFontDiff / $iMaxHitDiff) + OXTAGCLOUD_MINFONT;

        return $iSize;
    }

    /**
     * Takes tag string and makes shorter tags longer by adding underscore. This is needed for FULLTEXT index
     *
     * @param string $sTags
     *
     * @return string
     */
    public function prepareTags($sTags)
    {
        $aTags = explode(' ', $sTags);
        $sRes = '';
        foreach($aTags as $sTag) {
            if (!strlen($sTag))
                continue;

            if (strlen($sTag) < OXTAGCLOUD_MINTAGLENGTH)
            {
                $sLength = strlen($sTag);
                for ($i = 0; $i < OXTAGCLOUD_MINTAGLENGTH - $sLength; $i++)
                    $sTag .= '_';
            }

            $sRes .= strtolower($sTag) . " ";
        }

        return trim ($sRes);
    }

    /**
     * Trims underscores from tags.
     *
     * @param string $sTags
     *
     * @return string
     */
    public function trimTags($sTags)
    {
        $aTags = explode(' ', $sTags);
        $sRes = '';
        foreach($aTags as $sTag) {
            if (!strlen($sTag))
                continue;

            while($sTag[strlen($sTag) - 1] == '_')
                $sTag = substr($sTag, 0, -1);

            $sRes .= $sTag . " ";
        }

        return trim ($sRes);
    }

    /**
     * Resets tag cache
     *
     * @return null
     */
    public function resetTagCache()
    {
        $sCacheKey1 = $this->_getCacheKey(TRUE);
        oxUtils::getInstance()->toFileCache($sCacheKey1, null);

        $sCacheKey2 = $this->_getCacheKey(FALSE);
        oxUtils::getInstance()->toFileCache($sCacheKey2, null);
    }

    /**
     * Returns tag cache key name.
     *
     * @param bool $blExtended Whether to display full list
     */
    protected function _getCacheKey($blExtended)
    {
        return $this->_sCacheKey."_".$this->getConfig()->getShopId()."_".oxLang::getInstance()->getBaseLanguage()."_".$blExtended;
    }

}
