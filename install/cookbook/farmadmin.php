<?php

// vérification de l'autorisation
function is_farm_admin()
{
	global $isFarmAdmin;
	if (!isset($isFarmAdmin)) {
		global $WikiIsMainField;

		$isFarmAdmin = $WikiIsMainField && (RetrieveAuthPage('Accueil.Accueil', 'admin', false) !== false);
	}
	return $isFarmAdmin;
}
?>
