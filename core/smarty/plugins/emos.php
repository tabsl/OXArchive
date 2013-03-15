<?php
/*******************************************************************************
 * EMOS PHP Bib 2
 *
 * Copyright (c) 2004 - 2007 ECONDA GmbH Karlsruhe
 * All rights reserved.
 *
 * ECONDA GmbH
 * Haid-und-Neu-Str. 7
 * 76131 Karlsruhe
 * Tel. +49 (721) 6630350
 * Fax +49 (721) 66303510
 * info@econda.de
 * www.econda.de
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * Neither the name of the ECONDA GmbH nor the names of its contributors may
 * be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * $Id: emos.php 17677 2009-03-30 15:19:39Z vilma $
 */

/**
 * PHP Helper Class to construct a ECONDA Monitor statement for the later
 * inclusion in a HTML/PHP Page.
 */
class EMOS{

    /**
     * the EMOS statement consists of 3 parts
     * 1.   the inScript :<code><script type="text/javascript" src="emos2.js"></script>
     * 2,3. a part before and after this inScript (preScript/postScript)</code>
     *
     * @var string
     */
    public $preScript = "";

    /**
     * Here we store the call to the js bib
     *
     * @var string
     */
    public $inScript = "";

    /**
     * if we must put something behind the call to the js bin we put it here
     *
     * @var string
     */
    public $postScript = "";

    /**
     * path to the empos2.js script-file
     *
     * @var string
     */
    public $pathToFile = "";

    /**
     * Name of the script-file
     *
     * @var string
     */
    public $scriptFileName = "emos2.js";

    /**
     * if we use pretty print, we will set the lineseparator
     *
     * @var string
     */
    public $br = "\n";

    /**
     * if we use pretty print, we will set the tab here
     *
     * @var string
     */
    public $tab = "\t";

    /**
     * session id for 1st party sessions
     *
     * @var string
     */
    public $emsid = "";

    /**
     * visitor id for 1st partyx visitors
     *
     * @var string
     */
    public $emvid = "";

    /**
     * add compatibility function for php < 5.1
     *
     * @return string
     */
    public function htmlspecialchars_decode_php4( $str )
    {
        return strtr( $str, array_flip( get_html_translation_table( HTML_SPECIALCHARS ) ) );
    }

    /**
     * Constructor
     * Sets the path to the emos2.js js-bib and prepares the later calls
     *
     * @param $pathToFile The path to the js-bib (/opt/myjs)
     * @param $scriptFileName If we want to have annother Filename than emos2.js you can set it here
     *
     * @return null
     */
    public function __construct( $pathToFile = "", $scriptFileName = "emos2.js" )
    {
        $this->pathToFile = $pathToFile;
        $this->scriptFileName = $scriptFileName;
        $this->prepareInScript();

    }

    /**
     * formats data/values/params by eliminating named entities and xml-entities
     *
     * @param EMOS_Item $item item to format its parameters
     *
     * @return null
     */
    public function emos_ItemFormat( $item )
    {
        $item->productID = $this->emos_DataFormat( $item->productID );
        $item->productName = $this->emos_DataFormat( $item->productName );
        $item->productGroup = $this->emos_DataFormat( $item->productGroup );
        $item->variant1 = $this->emos_DataFormat( $item->variant1 );
        $item->variant2 = $this->emos_DataFormat( $item->variant2 );
        $item->variant3 = $this->emos_DataFormat( $item->variant3 );
        return $item;
    }

    /**
     * formats data/values/params by eliminating named entities and xml-entities
     *
     * @param string $str data input to format
     *
     * @return null
     */
    public function emos_DataFormat( $str )
    {
        $str = urldecode($str);
        //2007-05-10 Fix incompatibility with php4
        if ( function_exists('htmlspecialchars_decode' ) ) {
            $str = htmlspecialchars_decode( $str, ENT_QUOTES );
        } else {
            $str = $this->htmlspecialchars_decode_php4( $str );
        }
        $str = getStr()->html_entity_decode( $str );
        $str = strip_tags( $str );
        $str = trim( $str );

        //2007-05-10 replace translated &nbsp; with spaces
        $nbsp = chr(0xa0);
        $str = str_replace( $nbsp, " ", $str );
        $str = str_replace( "\"", "", $str );
        $str = str_replace( "'", "", $str );
        $str = str_replace( "%", "", $str );
        $str = str_replace( ",", "", $str );
        $str = str_replace( ";", "", $str );
        /* remove unnecessary white spaces*/
        while ( true ) {
            $str_temp = $str;
            $str = str_replace( "  ", " ", $str );

            if ( $str == $str_temp ) {
                break;
            }
        }
        $str = str_replace( " / ", "/", $str );
        $str = str_replace( " /", "/", $str );
        $str = str_replace( "/ ", "/", $str );

        $str = getStr()->substr( $str, 0, 254 );
        $str = rawurlencode( $str );
        return $str;
    }

    /**
     * sets the 1st party session id
     *
     * @param string $sid session id to set as parameter
     *
     * @return null
     */
    public function setSid( $sid = "" )
    {
        if ( $sid ) {
            $this->emsid = $sid;
            $this->appendPreScript( "<a name=\"emos_sid\" title=\"$sid\"></a>\n" );
        }
    }

    /**
     * set 1st party visitor id
     *
     * @param string $vid
     *
     * @return null
     */
    public function setVid( $vid = "" )
    {
        if ( $vid ) {
            $this->emvid = $vid;
            $this->appendPreScript( "<a name=\"emos_vid\" title=\"$vid\"></a>" );
        }
    }

    /**
     * switch on pretty printing of generated code. If not called, the output
     * will be in one line of html.
     *
     * @return null
     */
    public function prettyPrint()
    {
        $this->br .= "\n";
        $this->tab .= "\t";
    }

    /**
     * Concatenates the current command and the $inScript
     *
     * @param string $stringToAppend string to append
     *
     * @return null
     */
    public function appendInScript( $stringToAppend )
    {
        $this->inScript.= $stringToAppend;
    }

    /**
     * Concatenates the current command and the $proScript
     *
     * @return null
     */
    public function appendPreScript( $stringToAppend )
    {
        $this->preScript.= $stringToAppend;
    }

    /**
     * Concatenates the current command and the $postScript
     *
     * @return null
     */
    public function appendPostScript( $stringToAppend )
    {
        $this->postScript.= $stringToAppend;
    }

    /**
     * sets up the inScript Part with Initialisation Params
     *
     * @return null
     */
    public function prepareInScript()
    {
        $this->inScript .= "<script type=\"text/javascript\" " .
        "src=\"" . $this->pathToFile . $this->scriptFileName . "\">" .
        "</script>" . $this->br;
    }

    /**
     * returns the whole statement
     *
     * @return string
     */
    public function toString()
    {
        return $this->preScript.$this->inScript.$this->postScript;
    }

    /**
     * constructs a emos anchor tag
     *
     * @param string $title
     * @param string $rel
     * @param string $rev
     *
     * @return string
     */
    public function getAnchorTag( $title = "", $rel = "", $rev = "" )
    {
        $rel = $this->emos_DataFormat( $rel );
        $rev = $this->emos_DataFormat( $rev );
        $anchor = "<a name=\"emos_name\" " .
        "title=\"$title\" " .
        "rel=\"$rel\" " .
        "rev=\"$rev\"></a>$this->br";
        return $anchor;
    }

    /**
     * adds a anchor tag for content tracking
     * <a name="emos_name" title="content" rel="$content" rev=""></a>
     *
     * @param string $content content to add
     *
     * @return null
     */
    public function addContent( $content )
    {
        $this->appendPreScript( $this->getAnchorTag( "content", $content ) );
    }

    /**
     * adds a anchor tag for orderprocess tracking
     * <a name="emos_name" title="orderProcess" rel="$processStep" rev=""></a>
     *
     * @param string $processStep process step to add
     *
     * @return null
     */
    public function addOrderProcess( $processStep )
    {
        $this->appendPreScript( $this->getAnchorTag( "orderProcess", $processStep ) );
    }

    /**
     * adds a anchor tag for siteid tracking
     * <a name="emos_name" title="siteid" rel="$siteid" rev=""></a>
     *
     * @param string $siteid site id to add
     *
     * @return null
     */
    public function addSiteID( $siteid )
    {
        $this->appendPreScript( $this->getAnchorTag( "siteid", $siteid ) );
    }

    /**
     * adds a anchor tag for language tracking
     * <a name="emos_name" title="langid" rel="$langid" rev=""></a>
     *
     * @param string $langid language id to add
     *
     * @return null
     */
    public function addLangID( $langid )
    {
        $this->appendPreScript( $this->getAnchorTag( "langid", $langid ) );
    }

    /**
     * adds a anchor tag for country tracking
     * <a name="emos_name" title="countryid" rel="$countryid" rev=""></a>
     *
     * @param string $countryid country id to add
     *
     * @return null
     */
    public function addCountryID( $countryid )
    {
        $this->appendPreScript( $this->getAnchorTag( "countryid", $countryid ) );
    }

    /**
     * adds a Page ID to the current window (window.emosPageId)
     *
     * @param string $pageID page id to add
     *
     * @return null
     */
    public function addPageID( $pageID )
    {
        $this->appendPreScript( "\n<script type=\"text/javascript\">\n window.emosPageId = '$pageID';\n</script>\n" );
    }
    /**
     * adds a anchor tag for search tracking
     * <a name="emos_name" title="search" rel="$queryString" rev="$numberOfHits"></a>
     *
     * @param string $queryString  query string
     * @param int    $numberOfHits number of hits
     *
     * @return null
     */
    public function addSearch( $queryString, $numberOfHits )
    {
        $this->appendPreScript( $this->getAnchorTag( "search", $queryString, $numberOfHits ) );
    }

    /**
     * adds a anchor tag for registration tracking
     * The userid gets a md5() to fullfilll german datenschutzgesetz
     * <a name="emos_name" title="register" rel="$userID" rev="$result"></a>
     *
     * @param string $userID user id
     * @param string $result registration result
     *
     * @return null
     */
    public function addRegister( $userID, $result )
    {
        $this->appendPreScript($this->getAnchorTag( "register", md5( $userID ), $result ) );
    }


    /**
     * adds a anchor tag for login tracking
     * The userid gets a md5() to fullfilll german datenschutzgesetz
     * <a name="emos_name" title="login" rel="$userID" rev="$result"></a>
     *
     * @param string $userID user id
     * @param string $result login result
     *
     * @return null
     */
    public function addLogin( $userID, $result )
    {
        $this->appendPreScript( $this->getAnchorTag( "login", md5( $userID ), $result ) );
    }

    /**
     * adds a anchor tag for contact tracking
     * <a name="emos_name" title="scontact" rel="$contactType" rev=""></a>
     *
     * @param string $contactType contant type
     *
     * @return null
     */
    public function addContact( $contactType )
    {
        $this->appendPreScript( $this->getAnchorTag( "scontact", $contactType ) );
    }

    /**
     * adds a anchor tag for download tracking
     * <a name="emos_name" title="download" rel="$downloadLabel" rev=""></a>
     *
     * @param string $downloadLabel download label
     *
     * @return null
     */
    public function addDownload( $downloadLabel )
    {
        $this->appendPreScript( $this->getAnchorTag( "download", $downloadLabel ) );
    }

    /**
     * constructs a emosECPageArray of given $event type
     *
     * @param EMOS_Item $item  a instance of class EMOS_Item
     * @param string    $event Type of this event ("add","c_rmv","c_add")
     *
     * @return string
     */
    public function getEmosECPageArray( $item, $event )
    {
        $item = $this->emos_ItemFormat( $item );
        $out = "<script type=\"text/javascript\">$this->br" .
        "<!--$this->br" .
        "$this->tab var emosECPageArray = new Array();$this->br" .
        "$this->tab emosECPageArray['event'] = '$event';$this->br" .
        "$this->tab emosECPageArray['id'] = '$item->productID';$this->br" .
        "$this->tab emosECPageArray['name'] = '$item->productName';$this->br" .
        "$this->tab emosECPageArray['preis'] = '$item->price';$this->br" .
        "$this->tab emosECPageArray['group'] = '$item->productGroup';$this->br" .
        "$this->tab emosECPageArray['anzahl'] = '$item->quantity';$this->br" .
        "$this->tab emosECPageArray['var1'] = '$item->variant1';$this->br" .
        "$this->tab emosECPageArray['var2'] = '$item->variant2';$this->br" .
        "$this->tab emosECPageArray['var3'] = '$item->variant3';$this->br" .
        "// -->$this->br" .
        "</script>$this->br";
        return $out;
    }

    /**
     * constructs a emosBillingPageArray of given $event type
     *
     * @param string $billingID      billing id
     * @param string $customerNumber customer number
     * @param int    $total          total number
     * @param string $country        customer country title
     * @param string $cip            customer ip
     * @param string $city           customer city title
     *
     * @return null
     */
    public function addEmosBillingPageArray( $billingID = "", $customerNumber = "", $total = 0, $country = "", $cip = "", $city = "" )
    {
        $out = $this->getEmosBillingArray( $billingID, $customerNumber, $total, $country, $cip, $city, "emosBillingPageArray" );
        $this->appendPreScript( $out );
    }

    /**
     * gets a emosBillingArray for a given ArrayName
     *
     * @param string $billingID      billing id
     * @param string $customerNumber customer number
     * @param int    $total          total number
     * @param string $country        customer country title
     * @param string $cip            customer ip
     * @param string $city           customer city title
     * @param string $arrayName      name of JS array
     *
     * @return string
     */
    public function getEmosBillingArray( $billingID = "", $customerNumber = "", $total = 0, $country = "", $cip = "", $city = "", $arrayName = "" )
    {
        /******************* prepare data *************************************/
        /* md5 the customer id to fullfill requirements of german datenschutzgeesetz */
        $customerNumber = md5( $customerNumber );

        $country = $this->emos_DataFormat( $country );
        $cip = $this->emos_DataFormat( $cip) ;
        $city = $this->emos_DataFormat( $city );

        /* get a / separated location stzring for later drilldown */
        $ort = "";
        if ( $country ) {
            $ort .= "$country/";
        }

        if ( $cip ) {
            $ort .= substr( $cip, 0, 1 )."/".substr( $cip, 0, 2 )."/";
        }

        if ( $city ) {
            $ort .= "$city/";
        }

        if ( $cip ) {
            $ort.=$cip;
        }

        /******************* get output** *************************************/
        /* get the real output of this funktion */
        $out = "";
        $out .= "<script type=\"text/javascript\">$this->br" .
        "<!--$this->br" .
        "$this->tab var $arrayName = new Array();$this->br" .
        "$this->tab $arrayName" . "['0'] = '$billingID';$this->br" .
        "$this->tab $arrayName" . "['1'] = '$customerNumber';$this->br" .
        "$this->tab $arrayName" . "['2'] = '$ort';$this->br" .
        "$this->tab $arrayName" . "['3'] = '$total';$this->br" .
        "// -->$this->br" .
        "</script>$this->br";
        return $out;
    }

    /**
     * adds a emosBasket Page Array to the preScript
     *
     * @param array $basket basket items
     *
     * @return null
     */
    public function addEmosBasketPageArray( $basket )
    {
        $out = $this->getEmosBasketPageArray( $basket, "emosBasketPageArray" );
        $this->appendPreScript( $out );
    }

    /**
     * returns a emosBasketArray of given Name
     *
     * @param array  $basket    basket items
     * @param atring $arrayName name of JS array
     *
     * @return string
     */
    public function getEmosBasketPageArray( $basket, $arrayName )
    {
        $out = "<script type=\"text/javascript\">$this->br" .
        "<!--$this->br" .
        "var $arrayName = new Array();$this->br";
        $count = 0;
        foreach( $basket as $item ) {
            $item = $this->emos_ItemFormat( $item );
            $out .= $this->br;
            $out .= "$this->tab $arrayName"."[$count]=new Array();$this->br";
            $out .= "$this->tab $arrayName"."[$count][0]='$item->productID';$this->br";
            $out .= "$this->tab $arrayName"."[$count][1]='$item->productName';$this->br";
            $out .= "$this->tab $arrayName"."[$count][2]='$item->price';$this->br";
            $out .= "$this->tab $arrayName"."[$count][3]='$item->productGroup';$this->br";
            $out .= "$this->tab $arrayName"."[$count][4]='$item->quantity';$this->br";
            $out .= "$this->tab $arrayName"."[$count][5]='$item->variant1';$this->br";
            $out .= "$this->tab $arrayName"."[$count][6]='$item->variant2';$this->br";
            $out .= "$this->tab $arrayName"."[$count][7]='$item->variant3';$this->br";
            $count++;
        }
        $out .= "// -->$this->br" .
        "</script>$this->br";

        return $out;
    }

    /**
     * adds a detailView to the preScript
     *
     * @param EMOS_Item $item item to add to view
     *
     * @return null
     */
    public function addDetailView( $item )
    {
        $this->appendPreScript( $this->getEmosECPageArray( $item, "view" ) );
    }

    /**
     * adds a removeFromBasket to the preScript
     *
     * @param EMOS_Item $item item to remove from basket
     *
     * @return null
     */
    public function removeFromBasket( $item )
    {
        $this->appendPreScript( $this->getEmosECPageArray( $item, "c_rmv" ) );
    }

    /**
     * adds a addToBasket to the preScript
     *
     * @param EMOS_Item $item item to add to basket
     *
     * @return null
     */
    public function addToBasket( $item )
    {
        $this->appendPreScript( $this->getEmosECPageArray( $item, "c_add" ) );
    }

    /**
     * constructs a generic EmosCustomPageArray from a PHP Array
     *
     * @param array $aListOfValues list of custom values to assign to emos tracker
     *
     * @return string
     */
    public function getEmosCustomPageArray( $aListOfValues)
    {
        $out = "<script type=\"text/javascript\">$this->br" .
        "<!--$this->br" .
        "$this->tab var emosCustomPageArray = new Array();$this->br";

        $iCounter = 0;
        foreach ( $aListOfValues as $sValue ) {
            $sValue = $this->emos_DataFormat( $sValue );
            $out .= "$this->tab emosCustomPageArray[$iCounter] = '$sValue';$this->br";
            $iCounter ++;
        }

        return $out . "// -->$this->br" ."</script>$this->br";
    }

    /**
     * constructs a emosCustomPageArray with 8 Variables and shortcut
     *
     * @param string $cType Type of this event - shortcut in config
     * @param string  $cVar1 first variable of this custom event (optional)
     * @param string  $cVar2 second variable of this custom event (optional)
     * @param string  $cVar3 third variable of this custom event (optional)
     * @param string  $cVar4 fourth variable of this custom event (optional)
     * @param string  $cVar5 fifth variable of this custom event (optional)
     * @param string  $cVar6 sixth variable of this custom event (optional)
     * @param string  $cVar7 seventh variable of this custom event (optional)
     * @param string  $cVar8 eighth variable of this custom event (optional)
     * @param string  $cVar9 nineth variable of this custom event (optional)
     * @param string  $cVar10 tenth variable of this custom event (optional)
     * @param string  $cVar11 eleventh variable of this custom event (optional)
     * @param string  $cVar12 twelveth variable of this custom event (optional)
     * @param string  $cVar13 thirteenth variable of this custom event (optional)
     *
     * @return null
     */
    public function addEmosCustomPageArray( $cType = 0, $cVar1 = 0, $cVar2 = 0, $cVar3 = 0, $cVar4 = 0,
                                            $cVar5 = 0, $cVar6 = 0, $cVar7 = 0, $cVar8 = 0, $cVar9 = 0,
                                            $cVar10 = 0, $cVar11 = 0, $cVar12 = 0, $cVar13 = 0 )
    {
        $aValues[0] = $cType;
        if ( $cVar1 ) {
            $aValues[1] = $cVar1;
        }

        if ( $cVar2 ) {
            $aValues[2] = $cVar2;
        }

        if ( $cVar3 ) {
            $aValues[3] = $cVar3;
        }

        if ( $cVar4 ) {
            $aValues[4] = $cVar4;
        }

        if ( $cVar5 ) {
            $aValues[5] = $cVar5;
        }

        if ( $cVar6 ) {
            $aValues[6] = $cVar6;
        }

        if ( $cVar7 ) {
            $aValues[7] = $cVar7;
        }

        if ( $cVar8 ) {
            $aValues[8] = $cVar8;
        }

        if ( $cVar9 ) {
            $aValues[9] = $cVar9;
        }

        if ( $cVar10 ) {
            $aValues[10] = $cVar10;
        }

        if ( $cVar11 ) {
            $aValues[11] = $cVar11;
        }

        if ( $cVar12 ) {
            $aValues[12] = $cVar12;
        }

        if ( $cVar13 ) {
            $aValues[13] = $cVar13;
        }

        $this->appendPreScript( $this->getEmosCustomPageArray( $aValues ) );
    }

    /**
     *
     * @param EMOS_Item $item
     * @param string    $event
     *
     * @return string
     */
    public function getEmosECEvent( $item, $event )
    {
        $item = $this->emos_ItemFormat( $item );
        $out = "emos_ecEvent('$event'," .
        "'$item->productID'," .
        "'$item->productName'," .
        "'$item->price'," .
        "'$item->productGroup'," .
        "'$item->quantity'," .
        "'$item->variant1'" .
        "'$item->variant2'" .
        "'$item->variant3');";
        return $out;
    }

    /**
     * @param EMOS_Item $item viewable item
     *
     * @return string
     */
    public function getEmosViewEvent( $item )
    {
        return $this->getEmosECEvent( $item, "view" );
    }

    /**
     * @param EMOS_Item $item basket item added to basket
     *
     * @return string
     */
    public function getEmosAddToBasketEvent( $item )
    {
        return $this->getEmosECEvent( $item, "c_add" );
    }

    /**
     * @param EMOS_Item $item basket item to bark as removed
     *
     * @return string
     */
    public function getRemoveFromBasketEvent( $item )
    {
        return $this->getEmosECEvent( $item, "c_rmv" );
    }

    /**
     * @param string $billingID      billing id
     * @param string $customerNumber customer number
     * @param int    $total          total number
     * @param string $country        customer country title
     * @param string $cip            customer ip
     * @param string $city           customer city title
     *
     * @return string
     */
    public function getEmosBillingEventArray( $billingID = "", $customerNumber = "", $total = 0, $country = "", $cip = "", $city = "" )
    {
        return $this->getEmosBillingArray( $billingID, $customerNumber, $total, $country, $cip, $city, "emosBillingArray" );
    }

    /**
     *
     * @param array $basket basket items
     *
     * @return string
     */
    public function getEMOSBasketEventArray( $basket )
    {
        return $this->getEmosBasketArray( $basket, "emosBasketArray" );
    }
}


/**
 * A Class to hold products as well a basket items
 * If you want to track a product view, set the quantity to 1.
 * For "real" basket items, the quantity should be given in your
 * shopping systems basket/shopping cart.
 *
 * Purpose of this class:
 * This class provides a common subset of features for most shopping systems
 * products or basket/cart items. So all you have to do is to convert your
 * products/articles/basket items/cart items to a EMOS_Items. And finally use
 * the functionaltiy of the EMOS class.
 * So for each shopping system we only have to do the conversion of the cart/basket
 * and items and we can (hopefully) keep the rest of code.
 *
 * Shopping carts:
 *	A shopping cart / basket is a simple Array[] of EMOS items.
 *	Convert your cart to a Array of EMOS_Items and your job is nearly done.
 */
class EMOS_Item
{
    /**
     * unique Identifier of a product e.g. article number
     *
     * @var string
     */
    public $productID = "NULL";

    /**
     * the name of a product
     *
     * @var string
     */
    public $productName = "NULL";

    /**
     * the price of the product, it is your choice wether its gross or net
     *
     * @var string
     */
    public $price = "NULL";

    /**
     * the product group for this product, this is a drill down dimension
     * or tree-like structure
     * so you might want to use it like this:
     * productgroup/subgroup/subgroup/product
     *
     * @var string
     */
    public $productGroup = "NULL";

    /**
     * the quantity / number of products viewed/bought etc..
     *
     * @var string
     */
    public $quantity = "NULL";

    /**
     * variant of the product e.g. size, color, brand ....
     * remember to keep the order of theses variants allways the same
     * decide which variant is which feature and stick to it
     *
     * @var string
     */
    public $variant1 = "NULL";
    public $variant2 = "NULL";
    public $variant3 = "NULL";
}
