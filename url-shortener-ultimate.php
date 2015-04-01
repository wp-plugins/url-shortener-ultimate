<?php
/*
Plugin Name: Url Shortener Ultimate
Description: An easy way to place a url shortener ultimate on your Wordpress site.
Version: 1.0
Author: Jeff Bullins
Author URI: http://www.thinklandingpages.com
*/

if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/CMB2/init.php';
}

include_once 'cmb2Settings.php'; 

include_once 'usu-db.php';



function usu_url_activate() {
	usu_url_db_install();
	//global $wp_rewrite;
	//$wp_rewrite->flush_rules();
}


register_activation_hook( __FILE__, 'usu_url_activate');

 