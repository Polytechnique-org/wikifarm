#! /bin/sh
InstallDir=$(dirname $0)
FarmDir=${InstallDir}/../

# Nom du wiki en param�tre ou en read
if [ $# -lt 1 ]; then
	echo "Nom du nouveau champs wiki (doit pouvoir �tre un nom de dossier) :"
	read NomDuWiki
else
	NomDuWiki=$1
fi

FieldDir=${FarmDir}${NomDuWiki}

# V�rification de l'existance du champs
if [ -d $FieldDir ]; then
	echo "Ce champs existe d�j�."
	exit
fi

# R�cup�ration de l'url
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

# r�capitulatif
echo "Cr�ation du champs wiki $NomDuWiki"
echo "  dossier : $FieldDir"
echo "  url : $FieldUrl"
echo "  url relatif : $FieldUrlFolder"

# copie des fichiers
cp -Ra ${InstallDir}/NomDuWiki $FieldDir

# application des dossiers et url sp�cifiques au champs
sed -e "s,^RewriteBase .*$,RewriteBase $FieldUrlFolder," ${InstallDir}/NomDuWiki/.htaccess > $FieldDir/.htaccess
sed -e "s,^.*ScriptUrl.*$, \$ScriptUrl = '$FieldUrl';," ${InstallDir}/NomDuWiki/local/config.php > ${FieldDir}/local/config.php

#cr�ation des dossiers et fichiers attribu�s � l'utilisateur www-data
#wget --quiet ${FieldUrl}/Site/Admin?createconf=1 -O /dev/null
#wget --quiet ${FieldUrl}/Site/Admin?createconf=1 -O /dev/null

