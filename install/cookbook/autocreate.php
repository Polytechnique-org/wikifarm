<?php

function AutoCreatePage($page, $text, $redirect = null) {
	global $pagename, $WikiDir;
	$pagename_ = str_replace('/','.',$pagename);
	$group = substr($pagename_,0,strrpos($pagename_,'.'));

	$page = str_replace('$Group', $group, $page);
	if ($pagename_ == $page && !$WikiDir->exists($pagename_)) {
		$WikiDir->write($pagename_, array('text' => $text));
		if ($redirect != '') {
			$redirect = $pagename_;
		}
		Redirect($pagename_);
	}
}

?>
