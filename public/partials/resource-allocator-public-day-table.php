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

<?php
    $now = new DateTime ();
    $current_start = new DateTime ( $display->start );
    $current_end = new DateTime ( $display->end );
    $datafields = json_decode( $resource->datafields, true );
    $data_columns_count = count ( $datafields );

    $availability = json_decode ( $resource->availability, true );

    $day = clone $current_start;
    $interval = new DateInterval( 'P1D' );
    $allocation_start = new DateTime();
    $allocation_end = new DateTime();
?>
<thead>
    <tr>
        <th style="text-align:left" >
            <button type="button" class="<?php echo $this->plugin_name ?>" data-display='<?php echo json_encode( $display ) ?>' value="prev" >&lt;&lt;</button>
        </th>
        <th style="text-align:center" colspan="2"><strong><?php echo $current_start->format('F - Y') ?></strong></th>
        <th style="text-align:right ">
            <button type="button" class="<?php echo $this->plugin_name ?>" data-display='<?php echo json_encode( $display ) ?>' value="next" >&gt;&gt;</button>
        </th>
        <?php echo str_repeat( '<th></th>', max ($data_columns_count - 2, 0 ) ) ?>     
    </tr>
    <tr>
        <th><?php _e('Day', $this->plugin_name ) ?></th>
        <th><?php _e('Who', $this->plugin_name ) ?></th>
        <?php foreach ($datafields as $datafield => $datatype) : ?> 
        <th style="text-align:<?php echo $datatype == 'text' ? 'left' : 'right' ?>" ><?php echo $datafield ?></th>
        <?php endforeach ?>
    </tr>
</thead>
<tbody>

<?php

while ( $day < $current_end ) :
    $day_of_week = $day->format('w');
    $action = ( $day >= $now ); 

    if ( $availability[ "o$day_of_week" ] == 'checked' ) :
        $available = true;
        foreach ($allocations as $allocation) {
            $allocation_start->modify( $allocation->start );
            $allocation_end->modify( $allocation->end );
            if (( $allocation_start <= $day ) && ( $allocation_end >= $day )) {
                $available = false;
                $user = get_userdata( $allocation->user_id );
                $who = $user->display_name;
                $data = json_decode( $allocation->data, true );
                //error_log(print_r ($data, true));
                $valueshtml = '';
                foreach ($datafields as $field => $type) {
                    if (array_key_exists ($field, $data)) {
                        $valueshtml .=  '<td style="text-align:' . ( $type == 'text' ? 'left' : 'right' ) . '">' . $data[$field] . '</td>' ;
                        $values[$field] = $data[$field];
                    } else {
                        $valueshtml .= '<td></td>';
                        $values[$field] = '';
                    }
                }
                //error_log ($valueshtml);
                if ($allocation->user_id == get_current_user_id() ) {
                    $color = $action ? $this->options[ 'color_allocated' ] : $this->options[ 'color_available' ];                        
                } else {
                    $color = $action ? $this->options[ 'color_unavailable' ] : $this->options[ 'color_available' ];
                    $action = ($action and current_user_can( 'edit_others_posts' ));
                    $user_id = $allocation->user_id;
                }
                $allocation;
                break; // exit de foreach loop
            }
        }
        if ( $available ) {
            $color = $this->options[ 'color_available' ];
            $valueshtml = str_repeat( '<td></td>', $data_columns_count );
            $who = $action ? __('-available-', $this->plugin_name) : "";

            foreach ($datafields as $field => $type ) {
                $values[$field] = '';
            }
            $allocation = ['id'=> '',
                'start' => $day->format (DateTime::ATOM),
                'end' => $day->format (DateTime::ATOM),
                'resource_id' => $resource->id,
                'user_id' => get_current_user_id(),
                'data' => '',
                ];
        }
        ?>
        <tr> 
        <?php if ($action) : ?>
            <th><a class="<?php echo $this->plugin_name ?> thickbox"  
                href="#TB_inline?width=520&height=350&inlineId=<?php echo $this->plugin_name . '-box-' . $resource->id ?>" 
                rel="bookmark"
                data-display='<?php echo json_encode( $display ) ?>' 
                data-allocation='<?php echo json_encode( $allocation ) ?>' 
                data-values='<?php echo json_encode( $values ) ?>' > 
                <?php echo $day->format('j D') ?></a></th>
        <?php else : ?>
            <th><?php echo $day->format('j D') ?></th>
        <?php endif ?>
            <td style="color:<?php echo $color ?>" ><?php echo $who ?></td><?php echo $valueshtml ?>
        </tr>
<?php    
    endif;
    $day = $day->add( $interval );
endwhile ?>
</tbody>
<tfoot>
    <tr>
        <th style="text-align:left" >
            <button type="button" class="<?php echo $this->plugin_name ?>" data-display='<?php echo json_encode( $display ) ?>' value="prev" >&lt;&lt;</button>
        </th>
        <th style="text-align:center" colspan="2"><strong><?php echo $current_start->format('F - Y') ?></strong></th>
        <th style="text-align:right">
            <button type="button" class="<?php echo $this->plugin_name ?>" data-display='<?php echo json_encode( $display ) ?>' value="next" >&gt;&gt;</button>
        </th>
        <?php echo str_repeat( '<th></th>', max ($data_columns_count - 2, 0) ) ?>     
    </tr>
</tfoot>
        