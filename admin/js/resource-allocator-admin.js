(function( $ ) {
	'use strict';

        $(document).ready(function () {
            /**
             * render fields if the choice for allocation basis is hours
             */
            $( "input[value='hours']" ).change(function() {
                $( ".duration" ).each( function() { $(this).text(resourceallocator.hours)});
                $( ".hours" ).each( function() { $(this).show();});
                $( "#minduration" ).attr( "min", "1" ).attr( "max", "24" ).val('');
                $( "#maxduration" ).attr( "min", "1" ).attr( "max", "24" ).val('');
            });
            
            /**
             * render fields if the choice for allocation basis is days
             */
            $( "input[value='days']" ).change(function() { 
                $( ".duration" ).each( function() { $(this).text(resourceallocator.days)});
                $( ".hours").each( function() { $(this).hide();});
                $( "#minduration" ).attr( "min", "1" ).attr( "max", "999" ).val('');
                $( "#maxduration" ).attr( "min", "1" ).attr( "max", "999" ).val('');
            });
            
            /**
             * create time input fields for hours:minutes
             */
            $( ".timefield" ).each(function() {
                var day = $(this).attr( "name" ).slice(-1);
                var enable = $( "#available_" + day).prop( "checked" );
                $(this).timeEntry({
                    show24Hours: ( resourceallocator.timeformat.match( "[GH]" ) !== null ),
                    spinnerImage: ( "" )
                    }
                );
                $(this).timeEntry(enable ? "enable" : "disable" );
            });
            
            $( ".colorfield" ).each(function(){
                $(this).wpColorPicker();
            });
            
            /**
             * disable the time input fields for non available days
             */
            $( "input[name^='available_']" ).change(function() {
                var day = $(this).attr( "name" ).slice( -1 );
                var enable = this.checked; 
                $( "#start_" + day).timeEntry(enable ? "enable" : "disable" );                
                $( "#end_" + day).timeEntry(enable ? "enable" : "disable" );                
            });
            
            /**
             * Create dynamic fields for input of custom data fields
             */
            $( "#adddatafield" ).click (function () {
                var html = $('<tr></tr>').html("\
                    <th scope= 'row'><label for='fieldname[]' >" + resourceallocator.fieldname + "</label></th>\n\
                    <td><input name='fieldname[]' size='50' maxlength='255' /></td>\n\
                    <th scope='row'><label for='fieldtype[]' >" + resourceallocator.fieldtype + "</label></th>\n\
                    <td><select name='fieldtype[]'><option value='text' >" + resourceallocator.text + "</option>\n\
                    <option value='number' >" + resourceallocator.number + "</option></select></td>");
                html.appendTo($("#datatable"));
            });
            
            postboxes.add_postbox_toggles(pagenow);
        });
        
})( jQuery );
