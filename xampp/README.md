To run CAFM.TOOLS on Windows, use [XAMPP](https://www.apachefriends.org/de/index.html)  
  
  
This **README** expectes a portable version for example [XAMPP Poratble 8.0.19](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.0.19/xampp-portable-windows-x64-8.0.19-0-VS16.zip/download) on an USB Device  
  
1. [Download](https://github.com/cafmone/cafm.tools/archive/refs/heads/main.zip) CAFM.TOOLS and extract zip content to xampp/ directory   
2. Browse xampp/htpdocs directory  
3. create file boostrap.php  
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
define("PROFILESDIR", "/xampp/cafm.tools/xampp/profiles/");
?>
```
4. create file index.php  
```
<?php
require_once('bootstrap.php');
require_once(CLASSDIR.'cafm.tools.class.php');
$controller = new cafm_tools();
echo $controller->controller()->action()->get_string();
?>
```

