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
 * @copyright (C) OXID eSales AG 2003-2009
 * @version OXID eShop CE
 * $Id: oxpdf.php 17468 2009-03-20 09:14:04Z arvydas $
 */

// Diese Datei basiert auf den arbeiten von http://www.fpdf.org
// Vielen Dank fuer die Unterstuetzung !

define('FPDF_FONTPATH',oxConfig::getInstance()->getConfigParam( 'sCoreDir' ) . "fpdf/font/");
require_once( oxConfig::getInstance()->getConfigParam( 'sCoreDir' ) . "fpdf/fpdf.php");

class oxPDF extends FPDF
{
    var $B;
    var $I;
    var $U;
    var $HREF;

    function PDF($orientation='P',$unit='mm',$format='A4')
    {
        //Call parent constructor
        $this->FPDF($orientation,$unit,$format);
        //Initialization
        $this->B=0;
        $this->I=0;
        $this->U=0;
        $this->HREF='';
    }

    function WriteHTML($html)
    {
        //HTML parser
        $html=str_replace("\n",' ',$html);
        $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                //Text
                if($this->HREF)
                    $this->PutLink($this->HREF,$e);
                else
                    $this->Write(5,$e);
            }
            else
            {
                //Tag
                if($e{0}=='/')
                    $this->CloseTag(strtoupper(substr($e,1)));
                else
                {
                    //Extract attributes
                    $a2=explode(' ',$e);
                    $tag=strtoupper(array_shift($a2));
                    $attr=array();
                    foreach($a2 as $v)
                        if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3))
                            $attr[strtoupper($a3[1])]=$a3[2];
                    $this->OpenTag($tag,$attr);
                }
            }
        }
    }

    function OpenTag($tag,$attr)
    {
        //Opening tag
        if($tag=='B' or $tag=='I' or $tag=='U')
            $this->SetStyle($tag,true);
        if($tag=='A')
            $this->HREF=$attr['HREF'];
        if($tag=='BR')
            $this->Ln(5);
    }

    function CloseTag($tag)
    {
        //Closing tag
        if($tag=='B' or $tag=='I' or $tag=='U')
            $this->SetStyle($tag,false);
        if($tag=='A')
            $this->HREF='';
    }

    function SetStyle($tag,$enable)
    {
        //Modify style and select corresponding font
        $this->$tag+=($enable ? 1 : -1);
        $style='';
        foreach(array('B','I','U') as $s)
            if($this->$s>0)
                $style.=$s;
        $this->SetFont('',$style);
    }

    function PutLink($URL,$txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }

    function Text($x,$y,$txt)
    {
        // replaces some special code to chars
        $txt = str_replace( "&nbsp;", " ", $txt);
        $txt = str_replace( "&auml;", "ä", $txt);
        $txt = str_replace( "&ouml;", "ö", $txt);
        $txt = str_replace( "&uuml;", "ü", $txt);
        $txt = str_replace( "&Auml;", "Ä", $txt);
        $txt = str_replace( "&Ouml;", "Ö", $txt);
        $txt = str_replace( "&Uuml;", "Ü", $txt);
        $txt = str_replace( "&szlig;", "ß", $txt);

        // replacing html specific codes

        // if this doesn't help, we should create own entity table
        // and replace codes to symbols
        $txt = html_entity_decode($txt);

        // cleaning up possible html code
        $txt = strip_tags($txt);

        parent::Text($x,$y,$txt);
    }

}
?>
