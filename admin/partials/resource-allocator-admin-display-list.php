<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.sprako.nl/wordpress
 * @since      1.0.0
 *
 * @package    Resource_Allocator
 * @subpackage Resource_Allocator/admin/partials
 */
?>

<div class='wrap'>

<?php     
    $this->print_nav_tab_menu( 'list' );
    if (!current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.', $this->plugin_name ) );
    }
    $this->print_notification();
    
    $dt_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

    global $wpdb;
    $table_name_resources = $wpdb->prefix . 'ra_resources';
    $resources = $wpdb->get_results( "SELECT id, name, created, modified FROM $table_name_resources ORDER BY id ASC", OBJECT_K);
?>
    <p><?php _e( 'This is a list of your resources. Click the corresponding links within the list to edit, copy or delete a resource.', $this->plugin_name ); ?></p> 
    <p><?php _e( 'To insert a resource allocation table into a page, post, or text widget, copy its Shortcode 
        and paste it at the desired place in the editor. Each resource has a unique ID that needs to be adjusted in that Shortcode.', $this->plugin_name ); ?></p>
    <table class="widefat">
        <thead>
            <tr>
                <th style="text-align:right">ID</th>
                <th><?php _e( 'Resource', $this->plugin_name ) ?></th>
                <th><?php _e( 'Date created', $this->plugin_name ) ?></th>
                <th><?php _e( 'Date modified', $this->plugin_name ) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ( count ( $resources ) == 0 ) : ?>
            <tr>
                <td colspan="4">
                <p><?php printf ( __('No resources found. You should <a href="%1$s">add</a> a resource to get started!' ,$this->plugin_name), 
                    admin_url( 'admin.php?page=resourceallocator-add' )); ?></p>
                </td>
            </tr>            
        <?php else : foreach ($resources as $resource) : ?>
            <tr>
                <td style="text-align:right"><?php echo $resource->id; ?></td>
                <td><strong>
                        <a title="<?php esc_attr_e( $resource->name ); ?>" class="row-title" 
                                href="<?php echo admin_url( "admin.php?page=resourceallocator-edit&id=$resource->id" ); ?>" >
                        <?php echo $resource->name; ?></a>
                    </strong><br />
                    <div class="row-actions"><i>
                        <span class="edit"> 
                            <a title="<?php esc_attr_e ( sprintf ( __( 'Edit %s', $this->plugin_name ), $resource->name )); ?>"
                                href="<?php echo admin_url( "admin.php?page=resourceallocator-edit&id=$resource->id" ); ?>" >
                            <?php _e( 'Edit', $this->plugin_name ) ?></a> | </span>
                        <span class="shortcode">
                            <a title="[resource-allocator id=<?php echo $resource->id ?>" href="#" style="cursor:pointer" onclick="alert('<?php
                            esc_attr_e( sprintf ( __( 'Place the shortcode [resource-allocator id=%d] on your page to show the allocations for resource %s', $this->plugin_name ), $resource->id, $resource->name ) ); ?>')">
                            <?php _e( 'Get Code', $this->plugin_name ); ?></a> | </span>
                        <span class="copy"> 
                            <a title="<?php esc_attr_e( sprintf ( __( 'Copy %s', $this->plugin_name ), $resource->name )); ?>"
                                href="<?php echo wp_nonce_url( admin_url( "admin.php?page=resourceallocator-copy&id=$resource->id" ),
                                $this->plugin_name .  'copy' . $resource->id ); ?>" >
                            <?php _e( 'Copy', $this->plugin_name ) ?></a> | </span>
                        <span class="delete">
                            <a title="<?php esc_attr_e( sprintf ( __( 'Delete %s', $this->plugin_name ), $resource->name )); ?>" class="delete-link" onclick="return confirm('<?php  
                                esc_attr_e( sprintf ( __( 'You are about to delete this resource: %s. Press Cancel to stop, OK to delete', $this->plugin_name ), $resource->name )); ?>')" 
                                href="<?php echo wp_nonce_url( admin_url( "admin.php?page=resourceallocator-delete&id=$resource->id" ),
                                $this->plugin_name . 'delete' . $resource->id ); ?>" >
                            <?php _e( 'Delete', $this->plugin_name ) ?></a></span>
                    </i></div>
                </td>
                <td><?php echo date_i18n( $dt_format, strtotime($resource->created ) ); ?></td>
                <td><?php if ( '' <> $resource->modified ) : echo date_i18n( $dt_format, strtotime($resource->modified ) ); endif; ?></td>
            </tr>
        <?php   endforeach; endif ?>
        </tbody>
        <tfoot>
            <tr>
                <th style="text-align:right">ID</th>
                <th><?php _e( 'Resource', $this->plugin_name ) ?></th>
                <th><?php _e( 'Date created', $this->plugin_name ) ?></th>
                <th><?php _e( 'Date modified', $this->plugin_name ) ?></th>
            </tr>
        </tfoot>
    </table>
</div>