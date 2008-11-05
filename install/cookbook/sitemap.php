<?php if (!defined('PmWiki')) exit();
/*
               $Id: sitemap.php,v 1.7 2005/12/29 10:26:50 pts00065 Exp $
This file is NOT part of PmWiki; still you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.


This script generated a google sitemap, either automaticly  or as action=sitemap or both.

The automtic version creates a .gz file and is more efficient compared to the action version.

google requires that the generated file is located in the root of your pmwiki installation, typically the same place as your pmwiki.php or equivalent file. Thus:

http://www.brambring.nl/pmwiki.php -> sitemap must be in: http://www.brambring.nl/sitemap.xml.gz
    
place the file in the cookbook or local directory 
and include the file from your farmconfig.php or config.php
include_once("$FarmD/cookbook/sitemap.php");


Then add the URL to your sitemap in the google webform or ping google

automatic google ping is not implemented yet.

regards

bram

http://www.brambring.nl

     $Log: sitemap.php,v $
     Revision 1.7  2005/12/29 10:26:50  pts00065
     * support EnablePageListProtect
     * Added Site to exclude pattern



*/
SDV($LastModFile, "$WorkDir/.lastmod"); # same as in caching
SDV($SitemapFile, "sitemap.xml.gz"); #will need write access must be in root dir. Ensure dir is writable or create (symbolic) link

SDV($HandleActions['sitemap'], 'HandleSitemap'); # it is not usefull to have both an action 
// and automatic creation ( SitemapDelay >= 0
SDV($SitemapDelay, 3600); # Seconds to wait after last edit set to -1 to disable automatic generation
SDV($SitemapSquelch, 12*3600); # Squelch between generations of sitemap

$RobotActions['sitemap'] = 1;

SDVA($SitemapSearchPatterns, array());
$SitemapSearchPatterns[] = '!\.(All)?Recent(Changes|Uploads|Pages)$!';
$SitemapSearchPatterns[] = '!\.Group(Print)?(Header|Footer|Attributes)$!';
$SitemapSearchPatterns[] = '!^PmWiki\.!';
$SitemapSearchPatterns[] = '!^Site\.!';
$SitemapSearchPatterns[] = '!\.SideBar!';

SDV($SitemapMaxItems, 50000); # maximum items to display defined by google
SDV($SitemapMaxSize, 10); # maximum size is 10 Mbytes TODO
SDV($SitemapPing, "http://www.google.com/"); # Use ping with long SitemapDelay (like 24*60*60 ) TODO

// SDV($SitemapTimeFmt,'%Y-%m-%dT%H:%M:%sZ'); # seems to break in current version of google
SDV($SitemapTimeFmt, '%Y-%m-%d');

SDV($SiteMapItems, array());
SDV($SitemapChannelFmt, '<?xml version="1.0" encoding="UTF-8"?>
  <urlset xmlns="http://www.google.com/schemas/sitemap/0.84">
      ');
SDV($SitemapItemFmt, '
        <url>
          <loc>$PageUrl</loc>
          <lastmod>$SitemapItemPubDate</lastmod>
          <changefreq>$SitemapChangeFreq</changefreq>
          <priority>$SitemapPriority</priority>
        </url>');
SDV($HandleSitemapFmt, array(&$SitemapChannelFmt, &$SitemapItems, '</urlset>'));

if ( $action == 'browse'  ) {
    if ($SitemapDelay >= 0) {
        $l = @filemtime($LastModFile);
        $s = @filemtime($SitemapFile);
        if ((($Now - $l) > $SitemapDelay) && ($l > $s) && (($Now - $s) > $SitemapSquelch)) {
            $fp = @fopen($SitemapFile, "w");
            if ($fp) {
                ob_start();
                MakeSitemap();
                $x = gzencode (ob_get_clean(), 9);
                fwrite($fp, $x);
                fclose($fp);
            } 
        } 
    } 
}



function HandleSitemap()
{
    header("Content-type: text/xml");
    MakeSitemap();
    exit;
} 

function MakeSitemap()
{
    global $SitemapMaxItems, $SitemapChannelFmt, $SitemapTimeFmt,
    $SitemapItems, $SitemapItemFmt, $SearchPatterns,$FarmD,
    $EnablePageListProtect,
    $HandleSitemapFmt, $FmtV, $SitemapSearchPatterns, $Now;
    global $EntitiesTable;
    if (IsEnabled($EnablePageListProtect, 1)) $readf = 1000;

    $t = array();
    $t = @ListPages($SitemapSearchPatterns);
    $daily_weekly = 60 * 60 * 24 * 6; #TODO
    foreach ($t as $i => $pn) {
    $page= ($readf >= 1000)
              ? RetrieveAuthPage($pn, 'read', false, READPAGE_CURRENT)
              : ReadPage($pn, READPAGE_CURRENT);
    if (!$page) continue;

        // foreach ( $page as $k => $l ) { print "$k == $l <br />\n"; }
        if ( (count($SitemapItems) > $SitemapMaxItems)) continue;
        $FmtV['$SitemapChangeFreq'] = ($Now - $page['time'] < $daily_weekly)?'daily':'weekly'; #TODO
        $FmtV['$SitemapPriority'] = '0.5'; #TODO
        $FmtV['$SitemapItemPubDate'] = gmstrftime($SitemapTimeFmt, $page['time']);
        $SitemapItems[] = FmtPageName($SitemapItemFmt, $page['name']);
    } 

    #PrintFmt('', str_replace(array_keys($EntitiesTable), array_values($EntitiesTable), $HandleSitemapFmt));
    PrintFmt('',  $HandleSitemapFmt);
} 


