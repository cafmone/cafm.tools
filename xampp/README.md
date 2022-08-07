[XAMPP Homepage](https://www.apachefriends.org/de/index.html)  
[XAMPP Poratble 8.0.19](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.0.19/xampp-portable-windows-x64-8.0.19-0-VS16.zip/download)  
  
  
boostrap.php  
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
index.php  
```
<?php
require_once('bootstrap.php');
require_once(CLASSDIR.'cafm.tools.class.php');
$controller = new cafm_tools();
echo $controller->controller()->action()->get_string();
?>
```

