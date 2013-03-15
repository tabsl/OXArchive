<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title><?php echo( $aLang['HEADER_META_MAIN_TITLE'] ) ?> - <?php echo( $title ) ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo( $aLang['charset'] ) ?>">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <style type="text/css">
    	
    	body, p , form {margin:0; }
    	body, p, td, tr, ol, ul, input, textarea {font:11px/130% Trebuchet MS, Tahoma, Verdana, Arial, Helvetica, sans-serif;}
    	
    	a {text-decoration: none;color: #000;}
    	a:hover {text-decoration: underline;}
    	
    	#page {width:900px;margin:5% auto;}
    	#header {clear:both;margin-top:10px;}
    	#body   {clear:both;padding:20px 10px;background: #e4e4e4 url(setup.png) 0 -80px repeat-x;border:1px solid #ccc;border-top:none;margin:-10px 1px 0 0;min-height: 350px;}
    	#footer {clear:both;background:#888;color:#fff;padding:5px 10px;margin-right:1px;}

    	<?php 
		    $sTabWidth = '177';
		    $sHColor = '#ff3600';
		?>
        
    	dl.tab {float:left;width: <?php echo $sTabWidth; ?>px;height:80px;margin:0;margin-right:1px;background:#ccc url(setup.png);border:1px solid #ccc;border-bottom:none;margin-bottom:-1px;}
    	dl.tab dt{display:block;padding:0;margin:0;padding:10px 5px 0 5px;font-weight: bold;}
    	dl.tab a{color:#888;}
    	dl.tab dd{display:block;padding:0;margin:0;padding:5px;height: 50px;}

    	dl.tab.act {border-color:<?php echo $sHColor; ?>;}
    	dl.tab.act dt a{color: <?php echo $sHColor; ?>;}
    	dl.tab.act dd{}
    	dl.tab.act dd a{color: #000;}
    	
    </style>
    
    <?php
    	$sImagDir = '../out/admin/img';
    	
        if ( isset( $iRedir2Step) && $iRedir2Step ){
            echo( '<meta http-equiv="refresh" content="3; URL=index.php?istep='.$iRedir2Step.'&sid='.getSID().'">');
        }
    ?>
</head>

<body>

<div id="page">
	<a href="index.php?istep=1&sid=<?php echo( getSID()); ?>"><img src="<?php echo $sImagDir; ?>/setup_logo.gif" alt="OXID eSales" hspace="5" vspace="5" border="0"></a>
	<div id="header">
		<?php for ($_tab = 1;$_tab <= 6;$_tab++): 
            
            $_sStepNr = $_tab;
            
                if ($_tab == 5) continue;
                if ($_tab == 6) $_sStepNr = 5;
        ?>
		<dl class="tab <?php if( $istep[0] == $_tab){ echo "act";} ?>">
			<dt><?php if( $istep[0] == $_tab): ?><a href="index.php?istep=<?php echo $_tab; ?>&sid=<?php echo getSID();?>"><?php endif;?><?php echo $_sStepNr,'. ',$aLang['TAB_'.$_tab.'_TITLE']; ?><?php if( $istep[0] == $_tab): ?></a><?php endif;?></dt>
			<dd><?php if( $istep[0] == $_tab): ?><a href="index.php?istep=<?php echo $_tab; ?>&sid=<?php echo getSID();?>"><?php endif;?><?php echo $aLang['TAB_'.$_tab.'_DESC'] ?><?php if( $istep[0] == $_tab): ?></a><?php endif;?></dd>
		</dl>
		<?php endfor; ?>
	</div>
	
	<div id="body">
	<?php 
	
	if ( isset( $sMessage) && $sMessage) {
		echo "<br><b>$sMessage</b>";
	}
	
	if ( isset( $iRedir2Step) && $iRedir2Step) {
    	echo( "<br><br>" . $aLang['HEADER_TEXT_SETUP_NOT_RUNS_AUTOMATICLY'] . " " );
     	echo( '<a href="index.php?istep='.$iRedir2Step.'&sid='.getSID().'" id="continue"><b>' . $aLang['HERE'] . '</b></a>.<br><br>');
	}
