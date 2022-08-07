sudo bash  
apt -y install apache2  
apt -y install subversion  
apt -y install php libapache2-mod-php php-mysql php-mbstring php-zip php-gd php-json php-curl  
apt -y install nano  
apt -y -q install mysql-server  
mysql -u root -e "CREATE USER 'hostmaster'@'localhost' IDENTIFIED BY 'password'; FLUSH PRIVILEGES;"  
mysql -u root -e "GRANT ALL PRIVILEGES ON * . * TO 'hostmaster'@'localhost'; FLUSH PRIVILEGES;"  
mkdir /var/www/html/logs  
chmod 0777 /var/www/html/logs  
nano /etc/apache2/sites-enabled/000-default.conf
```
<VirtualHost *:80>
	ServerAdmin webmaster@localhost
	DocumentRoot /var/www/html/httpdocs
	<Directory "/var/www/html/httpdocs">
		AllowOverride All
	</Directory>
	LogFormat "%h %t \"%r\" %>s %b" custom
	ErrorLog "|/usr/bin/rotatelogs /var/www/html/logs/error-%Y-%m-%d.log 86400"
	CustomLog "|/usr/bin/rotatelogs /var/www/html/logs/access-%Y-%m-%d.log 86400" custom
</VirtualHost>
```
/etc/init.d/apache2 restart

