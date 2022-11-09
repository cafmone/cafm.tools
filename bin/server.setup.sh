#!/bin/bash

CURRENT=$(whoami)
if [ "$CURRENT" != "root" ]
	then
		echo "usage: sudo $0"
		exit
fi

TO="[Email here]"
FROM="[From here]"
SMTP_SERVER="[SMTP Server here]"
SMTP_PORT="[SMTP Port here]"
SMTP_USER="[SMTP User here]"
SMTP_PASSWORD="[SMTP Password here]"

## Apache
if ! [ -x "$(command -v apache2)" ]; then
	apt -y install apache2
else
	echo -e "Apache2 already installed - nothing to do. $(which apache2)"
fi

## Subversion (svn)
if ! [ -x "$(command -v svn)" ]; then
	apt -y install subversion
else
	echo -e "Subversion already installed - nothing to do. $(which svn)"
fi

## Mysql
if ! [ -x "$(command -v mysql)" ]; then
	apt -y -q install mysql-server
	mysql -u root -e "CREATE USER 'hostmaster'@'localhost' IDENTIFIED BY 'hostmaster'; FLUSH PRIVILEGES;"
	mysql -u root -e "GRANT GRANT OPTION ON * . * TO 'hostmaster'@'localhost'; FLUSH PRIVILEGES;"
	mysql -u root -e "GRANT ALL PRIVILEGES ON * . * TO 'hostmaster'@'localhost'; FLUSH PRIVILEGES;"
else
	echo -e "Mysql already installed - nothing to do. $(which mysql)"
fi

## PHP
if ! [ -x "$(command -v php)" ]; then
	apt -y install php libapache2-mod-php php-mysql php-mbstring php-zip php-gd php-json php-curl
else
	echo -e "PHP already installed - nothing to do. $(which php)"
fi

## PHPMyadmin
if [ ! -f /usr/share/phpmyadmin/config.inc.php ]; then
apt -y install software-properties-common
add-apt-repository ppa:ondrej/apache2
apt -y install phpmyadmin

cat <<EOT > /usr/share/phpmyadmin/config.inc.php
<?php
\$cfg['blowfish_secret'] = 'xc32z45bw6rh2woqthpqlm9fu35asqj';
\$i = 0;
\$i++;
\$cfg['Servers'][\$i]['auth_type'] = 'cookie';
\$cfg['Servers'][\$i]['host'] = 'localhost';
\$cfg['Servers'][\$i]['compress'] = false;
\$cfg['Servers'][\$i]['AllowNoPassword'] = false;
\$cfg['Servers'][\$i]['user'] = 'hostmaster';
?>
EOT
else
	echo -e "PHPMyadmin already installed - nothing to do."
fi

## Midnightcommander
if ! [ -x "$(command -v mc)" ]; then
	apt -y install mc
	echo -e "\nalias mc='. /usr/lib/mc/mc-wrapper.sh'" >> ~/.bashrc
	echo "1" | select-editor
else
	echo -e "Midnightcommander already installed - nothing to do. $(which mc)"
fi

## Mail
if ! [ -x "$(command -v msmtp)" ]; then
apt -y install msmtp msmtp-mta

cat <<EOT > /etc/msmtprc
defaults
port $SMTP_PORT
tls on
aliases /etc/aliases
account provider
host $SMTP_SERVER
from $FROM
auth on
user $SMTP_USER
password $SMTP_PASSWORD
account default : provider
EOT

cat <<EOT > /etc/aliases
default: $TO
EOT
else
	echo -e "Msmtp already installed - nothing to do. $(which msmtp)"
fi

## Security
## Send mail on ssh login
cat <<EOT > /etc/ssh/sshrc
#!/bin/bash
HOST=\$(ip addr show | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*'| grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1')
IP=\$( echo "\$SSH_CONNECTION" | cut -d " " -f 1 )
echo "Subject: ssh \$USER@\$HOST from \$IP" | msmtp default &
EOT

## Security
## clamav
if ! [ -x "$(command -v clamscan)" ]; then
	apt -y install clamav clamav-freshclam 

cat <<EOT > /root/clamav.cron.sh
#!/bin/bash
MSG=\$(
echo "Subject: clamav"
echo ""
echo ""
clamscan -ir /var/www/)

echo "\$MSG" | msmtp default
EOT
chmod 0777 /root/clamav.cron.sh
(crontab -u root -l 2>/dev/null; echo "0 3 * * * /root/clamav.cron.sh") | crontab -u root -
else
	echo -e "ClamAV already installed - nothing to do. $(which clamscan)"
fi

## Security
## chkrootkit
if ! [ -x "$(command -v chkrootkit)" ]; then
	apt -y install chkrootkit

cat <<EOT > /root/chkrootkit.cron.sh
#!/bin/bash
MSG=\$(
echo "Subject: chkrootkit"
echo ""
echo ""
echo "Message:"
/usr/sbin/chkrootkit -q)

echo "\$MSG" | msmtp default
EOT
chmod 0777 /root/chkrootkit.cron.sh
(crontab -u root -l 2>/dev/null; echo "0 2 * * * /root/chkrootkit.cron.sh") | crontab -u root -
else
	echo -e "Chkrootkit already installed - nothing to do. $(which chkrootkit)"
fi

## Security
## rkhunter
## https://wiki.ubuntuusers.de/rkhunter/
if ! [ -x "$(command -v rkhunter)" ]; then
	apt -y install rkhunter
	rkhunter --propupd

cat <<EOT > /root/rkhunter.cron.sh
#!/bin/bash
echo -e "Subject: rkhunter \n\n\$(rkhunter -c --rwo --no-append-log)" | msmtp default
EOT
chmod 0777 /root/rkhunter.cron.sh
(crontab -u root -l 2>/dev/null; echo "0 1 * * * /root/rkhunter.cron.sh") | crontab -u root -
else
	echo -e "Rkhunter already installed - nothing to do. $(which rkhunter)"
fi


#mkdir /var/www/class/
#chmod 0777 /var/www/class/
#svn checkout https://github.com/cafmone/cafm.tools.git/trunk /var/www/class/cafm.tools

