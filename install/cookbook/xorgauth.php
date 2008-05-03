<?php

$AuthFunction = 'XorgAuth';
$HandleActions['attr'] = 'XorgAuthHandleAttr';
$HandleActions['postattr'] = 'XorgAuthHandlePostAttr';
$HandleActions['connect'] = 'XorgAuthConnectPlatal';

if (isset($_POST['action']) && isset($_GET['action'])) {
    $action = $_REQUEST['action'] = $_GET['action'] = $_POST['action'];
}

Markup('grpattributes','inline','/\\(:groupattributes:\\)/e',"Keep(XorgAuthGroupAttributes())");

require_once("$FarmD/cookbook/autocreate.php");
AutoCreatePage('$Group.GroupAttributes', '(:groupattributes:)');

$HTMLHeaderFmt['xorg'] = '<script type="text/javascript" src="http://www.polytechnique.org/javascript/xorg.js"></script>';
$HTMLHeaderFmt['xorgcustomauth'] = '<script type="text/javascript">
  function AddCustomAuth(f){
    if (f.value == \'...\')
    {
      var newval =prompt(\'Sépare les différents autorisations par des espaces\\n\\tx,membre ou admin\\n\\tprenom.nom.promo d\\\'une personne\\n\\tle numéro d\\\'une promo\\nPar exemple pour autoriser les membres et Pascal Corpet :\\n\\tmembre pascal.corpet.2001\');
      f.value = newval;
      if (f.value != newval && newval)
      {
        var op = document.createElement(\'option\');
        op.appendChild(document.createTextNode(newval));
        f.insertBefore(op,f.childNodes[f.childNodes.length-1]);
        f.value = newval;
      }
    }
  }
  </script>';

Markup('[[~|','<[[~','/\\[\\[~(.*?)\|(.*?)\\]\\]/e',"Keep('<a href=\"http://www.polytechnique.org/profile/$1\" class=\"popup2\">$2</a>')");

Markup('xorgpage','inline','/\\(:xorgpage\\s*(.*?):\\)/e', "Keep('<iframe style=\"width:100%;height:400px;border:none\" src=\"http://dev.m4x.org/~x2001corpet/$1\"></iframe>')");
Markup('xnetpage','inline','/\\(:xnetpage\\s*(.*?):\\)/e', "XnetPage('$1')");
function XnetPage($page) {
  global $XnetWikiGroup;
  if (!$XnetWikiGroup) return;
  return Keep('<iframe style="width:100%;height:400px;border:none" src="http://dev.polytechnique.net/~x2001corpet/'.($_SESSION['xorgauth']?'login/':'').$XnetWikiGroup.'/'.$page.'"></iframe>');
}

// Récupère les droits au niveau du dossier (Group PmWiki)
function XorgAuthGetGroupAuth($pagename,$since) {
	global $GroupPasswords;
 	if (!isset($GroupPasswords)) {
 		$GroupPasswords = array();
 	}
 	$group = substr($pagename, 0, strpos($pagename, '.'));
 	if (!isset($GroupPasswords[$group])) {
 		$GroupPasswords[$group] = ReadPage($group.'.GroupAttributes', $since);
	}
	return $GroupPasswords[$group];
}

// essaie de se connecter via xorg
function XorgAuthConnectPlatal() {
  $privkey = '6e9c9fa9bac23541fe67697c4eff5be6';
	global $XnetWikiGroup;
	$returl = 'http://'.$_SERVER['SERVER_NAME'].str_replace('action=connect', '', $_SERVER['REQUEST_URI']);
	if (isset($_REQUEST['oldaction'])) {
		$returl .= '&action='.$_REQUEST['oldaction'];
	}
	@session_destroy();
	session_start();
 	$challenge = md5(rand());
	$_SESSION['challenge'] = $challenge; 
	$_SESSION['authsite'] = $XnetWikiGroup;
 	$url = "https://www.polytechnique.org/auth-groupex.php";
 	$url .= "?session=".session_id();
 	$url .= "&challenge=".$challenge;
 	$url .= "&pass=".md5($challenge.$privkey);
 	$returl .= "&challenge=".$challenge;
 	$url .= "&url=".urlencode($returl);
 	if ($XnetWikiGroup) {
 		$url .= "&group=".$XnetWikiGroup;
	}
 	header('Location: '.$url);
	exit();
}


 // comes back from auth
 @session_start();
 if (isset($_GET['auth']) && !$_SESSION['xorgauth'] && $_SESSION['challenge']) {
	$tohash = '1'.$_SESSION['challenge'].'6e9c9fa9bac23541fe67697c4eff5be6';
	$fields = explode(',','forlife,nom,prenom,promo,grpauth');
	foreach ($fields as $f) if (isset($_GET[$f])) {
		$tohash .= $_GET[$f];
	}
	$tohash .= '1';
	if ($_GET['auth'] == md5($tohash)) {
		$_SESSION['xorgauth'] = 1;
		foreach ($fields as $f) if (isset($_GET[$f])) {
			$_SESSION[$f] = $_GET[$f];
		}
	} else {
		$_SESSION['xorgauth'] = 0;
	}
 }
 if (isset($_SESSION['forlife']) && $_SESSION['forlife']) {
  $AuthId = $_SESSION['forlife'];
  $Author = $_SESSION['forlife'].' | '.$_SESSION['prenom'].' '.$_SESSION['nom'];
 }
$Conditions['connected'] = 'isset($_SESSION["xorgauth"])';

function XorgAuthTestPassword($password) {
  if (!$password) {
    return true;
  }
  $parts = explode(' ',$password);
  foreach ($parts as $pass) {
  	if ($pass == 'all' || $pass == 'public') {
  	 return true;
    }
    if ($pass == 'x' && $_SESSION['xorgauth']) {
    	return true;
    }
    if ($_SESSION['grpauth'] && $pass == $_SESSION['grpauth']) {
    	return true;
    }
    if ($_SESSION['forlife'] && $pass == $_SESSION['forlife']) {
    	return true;
    }
    if ($_SESSION['promo'] && $pass == $_SESSION['promo']) {
    	return true;
    }
  }
  return false;
}
 
function XorgAuthIsSiteAdmin() {
  global $DefaultPasswords;
  return XorgAuthTestPassword($DefaultPasswords['admin']);
}

// fonction d'authentification : appellée avant tout accès à une page
function XorgAuth($pagename, $level, $authprompt, $since) {
 global $XnetWikiGroup;
  if (isset($_SESSION['authsite']) && $XnetWikiGroup != $_SESSION['authsite']) {
    XorgAuthConnectPlatal();
   	return false;
  }
 $group = substr($pagename, 0, strpos($pagename, '.'));
 $page = ReadPage($pagename, $since);
 if (!$page) { return false; }
 if (XorgAuthIsSiteAdmin()) { return $page; }
 global $AuthCascade, $DefaultPasswords, $GroupPasswords;
 $password = "";
 do
 {
 	if (isset($page["passwd".$level])) {
	 	$password = $page["passwd".$level];
 	}
 	if (!$password) {
 		$gpAuth = XorgAuthGetGroupAuth($pagename,$since);
 		if (isset($gpAuth["passwd".$level])) {
 			$password = $gpAuth["passwd".$level];
 		}
 	}
 	if (!$password) {
 		if (isset($DefaultPasswords[$level])) {
 			$password = $DefaultPasswords[$level];
 		}
 	}
 } while (!$password && isset($AuthCascade[$level]) && $level = $AuthCascade[$level]);
 if (XorgAuthTestPassword($password)) {
 	return $page;
 }
 if (!$authprompt) {
 	return false;
}
 global $AuthPromptFmt, $PageStartFmt, $PageEndFmt;
  $postvars = '';
  foreach($_POST as $k=>$v) {
    if ($k == 'authpw' || $k == 'authid') continue;
    $v = str_replace('$', '&#036;', 
             htmlspecialchars(stripmagic($v), ENT_COMPAT));
    $postvars .= "<input type='hidden' name='$k' value=\"$v\" />\n";
  }
  $FmtV['action'] = $_REQUEST['action'];
  SDV($AuthPromptFmt, array(&$PageStartFmt, "page:Site.AuthForm", &$PageEndFmt));
 PrintFmt($pagename,$AuthPromptFmt);
 exit;
}
$XorgAuthLevels = array('read' => 'lecture','edit' => 'modification','attr' => 'administration');

function XorgAuthUsers() {
	global $XnetWikiGroup;
	if ($XnetWikiGroup) {
		return array('public' => 'tout le monde','x' => 'les X', 'membre' => 'membres du groupe', 'admin' => 'admins du groupe');
	} else {
		return array('public' => 'tout le monde','x' => 'les X', 'admin' => 'admins X.org');
	}
}

function XorgAuthPermissions($pagename) {
	global $XnetWikiGroup,$DefaultPasswords,$XorgAuthLevels;
	$XorgAuthUsers = XorgAuthUsers();
	$group = substr($pagename, 0, strpos($pagename, '.'));
	if ($pagename != $group.'.GroupAttributes')
		$groupAttr = XorgAuthGetGroupAuth($pagename, 0); 
	$page = ReadPage($pagename, 0);
	$attrshtml = '';
	foreach ($XorgAuthLevels as $level => $action) {
		$html = $action.' : <select name="passwd'.$level.'" onchange="AddCustomAuth(this)">';
		if (isset($groupAttr['passwd'.$level]) && $groupAttr['passwd'.$level]) {
			$text = 'comme le dossier ('.$XorgAuthUsers[$groupAttr['passwd'.$level]].')';
		} else {
			$text = 'comme le site ('.$XorgAuthUsers[$DefaultPasswords[$level]].')';
		}
		$htmloptions = '<option value="">'.$text.'</option>';
		foreach ($XorgAuthUsers as $passwd => $user) {
			$htmloptions .= '<option value="'.$passwd.'">'.$user.'</option>';
		}
		$htmloptionsselected = str_replace(' value="'.$page['passwd'.$level].'"', ' value="'.$page['passwd'.$level].'" selected="selected"', $htmloptions);
		$html .= $htmloptionsselected;
		if ($htmloptionsselected == $htmloptions) {
		  $html .= '<option value="'.$page['passwd'.$level].'" selected="selected">'.$page['passwd'.$level].'</option>';
    }
    $html .= '<option value="...">...</option>';
		$html .= '</select> ';
		if ($attrshtml) {
			$attrshtml .= ' - ';
		}

		$attrshtml .= $html;
	}
	return '<form action="?action=postattr" method="post">'.$attrshtml.'<input type="submit" value="ok"/></form>';
}

function XorgAuthHandleAttr($pagename, $auth = 'attr') {
  $page = RetrieveAuthPage($pagename, $auth, true);
  global $PageAttrFmt, $PageStartFmt, $PageEndFmt;
  SDV($PageAttrFmt,"<div class='wikiattr'>
    <h2 class='wikiaction'>$[{\$FullName} Attributes]</h2>
    <p>".XorgAuthPermissions($pagename)."</p></div>");
  SDV($HandleAttrFmt,array(&$PageStartFmt,&$PageAttrFmt,&$PageEndFmt));
  PrintFmt($pagename,$HandleAttrFmt);
}

function XorgAuthHandlePostAttr($pagename, $auth = 'attr') {
  global $XorgAuthLevels, $HandleActions;
  Lock(2);
  $page = RetrieveAuthPage($pagename, $auth, true);
  if (!$page) { Abort("?unable to read $pagename"); }
  foreach($XorgAuthLevels as $attr=>$p) {
    $v = stripmagic(@$_REQUEST['passwd'.$attr]);
    if ($v=='') unset($page['passwd'.$attr]);
    else if ($v != '...') $page['passwd'.$attr] = $v;
  }
  WritePage($pagename,$page);
  Lock(0);
  Redirect($pagename);
}

function XorgAuthGroupAttributes() {
	global $XnetWikiGroup,$DefaultPasswords,$XorgAuthLevels;
	$XorgAuthUsers = XorgAuthUsers();
  global $pagename, $WikiDir;
  if (substr($pagename, strpos($pagename, '.') + 1) != 'GroupAttributes') {
    return "";
  }
  if (!XorgAuth($pagename, 'attr', true,0)) {
    return "";
  }
  if (isset($_REQUEST['page']) && isset($_REQUEST['user']) && isset($_REQUEST['attr'])) {
    Lock(2);
    $page = RetrieveAuthPage(stripmagic(@$_REQUEST['page']), 'attr', true);
    if ($page && isset($XorgAuthLevels[stripmagic(@$_REQUEST['attr'])]) && (isset($XorgAuthUsers[stripmagic(@$_REQUEST['user'])]) || !$_REQUEST['user'])) {
      $page['passwd'.stripmagic(@$_REQUEST['attr'])] = stripmagic(@$_REQUEST['user']);
      if ($_REQUEST['user'] == "") {
        unset($page['passwd'.stripmagic(@$_REQUEST['attr'])]);
      }
      WritePage(stripmagic(@$_REQUEST['page']),$page);
    }
    Lock(0);
  }
  $html = '<table>';
  $html .= '<tr><td></td>';
	foreach ($XorgAuthLevels as $level => $action) {
    $html .= '<th>'.$action.'</th>';
  }
  $html .= '</tr>';
  $group = substr($pagename, 0,  strpos($pagename, '.'));
  $pages = $WikiDir->ls($group.'.*');
	$groupAttr = XorgAuthGetGroupAuth($pagename, 0); 
  foreach($pages as $p) if ($p != $pagename) {
  	$html .= '<tr>';
  	$page = ReadPage($p, 0);
  	$html .= '<th>'.substr($p,strpos($p,'.')+1).'</th>';
  	foreach ($XorgAuthLevels as $level => $action) {
  		$html .= '<td><select name="passwd'.$level.'" onchange="AddCustomAuth(this);document.location=\'?page='.$p.'&attr='.$level.'&user=\'+this.value">';
  		if (isset($groupAttr['passwd'.$level]) && $groupAttr['passwd'.$level]) {
  		  $textedossier = $groupAttr['passwd'.$level];
  		  if (isset($XorgAuthUsers[$textedossier])) {
  		    $textedossier = $XorgAuthUsers[$textedossier];
        }
  			$text = 'comme le dossier ('.$textedossier.')';
  		} else {
  			$text = 'comme le site ('.$XorgAuthUsers[$DefaultPasswords[$level]].')';
  		}
  		$htmloptions = '<option value="">'.$text.'</option>';
  		foreach ($XorgAuthUsers as $passwd => $user) {
  			$htmloptions .= '<option value="'.$passwd.'">'.$user.'</option>';
  		}
  		$htmloptionsselected = str_replace(' value="'.$page['passwd'.$level].'"', ' value="'.$page['passwd'.$level].'" selected="selected"', $htmloptions);
  		$html .= $htmloptionsselected;
  		if ($htmloptionsselected == $htmloptions) {
  		  $html .= '<option value="'.$page['passwd'.$level].'" selected="selected">'.$page['passwd'.$level].'</option>';
      }
  		$html .= '<option value="...">...</option></select></td>';
  	}
  	$html .= '</tr>';
  }
  $html .= '</table>';
  return '<h2>Edition des droits du dossier</h2>'.XorgAuthPermissions($pagename).'<h2>Edition des droits des pages du dossier</h2>'.$html;
}
?>
