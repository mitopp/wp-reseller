<?php
/**
 * WP Reseller.
 *
 * @package   WP Reseller Security
 * @author    Michael Topp <blog@codeschubser.de>
 * @license   GPL-2.0+
 * @copyright 2014 Michael Topp
 */

/**
 * @package WP Reseller Security
 * @author  Michael Topp <blog@codeschubser.de>
 */
if ( ! class_exists( 'WP_Reseller_Security' ) )
{
    class WP_Reseller_Security
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
         * Initialize the security class.
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
         * Remove the WordPress version from RSS feed.
         * @static
         * @access  public
         * @since   0.0.1
         * @param   string      $src
         * @return  string
         */
        public static function filter_remove_version( $src )
        {
            if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) )
                $src = remove_query_arg( 'ver', $src );
            return $src;
        }
        /**
         * Remove generator meta tag from head.
         * @static
         * @access  public
         * @since   0.0.1
         * @return  null
         */
        public static function filter_remove_generator()
        {
            return;
        }
    }
}