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
<div class="wrap">

<?php
    $this->print_nav_tab_menu( 'about' );
?>
    <form name="my_form" method="post">
        <?php  
            /* Used to save closed meta boxes and their order */
            wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); 
        ?>
        
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
<!--                <div id="post-body-content">
                     #post-body-content 
                </div>-->

                <div id="postbox-container-1" class="postbox-container">
                    <?php do_meta_boxes($this->plugin_name . '-about' , 'side' , '' ); ?>
                </div>
                
                <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes($this->plugin_name . '-about' , 'normal', '' ); ?>
                    <?php do_meta_boxes($this->plugin_name . '-about' , 'advanced', '' ); ?>
                </div>
            </div>
        </div>
    </form>
 </div>
