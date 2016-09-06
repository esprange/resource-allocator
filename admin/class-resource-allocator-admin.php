<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.sprako.nl/wordpress
 * @since      1.0.0
 *
 * @package    Resource_Allocator
 * @subpackage Resource_Allocator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Resource_Allocator
 * @subpackage Resource_Allocator/admin
 * @author     Eric Sprangers <e.sprangers@sprako.nl>
 */
class Resource_Allocator_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

        /**
	 * The optional message to display to the admin user.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $message      The optional message to be displayed.
	 */
	private $message;
        
        /**
	 * The options for the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array     $options      The option settings used in plugin.
	 */
	private $options = [];
        
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
                $this->message = "";
                $this->options = get_option( $this->plugin_name . '_options');
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/resource-allocator-admin.css', [], $this->version, 'all' );
                wp_enqueue_style( 'time_entry', plugin_dir_url( dirname(__FILE__) ) . 'vendor/timeentry/jquery.timeentry.css', [] );
                wp_enqueue_style( 'wp-color-picker' );                
        }

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/resource-allocator-admin.js', [ 'jquery', 'wp-color-picker' ], $this->version, false );
                wp_localize_script( $this->plugin_name, $this->plugin_name, [
                    'fieldname'=> __( 'Custom field name', $this->plugin_name ),
                    'fieldtype'=> __( 'Field type', $this->plugin_name ),
                    'text'=> __( 'Text', $this->plugin_name ),
                    'number'=> __( 'Numeric', $this->plugin_name ),
                    'hours'=> __( 'hours', $this->plugin_name ),
                    'days'=> __( 'days', $this->plugin_name ),
                    'timeformat'=> get_option('time_format'),
//                    'dateformat'=> get_option('date_format'),
                    ]
                );
                wp_enqueue_script ( 'time_entry_plugin', plugin_dir_url( dirname(__FILE__) ) . 'vendor/timeentry/jquery.plugin.js', array( 'jquery' ) );
                wp_enqueue_script ( 'time_entry', plugin_dir_url( dirname(__FILE__) ) . 'vendor/timeentry/jquery.timeentry.js', array( 'jquery' ) );
                if ( $this->plugin_name . '-about' === get_current_screen() ) {
                    wp_enqueue_script ( 'postbox' ); // only used in about tab
                }
	}

        /**
        * Add settings action link to the plugins page.
        *
        * @since    1.0.0
        */
        public function add_action_links( $links ) {
                /*
                *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
                */
                $settings_link = [ 'settings' =>
                    '<a href="' . admin_url( '?page=' . $this->plugin_name . '-options' ) . '">' . __('Options', $this->plugin_name) . '</a>',
          //          '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
                ];
                return array_merge(  $settings_link, $links );
       }

       /**
        * Add settings field, wrapper for wp function.
        *
        * @since    1.0.0
        */
       private function add_settings_field ( $field, $fieldtext ) {
                add_settings_field(
                        $this->plugin_name . '_' . $field,
                        $fieldtext,
                        [ $this, $field . '_callback' ],
                        $this->plugin_name,
                        $this->plugin_name . '_options',
                        [ 'label_for' => $this->plugin_name . '_options[' . $field . ']' ]
                );
       }
       /**
        * Register settings sections for  plugin options page.
        *
        * @since    1.0.0
        */
        public function register_settings() {
                add_settings_section( 
                        $this->plugin_name . '_options', 
                        __( 'Options', $this->plugin_name ), 
                        [ $this, 'introduction_callback'], 
                        $this->plugin_name 
                );

                $this->add_settings_field( 'df_basis', __( 'Default basis', $this->plugin_name ) );
                $this->add_settings_field( 'df_minduration_hrs', __( 'Default minimum duration for hourly allocated resources', $this->plugin_name ) );
                $this->add_settings_field( 'df_maxduration_hrs', __( 'Default maximum duration for hourly allocated resources', $this->plugin_name ) );
                $this->add_settings_field( 'df_minduration_days', __( 'Default minimum duration for daily allocated resources', $this->plugin_name ) );
                $this->add_settings_field( 'df_maxduration_days', __( 'Default maximum duration for daily allocated resources', $this->plugin_name ) );
                $this->add_settings_field( 'color_available', __( 'Resource available color', $this->plugin_name ) );
                $this->add_settings_field( 'color_unavailable', __( 'Resource unavailable color', $this->plugin_name ) );
                $this->add_settings_field( 'color_allocated', __( 'Resource allocated color', $this->plugin_name ) );

                register_setting( $this->plugin_name, $this->plugin_name . '_options', [ $this, 'sanitize_options' ] );        
        }
        
       /**
        * Callback function to display introduction text on options screen
        * 
        * @since    1.0.0
        */
        public function introduction_callback () {
                echo '<p>' . __( 'The Resource Allocator plugin has some options which you can change if needed', $this->plugin_name ) . '</p>';
        }

       /**
        * Callback function to display input field for allocation basis
        * 
        * @since    1.0.0
        */
        public function df_basis_callback () {
                echo '<fieldset><label>
                        <input type="radio" name="' . $this->plugin_name . '_options[df_basis]" id="' . $this->plugin_name . '_options[df_basis]" value="hours" ' . 
                        checked($this->options[ 'df_basis' ], 'hours', false ) . ' >' . __( 'Hours', $this->plugin_name ) . '</label><br><label>
                        <input type="radio" name="' . $this->plugin_name . '_options[df_basis]" value="days" ' . checked($this->options[ 'df_basis' ], 'days', false ) . ' >' .
                        __( 'Days', $this->plugin_name ) .
                        '</label></fieldset>';
        }
       
       /**
        * Callback function to display input field for minimum duration hours
        * 
        * @since    1.0.0
        */
        public function df_minduration_hrs_callback () {
                $minduration = $this->options[ 'df_minduration_hrs' ];
                echo '<input type="number" name="' . $this->plugin_name . '_options[df_minduration_hrs]" id="' . $this->plugin_name . '_options[df_minduration_hrs]" '
                        . 'value="' . $minduration . '" min="1" max="24" > '. __( 'hours', $this->plugin_name );
        }
       
       /**
        * Callback function to display input field for maximum duration hours
        * 
        * @since    1.0.0
        */
        public function df_maxduration_hrs_callback () {
                $maxduration = $this->options[ 'df_maxduration_hrs' ];
                echo '<input type="number" name="' . $this->plugin_name . '_options[df_maxduration_hrs]" id="' . $this->plugin_name . '_options[df_maxduration_hrs]" '
                        . 'value="' . $maxduration . '" min="1" max="24" > '. __( 'hours', $this->plugin_name );
        }
        
       /**
        * Callback function to display input field for minimum duration days
        * 
        * @since    1.0.0
        */
        public function df_minduration_days_callback () {
                $minduration = $this->options[ 'df_minduration_days' ];
                echo '<input type="number" name="' . $this->plugin_name . '_options[df_minduration_days]" id="' . $this->plugin_name . '_options[df_minduration_days]" '
                        . 'value="' . $minduration . '" min="1" max="999" > '. __( 'days', $this->plugin_name );
        }
       
       /**
        * Callback function to display input field for maximum duration days
        * 
        * @since    1.0.0
        */
        public function df_maxduration_days_callback () {
                $maxduration = $this->options[ 'df_maxduration_days' ];
                echo '<input type="number" name="' . $this->plugin_name . '_options[df_maxduration_days]" id="' . $this->plugin_name . '_options[df_maxduration_days]" '
                        . 'value="' . $maxduration . '" min="1" max="999" > '. __( 'days', $this->plugin_name );
        }
        
       /**
        * Callback function to display color picker field for color when resource is available
        * 
        * @since    1.0.0
        */
        public function color_available_callback () {
                $color = $this->options[ 'color_available' ];
                echo '<input class="colorfield" type="text" name="' . $this->plugin_name . '_options[color_available]" id="' . $this->plugin_name . '_options[color_available]" value="' . $color . '" >';
        }
        
       /**
        * Callback function to display color picker field for color when resource is unavailable
        * 
        * @since    1.0.0
        */
        public function color_unavailable_callback () {
                $color = $this->options[ 'color_unavailable' ];
                echo '<input class="colorfield" type="text" name="' . $this->plugin_name . '_options[color_unavailable]" id="' . $this->plugin_name . '_options[color_unavailable]" value="' . $color . '" >';
        }
        
       /**
        * Callback function to display color picker field for color when resource is allocated
        * 
        * @since    1.0.0
        */
        public function color_allocated_callback () {
                $color = $this->options[ 'color_allocated' ];
                echo '<input class="colorfield" type="text" name="' . $this->plugin_name . '_options[color_allocated]" id="' . $this->plugin_name . '_options[color_allocated]" value="' . $color . '" >';
        }
        
       /**
        * Callback function to validate and sanitize the options entered in options screen
        *  
        * @since    1.0.0
        */
        public function sanitize_options( $fields ) {
                $valid_fields = [];
                $success = true;

                if ( in_array( $fields[ 'df_basis' ], [ 'hours', 'days' ], true ) ) {
                    $valid_fields[ 'df_basis' ] = $fields[ 'df_basis'];
                } else {
                    add_settings_error( $this->plugin_name . '-options', $this->plugin_name . '_error', __( 'Select a valid allocation basis', $this->plugin_name ), 'error' ); 
                    $valid_fields[ 'df_basis' ] = $this->options[ 'df_basis' ];
                    $success = false;
                }
                if ( intval ( $fields[ 'df_minduration_hrs' ] ) > 0 &&
                     intval ( $fields[ 'df_minduration_hrs' ] ) <= 24 &&
                     intval ( $fields[ 'df_minduration_hrs' ] ) <= intval ( $fields [ 'df_maxduration_hrs'] ) ) {
                    $valid_fields[ 'df_minduration_hrs' ] = $fields[ 'df_minduration_hrs'];
                } else {
                    add_settings_error( $this->plugin_name . '-options', $this->plugin_name . '_error', __( 'Enter a minimum duration between 1 and 24, equal or less the maximum duration', $this->plugin_name ), 'error' ); 
                    $valid_fields[ 'df_minduration_hrs' ] = min ($this->options[ 'df_minduration_hrs' ], $this->options[ 'df_maxduration_hrs' ] );
                    $success = false;
                }
                if ( intval ( $fields[ 'df_maxduration_hrs' ] ) > 0 &&
                     intval ( $fields[ 'df_maxduration_hrs' ] ) <= 24 &&
                     intval ( $fields[ 'df_maxduration_hrs' ] ) >= intval ( $fields [ 'df_minduration_hrs'] ) ) {
                    $valid_fields[ 'df_maxduration_hrs' ] = $fields[ 'df_maxduration_hrs'];
                } else {
                    add_settings_error( $this->plugin_name . '-options', $this->plugin_name . '_error', __( 'Enter a maximum duration between 1 and 24, equal or larger the minimum duration', $this->plugin_name ), 'error' ); 
                    $valid_fields[ 'df_maxduration_hrs' ] = max ($this->options[ 'df_maxduration_hrs' ], $this->options[ 'df_minduration_hrs' ]);
                    $success = false;
                }
                if ( intval ( $fields[ 'df_minduration_days' ] ) > 0 &&
                     intval ( $fields[ 'df_minduration_days' ] ) <= 999 &&
                     intval ( $fields[ 'df_minduration_days' ] ) <= intval ( $fields [ 'df_maxduration_days'] ) ) {
                    $valid_fields[ 'df_minduration_days' ] = $fields[ 'df_minduration_days'];
                } else {
                    add_settings_error( $this->plugin_name . '-options', $this->plugin_name . '_error', __( 'Enter a minimum duration between 1 and 999, equal or less the maximum duration', $this->plugin_name ), 'error' ); 
                    $valid_fields[ 'df_minduration_days' ] = min ($this->options[ 'df_minduration_days' ], $this->options[ 'df_maxduration_days' ] );
                    $success = false;
                }
                if ( intval ( $fields[ 'df_maxduration_days' ] ) > 0 &&
                     intval ( $fields[ 'df_maxduration_days' ] ) <= 999 &&
                     intval ( $fields[ 'df_maxduration_days' ] ) >= intval ( $fields [ 'df_minduration_days'] ) ) {
                    $valid_fields[ 'df_maxduration_days' ] = $fields[ 'df_maxduration_days'];
                } else {
                    add_settings_error( $this->plugin_name . '-options', $this->plugin_name . '_error', __( 'Enter a maximum duration between 1 and 999, equal or larger the minimum duration', $this->plugin_name ), 'error' ); 
                    $valid_fields[ 'df_maxduration_days' ] = max ($this->options[ 'df_maxduration_days' ], $this->options[ 'df_minduration_days' ]);
                    $success = false;
                }
                if ( preg_match( '/^#[a-f0-9]{6}$/i', $fields[ 'color_available' ] ) ) {
                    $valid_fields[ 'color_available' ] = $fields[ 'color_available' ];
                } else {
                    add_settings_error( $this->plugin_name . '-options', $this->plugin_name . '_error', __( 'Select a valid color', $this->plugin_name ), 'error' ); 
                    $valid_fields[ 'color_available' ] = $this->options[ 'color_available' ];                
                    $success = false;
                }
                if ( preg_match( '/^#[a-f0-9]{6}$/i', $fields[ 'color_unavailable' ] ) ) {
                    $valid_fields[ 'color_unavailable' ] = $fields[ 'color_unavailable' ];
                } else {
                    add_settings_error( $this->plugin_name . '-options', $this->plugin_name . '_error', __( 'Select a valid color', $this->plugin_name ), 'error' ); 
                    $valid_fields[ 'color_unavailable' ] = $this->options[ 'color_unavailable' ];                
                    $success = false;
                }
                if ( preg_match( '/^#[a-f0-9]{6}$/i', $fields[ 'color_allocated' ] ) ) {
                    $valid_fields[ 'color_allocated' ] = $fields[ 'color_allocated' ];
                } else {
                    add_settings_error( $this->plugin_name . '-options', $this->plugin_name . '_error', __( 'Select a valid color', $this->plugin_name ), 'error' ); 
                    $valid_fields[ 'color_allocated' ] = $this->options[ 'color_allocated' ];                
                    $success = false;
                }
                if ($success) {
                    if ( !array_search( $this->plugin_name . '_updated', get_settings_errors( $this->plugin_name . '-options' ))) {
                        add_settings_error( $this->plugin_name . '-options', $this->plugin_name . '_updated', __( 'The options have been stored', $this->plugin_name ), 'updated' ); 
                    }
                }
                return apply_filters( 'sanitize_options', $valid_fields, $fields);
        }
        
        /**
         * Render the list resources page for this plugin.
         *
         * @since    1.0.0
         */
        public function display_list_page() {
                include_once( 'partials/resource-allocator-admin-display-list.php' );
        }

        /**
         * Render the add new resource page for this plugin.
         *
         * @since    1.0.0
         */
        public function display_add_page() {
                include_once( 'partials/resource-allocator-admin-display-edit.php' );
        }

        /**
         * Render the edit resource page for this plugin.
         *
         * @since    1.0.0
         */
        public function display_edit_page() {
                include_once( 'partials/resource-allocator-admin-display-edit.php' );
        }

        /**
         * Render the delete resource page for this plugin.
         *
         * @since    1.0.0
         */
        public function display_delete_page() {
                $this->delete_resource();
                // display the list view 
                include_once( 'partials/resource-allocator-admin-display-list.php' );
        }

        /**
         * Render the copy resource page for this plugin.
         *
         * @since    1.0.0
         */
        public function display_copy_page() {
                $this->copy_resource();
                // display the list view 
                include_once( 'partials/resource-allocator-admin-display-list.php' );
        }

        /**
         * Render the setup page for this plugin.
         *
         * @since    1.0.0
         */
        public function display_options_page() {
                include_once( 'partials/resource-allocator-admin-display-options.php' );
        }

        /**
         * Render the about page for this plugin.
         *
         * @since    1.0.0
         */
        public function display_about_page() {
            
                /* Add screen option: user can choose between 1 or 2 columns (default 2) */
                add_screen_option('layout_columns', [ 'max' => 2, 'default' => 2 ] );

                add_meta_box( $this->plugin_name . 'plugin-purpose', __( 'Plugin Purpose', $this->plugin_name ), 
                        [ $this, 'about_texts' ], $this->plugin_name . '-about', 'normal', 'default', ['box'=>'purpose'] );
                add_meta_box( $this->plugin_name . 'usage', __( 'Usage', $this->plugin_name ), 
                        [ $this, 'about_texts' ], $this->plugin_name . '-about', 'normal', 'default', ['box'=>'usage'] );
                add_meta_box( $this->plugin_name . 'more-information', __( 'More Information and Documentation', $this->plugin_name ), 
                        [ $this, 'about_texts' ], $this->plugin_name . '-about', 'normal', 'default', ['box'=>'more_info'] );
                add_meta_box( $this->plugin_name . 'help-support', __( 'Help and Support', $this->plugin_name ), 
                        [ $this, 'about_texts' ], $this->plugin_name . '-about', 'normal', 'default', ['box'=>'help'] );
                add_meta_box( $this->plugin_name . 'author-license', __( 'Author and License', $this->plugin_name ), 
                        [ $this, 'about_texts' ], $this->plugin_name . '-about', 'side', 'default', ['box'=>'author'] );
                //add_meta_box( $this->plugin_name . 'credits-thanks', __( 'Credits and Thanks', $this->plugin_name ), 
                //      [ $this, 'about_texts' ], $this->plugin_name . '-about', 'side', 'default', ['box'=>'thanks'] );
                add_meta_box( $this->plugin_name . 'debug-version-information', __( 'Debug and Version Information', $this->plugin_name ), 
                        [ $this, 'about_texts' ], $this->plugin_name . '-about', 'side', 'default', ['box'=>'version'] );
      
                include_once( 'partials/resource-allocator-admin-display-about.php' );
        }

        /**
         * Render the menu navigation for this plugin.
         *
         * @since    1.0.0
         */
        private function print_nav_tab_menu($action) {
                $pages = ['list' => [
                        'show_entry' => true,
                        'nav_tab_title' => __( 'All Resources', $this->plugin_name ),
                    ],
                    'add' => [
                        'show_entry' => true,
                        'nav_tab_title' => __( 'Add New', $this->plugin_name ),
                    ],
                    'edit' => [
                        'show_entry' => false,
                        'nav_tab_title' => __('Edit', $this->plugin_name ),
                    ],
                    'options' => [
                        'show_entry' => true,
                        'nav_tab_title' => __( 'Plugin Options', $this->plugin_name ),
                    ],
                    'about' => [
                        'show_entry' => true,
                        'nav_tab_title' => __( 'About', $this->plugin_name ),
                    ],
                ];
                echo '<h1 id="' . $this->plugin_name . '-nav" class="nav-tab-wrapper">';
                echo '<span class="plugin-name">' . __( 'Resource Allocator', $this->plugin_name ) . '</span><span class="separator"></span>';
                foreach ( $pages as $page => $entry ) {
                    if ('edit' === $action) {
                        if ( ('edit' !== $page) && (!$entry[ 'show_entry' ]) ) {
                            continue;
                        }
                    } else if (!$entry[ 'show_entry' ]) {
                        continue;                            
                    }
                    if ( 'options' === $page ) {
                        echo '<span class="separator"></span><span class="separator"></span>';
                    }

                    $slug = $this->plugin_name;
                    if ( 'list' !== $page ) {
                        $slug .= '-' . $page;
                    }
                    $active = ( $action === $page ) ? ' nav-tab-active' : '';
                    $url = admin_url( 'admin.php?page=' . $slug );
                    echo "<a class=\"nav-tab{$active}\" href=\"{$url}\">{$entry['nav_tab_title']}</a>";

                }
                echo '</h1>';
        }

        /**
         * Render the notification block for this plugin.
         *
         * @since    1.0.0
         */
        private function print_notification() {
                if ( $this->message === "" ) { 
                    $this->message = filter_input( INPUT_GET, 'message' , FILTER_SANITIZE_STRING);
                }
                if (!is_null ($this->message)) {
                    $level = substr ($this->message, 0, 1) == 'S' ? 'updated' : 'error';
                    $message = substr( $this->message, 1);
                    echo '<div id="message" class="' . $level . ' fade" ><p><strong>' . $message . '</strong></p></div>';
                }
                $this->message = "";
        }

        /**
         * Add the plugin dashboard menu and submenu pages.
         *
         */
        public function add_plugin_admin_menu() {
                $pages = [ 'list' => ['show_entry' => true,
                        'page_title' => __( 'All Resources', $this->plugin_name ),
                        'admin_menu_title' => __( 'All Resources', $this->plugin_name ),
                    ],
                    'add' => [ 'show_entry' => true,
                        'page_title' => __( 'Add New Resource', $this->plugin_name ),
                        'admin_menu_title' => __( 'Add New Resource', $this->plugin_name ),
                    ],
                    'edit' => [ 'show_entry' => false,
                        'page_title' => __( 'Edit Resource', $this->plugin_name ),
                        'admin_menu_title' => '',
                    ],
                    'copy' => [ 'show_entry' => false,
                        'page_title' => __( 'Copy Resource', $this->plugin_name ),
                        'admin_menu_title' => '',
                    ],
                    'delete' => [ 'show_entry' => false,
                        'page_title' => __( 'Delete Resource', $this->plugin_name ),
                        'admin_menu_title' => '',
                    ],
                    'options' => [ 'show_entry' => true,
                        'page_title' => __( 'Plugin Options', $this->plugin_name ),
                        'admin_menu_title' => __( 'Plugin Options', $this->plugin_name ),
                    ],
                    'about' => [ 'show_entry' => true,
                        'page_title' => __( 'About', $this->plugin_name ),
                        'admin_menu_title' => __( 'About Resource Allocator', $this->plugin_name ),
                    ],
                ];
                add_menu_page( 'Resource Allocator', 'Resource Allocator', 'manage_options', $this->plugin_name,
                    '', plugins_url( '/images/resource-allocator.png', __FILE__ ), ++$GLOBALS[ '_wp_last_object_menu' ] );
                foreach ( $pages as $page=>$entry ) {
                    $callback = 'display_' . $page . '_page';
                    if ($entry['show_entry']) {
                        $parentslug = $this->plugin_name;
                    } else {
                        $parentslug = '';
                    }
                    $slug = $this->plugin_name;
                    if ( 'list' !== $page ) {
                            $slug .= '-' . $page;
                    }
                    add_submenu_page( $parentslug, $entry['page_title'], $entry['admin_menu_title'], 'manage_options',
                        $slug, [ $this, $callback ]);
                }      
        }

        /**
         * Helper function to store resource into database.
         *
         */
        private function save_resource( $id = 0 ) {
                $name = sanitize_text_field( filter_input( INPUT_POST, 'name' ) );
                $allocationbasis = sanitize_text_field( filter_input ( INPUT_POST, 'allocationbasis' ) );
                $minduration = absint( filter_input( INPUT_POST, 'minduration' ) );
                $maxduration = absint( filter_input( INPUT_POST, 'maxduration' ) );
                $availability = [];
                for ($i = 0; $i < 7; $i++) {
                    $availability["o$i"] = sanitize_text_field( filter_input( INPUT_POST, 'available_' . $i ) );
                    $availability["s$i"] = sanitize_text_field( filter_input( INPUT_POST, 'start_' . $i ) );
                    $availability["e$i"] = sanitize_text_field( filter_input( INPUT_POST, 'end_' . $i ) );
                }
                $fieldnames = filter_input( INPUT_POST, 'fieldname',  FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
                foreach ( $fieldnames as &$fieldname ) {
                    $fieldname = sanitize_text_field ( $fieldname );
                }
                if ($fieldnames) {
                    $fieldtypes = filter_input( INPUT_POST, 'fieldtype', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
                    $datafields = array_combine ($fieldnames, $fieldtypes);
                } else {
                    $datafields = "";
                }
                    
                global $wpdb;
                $table_name_resources = $wpdb->prefix . 'ra_resources';

                if ( $id ) {
                    // a modified resource
                    return $wpdb->update( $table_name_resources, [
                        'name' => $name, 
                        'minduration' => $minduration, 
                        'maxduration' => $maxduration, 
                        'allocationbasis' => $allocationbasis,
                        'availability' => json_encode($availability),
                        'datafields' => json_encode($datafields),
                        'modified' => current_time( 'mysql' )], [
                        'id' => $id]);
                } else {
                    // a new resource
                    return $wpdb->insert( $table_name_resources, [
                        'name' => $name, 
                        'minduration' => $minduration, 
                        'maxduration' => $maxduration, 
                        'allocationbasis' => $allocationbasis,
                        'availability' => json_encode($availability),
                        'datafields' => json_encode($datafields),
                        'created' => current_time( 'mysql' )]); //date('Y-m-d H:i:s', time())]);
                }
        }
        
        /**
         * Callback for display-add form.
         *
         */
        public function add_resource() {
                if (!check_admin_referer( $this->plugin_name . 'add' )) { 
                    die( __('Security check', $this->plugin_name )); 
                } else {
                    if ( $this->save_resource() ) {
                        $message = 'S' . __( 'The resource has successfully been added to the database', $this->plugin_name );
                    } else {
                        $message = 'F' . __( 'It was not possible to store the resource to the database. Please try again.', $this->plugin_name );
                    }
                }
                wp_redirect ( admin_url( 'admin.php?page=' . $this->plugin_name . '&message=' . urlencode( $message )) );
                exit;
        }
        
        /**
         * Callback for display-update form.
         *
         */
        public function edit_resource() {
                $id = absint( filter_input( INPUT_POST, 'id' ) );
                if (!check_admin_referer( $this->plugin_name . 'edit' . $id )) { 
                    die( __('Security check', $this->plugin_name )); 
                } else {
                    if ($this->save_resource($id) ) {
                        $message = 'S' . __( 'The resource has successfully been updated in the database', $this->plugin_name );
                    } else {
                        $message = 'F' . __( 'It was not possible to update the resource in the database. Please try again.', $this->plugin_name );
                    }
                }
                wp_redirect ( admin_url( 'admin.php?page=' . $this->plugin_name . '&message=' . urlencode( $message )) );
                exit;
        }
        
        /**
         * Callback for display-delete action.
         *
         */
        public function delete_resource() {
                $id = absint( filter_input( INPUT_GET, 'id' ) );
                if (!check_admin_referer( $this->plugin_name . 'delete' . $id )) { 
                    die( __('Security check', $this->plugin_name )); 
                } else {
                    global $wpdb;
                    $table_name_resources = $wpdb->prefix . 'ra_resources';
                    
                    if ($wpdb->delete( $table_name_resources, [ 'id' => $id ] )) {
                        $this->message = 'S' . __( 'The resource has successfully been removed from the database', $this->plugin_name );
                    } else {
                        $this->message = 'F' . __( 'It was not possible to remove the resource from the database. Please try again.', $this->plugin_name );
                    }
                }
        }
        
        /**
         * Callback for display-copy action.
         *
         */
        public function copy_resource() {
                $id = absint( filter_input( INPUT_GET, 'id' ) );
                if (!check_admin_referer( $this->plugin_name . 'copy' . $id )) { 
                    die( __('Security check', $this->plugin_name )); 
                } else {
                    global $wpdb;
                    $table_name_resources = $wpdb->prefix . 'ra_resources';
                    $resource = $wpdb->get_row( "SELECT * FROM $table_name_resources WHERE id = '$id'", OBJECT );

                    //error_log (print_r($resource,true));
                    if ($resource) {
                        $resource->name = __( 'Copy of ', $this->plugin_name ) . $resource->name;
                        $resource->created = current_time( 'mysql' );
                        $resource->modified = null;
                        unset( $resource->id );
                        if ($wpdb->insert( $table_name_resources, (array) $resource) ) {
                            $this->message = 'S' . __( 'A copy of the resource has successfully been stored into the database', $this->plugin_name );
                        } else {
                            $this->message = 'F' . __( 'It was not possible to store the resource to the database. Please try again.', $this->plugin_name );
                        }
                    } else {
                        $this->message = 'F' . __( 'The selected resource could not be found in the database', $this->plugin_name );
                    }
                }
        }
        
        /**
         * Callback for about meta boxes
         *
         */
        public function about_texts( $post, $metabox ) {
                switch ($metabox['args']['box']) {
                    case 'purpose':
                        echo '<p>' .
                            __('With Resource Allocator the users of your site can allocate defined resources or check when they are in use.
                            Resources can be all kind of things. Think about office space, meeting rooms, equipment, vehicles. The allocations per
                            resource are shown in a table which can be made visible using a shortcode.', $this->plugin_name ) .
                            '</p>';
                        break;
                    case 'usage':
                        echo '<p>' . __('First you need to add the resource. Each resource has a name. Although the plugin does not require the
                            name to be unique, the name is shown in the allocation table to the user. Using unique names prevents the user
                            making incorrect allocations. ', $this->plugin_name ) .
                            '</p><p>' . __('Determine if the resource is to be allocated on a day basis or by the hour. And restrict, if necessary, the 
                            limits for the duration of the allocation. By default these set to 1 at minimum and 999 days at maximum for resources that
                            are reserved on a daily basis. And 1 hour minimum to 24 hours maximum for resources reserved on a hourly basis.', $this->plugin_name ) .
                            '</p><p>' . __('It is possible to define extra data fields that the user may fill in when creating or updating an allocation. 
                            Define the name of the field to use for display in the allocation table. Use short, meaningfull names, as they are used 
                            in the allocation entry form and in the allocation table itself. Again, it is not required to use unique names but
                            strongly advised to prevents mistakes. Per field the input can be restricted to text or numbers only. The order in which
                            the fields are created also defines the order in which they are displayed in the allocation table.', $this->plugin_name ) .
                            '</p>';
                        break;
                    case 'more_info':
                        echo '<p>' . __('Additional information can be found at the plugin site HYPERLINK TO BE ADDED or the Wordpress Plugins page HYPERLINK TO BE ADDED', $this->plugin_name ) .
                            '</p>';
                        break;
                    case 'help':
                        echo '<p>' . 'TO BE ADDED' .
                            '</p>';
                        break;
                    case 'author':
                        echo '<p>' . printf (__( 'This plugin was written and developed by <a href="%s">Eric Sprangers</a>. ', $this->plugin_name ), 'http://www.sprako.nl/' ) .
                        __( 'It is licensed as Free Software under GNU General Public License 2 (GPL 2).', $this->plugin_name ) .
                        '<br />' .
                        printf( __( 'If you like the plugin, <a href="%s"><strong>giving a donation</strong></a> is recommended. ', $this->plugin_name ), 'http://www.sprako.nl/donate/' ) .
                        printf( __( 'Please rate and review the plugin in the <a href="%s">WordPress Plugin Directory</a>. ', $this->plugin_name ), 'https://wordpress.org/support/view/plugin-reviews/resource-allocator' )  .
                        '<br />' .
                        __( 'Donations and good ratings encourage me to further develop the plugin and to provide countless hours of support. Any amount is appreciated! Thanks!', $this->plugin_name ) .
                        '</p>';
                        break;
                    case 'thanks':
                        echo '<p>' . 'TO BE ADDED' .
                            '</p>';
                        break;
                    case 'version':
                            $mysqli = ( isset( $GLOBALS['wpdb'] ) && isset( $GLOBALS['wpdb']->use_mysqli ) && $GLOBALS['wpdb']->use_mysqli && isset( $GLOBALS['wpdb']->dbh ) );
                            echo '<p><strong>' . __( 'Please provide this information in bug reports and support requests.', $this->plugin_name ) . '</strong></p>' .
                                '<p class="ltr">' .
                                '&middot; Website: ' . site_url() . 
                                '<br />&middot; Resource Allocator: ' . $this->version .
                                '<br />&middot; Resource Allocator (DB): ' . get_option($this->plugin_name . '_db_version' ) .
    ////      NA                      '<br />&middot; Resource Allocator table scheme: ' . Resource_Allocator::table_scheme_version .
                                '<br />&middot; Plugin installed: ' . date( 'Y/m/d H:i:s', get_option($this->plugin_name . '_installed', time() ) ) .
                                '<br />&middot; WordPress: ' . $GLOBALS['wp_version'] .
                                '<br />&middot; Multisite: ' . (is_multisite() ? 'yes' : 'no') . 
                                '<br />&middot; PHP: ' . phpversion() . 
                                '<br />&middot; mysqli Extension: ' . ($mysqli ? 'true' : 'false') . 
                                '<br />&middot; mySQL (Server): ' . ($mysqli ? mysqli_get_server_info( $GLOBALS['wpdb']->dbh ) : mysql_get_server_info() ) .
                                '<br />&middot; mySQL (Client): ' . ($mysqli ? mysqli_get_client_info( $GLOBALS['wpdb']->dbh ) : mysql_get_client_info() ) .
    ////      NA                      '<br />&middot; ZIP support: ' . $data['zip_support_available'] ? 'yes' : 'no' .
                                '<br />&middot; UTF-8 conversion: ' . (( function_exists( 'mb_detect_encoding' ) && function_exists( 'iconv' ) ) ? 'yes' : 'no' ) .
                                '<br />&middot; WP Memory Limit: ' . WP_MEMORY_LIMIT . 
                                '<br />&middot; Magic Quotes: ' . ( get_magic_quotes_gpc() ? 'on' : 'off' ) .
                                '<br />&middot; WP_DEBUG: ' . ( WP_DEBUG ? 'true' : 'false' ) . 
                                '<br />&middot; WP_POST_REVISIONS: ' . ( is_bool( WP_POST_REVISIONS ) ? ( WP_POST_REVISIONS ? 'true' : 'false' ) : WP_POST_REVISIONS ) . 
                                '</p>';
                        break;
                    default:
                        echo 'this should not be displayed';
                        break;
                }
        }
}
