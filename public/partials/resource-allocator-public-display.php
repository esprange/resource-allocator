<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://www.sprako.nl/wordpress
 * @since      1.0.0
 *
 * @package    Resource_Allocator
 * @subpackage Resource_Allocator/public/partials
 */
?>

<?php if (!is_user_logged_in() ) : ?>
    <section>
        <h2><?php _e( 'Allocation of resources is only allowed by logged-in users', $this->plugin_name ) ?></h2>
    </section>
<?php else : 
    add_thickbox();

    $tablewidth = 120 + 120; //em
    $datafields = json_decode ( $resource->datafields, true );
    foreach ($datafields as $datafield){
        $tablewidth += 120;//strlen($datafield);
    }
    if (current_user_can ( 'edit_others_posts' )) { //$this->canUserDoRoleOption(self::_optionCanOverruleAllocation)) {
        $current_user = wp_get_current_user();
        $users = get_users(['fields' => [ 'id', 'display_name' ], 'orderby' => [ 'nicename' ]]);
    }

    ?>
    <section>
    <h2 id="<?php echo $this->plugin_name . '-title-' . $resource->id ?>"> <?php echo __( 'Allocations for' , $this->plugin_name) . ' ' . $resource->name ?></h2>
    <div class="<?php echo $this->plugin_name ?>">
        <table id="<?php echo $this->plugin_name . '-contents-' . $resource->id ?>" data-resource-id="<?php echo $resource->id ?>" data-type="<?php echo $resource->allocationbasis ?>" class="<?php echo $this->plugin_name ?>" style="width:<?php echo $tablewidth ?>px">
            <tr><th><img src="<?php echo plugin_dir_url( __FILE__ ) . '../images/pleasewait.gif' ?>" alt="<?php _e('Please wait...', $this->plugin_name ) ?>" />&nbsp;<?php _e('Allocation data is being retrieved from the database...', $this->plugin_name) ?></th></tr>
        </table>

        <div id ="<?php echo $this->plugin_name . '-box-' . $resource->id ?>" class="thickbox <?php echo $this->plugin_name . '-form' ?>" >
        <form action="#" method="post">                 
            <table class="<?php echo $this->plugin_name . '-form' ?>">
            <thead>
                <tr>
                <th colspan="3" ><?php printf(__('Allocate %s at %s', $this->plugin_name), $resource->name, '<span id="' . $this->plugin_name . '-date-' . $resource->id . '"></span>' ) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <td></td>
                <td colspan="2">
                    <?php if (current_user_can ( 'edit_others_posts' )) : /* user can overrule */  ?> 
                    <select id="<?php echo $this->plugin_name . '-user-id-' . $resource->id ?>" >
                    <?php foreach ($users as $user) : ?>
                        <option value="<?php echo $user->id ?>" <?php selected( $user->id, $current_user->ID ) ?> ><?php echo $user->display_name ?></option>                    
                    <?php endforeach; ?>
                    </select>
                    <?php else : ?>
                    <input type ="hidden" id="<?php echo $this->plugin_name . '-user-id-' . $resource->id ?>">
                    <?php endif; ?>
                </td>
                </tr>
                <?php if ( $resource->allocationbasis == 'hours' ): ?>
                <tr><td><label><?php _e( 'Start time', $this->plugin_name ) ?></label></td>
                    <td colspan="2"><input type="text" class="timefield" id="<?php echo $this->plugin_name . '-start-' . $resource->id ?>" ></td>
                </tr>
                <tr><td><label><?php _e( 'Duration', $this->plugin_name ) ?></label></td>
                    <td colspan="2"><input type="text" class="timefield" id="<?php echo $this->plugin_name . '-duration-' . $resource->id  ?>" ></td>
                </tr>
                <?php endif; ?>
                <?php $j= 1; foreach ($datafields as $datafield => $datatype) : ?>
                <tr>
                    <td><label><?php echo $datafield ?></label></td>
                    <td colspan="2"><input type="<?php echo $datatype ?>" data-field="<?php echo $datafield ?>" id="<?php echo $this->plugin_name . '-data-' . $resource->id . '-' . $j++ ?>" ></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><th colspan="3">
                    <input type ="hidden" id="<?php echo $this->plugin_name . '-display-' . $resource->id ?>" >
                    <input type ="hidden" id="<?php echo $this->plugin_name . '-allocation-' . $resource->id ?>" >
                    <span id="<?php echo $this->plugin_name . '-text-' . $resource->id ?>" ></span></th></tr><tr>
                    <th><button type="button" id="<?php echo $this->plugin_name . '-save-' . $resource->id ?>" value="<?php echo $resource->id ?>" class="<?php echo $this->plugin_name . '-save' ?>" ></button></th>
                    <th><button type="button" id="<?php echo $this->plugin_name . '-delete-' . $resource->id ?>" value="<?php echo $resource->id ?>" class="<?php echo $this->plugin_name . '-delete' ?>" ><?php _e( 'Delete', $this->plugin_name ) ?></button></th>
                    <th><button type="button" onclick="self.parent.tb_remove();return false"><?php _e( 'Cancel', $this->plugin_name ) ?></button></th>
                </tr>
            </tfoot>
            </table>
        </form>
        </div>
    </div>
    </section>
<?php endif;