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
    $start_time = new DateTime();
    $end_time = new DateTime();
    $datafields = json_decode( $resource->datafields, true );

    $availability = json_decode ( $resource->availability, true );

    $day = clone $current_start;
    $interval = new DateInterval( 'P1D' );
    $minduration = new DateInterval ( 'PT' . $resource->minduration . 'H' );

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
        <?php echo str_repeat( '<th></th>', max (count ($datafields) - 2, 0 ) ) ?>     
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

    if ( $availability[ "o$day_of_week" ] == 'checked' ) : ?>
    <tr class="<?php echo $this->plugin_name . '-accordion' ?>"> 
        <td colspan="<?php echo count($datafields) + 2 ?>" ><?php echo $day->format('j D') ?></td>
    </tr>        
        <?php 
        $start_time->modify ($day->format('Y-m-d') . " " . $availability[ "s$day_of_week" ] );
        $end_time->modify ($day->format('Y-m-d') . " " . $availability[ "e$day_of_week" ] );
        error_log('Current day: start_time ' . $start_time->format (DateTime::ATOM) . ' end_time ' . $end_time->format (DateTime::ATOM));

        foreach ($allocations as $allocation) :
            $allocation_start->modify( $allocation->start );
            $allocation_end->modify( $allocation->end );
            
            if (( $allocation_start > $start_time ) && 
                    ( $allocation_end <= $end_time ) &&
                    ($start_time->diff($allocation_start) >= $minduration) && 
                    ($allocation_start > $now )) :   
                error_log('Found empty: start_time ' . $start_time->format (DateTime::ATOM) . ' allocation_start ' . $allocation_start->format (DateTime::ATOM));
                echo $this->make_row ($start_time, $allocation_start, $display, $resource, true);
                $start_time->modify( $allocation->start );
            endif;
            
            if (( $allocation_start >= $start_time ) && 
                    ( $allocation_end <= $end_time )) :
                error_log('Found allocated: allocation_start ' . $allocation_start->format (DateTime::ATOM) . ' allocation_end ' . $allocation_end->format (DateTime::ATOM));
                echo $this->make_row ($allocation_start, $allocation_end, $display, $resource, false, $allocation);
                $start_time->modify( $allocation->end );
            endif;

        endforeach;
        //error_log('start_time ' . $start_time->format (DateTime::ATOM) . ' end_time ' . $end_time->format (DateTime::ATOM));
        // error_log('difference ' . print_r($start_time->diff($end_time),true) . ' duration ' . print_r($minduration, true));
        $remains = $start_time->diff($end_time);
        if (( $remains->h * 60 + $remains->i ) >= ( $minduration->h * 60 + $remains->i ) && 
                ($end_time > $now)) : 
            error_log('Found empty: start_time ' . $start_time->format (DateTime::ATOM) . ' end_time ' . $end_time->format (DateTime::ATOM) );
            echo $this->make_row ($start_time, $end_time, $display, $resource, true);
        endif;
    endif;
    $day->add( $interval );
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
        <?php echo str_repeat( '<th></th>', max (count($datafields) - 2, 0) ) ?>     
    </tr>
</tfoot>
