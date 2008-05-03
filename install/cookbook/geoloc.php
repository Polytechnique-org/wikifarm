<?php

Markup('dynamap','inline','/\\(:dynamap:\\)/e',"Keep(Dynamap())");

function Dynamap() {
	global $XnetWikiGroup;
	if ($XnetWikiGroup) {
		$mapfolder = 'http://www.polytechnique.net/'.$XnetWikiGroup.'/geoloc/';
	} else {
		$mapfolder = 'http://www.polytechnique.org/geoloc/';
	}
	$initfile = $mapfolder.'init/?only_current=on';
	$mapfile = $mapfolder.'dynamap.swf';
return '
<object
classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"
width="600"
height="450"
align="middle">
<param name="movie" value="'.$mapfile.'"/>
<param name="bgcolor" value="#ffffff"/>
<param name="wmode" value="opaque"/>
<param name="quality" value="high"/>
<param name="flashvars" value="initfile='.urlencode($initfile).'"/>
<embed
src="'.$mapfile.'"
quality="high"
bgcolor="#ffffff"
width="600"
height="450"
name="dynamap"
id="dynamap"
align="middle"
flashvars="initfile='.urlencode($initfile).'"
type="application/x-shockwave-flash"
menu="false"
wmode="opaque"
salign="tl"
pluginspage="http://www.macromedia.com/go/getflashplayer"/>
</object>';
}

?>
