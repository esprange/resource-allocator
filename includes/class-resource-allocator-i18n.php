<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://www.sprako.nl/wordpress
 * @since      1.0.0
 *
 * @package    Resource_Allocator
 * @subpackage Resource_Allocator/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Resource_Allocator
 * @subpackage Resource_Allocator/includes
 * @author     Eric Sprangers <e.sprangers@sprako.nl>
 */
class Resource_Allocator_i18n {

        private $domain;
    
        public function __construct( $plugin_name ) {
            $this->domain = $plugin_name;
        }

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			$this->domain,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
