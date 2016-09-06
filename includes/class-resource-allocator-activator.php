<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.sprako.nl/wordpress
 * @since      1.0.0
 *
 * @package    Resource_Allocator
 * @subpackage Resource_Allocator/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Resource_Allocator
 * @subpackage Resource_Allocator/includes
 * @author     Eric Sprangers <e.sprangers@sprako.nl>
 */
class Resource_Allocator_Activator {
        
        
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
                $db_version = 1;
                $options_version = 1;

                global $wpdb;
                $installed_ver = get_option ('resourceallocator_db_version', 0);
                
                if ( $installed_ver < $db_version ) {
                    $charset_collate = $wpdb->get_charset_collate();
                    $table_name_resources = $wpdb->prefix . 'ra_resources';
                    $table_name_allocations = $wpdb->prefix . 'ra_allocations';
                    // note, two spaces needed between primary key and (id) !
                    $sql = "CREATE TABLE $table_name_resources (
                            id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                            name tinytext,
                            minduration smallint(4),
                            maxduration smallint(4),
                            allocationbasis tinytext,
                            availability text,
                            datafields text,
                            created datetime,
                            modified datetime,
                            PRIMARY KEY  (id)
                            ) $charset_collate;
                            CREATE TABLE $table_name_allocations (
                            id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                            resource_id bigint UNSIGNED NOT NULL,
                            user_id bigint UNSIGNED NOT NULL,
                            start datetime NOT NULL,
                            end datetime NOT NULL,
                            data longtext,
                            created datetime,
                            modified datetime,
                            PRIMARY KEY  (id),
                            KEY user_id (user_id),
                            KEY resource_id (resource_id),
                            CONSTRAINT FOREIGN KEY (resource_id) REFERENCES $table_name_resources (id) ON DELETE CASCADE
                    ) $charset_collate;";

                    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                    dbDelta( $sql );
                    
                    update_option ('resourceallocator_db_version', $db_version);
                    
                }
                $options = get_option ('resourceallocator_options', ['version' => 0] );
                if ($options['version'] < $options_version) {
                   update_option ('resourceallocator_options',
                        [ 'version' => 1,
                        'df_basis' => 'hours',
                        'df_minduration_hrs' => 1,
                        'df_minduration_days' => 1,
                        'df_maxduration_hrs' => 24,
                        'df_maxduration_days' => 999,
                        'color_available' => '#0000FF',
                        'color_unavailable' => '#FF0000',
                        'color_allocated' => '#008000',
                        ] );
                } else {
                    // do upgrade actions to $options 
                    // $options ['x'] = y;
                    // update_option ( 'resourceallocator_options', $options );
                }
 	}

}
