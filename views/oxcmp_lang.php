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
 * @package views
 * @copyright © OXID eSales AG 2003-2008
 * $Id: oxcmp_lang.php 13614 2008-10-24 09:36:52Z sarunas $
 */

/**
 * Shop language manager.
 * Performs language manager function: changes template settings, modifies URL's.
 * @subpackage oxcmp
 */
class oxcmp_lang extends oxView
{
    /**
     * Array of shop languages.
     * @var array
     */
    public $aLanguages = null;

    /**
     * Marking object as component
     * @var bool
     */
    protected $_blIsComponent = true;

    /**
     * Searches for language passed by URL, session or posted
     * vars. If current client is search engine - sets language
     * to session (this way active language is allways kept).
     * Additionally language changing URLs is formed and stored
     * to oxcmp_lang::aLanguages array. Finally executes parent
     * method parent::init().
     *
     * @return null
     */
    public function init()
    {
        $myConfig = $this->getConfig();

        // Performance
        if ( !$myConfig->getConfigParam( 'bl_perfLoadLanguages' ) ) {
            return;
        }

        $iLang = oxLang::getInstance()->getBaseLanguage();
        $iChangeLang = oxConfig::getParameter( 'lang' );

        if ( oxConfig::getParameter( 'changelang' ) ) {
            $iChangeLang = oxConfig::getParameter("changelang");
        }

        if ( isset( $iChangeLang ) ) {
            // set new language
            $iChangeLang = oxLang::getInstance()->validateLanguage( $iChangeLang );
            oxLang::getInstance()->setTplLanguage( $iChangeLang );
            oxLang::getInstance()->setBaseLanguage( $iChangeLang );

            // recalc basket
            $oBasket = $this->getSession()->getBasket();
            $oBasket->onUpdate();
        }

        $this->aLanguages = oxLang::getInstance()->getLanguageArray();

        parent::init();
    }

    /**
     * Executes parent::render() and returns array with languages.
     *
     * @return  array   $this->aLanguages   languages
     */
    public function render()
    {
        parent::render();
        $myConfig = $this->getConfig();

        // Performance
        if ( !$myConfig->getConfigParam( 'bl_perfLoadLanguages' ) ) {
            return;
        }

        reset($this->aLanguages);
        $aLanguageURLs = $myConfig->getConfigParam( 'aLanguageURLs' );

        while ( list( $sKey, $oVal ) = each( $this->aLanguages ) ) {
            $this->aLanguages[$sKey]->link = $this->getParent()->getLink($oVal->id);
        }
        return $this->aLanguages;
    }
}
