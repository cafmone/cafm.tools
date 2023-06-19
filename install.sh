#!/bin/bash
DEFAULT_PROFILES="/var/www/profiles"
DEFAULT_HTTPDOCS="/var/www/html"
DEFAULT_PASSWORD=$(openssl rand -base64 10)

if [ ! -z $1 ]; then
	DEFAULT_FOLDER="$1"
else
	echo "Usage: $0 directory"
	exit
fi

unset CLASSDIR
declare -A CLASSDIR
unset PROFILESDIR
declare -A PROFILESDIR
unset HTTPDOCS
declare -A HTTPDOCS

copyright() {
	echo 'cafm.tools installer'
	echo ''
	echo '@package phppublisher'
	echo '@author Alexander Kuballa [akuballa@users.sourceforge.net]'
	echo '@copyright Copyright (c) 2008 - 2022, Alexander Kuballa'
	echo '@license see LICENSE.TXT'
	echo '@version 1.0'
	echo ''
}
basedir() {
	local SOURCE="${BASH_SOURCE[0]}"
	while [ -h "$SOURCE" ]; do
		local DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
		local SOURCE="$(readlink "$SOURCE")"
		[[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE"
	done
	local DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
	echo "$DIR"
}
classdir() {
	CLASSDIR=$(basedir)
	read -e -p "Path to classdir: " -i $CLASSDIR CLASSDIR
	if [ -f "$CLASSDIR/cafm.tools.class.php" ]; then
		echo "$CLASSDIR" | sed -e 's#/$##g'
	else
		classdir
	fi
}
profiles() {
	read -e -p "Path to profiles: " -i "$DEFAULT_PROFILES/$DEFAULT_FOLDER" PROFILESDIR
	if [ -d $PROFILESDIR ]; then
		PROFILESDIR=$(echo "$PROFILESDIR" | sed -e 's#/$##g')
		return 0
	else
		if askcreate; then
			PROFILESDIR=$(echo "$PROFILESDIR" | sed -e 's#/$##g')
		else
			profiles
		fi
	fi
}
httpdocs() {
	read -e -p "Path to httpdocs: " -i "$DEFAULT_HTTPDOCS/$DEFAULT_FOLDER" HTTPDOCS
	if [ -d $HTTPDOCS ]; then
		HTTPDOCS=$(echo "$HTTPDOCS" | sed -e 's#/$##g')
		return 0
	else
		if askcreate; then
			HTTPDOCS=$(echo "$HTTPDOCS" | sed -e 's#/$##g')
		else
			httpdocs
		fi
	fi
}
askcreate() {
	echo -n "Create dir (y/n)? "
	while read -r -n 1 -s answer; do
		if [[ $answer = [YyNn] ]]; then
			[[ $answer = [Yy] ]] && retval=0
			[[ $answer = [Nn] ]] && retval=1
			break
		fi
	done
	echo ""
	return $retval
}
askinstall() {
	echo -n "Install cafm.tools (y/n)?"
	while read -r -n 1 -s answer; do
		if [[ $answer = [YyNn] ]]; then
			[[ $answer = [Yy] ]] && retval=0
			[[ $answer = [Nn] ]] && retval=1
			break
		fi
	done
	echo ""
	return $retval
}
createdir() {
	DIR=$1
	ERROR=""
	if [ ! -d $DIR ]; then
		ERROR=$(mkdir $DIR)
	fi
	if [ "$ERROR" == "" ]; then
		ERROR=$(chmod 0777 $DIR)
	fi
	echo $ERROR
}
bootstrap() {
	BOOTSTRAP=$(cat $CLASSDIR/setup/bootstrap.php)
	BOOTSTRAP=$(echo "$BOOTSTRAP" | sed -e "s#{CLASSDIR}#$CLASSDIR/#g")
	BOOTSTRAP=$(echo "$BOOTSTRAP" | sed -e "s#{PROFILESDIR}#$PROFILESDIR/#g")
	echo "$BOOTSTRAP" > $HTTPDOCS/bootstrap.php
	chmod 0666 $HTTPDOCS/bootstrap.php
	ln -s $CLASSDIR/setup/index.php $HTTPDOCS/index.php
}
foldersetup() {
	[ -d $HTTPDOCS/css ] || mkdir -m 777 $HTTPDOCS/css
	[ -d $HTTPDOCS/img ] || mkdir -m 777 $HTTPDOCS/img
	[ -d $HTTPDOCS/js ] || mkdir -m 777 $HTTPDOCS/js
	[ -d $HTTPDOCS/login ] || mkdir -m 777 $HTTPDOCS/login
	[ -d $HTTPDOCS/fonts ] || mkdir -m 777 $HTTPDOCS/fonts
}
urlsetup() {
	read -e -p "Baseurl: " -i "/$DEFAULT_FOLDER/" BASEURL
	if [[ ! "$HTTPDOCS/" == *$BASEURL ]]; then
		echo "Url not found. Please try again"
		settings
	fi
	BASEURL=$(echo "$BASEURL" | sed -e 's#/$##g')
}
dbsetup() {
	DBPASS=$(openssl rand -base64 16)
	read -e -p "MySQL User: " -i "root" MYUSER
	read -e -p "MySQL Password: " -i "" MYPASS
	export MYSQL_PWD=$MYPASS
	ERROR=$(mysql --user=$MYUSER -e "CREATE USER  IF NOT EXISTS '${DEFAULT_FOLDER}'@'localhost';" 2>&1)
	if [ $? == 1 ]; then
		echo "$ERROR"
		dbsetup
	fi
	ERROR=$(mysql --user=$MYUSER -e "ALTER USER '${DEFAULT_FOLDER}'@'localhost' IDENTIFIED BY '${DBPASS}';" 2>&1)
	if [ $? == 1 ]; then
		echo "$ERROR"
		dbsetup
	fi
	ERROR=$(mysql --user=$MYUSER -e "GRANT USAGE ON *.* TO '${DEFAULT_FOLDER}'@'localhost';" 2>&1)
	if [ $? == 1 ]; then
		echo "$ERROR"
		dbsetup
	fi
	ERROR=$(mysql --user=$MYUSER -e "CREATE DATABASE IF NOT EXISTS \`${DEFAULT_FOLDER}\` CHARACTER SET utf8 COLLATE utf8_general_ci;" 2>&1)
	if [ $? == 1 ]; then
		echo "$ERROR"
		dbsetup
	fi
	ERROR=$(mysql --user=$MYUSER -e "GRANT ALL PRIVILEGES ON \`${DEFAULT_FOLDER}\`.* TO '${DEFAULT_FOLDER}'@'localhost';" 2>&1)
	if [ $? == 1 ]; then
		echo "$ERROR"
		dbsetup
	fi
	ERROR=$(mysql --user=$MYUSER -e "FLUSH PRIVILEGES;" 2>&1)
	if [ $? == 1 ]; then
		echo "$ERROR"
		dbsetup
	fi
}
settings() {
	SETTINGS=$(cat $CLASSDIR/setup/profiles/settings.ini)
	SETTINGS=$(echo "$SETTINGS" | sed -e "s#{BASEDIR}#$HTTPDOCS/#g")
	SETTINGS=$(echo "$SETTINGS" | sed -e "s#{BASEURL}#$BASEURL/#g")
	SETTINGS=$(echo "$SETTINGS" | sed -e "s#{TITLE}#$DEFAULT_FOLDER#g")
	SETTINGS=$(echo "$SETTINGS" | sed -e "s#{DB}#$DEFAULT_FOLDER#g")
	SETTINGS=$(echo "$SETTINGS" | sed -e "s#{DBUSER}#$DEFAULT_FOLDER#g")
	SETTINGS=$(echo "$SETTINGS" | sed -e "s#{DBPASS}#$DBPASS#g")
	echo "$SETTINGS" > $PROFILESDIR/settings.ini
	chmod 0666 $PROFILESDIR/settings.ini
}
usersetup() {
	htpasswd -bc $PROFILESDIR/.htpasswd admin $DEFAULT_PASSWORD
	chmod 0666 $PROFILESDIR/.htpasswd
	cp $CLASSDIR/setup/profiles/users $PROFILESDIR/users
	cp $CLASSDIR/setup/profiles/groups $PROFILESDIR/groups
	cp $CLASSDIR/setup/profiles/users2groups $PROFILESDIR/users2groups
	chmod 0666 $PROFILESDIR/*

	HTACCESS=$(cat $CLASSDIR/setup/login/.htaccess)
	HTACCESS=$(echo "$HTACCESS" | sed -e "s#{HTPASSWD}#$PROFILESDIR/.htpasswd#g")
	echo "$HTACCESS" > $HTTPDOCS/login/.htaccess
	chmod 0666 $HTTPDOCS/login/.htaccess
	
	ln -s $CLASSDIR/setup/login/*.php $HTTPDOCS/login/
}

debugme() {
	mysql --user=$DEFAULT_FOLDER --password=$DBPASS -e "";
}


phppublishersetup() {
	copyright
	CLASSDIR=$(classdir)
	if profiles; then
		if httpdocs; then
			echo ''
			if askinstall; then
				ERROR=$(createdir $PROFILESDIR)
				if [ "$ERROR" == "" ]; then
					ERROR=$(createdir $HTTPDOCS)
					if [ "$ERROR" == "" ]; then
						echo ''
						bootstrap
						foldersetup
						urlsetup
						dbsetup
						settings
						usersetup
						echo ''
						echo 'Finished installation'
						echo "Login admin:$DEFAULT_PASSWORD"
						echo ''
						exit 0
					else
						echo $ERROR
						exit 0
					fi
				else
					echo $ERROR
					exit 0
				fi
			else
				phppublishersetup
			fi
		fi
	fi
}

phppublishersetup
