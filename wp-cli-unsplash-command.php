<?php
/*
Plugin Name: WP-CLI Unsplash Command
Plugin URI: https://github.com/A5hleyRich/wp-cli-unsplash-command
Description: Adds the command `wp unsplash` to WP-CLI.
Author: Ashley Rich
Version: 1.0
Author URI: https://ashleyrich.com
Network: True
*/

if ( defined( 'WP_CLI' ) && WP_CLI && ! class_exists( 'WP_CLI_Unsplash_Command' ) ) {
	require_once dirname( __FILE__ ) . '/class-wp-cli-unsplash-command.php';
}