<?php
/**
 * WP Reseller.
 *
 * @package   WP Reseller
 * @author    Michael Topp <blog@codeschubser.de>
 * @license   GPL-2.0+
 * @copyright 2014 Michael TOpp
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * @package WP Reseller
 * @author  Michael Topp <blog@codeschubser.de>
 */
if ( ! class_exists( 'WP_Reseller' ) )
{
    class WP_Reseller
    {
        /**
         * Plugin version, used for cache-busting of style and script file references.
         * @since   0.0.1
         * @var     string
         */
        const VERSION = '0.0.1';
        /**
         * Unique identifier for your plugin.
         * The variable name is used as the text domain when internationalizing strings
         * of text. Its value should match the Text Domain file header in the main
         * plugin file.
         * @access  protected
         * @since   0.0.1
         * @var     string
         */
        protected $plugin_slug = 'wp-reseller';
        /**
         * Instance of this class.
         * @static
         * @access  protected
         * @since   0.0.1
         * @var     object
         */
        protected static $instance = null;

        /**
         * Initialize the plugin by setting localization and loading public scripts
         * and styles.
         * @access  private
         * @since   0.0.1
         * @return  void
         */
        private function __construct()
        {
            // Load plugin text domain
            add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

            // Activate plugin when new blog is added
            add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

            // Load public-facing style sheet and JavaScript.
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            // Remove generator version from head and source files
            add_filter( 'the_generator', '__return_null' );
            add_filter( 'style_loader_src', array( $this, 'filter_remove_version' ), 9999 );
            add_filter( 'script_loader_src', array( $this, 'filter_remove_version' ), 9999 );
        }
        /**
         * Return the plugin slug.
         * @access  public
         * @since   0.0.1
         * @return  Plugin slug variable.
         */
        public function get_plugin_slug()
        {
            return $this->plugin_slug;
        }
        /**
         * Return an instance of this class.
         * @static
         * @access  public
         * @since   0.0.1
         * @return  object      A single instance of this class.
         */
        public static function get_instance()
        {
            // If the single instance hasn't been set, set it now.
            if ( null === self::$instance )
            {
                self::$instance = new self;
            }

            return self::$instance;
        }
        /**
         * Fired when the plugin is activated.
         * @static
         * @access  public
         * @since   0.0.1
         * @param   boolean     $network_wide   True if WPMU superadmin uses
         *                                      "Network Activate" action, false
         *                                      if WPMU is disabled or plugin is
         *                                      activated on an individual blog.
         * @return  void
         */
        public static function activate( $network_wide )
        {
            if ( function_exists( 'is_multisite' ) && is_multisite() )
            {
                if ( $network_wide  )
                {
                    // Get all blog ids
                    $blog_ids = self::get_blog_ids();

                    foreach ( $blog_ids as $blog_id )
                    {
                        switch_to_blog( $blog_id );
                        self::single_activate();

                        restore_current_blog();
                    }
                }
                else
                {
                    self::single_activate();
                }
            }
            else
            {
                self::single_activate();
            }
        }
        /**
         * Fired when the plugin is deactivated.
         * @static
         * @access  public
         * @since   0.0.1
         * @param   boolean     $network_wide   True if WPMU superadmin uses
         *                                      "Network Deactivate" action,
         *                                      false if WPMU is disabled or
         *                                      plugin is deactivated on an
         *                                      individual blog.
         * @return  void
         */
        public static function deactivate( $network_wide )
        {
            if ( function_exists( 'is_multisite' ) && is_multisite() )
            {
                if ( $network_wide )
                {
                    // Get all blog ids
                    $blog_ids = self::get_blog_ids();

                    foreach ( $blog_ids as $blog_id )
                    {
                        switch_to_blog( $blog_id );
                        self::single_deactivate();

                        restore_current_blog();
                    }
                }
                else
                {
                    self::single_deactivate();
                }
            }
            else
            {
                self::single_deactivate();
            }
        }
        /**
         * Fired when a new site is activated with a WPMU environment.
         * @access  public
         * @since   0.0.1
         * @param   integer     $blog_id        ID of the new blog.
         * @return  void
         */
        public function activate_new_site( $blog_id )
        {
            if ( 1 !== did_action( 'wpmu_new_blog' ) )
            {
                return;
            }

            switch_to_blog( $blog_id );
            self::single_activate();
            restore_current_blog();
        }
        /**
         * Get all blog ids of blogs in the current network that are:
         * - not archived
         * - not spam
         * - not deleted
         * @static
         * @access  private
         * @since   0.0.1
         * @global  $wpdb       WordPress database object
         * @return  array|false The blog ids, false if no matches.
         */
        private static function get_blog_ids()
        {
            global $wpdb;

            // get an array of blog ids
            $sql = "SELECT blog_id FROM $wpdb->blogs
                    WHERE archived = '0' AND spam = '0'
                    AND deleted = '0'";

            return $wpdb->get_col( $sql );
        }

        /**
         * Fired for each blog when the plugin is activated.
         * @static
         * @access  private
         * @since   0.0.1
         * @return  void
         */
        private static function single_activate() {}
        /**
         * Fired for each blog when the plugin is deactivated.
         * @static
         * @access  private
         * @since   0.0.1
         * @return  void
         */
        private static function single_deactivate() {}
        /**
         * Load the plugin text domain for translation.
         * @access  public
         * @since   0.0.1
         * @return  void
         */
        public function load_plugin_textdomain()
        {
            load_plugin_textdomain(
                $this->plugin_slug,
                false,
                basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/'
            );
        }
        /**
         * Register and enqueue public-facing style sheet.
         * @access  public
         * @since   0.0.1
         * @return  void
         */
        public function enqueue_styles()
        {
            wp_enqueue_style(
                $this->plugin_slug . '-plugin-styles',
                plugins_url( 'assets/css/public.css', __FILE__ ),
                array(),
                self::VERSION
            );
        }
        /**
         * Register and enqueues public-facing JavaScript files in footer.
         * @access  public
         * @since   0.0.1
         * @return  void
         */
        public function enqueue_scripts()
        {
            wp_enqueue_script(
                $this->plugin_slug . '-plugin-script',
                plugins_url( 'assets/js/public.js', __FILE__ ),
                array( 'jquery' ),
                self::VERSION,
                true
            );
        }
        /**
         * Remove the WordPress version from RSS feed
         * @access  public
         * @since   0.0.1
         * @param   string      $src
         * @return  string
         */
        public function filter_remove_version( $src )
        {
            if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) )
                $src = remove_query_arg( 'ver', $src );
            return $src;
        }
    }
}