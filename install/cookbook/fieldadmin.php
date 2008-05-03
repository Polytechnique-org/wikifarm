<?php

@include_once("$FarmD/cookbook/autocreate.php");

AutoCreatePage('Site.Admin', '(:fieldadmin:)');

Markup('fieldadmin','inline','/\\(:fieldadmin:\\)/e',"Keep(FieldAdmin())");

function WriteAdminConfig($f, $_wikititle, $_skin, $_pageLogoUrl, $_passwdread, $_passwdedit) {
		fwrite($f, '<'.'?php '."\n");
		fwrite($f, ' $WikiTitle = '.var_export($_wikititle,true).';'."\n");
		fwrite($f, ' $Skin = '.var_export($_skin,true).';'."\n");
		fwrite($f, ' $PageLogoUrl = '.var_export($_pageLogoUrl,true).';'."\n");
		fwrite($f, ' $DefaultPasswords[\'read\'] = '.var_export($_passwdread,true).';'."\n");
		fwrite($f, ' $DefaultPasswords[\'edit\'] = '.var_export($_passwdedit,true).';'."\n");
		fwrite($f, '?'.'>');
}

function FieldAdmin() {

  if (isset($_REQUEST['createconf']) && !($f = @fopen('local/webconfig.php','r'))) {
    $f = @fopen('local/webconfig.php','w');
  	global $WikiTitle, $FarmD, $Skin, $PageLogoUrl, $DefaultPasswords;
    WriteAdminConfig($f, $WikiTitle, $Skin, $PageLogoUrl, $DefaultPasswords['read'], $DefaultPasswords['edit']);
    fclose($f);
    mkdirp('uploads');
  }
  
	RetrieveAuthPage('Site.Admin', 'admin', true);

	if (isset($_REQUEST['admin'])) {
		$f = @fopen('local/webconfig.php','w');
		if (!$f) {
			$f = @fopen('local/webconfig.php','r');
			if ($f) {
				fclose($f);
				$error = 'Il faut que le fichier local/webconfig.php soit accessible en écriture par l\'utilisateur www-data. Il faut régler le problème puis actualiser cette page.';
			} else {
				$error = 'Il faut passer le dossier local/ en 2777 puis actualiser cette page et enfin repasser le dossier en 755.';
			}
			return 'Impossible d\'écrire la nouvelle configuration. '.$error;
		}
		WriteAdminConfig($f, 
      stripmagic($_REQUEST['wikititle']),
      stripmagic($_REQUEST['skin']),
      stripmagic($_REQUEST['logo']),
      stripmagic($_REQUEST['passwdread']),
      stripmagic($_REQUEST['passwdedit']));
		fclose($f);
		redirect('Site.Admin?modified=ok');
	}

	global $WikiTitle, $FarmD;
	$pagehtml = '';
	$pagehtml .= '<h1>Administration du wiki <strong>'.$WikiTitle.'</strong></h1>';
	if (isset($_REQUEST['modified'])) {
		$pagehtml .= '<span style="color:darkgreen;font-weight:bold">Configuration modifiée</span>';
	}
	$pagehtml .= '<form method="post" action="?"><ul>';

	// titre du wiki
	$pagehtml .= '<li>Nom du site : <input type="text" name="wikititle" value="'.htmlspecialchars($WikiTitle).'"/></li>';

	// url du wiki
	global $ScriptUrl;
	$pagehtml .= '<li>Adresse du site : <input type="text" size="40" disabled="disabled" value="'.htmlspecialchars($ScriptUrl).'"/></li>';

	// skin
	global $Skin;
	$dh = opendir("$FarmD/pub/skins/");
	$optionsSkins = '';
	while (($file = readdir($dh)) !== false) if ($file && $file{0} != '.' && (file_exists("$FarmD/pub/skins/$file/$file.tmpl") || file_exists("$FarmD/pub/skins/$file/skin-$file.tmpl"))) {
		$optionsSkins .= '<option value="'.$file.'">'.$file.'</option>';
	}
	$pagehtml .= '<li>Skin : <select name="skin">'.str_replace(' value="'.$Skin.'"',' value="'.$Skin.'" selected="selected"', $optionsSkins).'</select></li>';

	// url du logo
	global $PageLogoUrl;
	$pagehtml .= '<li>Image du logo : <input type="text" size="60" name="logo" value="'.htmlentities($PageLogoUrl).'"/></li>';

	// droits liés au groupe xnet
	global $XnetWikiGroup;
	if (isset($XnetWikiGroup)) {
		$pagehtml .= '<li>Authentification liée au groupe <a href="http://www.polytechnique.net/login/'.$XnetWikiGroup.'/">'.$XnetWikiGroup.'</a></li>';
	}

	// droits de lecture et de modification de tout le site
	global $DefaultPasswords;
	$XorgAuthUsers = XorgAuthUsers();
	$optionsUsers = '';
	foreach ($XorgAuthUsers as $v => $text) {
		$optionsUsers .= '<option value="'.$v.'">'.$text.'</option>';
	}
	$optionsUsers .='<option value="...">...</option>';
	$pagehtml .= '<li>Limiter les droits d\'accès au site : <br/>';
	$pagehtml .= ' en lecture <select name="passwdread" onchange="AddCustomAuth(this)">'.str_replace('value="'.$DefaultPasswords['read'].'"', 'value="'.$DefaultPasswords['read'].'" selected="selected"', $optionsUsers).'</select><br/>';
	$pagehtml .= ' en écriture <select name="passwdedit" onchange="AddCustomAuth(this)">'.str_replace('value="'.$DefaultPasswords['edit'].'"', 'value="'.$DefaultPasswords['edit'].'" selected="selected"', $optionsUsers).'</select><br/>';
	$pagehtml .= '</li>';

	$pagehtml .= '</ul><input type="submit" name="admin"/>';
	$pagehtml .= '</form>';
	return $pagehtml;
}

if (file_exists("$LocalDir/webconfig.php")) {
    include_once("$LocalDir/webconfig.php");
}

?>
