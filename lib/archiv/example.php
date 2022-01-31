<?php
require_once('archive.php');

// Assume the following script is executing in /var/www/htdocs/test
// Create a new gzip file test.tgz in htdocs/test
$test = new gzip_file("hallotest.tgz");
// Set basedir to "../..", which translates to /var/www
// Overwrite /var/www/htdocs/test/test.tgz if it already exists
// Set compression level to 1 (lowest)
$test->set_options(array('basedir' => ".", 'overwrite' => 1, 'level' => 1));
#$test->set_options(array('basedir' => "", 'overwrite' => 1, 'level' => 1));
// Add entire htdocs directory and all subdirectories
// Add all php files in htsdocs and its subdirectories
$test->add_files(array("test"));
// Exclude all jpg files in htdocs and its subdirectories
#$test->exclude_files("htdocs/*.jpg");
// Create /var/www/htdocs/test/test.tgz
$test->create_archive();


if (count($test->errors) > 0)
	print ("Errors occurred."); // Process errors here


?>
