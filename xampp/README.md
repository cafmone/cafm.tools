To run CAFM.TOOLS on Windows, use [XAMPP](https://www.apachefriends.org/de/index.html)  
  
  
This **README** expectes a portable version (example: [XAMPP Poratble 8.0.19](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.0.19/xampp-portable-windows-x64-8.0.19-0-VS16.zip/download)) running on an USB Drive  
  
1. Download [CAFM.TOOLS](https://github.com/cafmone/cafm.tools/archive/refs/heads/main.zip) and extract ZIP to \[USB]/xampp/ directory   
2. Navigate to \[USB]/xampp/htpdocs directory  
3. Create file boostrap.php  
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
4. Create file index.php  
```
<?php
require_once('bootstrap.php');
require_once(CLASSDIR.'cafm.tools.class.php');
$controller = new cafm_tools();
echo $controller->controller()->action()->get_string();
?>
```

