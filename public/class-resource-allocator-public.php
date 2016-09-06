<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.sprako.nl/wordpress
 * @since      1.0.0
 *
 * @package    Resource_Allocator
 * @subpackage Resource_Allocator/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Resource_Allocator
 * @subpackage Resource_Allocator/public
 * @author     Eric Sprangers <e.sprangers@sprako.nl>
 */
class Resource_Allocator_Public {

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
         * The ajax url of this plugin.
         * 
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $ajax_url    The ajax_url for front-end communication.
         */
        private $ajax_url;
        
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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
                $this->ajax_url = $plugin_name . '/v1';
                $this->options = get_option( $this->plugin_name . '_options');
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Resource_Allocator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Resource_Allocator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/resource-allocator-public.css', [], $this->version, 'all' );
                wp_enqueue_style( 'time_entry', plugin_dir_url( dirname(__FILE__) ) . 'vendor/timeentry/jquery.timeentry.css', [] );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Resource_Allocator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Resource_Allocator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/resource-allocator-public.js', [ 'jquery' ], $this->version, false );
                wp_localize_script( $this->plugin_name, $this->plugin_name, [
                    'locale'=> str_replace( '_', '-', get_locale()),
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'base_url' => rest_url( $this->ajax_url ),
                    'text_edit_delete' => __('Do you want to change or delete the allocation ?', $this->plugin_name ),
                    'text_create' => __('Do you want to create the allocation ?', $this->plugin_name ),
                    'edit' => __('Edit', $this->plugin_name ),
                    'create' => __('Create', $this->plugin_name ),
                    'timeformat'=> get_option('time_format'),
                    ]
                );
                wp_enqueue_script ( 'time_entry_plugin', plugin_dir_url( dirname(__FILE__) ) . 'vendor/timeentry/jquery.plugin.js', array( 'jquery' ) );
                wp_enqueue_script ( 'time_entry', plugin_dir_url( dirname(__FILE__) ) . 'vendor/timeentry/jquery.timeentry.js', array( 'jquery' ) );

	}
        
        /**
         * helper function to sanitize and unserialize an allocation
         * 
         * @param type $json_allocation         serialized allocation
         * @return type array
	 * @since    1.0.0
         */
        private function sanitize_allocation( $json_allocation, $json_values = '') {
            
            // error_log (print_r($json_allocation, true));
            $allocation = json_decode ( $json_allocation, true );
            
            $values = json_decode ( $json_values, true );
            //error_log (print_r($values, true));
            
            $validated_values = [];
            if (is_array ($values)) {
                foreach ( $values as $key => $value ) {
                    $validated_values[ sanitize_text_field( $key ) ] = sanitize_text_field( $value ) ;
                }
            }
            $validated_allocation = [ 'id' => $allocation[ 'id' ] != '' ? absint( $allocation[ 'id' ] ) : '',
                        'resource_id' => absint ( $allocation[ 'resource_id' ] ),
                        'user_id' => absint ( $allocation[ 'user_id'] ),
                        'start' => sanitize_text_field ( $allocation[ 'start' ] ),
                        'end' => sanitize_text_field ( $allocation[ 'end' ] ),
                        'data' => json_encode( $validated_values ) ];
            //error_log (print_r($validated_allocation, true ));
            return (object) $validated_allocation;
        }

        /**
         * Helper function to sanitize and unserialize display parameters
         * 
         * @param type $json_display
         * @return type array
	 * @since    1.0.0
         */
        private function sanitize_display ( $json_display ) {
            //error_log (print_r($json_display, true));
            $display = json_decode ( $json_display, true );

            $validated_display = [
                        'resource_id' => absint ( $display['resource_id'] ),
                        'type' => sanitize_text_field ( $display['type'] ),
                        'start' => date_parse ( $display[ 'start' ] ) ? $display[ 'start' ] : '0000-00-00',
                        'end' => date_parse ( $display[ 'end' ] ) ? $display[ 'end' ] : '0000-00-00' ];
            //error_log (print_r($validated_display, true ));
            return (object) $validated_display;
        }
        
        /**
         * Helper function to create the contents for the table with allocations by the hour
         * 
         * @param type $display
         * @param type $resource
         * @param type $allocations
         * @return type string
         */
        private function hour_table_contents ( &$display, &$resource, &$allocations ) {
                ob_start();
                include( 'partials/resource-allocator-public-hour-table.php' );
                return ob_get_clean();
        }

        /**
         * Helper function to create the contents for the table with daily allocations
         * 
         * @param type $display
         * @param type $resource
         * @param type $allocations
         * @return type string
         */
        private function day_table_contents ( &$display, &$resource, &$allocations ) {
                ob_start();
                include( 'partials/resource-allocator-public-day-table.php' );
                return ob_get_clean();
        }

	/**
	 * Callback service for front-end ajax show call
         * 
	 * @since    1.0.0
	 */
        public function callback_show( WP_REST_Request $request ) {
                global $wpdb;
                $display = $this->sanitize_display ( $request->get_param( 'display' ) );
                
                $table_name_allocations = $wpdb->prefix . 'ra_allocations';
                $allocations = $wpdb->get_results ( "SELECT * FROM $table_name_allocations WHERE
                    ( '$display->resource_id' = resource_id ) AND (
                    ( '$display->start' < start AND '$display->end' > end ) OR
                    ( '$display->start' BETWEEN start AND end ) OR
                    ( '$display->end' BETWEEN start AND end ) ) ORDER BY start ASC" , OBJECT );
                
                $table_name_resources = $wpdb->prefix . 'ra_resources';
                $resource = $wpdb->get_row( "SELECT * FROM $table_name_resources WHERE 
                    ( '$display->resource_id' = id )", OBJECT );
                $html = ( $display->type == 'days' ) ?
                    $this->day_table_contents ( $display, $resource, $allocations ) :
                    $this->hour_table_contents ( $display, $resource, $allocations );
               // error_log ($html);
                return new WP_REST_response([ 'html' => $html, 'id' => $display->resource_id ]);
        }
        
        /**
	 * Callback service for front-end ajax delete call
         * 
         * @param type WP_REST_Request
         * @return type WP_REST_response
	 * @since    1.0.0
	 */
        public function callback_delete( WP_REST_Request $request ) {
                $display = $this->sanitize_display ( $request->get_param( 'display' ) );
                $allocation = $this->sanitize_allocation ( $request->get_param( 'allocation' ) );
                
                global $wpdb;
                $table_name_allocations = $wpdb->prefix . 'ra_allocations';
                    
                if ($wpdb->delete( $table_name_allocations, [ 'id' => $allocation->id ] )) {
                    return $this->callback_show ( $request );
                } else {
                    $html = "<div class=\"error fade\">".__( 'The allocation could not be removed from the database', $this->plugin_name ). "</div>"; 
                    return new WP_REST_response([ 'html' => $html, 'display' => $display ]);
                }
        }
        /**
         * Callback service for front-end ajax edit call
         * 
         * @param type WP_REST_Request
         * @return type WP_REST_response
	 * @since    1.0.0
	 */
        public function callback_save(WP_REST_Request $request) {
                $display = $this->sanitize_display ( $request->get_param( 'display') );
                $allocation = $this->sanitize_allocation ( $request->get_param( 'allocation'),
                        $request->get_param( 'values' ));

                global $wpdb;
                $table_name_allocations = $wpdb->prefix . 'ra_allocations';

                if ($allocation->id !== '' ) {
                    $allocation->modified = current_time( 'mysql' );
                    if ( $wpdb->update ( $table_name_allocations, ( array ) $allocation, [ 'id' => $allocation->id ] )) {
                        return $this->callback_show ($request);
                    }
                } else {
                    $allocation->created = current_time( 'mysql' );
                    if ( $wpdb->insert ( $table_name_allocations, ( array ) $allocation ) ) {
                        return $this->callback_show ($request);
                    }         
                }
                $html = '<div class="error fade">' .__( 'The allocation could not be saved to the database', $this->plugin_name) . "</div>"; 
                return new WP_REST_response([ 'html' => $html, 'display'=> $display ]);
        }

        /**
         * Helper function, creates one row in allocation table.
         * 
         * @param DateTime $start
         * @param DateTime $end
         * @param Object $display
         * @param Object $resource
         * @param boolean $available
         * @param array $allocation
         * @return string
         */
        function make_row (DateTime $start, DateTime $end, $display=[], $resource=[], $available = true, $allocation = []) {
                $now = new DateTime();
                $action = $end > $now;
                $datafields = json_decode( $resource->datafields, true );
                $minduration = new DateInterval ( 'PT' . $resource->minduration . 'H' );

                if ($available) {
                    $who = $action ? __('-available-', $this->plugin_name) : "";
                    $valueshtml = str_repeat( '<td></td>', count ($datafields) );
                    foreach ($datafields as $field => $type ) {
                        $values[$field] = '';
                    }
                    $color = $this->options[ 'color_available' ];
                    $allocation = ['id'=> '',
                        'start' => $start->format (DateTime::ATOM),
                        'end' => $start->add($minduration)->format (DateTime::ATOM),
                        'resource_id' => $display->resource_id,
                        'user_id' => get_current_user_id(),
                        'data' => '',
                        ];             
                } else {
                    $user = get_userdata( $allocation->user_id );
                    $who = $user->display_name;
                    $valueshtml = '';
                    $data = json_decode( $allocation->data, true );
                    foreach ($datafields as $field => $type) {
                        if (array_key_exists ($field, $data)) {
                            $valueshtml .=  '<td style="text-align:' . ( $type == 'text' ? 'left' : 'right' ) . '">' . $data[$field] . '</td>' ;
                            $values[$field] = $data[$field];
                        } else {
                            $valueshtml .= '<td></td>';
                            $values[$field] = '';
                        }
                    }
                    if ($allocation->user_id == get_current_user_id() ) {
                        $color = $action ? $this->options[ 'color_allocated' ] : $this->options[ 'color_available' ];                        
                    } else {
                        $color = $action ? $this->options[ 'color_unavailable' ] : $this->options[ 'color_available' ];
                        $action = ($action and current_user_can( 'edit_others_posts' ));
                        //$user_id = $allocation->user_id;
                    }
                }
                if ($action) {
                    $html = "<tr class=\"$this->plugin_name-panel\" >
                            <td><a class=\"$this->plugin_name thickbox\"  
                                href=\"#TB_inline?width=520&inlineId=$this->plugin_name-box-$display->resource_id\" 
                                rel=\"bookmark\"
                                data-display='" . json_encode( $display ) . "' 
                                data-allocation='" . json_encode( $allocation ) . "' 
                                data-values='" . json_encode( $values ) . "' >" . 
                            $start->format('H:i') . '...' . $end->format('H:i') . "</a>
                            </td><td style=\"color:$color\" >$who</td>$valueshtml</tr>";
                } else {
                    $html = "<tr class=\"$this->plugin_name-panel\" >
                            <td>" .  
                            $start->format('H:i') . '...' . $end->format('H:i') . "</a>
                           </td><td style=\"color:$color\" >$who</td>$valueshtml</tr>";
                }
                return $html;
        }

        
        /**
	 * Handle the shortcode for display of the allocation tables.
	 *
         * @param type WP_REST_Request
         * @return type WP_REST_response
	 * @since    1.0.0
	 */
        public function shortcode_handler( $atts ) {        
                $atts = ( shortcode_atts( [ 'id' => 'missing' ], $atts, $this->plugin_name ));
                $id = intval( $atts['id'] );
                if ( $id > 0) {
                    global $wpdb;
                    $table_name_resources = $wpdb->prefix . 'ra_resources';
                    $resource = $wpdb->get_row( "SELECT * FROM $table_name_resources WHERE id = '$id'", OBJECT );
                    if ( $resource ) {
                        ob_start();
                        include( 'partials/resource-allocator-public-display.php' );
                        return ob_get_clean();

                    } else {
                        return '<p>' . sprintf( __( 'A resource with the id %d is undefined', $this->plugin_name ), $id ) . '</p>';
                    }
                } else {                
                    return '<p>' . __( 'The id is unspecified or not a valid number', $this->plugin_name ) .'</p>';
                }
        }

        /**
	 * Register the shortcode for display of the allocation tables.
	 *
	 * @since    1.0.0
	 */
        public function register_shortcode() {
                // Register short code
                add_shortcode( $this->plugin_name, [$this, 'shortcode_handler' ]);
            
        }

        /**
	 * Register the ajax endpoints 
	 *
	 * @since    1.0.0
	 */
        public function register_endpoints() {
            register_rest_route(
                $this->ajax_url, '/save', [
                    'methods' => WP_REST_Server::EDITABLE ,
                    'callback' => [$this, 'callback_save' ],
                    'args' => [
                        'display' => [ 'required' => true],
                        'allocation' => [ 'required' => true],
                        'values' => [ 'required' => true],
                        ],
                    'permission_callback' => function() {
                        return is_user_logged_in();
                    }
                ]
            );
            register_rest_route(
                $this->ajax_url, '/delete', [
                    'methods' => WP_REST_Server::EDITABLE ,
                    'callback' => [$this, 'callback_delete' ],
                    'args' => [
                        'display' => [ 'required' => true],
                        'allocation' => [ 'required' => true],
                        ],
                    'permission_callback' => function() {
                        return is_user_logged_in();
                    }
                ]
            );
            register_rest_route(
                $this->ajax_url, '/show', [
                    'methods' => WP_REST_Server::READABLE ,
                    'callback' => [$this, 'callback_show' ],
                    'args' => [
                        'display' => [ 'required' => true],
                    ],
                    'permission_callback' => function() {
                        return is_user_logged_in();
                    }
                ]
            );
        }
}
