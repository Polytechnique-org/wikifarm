<?php

$ROSPatterns['/\\(:faq:\\)/e'] = 'CreateFAQ()';
$HandleActions['faq'] = 'HandleFAQ';
$HandleActions['faqAnswer'] = 'HandleFAQAnswer';

Markup('faqNewQuestion','inline','/\\(:faqNewQuestion (\\w+) (\\w+):\\)/e',"newFAQ('$1', '$2')");
Markup('faqNewAnswer','inline','/\\(:faqNewAnswer (\\w+) (\\w+) (\\w+):\\)/e',"newFAQAnswer('$1', '$2', '$3')");
Markup('faqList','inline','/\\(:faqList (\\w+):\\)/','');

function CreateFAQ() {
  $idfaq = substr(md5(rand()),0,4);
  return "\n".
    '!Foire aux questions'."\n".
    '(:faqList '.$idfaq.':)'."\n".
    '(:if auth edit:)(:faqNewQuestion '.$idfaq.' 1:)(:if:)';
}

function newFAQ($idfaq, $numquestion) {
  return Keep('<h3>Poser une nouvelle question :</h3>
  <form method="post">
    <input type="hidden" name="action" value="faq"/>
    <input type="hidden" name="faqId" value="'.$idfaq.'"/>
    <input type="hidden" name="numQuestion" value="'.$numquestion.'"/>
    <input type="text" name="question" size="80"/>
  </form>');
}

function newFAQAnswer($idfaq, $numquestion, $numanswer) {
  $idfaqanswer = 'faqAnswer-'.$idfaq.'-'.$numquestion.'-'.$numanswer;
  return Keep('<blockquote>
  <input type="button" value="Répondre" onclick="
    this.style.display=\'none\';
    var f = document.getElementById(\''.$idfaqanswer.'\');
    f.style.display=\'\';
    f.answer.focus()"/>
  <form method="post" id="'.$idfaqanswer.'" style="display:none">
    <input type="hidden" name="action" value="faqAnswer"/>
    <input type="hidden" name="faqId" value="'.$idfaq.'"/>
    <input type="hidden" name="numQuestion" value="'.$numquestion.'"/>
    <input type="hidden" name="numAnswer" value="'.$numanswer.'"/>
    <textarea name="answer" rows="3" cols="80"></textarea>
    <br/>
    <input type="submit" value="Envoyer la réponse"/>
  </form>
  </blockquote>');
}

function HandleFAQ() {
  global $pagename, $CurrentTime, $Author;
  Lock(2);
  if (isset($_REQUEST['question']) && $_REQUEST['question'] &&
    ($page = RetrieveAuthPage($pagename, 'edit', true))) {
    $idfaq = stripmagic($_REQUEST['faqId']);
    $question = stripmagic($_REQUEST['question']);
    $numquestion = stripmagic($_REQUEST['numQuestion']);
    $titre = $question;
    if (strlen($titre) > 30) {
      $titre = substr($question, 0, 50).'...';
      $question = $titre."\n".$question;
    }
    $page['text'] = preg_replace(
      ',(\(:faqList '.$idfaq.':\)),',
      "\n".'*[[{$FullName}#faq'.$idfaq.'-'.$numquestion.' | '.$titre.']]$1',
      $page['text']);
    $page['text'] = preg_replace(
      ',(\(:if auth edit:\)\(:faqNewQuestion '.$idfaq.') '.$numquestion.':\),',
      '!![[#faq'.$idfaq.'-'.$numquestion.']]Q : '.$question.'\\\\\\\\'."\n".
        '[-\'\'question posée le '.$CurrentTime.($Author?(' par [[~ '.$Author.' ]]\'\'-]'):'')."\n".
        '(:if auth edit:)(:faqNewAnswer '.$idfaq.' '.$numquestion.' 1:)(:if:)'."\n".
        '$1 '.($numquestion + 1).':)',
      $page['text']);
    WritePage($pagename,$page);
    Redirect($pagename);
  }
  Lock(0);
  return "";
}

function HandleFAQAnswer() {
  global $pagename, $CurrentTime, $Author;
  Lock(2);
  if (isset($_REQUEST['answer']) && $_REQUEST['answer'] &&
    ($page = RetrieveAuthPage($pagename, 'edit', true))) {
    $idfaq = stripmagic($_REQUEST['faqId']);
    $answer = str_replace("\n", "\n->", stripmagic($_REQUEST['answer']));
    $numanswer = stripmagic($_REQUEST['numAnswer']);
    $numquestion = stripmagic($_REQUEST['numQuestion']);
    $page['text'] = preg_replace(
      ',(\(:if auth edit:\)\(:faqNewAnswer '.$idfaq.' '.$numquestion.') '.$numanswer.':\),',
      '->[[#faq'.$idfaq.'-'.$numquestion.'-'.$numanswer.']]R : '.$answer."\n".
        '->[-\'\'réponse le '.$CurrentTime.($Author?(' par [[~ '.$Author.' ]]\'\'-]'):'')."\n".
        '$1 '.($numanswer + 1).':)',
      $page['text']);
    WritePage($pagename,$page);
    Redirect($pagename);
  }
  Lock(0);
  return "";
}

?>
