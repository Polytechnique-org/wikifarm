<?php

$ROSPatterns['/\\(:chat(\\s+(.*))?:\\)/e'] = "CreateChat('$2')";
$HandleActions['chat'] = 'HandleChat';

Markup('chatBegin','inline','/\\(:chatBegin (\\w+):\\)/','');
Markup('chatEnd','inline','/\\(:chatEnd (\\w+):\\)/','<br/>');
Markup('chatNext','inline','/\\(:chatNext:\\)/','<br/>');
Markup('chatTalk','inline','/\\(:chatTalk (\\w+) (\\w+):\\)/e',"ChatTalk('$1', '$2')");

function CreateChat($size = 10) {
  $idchat = substr(md5(rand()),0,4);
  if (!$size) { $size = 10; }
  return 
    '(:chatBegin '.$idchat.':)'."\n".
    '(:chatEnd '.$idchat.':)'."\n\n".
    '(:if auth edit:)(:chatTalk '.$idchat.' '.$size.':)(:if:)';
}

function ChatTalk($idchat, $size = 10) {
  return Keep(
  '<form method="post" id="chat-'.$idchat.'">
    <input type="text" name="postchat" size="50"/>
    <input type="hidden" name="chatId" value="'.$idchat.'"/>
    <input type="hidden" name="size" value="'.$size.'"/>
    <input type="hidden" name="action" value="chat"/>
  </form>
  <script type="text/javascript">
    document.getElementById("chat-'.$idchat.'").postchat.focus();
  </script>');
}

function HandleChat() {
  global $pagename, $CurrentTime, $Author;
  Lock(2);
  if (isset($_REQUEST['postchat']) && $_REQUEST['postchat'] &&
    ($page = RetrieveAuthPage($pagename, 'edit', true))) {
    $postchat = stripmagic($_REQUEST['postchat']);
    $idchat = stripmagic($_REQUEST['chatId']);
    $size = stripmagic($_REQUEST['size']);
    
    $origine = array('(:chatNext:)');
    $destination = array('');
    $chatsyntax = RetrieveAuthPage('Site.ChatSyntax', 'read', false);
    if ($chatsyntax) {
      $chatsyntax = explode("\n",$chatsyntax['text']);
      foreach($chatsyntax as $s) if (preg_match('/^(.*) => (.*)$/', trim($s), $matches)) {
        $origine[] = $matches[1];
        $destination[] = $matches[2];
      }
    }

    $newchat = $CurrentTime.' [[~'.$Author.']] : '.str_replace($origine, $destination, $postchat);

    $talkpos = strpos($page['text'], '(:chatTalk '.$idchat.' ');
    $beginpos = strpos($page['text'], '(:chatBegin '.$idchat.':)');
    $endpos = strpos($page['text'], '(:chatEnd '.$idchat.':)');
    
    $beginpos += strlen('(:chatBegin '.$idchat.':)');
    $lignes = explode('(:chatNext:)',substr($page['text'], $beginpos, $endpos - $beginpos));
    
    if ($talkpos > $beginpos) {
      $afac = count($lignes) - $size - 1;
      $lignes[] = $newchat;
      for ($i = 0; $i < $afac; $i++) {
        unset($lignes[$i]);
      }
    } else {
      $nouvelleslignes = array($newchat);
      for ($i = 0; $i < count($lignes) && $i < $size - 1; $i++) {
        $nouvelleslignes[] = $lignes[$i];
      }
      $lignes = $nouvelleslignes;
    }
    $chatcontent = implode('(:chatNext:)', $lignes);
    $page['text'] = substr($page['text'],0,$beginpos).$chatcontent.substr($page['text'],$endpos);
    WritePage($pagename,$page);
    Lock(0);
    Redirect($pagename);
  }
  Lock(0);
  return "";
}
function afac() {
  global $pagename, $Author, $CurrentTime;
  if (isset($_REQUEST['postchat']) && $_REQUEST['postchat']) {
    Lock(2);
    $page = RetrieveAuthPage($pagename, 'edit', false);
    if (!$page) {
      Lock(0);
      return "";
    }
    
    
    
    
    $poschat = strpos($page['text'],'(:beginchat:)');
    $finchat = strpos($page['text'],'(:chat');
    if ($poschat === false) {
      $poschat = $finchat;
      $page['text'] = substr($page['text'],0,$poschat).'(:beginchat:)'.substr($page['text'],$poschat);
      $finchat += strlen('(:beginchat:)');
    }
    $poschat += strlen('(:beginchat:)');
    
    $avantchat = substr($page['text'],0,$poschat);    
    $chatcontent = substr($page['text'], $poschat, $finchat - $poschat);
    $aprechat = substr($page['text'],$finchat);
    
    $lignes = explode("\n\n",$chatcontent);
    $page['text'] = $avantchat.implode("\n\n", $lignes)."\n\n".$CurrentTime.' [[~'.$Author.']] : '.$newchat.$aprechat;
    WritePage($pagename,$page);
    Lock(0);
    Redirect($pagename);
    return "";  
  }
  $page = RetrieveAuthPage($pagename, 'edit', false);
  if (!$page) {
    return '';
  }
  return '<form method="post"><input type="text" name="postchat" size="50"/></form><script type="text/javascript">document.getElementsByName("postchat")[0].focus();</script>';
}

?>
