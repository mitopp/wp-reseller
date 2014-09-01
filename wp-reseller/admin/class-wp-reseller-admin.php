<?php
/**
 * WP Reseller.
 *
 * @package   WP Reseller Admin
 * @author    Michael Topp <blog@codeschubser.de>
 * @license   GPL-2.0+
 * @copyright 2014 Michael Topp
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package WP Reseller Admin
 * @author  Michael Topp <blog@codeschubser.de>
 */
if ( ! class_exists( 'WP_Reseller_Admin' ) )
{
    class WP_Reseller_Admin
    {
        /**
         * Instance of this class.
         * @static
         * @access  protected
         * @since   0.0.1
         * @var     object
         */
        protected static $instance = null;
        /**
         * Slug of the plugin screen.
         * @access  protected
         * @since   0.0.1
         * @var     string
         */
        protected $plugin_screen_hook_suffix = null;

        /**
         * Initialize the plugin by loading admin scripts & styles and adding a
         * settings page and menu.
         * @access  private
         * @since   0.0.1
         * @return  void
         */
        private function __construct()
        {
            if( ! is_super_admin() )
            {
                return;
            }

            // Call $plugin_slug from public plugin class.
            $plugin = WP_Reseller::get_instance();
            $this->plugin_slug = $plugin->get_plugin_slug();

            // Load admin style sheet and JavaScript.
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

            // Add the options page and menu item.
            add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

            // Add an action link pointing to the options page.
            $plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
            add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

            // Define custom functionality.
            add_action( 'admin_footer_text', array( $this, 'action_admin_footer' ) );
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
            if( ! is_super_admin() )
            {
                return;
            }

            // If the single instance hasn't been set, set it now.
            if ( null === self::$instance )
            {
                self::$instance = new self;
            }

            return self::$instance;
        }
        /**
         * Register and enqueue admin-specific style sheet.
         * @access  public
         * @since   0.0.1
         * @return  null        Return early if no settings page is registered.
         */
        public function enqueue_admin_styles()
        {
            if ( ! isset( $this->plugin_screen_hook_suffix ) )
            {
                return;
            }

            $screen = get_current_screen();
            if ( $this->plugin_screen_hook_suffix == $screen->id )
            {
                wp_enqueue_style(
                    $this->plugin_slug .'-admin-styles',
                    plugins_url( 'assets/css/admin.css', __FILE__ ),
                    array(),
                    WP_Reseller::VERSION
                );
            }
        }
        /**
         * Register and enqueue admin-specific JavaScript in footer.
         * @access  public
         * @since   0.0.1
         * @return  null        Return early if no settings page is registered.
         */
        public function enqueue_admin_scripts()
        {
            if ( ! isset( $this->plugin_screen_hook_suffix ) )
            {
                return;
            }

            $screen = get_current_screen();
            if ( $this->plugin_screen_hook_suffix == $screen->id )
            {
                wp_enqueue_script(
                    $this->plugin_slug . '-admin-script',
                    plugins_url( 'assets/js/admin.js', __FILE__ ),
                    array( 'jquery' ),
                    WP_Reseller::VERSION,
                    true
                );
            }
        }
        /**
         * Register the administration menu for this plugin into
         * the WordPress Dashboard menu.
         * @access  public
         * @since   0.0.1
         * @return  void
         */
        public function add_plugin_admin_menu()
        {
            $this->plugin_screen_hook_suffix = add_options_page(
                'WP Reseller ' . __( 'Settings', $this->plugin_slug ),
                'WP Reseller',
                'manage_options',
                $this->plugin_slug,
                array( $this, 'display_plugin_admin_page' )
            );
        }
        /**
         * Render the settings page for this plugin.
         * @access  public
         * @since   0.0.1
         * @return  void
         */
        public function display_plugin_admin_page()
        {
            include_once( 'views/admin.php' );
        }
        /**
         * Add settings action link to the plugins page.
         * @access  public
         * @since   0.0.1
         * @param   array       $links
         * @return  array
         */
        public function add_action_links( $links )
        {
            return array_merge(
                array(
                    'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
                ),
                $links
            );
        }
        /**
         * Add a plugin notice to admin footer.
         * @access  public
         * @since   0.0.1
         * @return  void
         */
        public function action_admin_footer()
        {
            echo '<span id="footer-thankyou">Danke für das Vertrauen in <a href="https://wordpress.org/">WordPress</a>. | Customized with WP Reseller Version ' . WP_Reseller::VERSION . '</span>';
        }
    }
}