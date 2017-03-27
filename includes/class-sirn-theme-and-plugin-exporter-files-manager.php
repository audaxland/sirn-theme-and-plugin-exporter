<?php
class Sirn_Theme_And_Plugin_Exporter_Files_Manager
{
	
	/* 
	 * main class instance
	 * @since 1.0.0
	 * @var object $_instance : instance of Sirn_Theme_And_Plugin_Exporter
	 */
	protected static $_instance = null;
	
	/*
	 * @since 1.0.0
	 * @return object : instance of this class
	 * 
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) self::$_instance = new self();
		return self::$_instance;
	}
	
	/*
	 * locates and checks the upload directory where the .zip files will be created 
	 * and will create those directories if needed
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( is_null( self::$_instance ) ) self::$_instance = $this;
		
		// we define a random directory name that will contain the .zip files
		// this allows some level of security to avoid giving access to these files to non-intended peaople
		$secure_str = (string) get_option( 'sirn_theme_and_plugin_exporter_secure_dir_str', '' );
		if ( empty( $secure_str) ) {
			$secure_str = md5( 'Sirn_Theme_And_Plugin_Exporter_' . mt_rand() . 'secure_str' . time() );
			update_option( 'sirn_theme_and_plugin_exporter_secure_dir_str', $secure_str );
		}
		
		//define where to write the .zip files
		$upload_dir = wp_upload_dir();
		$this->base_dir = $upload_dir['basedir'] . '/sirn-theme-and-plugin-exporter';
		$this->upload_dir = $upload_dir['basedir'] . '/sirn-theme-and-plugin-exporter/' . $secure_str;
		$this->upload_url = $upload_dir['baseurl'] . '/sirn-theme-and-plugin-exporter/' . $secure_str;
		
		// check that the directories exists and create them if not
		// also add an empty index.php file in each directory
		if( ! file_exists( $this->base_dir ) ) {
			@mkdir( $this->base_dir );
		}
		if( file_exists( $this->base_dir ) && ! is_writable( $this->base_dir ) ){
			@chmod( $this->base_dir, '0755' );
		}
		if( is_writable( $this->base_dir ) && ! file_exists( $this->base_dir . '/index.php' ) ){
			file_put_contents( $this->base_dir . '/index.php', '<?php //silence is golden' );
		}
		if( ! file_exists( $this->upload_dir ) ) {
			@mkdir( $this->upload_dir );
		}
		if( file_exists( $this->upload_dir ) && ! is_writable( $this->upload_dir ) ){
			@chmod( $this->upload_dir, '0755');
		}
		if( is_writable( $this->upload_dir ) && ! file_exists( $this->upload_dir . '/index.php' ) ){
			file_put_contents( $this->upload_dir . '/index.php', '<?php //silence is golden' );
		}
	}
	
	/*
	 * full path to the base directory where the plugin will be writing 
	 * the .zip files will be written in a subdirectory of this directory
	 * usualy the directory :  wp-content/uploads/sirn-theme-and-plugin-exporter/ 
	 * @since 1.0.0
	 * @var string $base_dir : full path to the base directory where the plugins will be writing
	 */
	protected $base_dir = null;
	
	/*
	 * This plugins will create a folder with a random name to give some security
	 * $upload_dir is the full path to that directory, it is a subdirectory of the $base_dir just above
	 * this id usualy the directory : wp-content/uploads/sirn-theme-and-plugin-exporter/[ random string ] 
	 * @since 1.0.0
	 * @var string $upload_dir : full path to the sub-directory where the plugins will be writing
	 */
	protected $upload_dir = null;
	
	/*
	 * This plugins will create a folder with a random name to give some security
	 * $upload_url is the full url to that directory, it is a subdirectory of the $base_dir just above
	 * this is usualy the directory : wp-content/uploads/sirn-theme-and-plugin-exporter/[ random string ]
	 * @since 1.0.0
	 * @var string $upload_dir : full path to the sub-directory where the plugins will be writing
	 */
	protected $upload_url = null;
	
	
	/*
	 * @since 1.0.0
	 * @param string $path : relative path of a file inside this plugin directory
	 * @return string : full path to the $upload_dir directory or to a specific file from that directory
	 */
	public function upload_dir( $file = '' ) {
		return $this->proper_slashing( $this->upload_dir, $file );
	}
	
	/*
	 * @since 1.0.0
	 * @param string $path : relative path of a file inside this plugin directory
	 * @return string : full url to the $upload_dir directory or to a specific file from that directory
	 */
	public function upload_url( $file = '' ) {
		return $this->proper_slashing( $this->upload_url, $file );
	}
	
	/*
	 * proper_slashing() concatenates two strings with a single slach between the two
	 * @since 1.0.0
	 * @param string $first : full path or url to the $upload_dir directory
	 * @param string $second : relative path to a file form the $upload directory
	 * @return string : the full path or full url to a file inside the $upload_directory
	 */
	public function proper_slashing( $first, $second ) {
		$first = untrailingslashit( $first );
		if ( !empty( $second ) && ( substr( $second, 0, 1 ) != '/' ) ) {
			$second = '/' . $second;
		}
		return $first . $second ;
	}
	
	
	/* 
	 * makes the themes .zip file
	 * first check that the directroy for the themes .zip files exists, and creates it if not
	 * @since 1.0.0
	 * @para string $theme_name : the name of the theme
	 * @para string $source : full path of the theme directory
	 * @retrun boolean : True is success, False if failed
	 */
	public function make_theme_zip( $theme_name, $source ) {
		$theme_dir = $this->upload_dir( '/themes/' );
		if ( !file_exists( $theme_dir ) ) {
			$sucess = mkdir($theme_dir );
			if ( $sucess && !file_exists( $this->upload_dir( '/themes/index.php' ) ) ) {
				file_put_contents( $this->upload_dir( '/themes/index.php' ) , '<?php // silence is golden ' );
			}
			if ( $sucess == false ) return false;
		}
		$destination = $this->proper_slashing( $theme_dir, $theme_name . '.zip' );
		return $this->zip_directory( $source, $destination );
		
	}

	/* 
	 * makes the plugin .zip file
	 * first check that the directroy for the plugins .zip files exists, and creates it if not
	 * @since 1.0.0
	 * @para string $plugin_name : the name of the plugin
	 * @para string $source : full path of the plugin directory or file
	 * @retrun boolean :  True is success, False if failed
	 */
	public function make_plugin_zip( $plugin_name, $source ) {
		$plugin_dir = $this->upload_dir( '/plugins/' );
		if ( !file_exists( $plugin_dir ) ) {
			$sucess = mkdir( $plugin_dir );
			if ( $sucess && !file_exists( $this->upload_dir( '/plugins/index.php' ) ) ) {
				file_put_contents( $this->upload_dir( '/plugins/index.php' ) , '<?php // silence is golden ' );
			}
			if ( $sucess == false ) return false;
		}
		$destination = $this->proper_slashing( $plugin_dir, $plugin_name . '.zip' );
		if ( file_exists( $source ) && is_dir( $source ) ) {
			return $this->zip_directory( $source, $destination );
		} elseif ( file_exists( $source ) && ( substr(  $plugin_name, -4 ) == '.php' ) ) {
			return $this->zip_file( $source, $destination );
		} else {
			return false;
		}
		
	}
	
	/* 
	 * makes the mu-plugin .zip file
	 * first check that the directroy for the mu-plugins .zip files exists, and creates it if not
	 * @since 1.0.0
	 * @para string $plugin_name : the name of the mu-plugin
	 * @para string $source : full path of the mu-plugin directory or file
	 * @retrun boolean : True is success, False if failed
	 */
	public function make_mu_plugin_zip( $plugin_name, $source ){
		$plugin_dir = $this->upload_dir( '/mu-plugins/' );
		if ( !file_exists( $plugin_dir ) ) {
			$sucess = mkdir( $plugin_dir );
			if ( $sucess && !file_exists( $this->upload_dir( '/mu-plugins/index.php' ) ) ) {
				file_put_contents( $this->upload_dir( '/mu-plugins/index.php' ) , '<?php // silence is golden ' );
			}
			if ( $sucess == false ) return false;
		}
		$destination = $this->proper_slashing( $plugin_dir, $plugin_name . '.zip' );
		if ( file_exists( $source ) && is_dir( $source ) ) {
			return $this->zip_directory( $source, $destination );
		}elseif( file_exists( $source ) && ( substr(  $plugin_name, -4 ) == '.php' ) ){
			return $this->zip_file( $source, $destination );
		}else{
			return false;
		}
	
	}
	
	/*
	 * clear_upload_dir() will empty the $upload_dir and leave in it a blank index.php file
	 * @since 1.0.0
	 * @retrun void
	 */
	public function clear_upload_dir(){
		$this->empty_dir( $this->upload_dir );
		file_put_contents( $this->upload_dir . '/index.php', '<?php //silence is golden' );
	}
	
	/*
	 * zip_directory() will create a .zip file that will contain a chosen directory
	 * @since 1.0.0
	 * @param string $source : full path to directory to compress into a .zip file
	 * @param string $destination : the name of the .zip file to create
	 * @param boolean $overwrite : 	if set to true existing .zip files will be overwritten, 
	 * 								if set to false existing .zip files will be ingored
	 * @return boolean : true if sucess, false if failure
	 */
	public function zip_directory( $source, $destination, $overwrite = true ){
		if( file_exists( $destination ) ){
			if( $overwrite ){
				if( is_dir( $destination ) ) $this->empty_dir( $destination );
				else unlink( $destination ) ;
			}
			else return false ;
		}
		$dirname = explode( '/', $source );
		$dirname = $dirname[ count( $dirname ) - 1 ];
		$zip = new ZipArchive();
		$dir_stack = array( '');
		$sucess = $zip->open( $destination , ZipArchive::CREATE );
		if( $sucess ){
			while( count( $dir_stack ) > 0 ){
				$current = array_pop( $dir_stack );
				$current_dash = empty( $current ) ? '' : $current . '/';
				$scan = scandir( $source . '/' . $current );
				foreach( $scan as $file ){
					if( in_array( $file, array( '.', '..', '.svn' ) ) ) continue;
					if( is_dir( $source . '/' . $current_dash . $file ) ){
						array_push( $dir_stack, $current_dash . $file );
					}else{
						$zip->addFile( $source . '/' . $current_dash . $file, $dirname . '/' .$current_dash . $file );
					}
						
				}
			}
		}
		$zip->close();
		return $sucess;
	}
	
	/*
	 * zip_file() will create a .zip file that will contain a chosen file
	 * @since 1.0.0
	 * @param string $source : full path to file to compress into a .zip file
	 * @param string $destination : the name of the .zip file to create
	 * @param boolean $overwrite : 	if set to true existing .zip files will be overwritten,
	 * 								if set to false existing .zip files will be ingored
	 * @return boolean : true if sucess, false if failure
	 */
	public function zip_file( $source, $destination, $overwrite = true ){
		if( file_exists( $destination ) ){
			if( $overwrite ){
				if( is_dir( $destination ) ) $this->empty_dir( $destination );
				else unlink( $destination ) ;
			}
			else return false;
		}
		$filename = explode( '/', $source );
		$filename = $filename[ count( $filename ) - 1 ];
		$zip = new ZipArchive();
		$dir_stack = array( '');
		$sucess = $zip->open( $destination , ZipArchive::CREATE );
		if( $sucess ){
			$zip->addFile( $source , $filename );
		}
		$zip->close();
		return $sucess ;
	}
	
	/* 
	 * emplty_dir() recursively deletes all files and directories inside a chosen dirctory
	 * @since 1.0.0
	 * @param string $dir : full path to a directroy to empty
	 * @return void
	 */
	public function empty_dir( $dir ){
		if( in_array( $dir, array( '.', '..' ) ) ) return;
		if( substr( $dir, 0, strlen( $this->base_dir ) ) != $this->base_dir ){
			if( substr( $dir, 0, 1 ) != '/' ) $dir = '/' . $dir;
			$dir = $this->base_dir . $dir ;
		}
		if( file_exists( $dir ) && is_dir( $dir ) ) {
			$scan = scandir( $dir );
			foreach( $scan as $file ){
				if( in_array( $file, array( '.', '..' ) ) ) continue;
				if( is_dir( $dir . '/' . $file ) ){
					$this->empty_dir( $dir . '/' . $file );
					rmdir( $dir . '/' . $file );
				}else{
					unlink( $dir . '/' . $file );
				}
			}
		}
	}
	
	/*
	 * desactivation_hook() removes all files, directory and database settings created by the plugin
	 * @since 1.0.0
	 * @see Sirn_Theme_And_Plugin_Exporter::deactivation_hook()
	 */
	public static function desactivation_hook(){
		$I = self::instance();
		$I->empty_dir( $I->base_dir );
		rmdir( $I->base_dir );
		delete_option( 'sirn_theme_and_plugin_exporter_secure_dir_str' );
	}
	
}