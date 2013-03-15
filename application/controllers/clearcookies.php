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
 * @link      http://www.oxid-esales.com
 * @package   views
 * @copyright (C) OXID eSales AG 2003-2012
 * @version OXID eShop CE
 * @version   SVN: $Id: clearcookies.php 45405 2012-05-18 14:44:48Z saulius.stasiukaitis $
 */

/**
 * CMS - loads pages and displays it
 */
class ClearCookies extends oxUBase
{
    /**
     * Current view template
     * @var string
     */
    protected $_sThisTemplate = 'page/info/clearcookies.tpl';

    /**
     * Executes parent::render(), passes template variables to
     * template engine and generates content. Returns the name
     * of template to render content::_sThisTemplate
     *
     * @return  string  $this->_sThisTemplate   current template file name
     */
    public function render()
    {
        parent::render();

        $this->_removeCookies();

        return $this->_sThisTemplate;
    }

    /**
     * Clears all cookies
     *
     * @return null
     */
    protected function _removeCookies()
    {
        if ( isset( $_SERVER['HTTP_COOKIE'] ) ) {
            $aCookies = explode( ';', $_SERVER['HTTP_COOKIE'] );
            foreach ( $aCookies as $sCookie ) {
                $sRawCookie = explode('=', $sCookie);
                setcookie( trim( $sRawCookie[0] ), '', time() - 10000, '/' );
            }
        }
        setcookie( 'language', '', time() - 10000, '/' );
        setcookie( 'displayedCookiesNotification', '', time() - 10000, '/' );
    }

    /**
     * Returns Bread Crumb - you are here page1/page2/page3...
     *
     * @return array
     */
    public function getBreadCrumb()
    {
        $aPaths = array();
        $aPath = array();

        $aPath['title'] = oxRegistry::getLang()->translateString( 'PAGE_INFO_CLEARCOOKIE_TITLE', oxRegistry::getLang()->getBaseLanguage(), false );
        $aPath['link']  = $this->getLink();
        $aPaths[] = $aPath;

        return $aPaths;
    }

}