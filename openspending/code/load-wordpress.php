<?php
/*  Copyright 2013 Open Knowledge Foundation ( http://okfn.org )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
   Get the absolute path to wp-load.php so that we can load the WordPress
   functions and access them in our popup.
*/

// If you (the user) have put wp-content in a non-standard folder you need
// to put in the path and uncomment the following line
// define('WP_LOAD_PATH', '/path/to/wp-load.php');

// If WP_LOAD_PATH hasn't been define before we need to find it
if ( !defined('WP_LOAD_PATH') ) {
  // This file is in wordpressfolder/wp-content/plugins/openspending/js/
  // wp-load.php is in "wordpressfolder"
  // Get the directory name (and append a slash)
  $dir = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/' ;

  // Check if wp-load.php is a file in the directory
  // If so we can define WP_LOAD_PATH
  if (file_exists( $dir . 'wp-load.php') )
    define( 'WP_LOAD_PATH', $dir . 'wp-load.php');
  // If not, we exit
  else
    exit("An error came up when loading WordPress");
}

// Load WordPress
require_once( WP_LOAD_PATH );
?>
