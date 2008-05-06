#! /bin/sh
InstallDir=$(dirname $0)
FarmDir=${InstallDir}/../

# Nom du wiki en paramètre ou en read
if [ $# -lt 1 ]; then
	echo "Nom du nouveau champs wiki (doit pouvoir être un nom de dossier) :"
	read NomDuWiki
else
	NomDuWiki=$1
fi

FieldDir=${FarmDir}${NomDuWiki}

# Vérification de l'existance du champs
if [ -d $FieldDir ]; then
	echo "Ce champs existe déjà."
	exit
fi

# Récupération de l'url
echo "Url du wiki (http://$NomDuWiki.polytechnique.org/):"
read FieldUrl
if [ -z "$FieldUrl" ]; then
	FieldUrl=http://$NomDuWiki.polytechnique.org/
	FieldUrlFolder=/
else
	if [ "$FieldUrl" = "ok" ]; then
		echo "url invalide ok"
		exit
	fi
	if [ $(echo $FieldUrl | sed -e "s,^http://[^/]*/.*$,ok,") != "ok" ]; then
		echo "url invalide"
		exit
	fi
	# suppression du / final dans l'url
	FieldUrl=$(echo $FieldUrl | sed -e "s,/$,,")
	FieldUrlFolder=$(echo $FieldUrl | sed -e "s,^http://[^/]*\(/.*\)$,\\1/,")
fi

# récapitulatif
echo "Création du champs wiki $NomDuWiki"
echo "  dossier : $FieldDir"
echo "  url : $FieldUrl"
echo "  url relatif : $FieldUrlFolder"

# copie des fichiers
cp -Ra ${InstallDir}/NomDuWiki $FieldDir

# application des dossiers et url spécifiques au champs
sed -e "s,^RewriteBase .*$,RewriteBase $FieldUrlFolder," ${InstallDir}/NomDuWiki/.htaccess > $FieldDir/.htaccess
sed -e "s,^.*ScriptUrl.*$, \$ScriptUrl = '$FieldUrl';," ${InstallDir}/NomDuWiki/local/config.php > ${FieldDir}/local/config.php

#création des dossiers et fichiers attribués à l'utilisateur www-data
#wget --quiet ${FieldUrl}/Site/Admin?createconf=1 -O /dev/null
#wget --quiet ${FieldUrl}/Site/Admin?createconf=1 -O /dev/null

