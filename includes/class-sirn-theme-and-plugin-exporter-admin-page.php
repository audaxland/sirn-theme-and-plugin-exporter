<?php

/*
 * Sirn_Theme_And_Plugin_Exporter_Admin_Page controls everything that happens on the plugins admin page
 * @since 1.0.0
 */
class Sirn_Theme_And_Plugin_Exporter_Admin_Page
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
	 * @since 1.0.0
	 * @return object : instance of this class
	 */
	public function __construct() {
		if ( is_null( self::$_instance ) ) self::$_instance = $this;
	}
	
	/* 
	 * init() loads the main hooks for the plugin to work
	 * @hook action 'init' : init() is executed during the 'init' hook 
	 * @since 1.0.0
	 */
	public static function init() {
		$I = self::instance();
		
		// set the admin menu for the plugin : menu 'Plugins->Sir Plugin Exporter'
		add_action( 'admin_menu', array( $I, 'admin_menu' ) ) ;
		
		// includes the file for the class : Sirn_Theme_And_Plugin_Exporter_Files_Manager
		add_action( 'load-plugins_page_sirn-theme-and-plugin-exporter', array( $I, 'includes' ), 0 );
		
		// processes the form on the admin page
		add_action( 'load-plugins_page_sirn-theme-and-plugin-exporter', array( $I, 'process_form_add' ) );
		add_action( 'load-plugins_page_sirn-theme-and-plugin-exporter', array( $I, 'process_form_delete' ) );
		
		// adds custom style for the plugins admin page
		add_action( 'admin_enqueue_scripts', array( $I, 'admin_enqueue_scripts' ) ) ;
	}
	
	/* 
	 * registration of the admin menu for the plugin : menu 'Plugins->Sir Plugin Exporter'
	 * @since 1.0.0
	 * @hook action 'admin_menu' : admin_menu() is executed during the 'admin_menu' action hook
	 * @return void
	 */
	public function admin_menu() {
		add_plugins_page(	'Sirmons Theme and Plugin Exporter', 
							'Sir Plugin Exporter', 
							'manage_options', 
							'sirn-theme-and-plugin-exporter', 
							array( $this, 'display_plugin_page' )  );
	}
	
	/* 
	 * includes the file containing the classes we need
	 * @since 1.0.0
	 * @hook action 'load-plugins_page_sirn-theme-and-plugin-exporter' : includes() is executed during the 'load-plugins_page_sirn-theme-and-plugin-exporter' action hook
	 * @return void
	 */
	public function includes() {
		// includes the file containing the class Sirn_Theme_And_Plugin_Exporter_Files_Manager
		require_once( Sirn_Theme_And_Plugin_Exporter::get_path( '/includes/class-sirn-theme-and-plugin-exporter-files-manager.php' ) );
	}
	
	
	/*
	 * enqueues custom styling for the plugin's admin page
	 * @since 1.0.0
	 * @hook action 'admin_enqueue_scripts' : admin_enqueue_scripts() is executed during the 'admin_enqueue_scripts' action hook
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook == 'plugins_page_sirn-theme-and-plugin-exporter' ){
			wp_register_style( 'sirn_theme_and_plugin_exporter_admin_css', 
								Sirn_Theme_And_Plugin_Exporter::get_url( '/css/admin-page.css' ), false, '1.0.0');
			wp_enqueue_style( 'sirn_theme_and_plugin_exporter_admin_css' );
		}
	}

	/*
	 * Processes the form that will create the .zip files of the themes and plugins
	 * @since 1.0.0
	 * @hook action 'load-plugins_page_sirn-theme-and-plugin-exporter' : executed during the 'load-plugins_page_sirn-theme-and-plugin-exporter' action hook
	 * @return void
	 */
	public function process_form_add() {
		// check nonce
		if ( !empty( $_POST['sirn_theme_and_plugin_exporter_add_nonce'] ) 
				&& check_admin_referer( 'sirn_theme_and_plugin_exporter_add_nonce', 'sirn_theme_and_plugin_exporter_add_nonce' ) ) {
			
			// the creation of the .zip files and their directories is done from the 
			// Sirn_Theme_And_Plugin_Exporter_Files_Manager class
			$files_manager = Sirn_Theme_And_Plugin_Exporter_Files_Manager::instance();
			
			// process the themes
			if ( isset( $_POST[ 'sirn_theme_and_plugin_exporter_theme' ] ) && is_array( $_POST['sirn_theme_and_plugin_exporter_theme'] ) ) {
				$themes_dir = get_theme_root();
				foreach ( $_POST[ 'sirn_theme_and_plugin_exporter_theme' ] as $theme => $value ){
					if ( ( $value == 'add' ) && file_exists( $themes_dir . '/' . $theme ) ){
						$files_manager->make_theme_zip( $theme, $themes_dir . '/' . $theme );
					}
				}		
			}
			
			// process the normal plugins
			if ( isset( $_POST['sirn_theme_and_plugin_exporter_plugins'] ) && is_array( $_POST['sirn_theme_and_plugin_exporter_plugins'] ) ) {
				$plugins_dir = WP_PLUGIN_DIR . '/' ;
				foreach ( $_POST[ 'sirn_theme_and_plugin_exporter_plugins' ] as $plugin => $value){
					if ( ( $value == 'add' ) && file_exists( $plugins_dir . $plugin )  ) {
						$files_manager->make_plugin_zip( $plugin, $plugins_dir . $plugin  );
					}	
				}
			}
			
			// process the mu-plugins
			if ( isset( $_POST['sirn_theme_and_plugin_exporter_mu_plugins'] ) && is_array( $_POST['sirn_theme_and_plugin_exporter_mu_plugins'] ) ) {
				$plugins_dir = WPMU_PLUGIN_DIR . '/' ;
				foreach ( $_POST[ 'sirn_theme_and_plugin_exporter_mu_plugins' ] as $plugin => $value) {
					if ( ( $value == 'add' ) && file_exists( $plugins_dir . $plugin )  ) {
						$files_manager->make_mu_plugin_zip( $plugin, $plugins_dir . $plugin  );
					}
				}
			}
		}
	}
	
	/*
	 * Processes the form that will all the previously created .zip files
	 * @since 1.0.0
	 * @hook action 'load-plugins_page_sirn-theme-and-plugin-exporter' : executed during the 'load-plugins_page_sirn-theme-and-plugin-exporter' action hook
	 * @return void
	 */
	public function process_form_delete() {
		// check nonce
		if ( !empty( $_POST['sirn_theme_and_plugin_exporter_delete_nonce'] )
				&& check_admin_referer( 'sirn_theme_and_plugin_exporter_delete_nonce', 'sirn_theme_and_plugin_exporter_delete_nonce' ) ) {
			
			// the deletion of the .zip files and their directories is done from the
			// Sirn_Theme_And_Plugin_Exporter_Files_Manager class
			$files_manager = Sirn_Theme_And_Plugin_Exporter_Files_Manager::instance();
			$files_manager->clear_upload_dir();
		}
	}
	
	/*
	 * The callback method for displaying the plugins admin page
	 * @since 1.0.0
	 * @see $this->admin_menu()
	 * @return void
	 */
	public function display_plugin_page() {
		// $files_manager allows checking that the upload dir is writable
		$files_manager = Sirn_Theme_And_Plugin_Exporter_Files_Manager::instance();
		?>
			<div class="wrap sirn-theme-and-plugin-exporter-admin-wrap">
				<h1>Sirmons Theme and Plugin Exporter</h1>
				<p class="plugin-intro">
					<?php esc_html_e( 'This plugin allows you to create .zip files from your themes and plugins which will be ready to use for installing them on another WordPress website', 'sirn-theme-and-plugin-exporter' ); ?>
				</p>
				<p class="plugin-intro">
					<?php esc_html_e( 'Select below the themes and plugin that you wish to export and then click on "Create .zip files" to generate the files.', 'sirn-theme-and-plugin-exporter' ); ?>
				</p>
				<p class="plugin-intro">
					<?php esc_html_e( 'When generated, the .zip files are stored into your upload directory and the links to those files are listed below. ', 'sirn-theme-and-plugin-exporter' ); ?>
					<?php esc_html_e( 'You can then download those files by clicking on the corresponding links at the bottom of this page.', 'sirn-theme-and-plugin-exporter' ); ?>
				</p>
				<p class="plugin-intro">
					<?php esc_html_e( 'Once you\'re done downloading your .zip files you can delete theme form the server by clicking on "Delete all .zip files".', 'sirn-theme-and-plugin-exporter' ); ?>
				</p>
				<hr />
				<?php if ( ! is_writable( $files_manager->upload_dir() ) ) : ?>
					<strong>
						<?php _e( 'The upload directory is not writable on this server, you must make it writable for this plugin to work', 'sirn-theme-and-plugin-exporter' ) ;?>
					</strong>
				<?php else : ?>
					<h2><?php _e( 'Select which themes and plugins you wish to export', 'sirn-theme-and-plugin-exporter' ) ;?></h3>
					<hr />
					<form method="post">
						<?php wp_nonce_field( 'sirn_theme_and_plugin_exporter_add_nonce', 'sirn_theme_and_plugin_exporter_add_nonce' );?>
						<div class="checkbox-items-list" >
							<h3><?php _e( 'Select the themes you want to export', 'sirn-theme-and-plugin-exporter' ) ;?></h3>
							<?php echo $this->list_themes(); ?>
						</div>
						<hr />
						<div class="checkbox-items-list">
							<h3><?php _e( 'Select the plugins you want to export', 'sirn-theme-and-plugin-exporter' ) ;?></h3>
							<?php echo $this->list_plugins(); ?>
						</div>
						<hr>
						<div class="checkbox-items-list">
							<h3><?php _e( 'Select the mu-plugins you want to export', 'sirn-theme-and-plugin-exporter' ) ;?></h3>
							<?php echo $this->list_mu_plugins(); ?>
						</div>
						<hr>

						<input type="submit" name="sirn-theme-and-plugin-exporter-form-create-zip" 
						value="<?php esc_attr_e( 'Create .zip files', 'sirn-theme-and-plugin-exporter' ) ; ?>" class="button button-primary" />

					</form>
					<hr>
				<?php endif; ?>
				<hr>
				<h2><?php _e( 'Download your themes and plugins in their .zip format below', 'sirn-theme-and-plugin-exporter' ) ; ?></h2>
				<div class="link-items-list">
					<h3><?php _e( 'List of theme .zip files available', 'sirn-theme-and-plugin-exporter' ); ?></h3>
					<?php echo $this->list_zip_files( 'themes'); ?>
				</div>
				<hr>
				<div class="link-items-list">
					<h3><?php _e( 'List of plugin .zip files available', 'sirn-theme-and-plugin-exporter' ); ?></h3>
					<h4><?php _e( 'The normal plugins', 'sirn-theme-and-plugin-exporter' ); ?></h4>
					<?php echo $this->list_zip_files( 'plugins'); ?>		
					<h4><?php _e( 'The mu-plugins', 'sirn-theme-and-plugin-exporter' );?></h4>
					<?php echo $this->list_zip_files( 'mu-plugins'); ?>				
				</div>
				<hr>
				<form method="post">
					<?php wp_nonce_field( 'sirn_theme_and_plugin_exporter_delete_nonce', 'sirn_theme_and_plugin_exporter_delete_nonce' );?>
					<input type="submit" name="sirn-theme-and-plugin-exporter-form-delete-zip" 
						value="<?php esc_attr_e( 'Delete all .zip files', 'sirn-theme-and-plugin-exporter' ) ; ?>" class="button button-primary" />
				</form>
			
			</div>
		<?php
		}
		
		/*
		 * list_themes() gives the list of all themes currently installed on your WordPress
		 * @since 1.0.0
		 * @called_from $this->display_plugin_page()
		 * @return string : html list of checkboxes to choose the themes to export
		 */
		public function list_themes() {
			$themes_dir = get_theme_root();
			$dir_content = scandir( $themes_dir );
			$output = '';
			foreach ( $dir_content as $file ) {
				if ( is_dir( $themes_dir . '/' . $file ) && !  in_array( $file, array( '.', '..' ) ) ) {
					$output .= '<label><input type="checkbox" name="sirn_theme_and_plugin_exporter_theme[' . $file . ']" value="add" />';
					$output .= $file . "</label><br> \n";
				}
			}
			return $output;
		}
		
		/*
		 * list_plugins() gives the list of all plugins currently installed on your WordPress
		 * @since 1.0.0
		 * @called_from $this->display_plugin_page()
		 * @return string : html list of checkboxes to choose the plugins to export
		 */
		public function list_plugins() {
			$plugins_dir = WP_PLUGIN_DIR . '/' ;
			$dir_content = scandir( $plugins_dir );
			$output = '';
			foreach ( $dir_content as $file ) {
				if ( is_dir( $plugins_dir . $file ) && !  in_array( $file, array( '.', '..' ) ) ){
					$output .= '<label><input type="checkbox" name="sirn_theme_and_plugin_exporter_plugins[' . $file . ']" value="add" />';
					$output .= $file . "</label><br> \n";
				} elseif ( substr( $file, -4 ) == '.php' && $file != 'index.php' ) {
					$output .= '<label><input type="checkbox" name="sirn_theme_and_plugin_exporter_plugins[' . $file . ']" value="add" />';
					$output .= $file . "</label><br> \n";
				}
			}
			if ( empty( $output ) ) {
				return '<em class="no-item-available">' . __( 'No plugin on your WordPress', 'sirn-theme-and-plugin-exporter' ) . '</em>';
			}
			return $output;
		}
		
		/*
		 * list_mu_plugins() gives the list of all plugins currently installed in the mu-plugin directory on your WordPress
		 * @since 1.0.0
		 * @called_from $this->display_plugin_page()
		 * @return string : html list of checkboxes to choose the plugins from the mu-plugin directory to export
		 */
		public function list_mu_plugins() {
			$plugins_dir = WPMU_PLUGIN_DIR . '/' ;
			$output = '';
			if ( file_exists( $plugins_dir ) && is_dir( $plugins_dir ) && ( WP_PLUGIN_DIR != WPMU_PLUGIN_DIR) ) {
				$dir_content = scandir( $plugins_dir );
				foreach ( $dir_content as $file ) {
					if ( is_dir( $plugins_dir . $file ) && !  in_array( $file, array( '.', '..' ) ) ) {
						$output .= '<label><input type="checkbox" name="sirn_theme_and_plugin_exporter_mu_plugins[' . $file . ']" value="add" />';
						$output .= $file . "</label><br> \n";
					} elseif ( substr( $file, -4 ) == '.php' && $file != 'index.php' ) {
						$output .= '<label><input type="checkbox" name="sirn_theme_and_plugin_exporter_mu_plugins[' . $file . ']" value="add" />';
						$output .= $file . "</label><br> \n";
					}
				}
			}
			if ( empty( $output ) ) {
				return '<em class="no-item-available">' . __( 'No mu-plugin on your WordPress', 'sirn-theme-and-plugin-exporter' ) . '</em>';
			}
			return $output;
		}
		
		/*
		 * list_zip_files() gives the list of .zip files created by the plugin
		 * @since 1.0.0
		 * @called_from $this->display_plugin_page()
		 * @param string $dir : choose from 'themes/plugins/mu-plugins' : determines which .zip files are listed
		 * @return string : html list of links to the .zip files created by this plugin
		 */
		public function list_zip_files( $dir = '' ) {
			$files_manager = Sirn_Theme_And_Plugin_Exporter_Files_Manager::instance();
			$path = $files_manager->upload_dir( $dir );
			$output = '';
			if ( file_exists( $path ) && is_dir( $path ) ) {
				$files = scandir( $path );
				foreach ( $files as $file ) {
					if( '.zip' == substr( $file, -4 ) )	{
						$url = $files_manager->proper_slashing( $files_manager->upload_url( $dir  ), $file ) ;
						$output .= '<a href="' . $url . '">' . $file . '</a><br />';
					}
				}	
			}
			if ( empty( $output ) ) {
				return '<em class="no-item-available">' . __( 'No file available', 'sirn-theme-and-plugin-exporter' ) . '</em>';
			}
			return $output;
		}
}