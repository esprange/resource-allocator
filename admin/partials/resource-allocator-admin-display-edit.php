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
    $id = absint( filter_input( INPUT_GET, 'id') );
    $action = ( $id ) ? 'edit' : 'add';

    $this->print_nav_tab_menu( $action );
    if (!current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.', $this->plugin_name ) );
    }
    $this->print_notification();
    
    if ( $id ) {
        global $wpdb;
        $table_name_resources = $wpdb->prefix . 'ra_resources';
        $resource = $wpdb->get_row("SELECT * FROM $table_name_resources WHERE id = '$id'", OBJECT);
    
        if (!$resource) {
            // exit with error message, resource could not be found ...
        };
        $name = $resource->name;
        $allocationbasis = $resource->allocationbasis;
        $minduration = $resource->minduration;
        $maxduration = $resource->maxduration;
        $availability = json_decode($resource->availability, true);
        $datafields = json_decode($resource->datafields, true);
        $introduction = __('All resource settings can be changed and new custom fields can be added. A custom field can be removed by clearing
            the field name. Please remember that these changes are only effective for future allocations. 
            New custom fields will not contain values for existing allocations unless users edit the allocations.', $this->plugin_name );
        
    } else {
        $id = '';
        $name = '';
        $allocationbasis = $this->options[ 'df_basis' ];
        
        $minduration = ($allocationbasis === 'hours') ? $this->options[ 'df_minduration_hrs' ] : $this->options[ 'df_minduration_days' ];
        $maxduration = ($allocationbasis === 'hours') ? $this->options[ 'df_maxduration_hrs' ] : $this->options[ 'df_maxduration_days' ];
        for ($i = 0; $i < 7; $i++) {
            $availability["o$i"] = 'checked';
            $availability["s$i"] = '00:00';
            $availability["e$i"] = '24:00';
        }
        $datafields = [];
        $introduction = __('To add a new resource, enter its name, the allocation and availability settings into the form below. 
            You can add datafields which can be used to store additional information which each allocation. 
            You can always change the name, settings and optional datafields of your resource later.', $this->plugin_name);
    }
    $duration_max = ( $allocationbasis === 'hours') ? 24 : 999;

    $days = [ 0 =>__( 'Sunday', $this->plugin_name ),
              1 =>__( 'Monday', $this->plugin_name ),
              2 =>__( 'Tuesday', $this->plugin_name ),
              3 =>__( 'Wednesday', $this->plugin_name ),
              4 =>__( 'Thursday', $this->plugin_name ),
              5 =>__( 'Friday', $this->plugin_name ),
              6 =>__( 'Saturday', $this->plugin_name ),
        ];
    $allocationbasis_txt = $allocationbasis == 'hours' ? __( 'hours', $this->plugin_name ) : __( 'days', $this->plugin_name );
    
    function weekday( $index ) {
        $day = get_option('start_of_week') + $index;
        return ($day < 7 ) ? $day : $day - 7;
    }
?>

    <p><?php echo $introduction ?></p>
    <form method="post" id="<?php echo $this->plugin_name; ?>-form" action="<?php esc_attr_e( admin_url( 'admin-post.php' )); ?> ">
        <input type="hidden" name="action" value="ra_<?php echo $action ?>"/>
        <input type="hidden" name="_wpnonce" id="_wpnonce" value="<?php echo wp_create_nonce( $this->plugin_name . $action . $id ); ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>" /> 
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="name"><?php _e( 'Name', $this->plugin_name ); ?>
                        <span class="description"><?php _e( 'Required', $this->plugin_name ); ?></span></label></th>
                        <td><input type="text" name="name" size="50" maxlength="255" value="<?php echo $name ?>"/></td>
                </tr>
                <tr>
                    <th scope="row"><label for="allocationbasis"><?php _e( 'Allocation is done in ',$this->plugin_name ); ?></label></th>
                    <td><input type="radio" name="allocationbasis" value="hours" <?php checked ($allocationbasis, 'hours' ) ?> /><?php _e( 'Hours', $this->plugin_name ); ?>
                        <input type="radio" name="allocationbasis" value="days" <?php checked ($allocationbasis, 'days' ) ?> /><?php _e( 'Days', $this->plugin_name ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="minduration"><?php _e('Minimum duration in ',$this->plugin_name); ?>
                        <span class="duration"><?php echo $allocationbasis_txt; ?></span></label></th>
                    <td><input type="number" name="minduration" id="minduration" min="1" max="<?php echo $duration_max ?>" value="<?php echo $minduration; ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="maxduration"><?php _e('Maximum duration in ',$this->plugin_name); ?>
                        <span class="duration"><?php echo $allocationbasis_txt; ?></span></label></th>
                    <td><input type="number" name="maxduration" id="maxduration" min="1" max="<?php echo $duration_max ?>" value="<?php echo $maxduration; ?>" /></td>
                </tr>
            </tbody>
        </table>
        <table class="form-table">
            <tr>
                <th scope="row"><label><?php _e('Available at', $this->plugin_name ) ?></label></th>
                <?php for ($i = 0; $i < 7; $i++) : $day = weekday( $i ); ?>
                <td style="width:5em"><?php echo $days[ $day ]; ?><br />
                <input type="checkbox" value="checked" name="available_<?php echo $day; ?>" id="available_<?php echo $day; ?>" <?php echo $availability[ 'o' . $day ]; ?> />
                </td>
                <?php endfor; ?>
            </tr>
            <tr class="hours" style="<?php echo ($allocationbasis <> "hours" ? "display:none" : ""); ?>" >
                <th scope="row"><label><?php _e('Start', $this->plugin_name ) ?></label></th>
                <?php for ($i = 0; $i < 7 ; $i++ ) : $day = weekday( $i ); ?>
                <td><input type="text" class="timefield" name="start_<?php echo $day; ?>" id="start_<?php echo $day; ?>" value="<?php echo $availability[ 's' . $day ]; ?>" /></td>
                <?php endfor; ?>
            </tr>
            <tr class="hours" style="<?php echo ($allocationbasis <> "hours" ? "display:none" : ""); ?>" >
                <th scope="row"><label><?php _e('End', $this->plugin_name ) ?></label></th>
                <?php for ($i = 0; $i < 7 ; $i++ ) : $day = weekday( $i ); ?>
                <td><input type="text" class="timefield" name="end_<?php echo $day; ?>" id="end_<?php echo $day; ?>" value="<?php echo $availability[ 'e' . $day ]; ?>" /></td>
                <?php endfor; ?>  
            </tr>
        </table>
        <table class="form-table" id="datatable">
            <!--  ?php var_dump($datafields);var_dump (count($datafields)); ? -->
            <?php if ( is_array ($datafields) ) : foreach ($datafields as $fieldname => $fieldtype) : ?>
            <tr>
                <th scope= "row"><label for="fieldname[]" ><?php echo __( 'Custom field name', $this->plugin_name ); ?></label></th>
                <td><input name="fieldname[]" size="50" maxlength="255" value="<?php echo $fieldname; ?>"/></td>
                <th scope="row"><label for="fieldtype[]" ><?php echo __( 'Field type', $this->plugin_name ); ?></label></th>
                <td>
                    <select name="fieldtype[]">
                        <option value="text" <?php echo $fieldtype == 'text' ? 'selected' : '';?> ><?php _e( 'Text', $this->plugin_name ); ?></option>\n\
                        <option value="number" <?php echo $fieldtype == 'number' ? 'selected' : '';?> ><?php _e( 'Number', $this->plugin_name ); ?></option>
                    </select>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </table>
        <a href="#" id="adddatafield" ><?php _e( 'Add data field', $this->plugin_name ); ?></a>
        <p class="submit" id="<?php echo $this->plugin_name ?>-submit">
            <input type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', $this->plugin_name ); ?>" />
        </p>
    </form>
</div>    