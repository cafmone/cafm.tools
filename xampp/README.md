To run CAFM.TOOLS on Windows, use [XAMPP](https://www.apachefriends.org/de/index.html)  
  
  
This Installation Guide expectes a portable version (example: [XAMPP Poratble 8.0.19](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.0.19/xampp-portable-windows-x64-8.0.19-0-VS16.zip/download)) running on an USB Device  
  
1. Download [CAFM.TOOLS](https://github.com/cafmone/cafm.tools/archive/refs/heads/main.zip) and extract ZIP to \[USB]/xampp/ directory  
2. Navigate to \[USB]/xampp/htpdocs directory  
3. Create file \[USB]/xampp/htpdocs/boostrap.php  
```
<?php
/** 
 * Path to class directory.
 * Path must be absolute and end with a /.
 * Directory must be readable.
 * 
 */
define("CLASSDIR", "/xampp/cafm.tools-main/");
/** 
 * Path to profiles directory.
 * Path must be absolute and end with a /.
 * Directory must be writeable.
 */
define("PROFILESDIR", "/xampp/cafm.tools-main/xampp/profiles/");
?>
```
4. Create file \[USB]/xampp/htpdocs/index.php  
```
<?php
require_once('bootstrap.php');
require_once(CLASSDIR.'cafm.tools.class.php');
$controller = new cafm_tools();
echo $controller->controller()->action()->get_string();
?>
```
5. Start apache and database via XAMPP control interface  
  
![XAMPP Control Panel](https://raw.githubusercontent.com/cafmone/cafm.tools/main/xampp/howto/1.xampp.control.png)  
  
6. Start [phpMyAdmin](http://127.0.0.1/phpmyadmin) to create a database  
  
![phpMyAdmin](https://raw.githubusercontent.com/cafmone/cafm.tools/main/xampp/howto/2.phpmyadmin.png)  
  
7. Open [CAFM.TOOLS](http://127.0.0.1/index.php) and save settings  
  
![CAFM.TOOLS Settings](https://raw.githubusercontent.com/cafmone/cafm.tools/main/xampp/howto/3.cafm.tools.settings.png)  
  
8. Select and start plugin  
  
![CAFM.TOOLS Plugins](https://raw.githubusercontent.com/cafmone/cafm.tools/main/xampp/howto/4.cafm.tools.plugins.png)  
9. Configure plugin  
  
![CAFM.TOOLS Plugin](https://raw.githubusercontent.com/cafmone/cafm.tools/main/xampp/howto/5.cafm.tools.plugin.png)  
  
10. Import database  
  
![CAFM.TOOLS Plugin](https://raw.githubusercontent.com/cafmone/cafm.tools/main/xampp/howto/6.cafm.tools.import.png)  


