<?php

// Upper Level index.php within dreamportal directory.
if (file_exists(dirname(dirname(__FILE__)) . '/index.php'))
	include(dirname(dirname(__FILE__)) . '/index.php');
else
	exit;

?>