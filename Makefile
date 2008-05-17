COOKBOOKS = autocreate.php chat.php faq.php farmadmin.php fieldadmin.php geoloc.php skinchange.php xorgauth.php
SKINS = gemini monobook triad
LOCALS = farmconfig.php farmmap.txt
WIKILIBDS = Site.AuthForm Site.PageActions Site.PageFootMenu

all: pmwiki i18n-fr skins local cookbook wikilib.d rights

pmwiki:
	rm -f pmwiki-latest.tgz
	wget http://pmwiki.org/pub/pmwiki/pmwiki-latest.tgz
	tar -xzf pmwiki-latest.tgz
	rm -f pmwiki-latest.tgz
	mv pmwiki-[0-9]* pmwiki

i18n-fr: pmwiki/.i18n-fr
pmwiki/.i18n-fr:
	rm -f i18n-fr.zip
	wget http://pmwiki.org/pub/pmwiki/i18n/i18n-fr.zip
	(cd pmwiki && unzip ../i18n-fr.zip)
	rm -f i18n-fr.zip
	touch pmwiki/.i18n-fr

skins: $(addprefix pmwiki/pub/skins/,$(SKINS)) 
pmwiki/pub/skins/%/:
	rm -f $*.zip
	wget http://www.pmwiki.org/pmwiki/uploads/Cookbook/$*.zip
	(cd pmwiki/pub/skins/ && unzip ../../../$*.zip)
	rm -f $*.zip

local: $(addprefix pmwiki/local/,$(LOCALS))
pmwiki/local/%: install/local/%
	cp $< $@

cookbook: $(addprefix pmwiki/cookbook/,$(COOKBOOKS))
pmwiki/cookbook/%: install/cookbook/%
	cp $< $@

wikilib.d: pmwiki/wikilib.d/.xorg $(addprefix pmwiki/wikilib.d/,$(WIKILIBDS))
pmwiki/wikilib.d/.xorg:
	(cd pmwiki/wikilib.d && rm -f $(WIKILIBDS) && touch .xorg)
pmwiki/wikilib.d/%: install/wikilib.d/%
	cp $< $@

rights:
	@chmod g+ws install/NomDuWiki/uploads install/NomDuWiki/wiki.d
	@chmod g+w install/NomDuWiki/local/webconfig.php
