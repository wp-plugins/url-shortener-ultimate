<?php


function usu_url_db_install(){
	global $wpdb;

	$table_name = $wpdb->prefix . 'usu_url';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS " .$table_name. "(
		id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
		time_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		destination varchar(2084) NOT NULL,
		slug varchar(2084) NOT NULL,
		visits bigint UNSIGNED NOT NULL DEFAULT 0,
		PRIMARY KEY  (id)
	)".$charset_collate.";";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
}

?>