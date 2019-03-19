#!/usr/bin/env make

.PHONY: all addToHosts aptitudeInstall githubInstall phpInstall sourceguardian
default: all

addToHosts:
	echo "Adding website to hosts..."
	echo "127.0.0.1\t\twww.chat-api.net" >> /etc/hosts
	uniq /etc/hosts > temp_file
	mv temp_file /etc/hosts

aptitudeInstall:
	echo "Installing aptitude dependencies..."
	apt-get install -y "php7.0" "php7.0-dev" libmcrypt-dev gcc make autoconf libc6-dev pkg-config php7.0-gd php7.0-cli php7.0-mysql php7.0-zip php7.0-common php7.0-sqlite php7.0-curl php7.0-mcrypt

githubInstall:
	echo "Installing github dependencies..."
	for repo in "allegro/php-protobuf" "mgp25/curve25519-php" "bukka/php-crypto"; \
	do \
		git clone --recursive --depth 1 "https://github.com/$${repo}" temp_folder; \
		(cd temp_folder && phpize && ./configure && make && make install); \
		rm -rf temp_folder; \
	done

phpInstall:
	echo "Installing dependencies as php modules..."
	for repo in "protobuf" "crypto" "curve25519"; \
	do \
		echo "extension=$${repo}.so" > "/etc/php/7.0/mods-available/$${repo}.ini"; \
		phpenmod -v 7.0 $${repo}; \
	done

sourceguardian:
	echo "Installing sourceguardian..."
	mkdir temp_folder
	wget http://www.sourceguardian.com/loaders/download/loaders.linux-x86_64.tar.gz -O temp_folder/loader.tar.gz
	(cd temp_folder && tar -xzvf loader.tar.gz)
	cp temp_folder/ixed.7.0.lin /usr/lib/php/20151012/
	echo "[sourceguardian]\nzend_extension=/usr/lib/php/20151012/ixed.7.0.lin\n" > "/etc/php/7.0/mods-available/sourceguardian.ini"
	phpenmod -v 7.0 sourceguardian
	rm -rf temp_folder

all: addToHosts aptitudeInstall githubInstall phpInstall sourceguardian
	echo "Configuration complete"
