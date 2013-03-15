<style type="text/css">
#divDrag0 {
    position:absolute;
    left:0; top:0;
    text-align:right;
    visibility:hidden;
    clip:rect(0,200,200,0);
    height:80;
    width:200;
    layer-background-color:blue;
    background-color:blue;
    background:url('[{ $oViewConf->getImageUrl() }]/admin_start.jpg');
    -moz-opacity:0.7;
    filter: alpha(opacity=70);
}
#divDrag0 img {
    border:none
}
</style>
<script type="text/javascript" language="JavaScript">
<!--//
//
function hideMe()
{   oBanner.css.visibility = "hidden";
}
//
function dragBanner( oObj, nObj )
{    nObj = (!nObj) ? "" : "document." + nObj + ".";

       this.css  = banner.DOM ? document.getElementById( oObj).style : banner.IE4 ? document.all[oObj].style : banner.NE4 ? eval( nObj + "document.layers." + oObj) : 0;
    this.evnt = banner.DOM ? document.getElementById( oObj) : banner.IE4 ? document.all[oObj] : banner.NE4 ? eval( nObj + "document.layers." + oObj) : 0;
    this.x = ( banner.NE4 || banner.NE5) ? this.css.left : this.css.pixelLeft;
    this.y = ( banner.NE4 || banner.NE5) ? this.css.top  : this.css.pixelTop;
    this.moveBanner = moveMe;
    this.drag       = false;
    this.clickedX   = 0;
    this.clickedY   = 0;

    return this;
}
//
function getNavigator()
{    this.VERSION = navigator.appVersion;

    // checking DOM support
    this.DOM = 0;
    if ( document.getElementById ) this.DOM = 1;

    // checking browser version
    this.IE5 = 0;
    if ( this.VERSION.indexOf("MSIE 5") > -1 && this.DOM ) this.IE5 = 1;

    this.IE4 = 0;
    if ( document.all && !this.DOM ) this.IE4 = 1;

    this.NE5 = 0;
    if ( this.DOM && parseInt(this.VERSION ) >= 5) this.NE5 = 1;

    this.NE4 = 0;
    if ( document.layers && !this.DOM) this.NE4 = 1;

    // deside witch one
    this.banner = ( this.IE5 || this.IE4 || this.NE4 || this.NE5 );

    return this;
}
//
function mouseMovedOver( oObj )
{    oBannerTag = ( banner.NE5 || banner.NE4 ) ? oObj.target.name : window.event.srcElement.tagName;
    if ( oBannerTag == "DIV" || banner.NE4 || banner.NE5 )
    {   if ( oBanner)
            oBanner.isOver = true;
    }
}
//
function mouseMovedOut( oObj )
{    oBannerTag = ( banner.NE5 || banner.NE4 ) ? oObj.target.name : window.event.srcElement.tagName;
    if( oBannerTag == "DIV" || banner.NE4 || banner.NE5 )
    {   if( banner.NE5 )
            oBanner.isOver = false;
        else
            oBanner.isOver = false
    }
}
//
function moveMe( xPos, yPos )
{    this.x = xPos;
    this.y = yPos;
    this.css.left = this.x;
    this.css.top  = this.y;
}
//
function moveBannerUp()
{   if( oBanner.isOver )
        oBanner.drag = false;
}
//
function changePosition( oObj )
{    x = ( banner.NE4 || banner.NE5 ) ? oObj.pageX : event.x;
    y = ( banner.NE4 || banner.NE5 ) ? oObj.pageY : event.y;

    if( oBanner.drag )
        oBanner.moveBanner( x - oBanner.clickedX, y - oBanner.clickedY);

    return false;
}
//
function moveBannerDown( oObj )
{    x = ( banner.NE4 || banner.NE5 ) ? oObj.pageX : event.x;
    y = ( banner.NE4 || banner.NE5 ) ? oObj.pageY : event.y;
    if( oBanner.isOver )
    {   oBanner.drag = true;
        oBanner.clickedX   = x - oBanner.x;
        oBanner.clickedY   = y - oBanner.y;
        iLayerLevel++;
        oBanner.css.zIndex = iLayerLevel;
    }
}
//
function startBanner()
{    oBanner = new dragBanner("divDrag0");
    oBanner.evnt.onmouseover = mouseMovedOver;
    oBanner.evnt.onmouseout  = mouseMovedOut;

    if ( banner.NE4 )
        document.captureEvents( Event.MOUSEMOVE | Event.MOUSEDOWN | Event.MOUSEUP );

    document.onmousedown = moveBannerDown;
    document.onmouseup   = moveBannerUp;
    document.onmousemove = changePosition;

    iLeftPosition = ( document.body.clientWidth - 980 ) / 2;
    oBanner.moveBanner( iLeftPosition + 750,160 );
    oBanner.css.visibility = "visible";
}
//
iLayerLevel = 100;
banner = new getNavigator();
onload = startBanner;
//-->
</script>
<div id="divDrag0"><img onClick="hideMe()" src="[{ $oViewConf->getImageUrl() }]/transparent.gif" width="15" height="12" style="cursor:pointer;"><a rel="nofollow" href="[{ $oViewConf->getBaseDir() }]admin/"><img style="margin-top:32px;" src="[{ $oViewConf->getImageUrl() }]/transparent.gif"></a></div>