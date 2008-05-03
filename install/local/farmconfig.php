<?php

$FarmPubDirUrl = 'http://wikifarm.m4x.org/pub';
$PageLogoUrl = "http://www.polytechnique.org/images/skins/default_headlogo.jpg";
$Skin = 'gemini';
$EnablePathInfo = 1;

$DefaultPasswords = array('admin'=>'admin','read'=>'public','edit'=>'admin','attr'=>'admin');
$DefaultPasswords['upload'] = '';
$AuthCascade['upload'] = 'edit';

include_once("$FarmD/cookbook/xorgauth.php");
include_once("$FarmD/cookbook/geoloc.php");
include_once("$FarmD/cookbook/chat.php");
include_once("$FarmD/cookbook/faq.php");
include_once("$FarmD/cookbook/fieldadmin.php");


##---------------Francisation ------------------------------------

XLPage('fr','PmWikiFr.XLPage'); // Lrs chanes de PmWiki
#XLPage('fr','PmWikiFr.XLPageCookbook'); // S'il y a des modules
$XLLangs = array('fr','en');

##-- Chanes et noms de pages/groupes ----------------------------

$DefaultGroup = 'Accueil'; # Groupe par d�faut
$DefaultName = 'Accueil'; # Page de d�marrage groupe - d�faut 'HomePage' - 
$TimeFmt = "%d/%m/%Y %H:%M";  # Format date/heure 17/02/2004 00:14
$AuthorGroup='Profils'; # Nom du groupe des auteurs, d�faut 'Profiles'
$AuthorRequiredFmt = 'Saisir votre nom ou identifiant'; #quand auteur requis

##--Gestion des pages --------------------------------------------

$DefaultPageTextFmt = 'La page $Name n\'existe pas';

$PageNotFound = 'PmWikiFr.PageNonTrouv�e'; #Renvoi quand page inexistante

## Expression utilis�e pour indiquer qu'une page doit tre effac�e 
$DeleteKeyPattern = "^\\s*effacer\\s*$";
$PageRedirectFmt = '<p><i>redirig� depuis $FullName</p>';

	    ## D�finition des pages des derniers chargements (n'existe pas encore)
	    # $RecentUploads = array(...


	    ##-- Styles pr�d�finis --------------------------------------------

	    $WikiStyle['noir']['color'] = 'black';
	    $WikiStyle['blanc']['color'] = 'white';
	    $WikiStyle['rouge']['color'] = 'red';
	    $WikiStyle['vert']['color'] = 'green';
	    $WikiStyle['bleu']['color'] = 'blue';
	    $WikiStyle['jaune']['color'] = 'yellow';
	    $WikiStyle['gris']['color'] = 'gray';
	    $WikiStyle['argent']['color'] = 'silver';
	    $WikiStyle['marron']['color'] = 'maroon';
	    $WikiStyle['pourpre']['color'] = 'purple';
	    $WikiStyle['bleufonc�']['color'] = 'navy';

	    ##-- Groupes et pages � exclure des recherches -----------------------

	    $SearchPatterns['default'][] = '!\\.RechercheWiki$!';
	    $SearchPatterns['default'][] = '!\\.Attributes$!';
	    $SearchPatterns['default'][] = '!\\.(All)?Recent(Changes|Uploads)$!';
	    $SearchPatterns['default'][] = '!\\.Group(Print)?Header$!';
	    $SearchPatterns['default'][] = '!\\.Pr�sentation$!';
	    $SearchPatterns['default'][] = '!\\.Menu$!';
	    $SearchPatterns['default'][] = '!\\.Index!';

	    $SearchPatterns['tousgroupes'] = $SearchPatterns['default'];

	    $SearchPatterns['default'][] = '!^PmWiki\\.!'; # Exclusion groupe PmWiki
	    $SearchPatterns['default'][] = '!^Main\\.!';

	    ## Le groupe PmWiki est exclu des recherches et des listes de pages
	    ## car la traduction en Fran�ais est compl�te mais il est toujours
	    ## possible d'acc�der aux pages de ce groupe directement.
	    ## Pour permettre la recherche dans tous les groupes, on peut voir
	    ## ci-dessus que le tableau 'tousgroupes' a �t� cr��.
	    ## On peut alors faire des recherches ou listes comme suit:
	    ## (:pagelist group=PmWiki list=tousgroupes:)
	    ## ou en ajoutant dans le texte d'une recherche 'list=tousgroupes'

	    ## Exclusions compl�mentaire pour un Index des pages de
	    ## *documentation* du Wiki
	    $SearchPatterns['dict'] = $SearchPatterns['default'];
	    $SearchPatterns['dict'][] = '!^PmWikiFr\\.!'; # Exclusion groupe PmWikiFr

	    ##-- Modules ---------------------------------------------------

	    #-- Si le module RefCount est charg� --------------------------
	    $PageRefCountFmt = "<h1>R�f�rences crois�es</h1><p>"; # Titre
	    $RefCountTimeFmt = "<small>%d-%b-%Y %H:%M</small>"; # Format date

	    ##-- Chanes pour le rapport par courrier - Pour les Administrateurs
	    $MailPostsMessage = "Modifications r�centes du wiki:\n 
	      ($ScriptUrl/$DefaultGroup/ToutesLesModifs)\n\n\$MailPostsList\n";
	      $MailPostsSubject = "$WikiTitle : modifications r�centes du wiki";
	      #$MailPostsTimeFmt = $TimeFmt;
	      $MailPostsItemFmt = ' * $FullName . . . $PostTime par $Author';

	      ##-- Si le module approveurl est charg� -------------------------
	      $ApprovedUrlPagesFmt = array('$DefaultGroup.LiensApprouv�s');

	      ##-- Documentation ---------------------------------------------

	      ## Liste des pages o� sont d�finies les variables 
	      ## (pour l'�tablissement de liens automatiques)
	      $VarPagesFmt = array('PmWikiFr.Variables','PmWikiFr.VariablesDeBase',
	      'PmWikiFr.VariablesDeMiseEnPage','PmWikiFr.VariablesDeLiens',
	      'PmWikiFr.VariablesDdition','PmWikiFr.VariablesDeT�l�chargement',
	        'PmWikiFr.AutresVariables','PmWikiFr.EnvoiDeCourriel');
?>
