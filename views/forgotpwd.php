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
 * $Id: forgotpwd.php 13614 2008-10-24 09:36:52Z sarunas $
 */

/**
 * Password reminder page.
 * Collects toparticle, bargain article list. There is a form with entry
 * field to enter login name (usually email). After user enters required
 * information and submits "Request Password" button mail is sent to users email.
 * OXID eShop -> MY ACCOUNT -> "Forgot your password? - click here."
 */
class ForgotPwd extends oxUBase
{
    /**
     * Current class template name.
     * @var string
     */
    protected $_sThisTemplate = 'forgotpwd.tpl';

    /**
     * Send forgot E-Mail.
     * @var string
     */
    protected $_sForgotEmail = null;

    /**
     * Current view search engine indexing state:
     *     0 - index without limitations
     *     1 - no index / no follow
     *     2 - no index / follow
     */
    protected $_iViewIndexState = 1;

    /**
     * Executes oxemail::SendForgotPwdEmail() and sends login
     * password to user according to login name (email).
     *
     * Template variables:
     * <b>sendForgotMail</b>
     *
     * @return null
     */
    public function forgotPassword()
    {
        $sEmail = oxConfig::getParameter( 'lgn_usr' );
        $oEmail = oxNew( 'oxemail' );
        $oEmail->sendForgotPwdEmail( $sEmail );
        $this->_sForgotEmail = $sEmail?$sEmail:' - ';
    }

    /**
     * Executes parent::render(), loads action articles and returns
     * name of template to render forgotpwd::_sThisTemplate.
     *
     * @return  string  $this->_sThisTemplate   current template file name
     */
    public function render()
    {
        $this->_aViewData['sendForgotMail'] = $this->getForgotEmail();

        parent::render();

        // loading actions
        $this->_loadActions();

        return $this->_sThisTemplate;
    }

    /**
     * Template variable getter. Returns searched article list
     *
     * @return string
     */
    public function getForgotEmail()
    {
        return $this->_sForgotEmail;
    }
}
