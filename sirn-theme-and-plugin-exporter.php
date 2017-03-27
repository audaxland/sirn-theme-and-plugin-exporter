<?php
/*
 * Plugin Name: Sirmons Theme and Plugin Exporter
 * Plugin URI: http://www.sirmons.fr/mes-plugins-wordpress/sirmons-theme-and-plugin-exporter
 * Author: Nathanael SIRMONS
 * Author URI: http://www.sirmons.fr
 * Description: Allows you to export you're themes and plugins without ftp access. This plugin will generate .zip files from your Themes and Plugins so you can download them and later install them elsewhere.
 * Version: 1.0.0
 * Text Domain: sirn-plug-export
 * Domain Path: languages/
 * Licence: GPLv2 or later version
 */

/*  Copyright 2015 Nathanael SIRMONS  (email : sirn-theme-and-plugin-exporter@sirmons.fr)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2 or
(at your option) any later version, as
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
 * Sirn_Theme_And_Plugin_Exporter is the main class for the plugin
 * @since 1.0.0
 */
class Sirn_Theme_And_Plugin_Exporter
{
	/* 
	 * @ignore : not currently used
	 * main class instance
	 * @since 1.0.0
	 * @var object $_instance : instance of Sirn_Theme_And_Plugin_Exporter
	 */
	protected static $_instance = null;
	
	/*
	 * @ignore : not currently used
	 * @since 1.0.0
	 * @return object : instance of this class
	 */
	public static function instance(){
		if ( is_null( self::$_instance ) ) self::$_instance = new self();
		return self::$_instance;
	}
	
	/*
	 * @ignore : not currently used
	 * @since 1.0.0
	 */
	public function __construct(){
		if ( is_null( self::$_instance ) ) self::$_instance = $this;
	}
	
	/* 
	 * init() is the main setup function for the plugin
	 * @hook action 'init' : init() is executed during the 'init' action hook
	 * @since 1.0.0
	 * @return void
	 */
	public static function init(){
		// this plugin can only be used from within the admin
		if ( is_admin() ) {
			
			/* loads the plugins textdomain : 
			 * 1. searches for translations in the wp-content/language dir first
			 * 2. searches for translations in the plugins /language dir second
			 */
			load_plugin_textdomain( 'sirn-theme-and-plugin-exporter', false, WP_LANG_DIR . '/plugins' );
			load_plugin_textdomain( 'sirn-theme-and-plugin-exporter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			
			// inludes the file for the class : Sirn_Theme_And_Plugin_Exporter_Admin_Page
			require_once( self::get_path( '/includes/class-sirn-theme-and-plugin-exporter-admin-page.php') ) ;
			
			// executes the add_actions of the main hooks used by the class : Sirn_Theme_And_Plugin_Exporter_Admin_Page
			Sirn_Theme_And_Plugin_Exporter_Admin_Page::init();
		}
	}
	
	// the methods get_path(), path(), get_url() and url() give the paths or url relative to the plugin's direcpory

	
	/*
	 * @since 1.0.0
	 * @param string $path : relative path of a file inside this plugin directory
	 * @return string : full path to this file or to a specific file from this plugin 
	 */
	public static function get_path( $path = '' ){
		return self::proper_slashing( dirname( __FILE__ ),  $path ) ;
	}
	
	/*
	 * @since 1.0.0
	 * @param string $path : relative path of a file inside this plugin directory
	 * @return void
	 * @echo : echoes the full path to this file or to a specific file from this plugin
	 */
	public static function path( $path = '' ){
		echo self::proper_slashing( dirname( __FILE__ ), $path ) ;
	}
	
	/*
	 * @since 1.0.0
	 * @param string $path : relative path of a file inside this plugin directory
	 * @return string : full url to this file or to a specific file from this plugin 
	 */
	public static function get_url( $path = '' ){
		return self::proper_slashing( plugin_dir_url( __FILE__ ), $path ) ;
	}
	
	/*
	 * @since 1.0.0
	 * @param string $path : relative path of a file inside this plugin directory
	 * @return void
	 * @echo : echoes full url to this file or to a specific file from this plugin
	 */
	public static function url( $path = '' ){
		echo self::proper_slashing( plugin_dir_url( __FILE__ ),  $path ) ;
	}
	
	/*
	 * proper_slashing() concatenates two strings with a single slach between the two
	 * @since 1.0.0
	 * @param string $first : full path or url to this file
	 * @param string $second : relative path to a file form this plugin
	 * @return string : the full path or full url to a file inside this plugin
	 */
	protected static function proper_slashing( $first, $second ){
		$first = untrailingslashit( $first );
		if ( !empty( $second ) && ( substr( $second, 0, 1 ) != '/' ) ){
			$second = '/' . $second;
		}
		return $first . $second ;
	}
	

	/* desactivation hooks for this plugin : destroy's any file or directory created by the plugin
	 * and destroy's the settings for the plugin in the database
	 * @since 1.0.0
	 * @return void
	 */
	public static function deactivation_hook(){
		require_once( self::get_path( '/includes/class-sirn-theme-and-plugin-exporter-files-manager.php' ) );
		// deletes the directory where the .zip files are stored
		Sirn_Theme_And_Plugin_Exporter_Files_Manager::desactivation_hook();
	}
	
}
///////////
/* Main */
//////////

/*
 * loads the plugin at the 'init' action hook
 * @since 1.0.0
 */ 
add_action( 'init', array( 'Sirn_Theme_And_Plugin_Exporter', 'init' ) ) ;

/* registers the deactivation hook for this plugin
 * @since 1.0.0
 */
register_deactivation_hook( __FILE__, array( 'Sirn_Theme_And_Plugin_Exporter', 'deactivation_hook' )  );

