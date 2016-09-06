(function( $ ) {
	'use strict';

        /**
         * show the contents for every table at the page when loaded
         */
        $(document).ready(function(){
            /**
             * prepare the initial call to show the table contents
             */
            $( "table.resourceallocator" ).each(function () {
                var display = new Object();
                var today = new Date();
                var start = new Date(Date.UTC (today.getUTCFullYear(), today.getUTCMonth(), 1));
                var end = new Date(Date.UTC( today.getUTCFullYear(), today.getUTCMonth() + 1, 1));
                display[ "resource_id" ] = $(this).data( "resource-id" );
                display[ "type" ] = $(this).data( "type" );
                display[ "start" ] = start.toISOString();
                display[ "end" ] = end.toISOString();
                resourceallocator_show( JSON.stringify( display ) );
            });
            
            /**
             * handler for the accordion
             */
            $("body").on("click", "tr.resourceallocator-accordion", function () {
                $(this).parents("tbody").children("tr.resourceallocator-panel").hide();
                $(this).nextUntil("tr.resourceallocator-accordion", "tr.resourceallocator-panel").toggle();
            });
           
            /**
             * handler for prev and next buttons
             * As these are dynamically created, the $(document).on construct is needed
             */
            $("body").on("click", "button.resourceallocator", function(){
                var display = $(this).data( "display" );
                var start = new Date( display[ "start"] );
                var end = new Date( display[ "end" ] );

                if ( $(this).val() === "prev" ) {
                    start.setUTCMonth( start.getUTCMonth() - 1 );
                    end.setUTCMonth( end.getUTCMonth() - 1 );
                } else {
                    start.setMonth( start.getMonth() + 1 );
                    end.setMonth( end.getMonth() + 1 );
                }
                display[ "start" ] = start.toISOString();
                display[ "end" ] = end.toISOString();

                resourceallocator_show( JSON.stringify( display ) );
            });

            /**
             * handler for the allocation anchor 
             * As this is dynamically created, the $(document).on construct is needed
             */
            $("body").on("click", "a.resourceallocator", function(){
                var display = $(this).data( "display" );
                var allocation = $(this).data( "allocation" );
                var values = $(this).data( "values" );

                $( "#resourceallocator-display-" + allocation["resource_id"] ).val( JSON.stringify( display ) );
                $( "#resourceallocator-allocation-" + allocation["resource_id"] ).val( JSON.stringify( allocation ) );
                $( "#resourceallocator-user-id-" + allocation["resource_id"] ).val( allocation["user_id"] );

                var j = 1;
                for (var key in values) {
                    $("#resourceallocator-data-" + allocation["resource_id"] + "-" + j++).val(values[key]);
                }; 
                
                    var start = new Date( allocation["start"]);
                    $("#resourceallocator-date-" + allocation["resource_id"]).text(start.toLocaleDateString(resourceallocator.locale));
                if (display[ "type" ] === "days") {
                } else {
                    var start = new Date ( allocation["start"]);
                    var end = new Date ( allocation["end"]);
                    var duration = end.getTime(end) - start.getTime(start);
                    var tzo = start.getTimezoneOffset()/60;
                    $("#resourceallocator-start-" + allocation["resource_id"]).timeEntry();
                    $("#resourceallocator-duration-" + allocation["resource_id"]).timeEntry();
                    $("#resourceallocator-start-" + allocation["resource_id"]).val( resourceallocator_time (start.getHours() + tzo, start.getMinutes() ) );
                    $("#resourceallocator-duration-" + allocation["resource_id"]).val( resourceallocator_time (duration / 36e5, duration % 36e5 / 6e4 ) );
                }
                if (allocation["id"] != "") {
                    $("#resourceallocator-text-" + allocation["resource_id"]).text(resourceallocator.text_edit_delete);
                    $("#resourceallocator-save-" + allocation["resource_id"]).text(resourceallocator.edit);
                    $("#resourceallocator-delete-" + allocation["resource_id"]).show();
                } else {
                    $("#resourceallocator-text-" + allocation["resource_id"]).text(resourceallocator.text_create);
                    $("#resourceallocator-save-" + allocation["resource_id"]).text(resourceallocator.create);
                    $("#resourceallocator-delete-" + allocation["resource_id"]).hide();
                }
            });
            
            /**
             * handler for save (edit/create) button
             */
            $( "button.resourceallocator-save" ).click( function () {
                var resource_id = $(this).val();
                var user_id = $( "#resourceallocator-user-id-" + resource_id ).val();
                var display_json = $( "#resourceallocator-display-" + resource_id ).val();
                var allocation_json = $( "#resourceallocator-allocation-" + resource_id ).val();
                var allocation = JSON.parse ( allocation_json );
                allocation[ "user_id" ] = user_id;
                
                var values = new Object();
                $("[id^='resourceallocator-data-" + resource_id + "']").each(function() {
                    values[$(this).data("field")] = $(this).val();
                });
                resourceallocator_save( display_json, JSON.stringify( allocation ), JSON.stringify( values ));
            });  
            
            /**
             * handler for delete button
             */
            $( "button.resourceallocator-delete" ).click( function () {
                var resource_id = $(this).val();
                var display_json = $( "#resourceallocator-display-" + resource_id ).val();
                var allocation_json = $( "#resourceallocator-allocation-" + resource_id ).val();
                resourceallocator_delete( display_json, allocation_json );
            });  

            /**
             * create time input fields for hours:minutes
             */
            $( ".timefield" ).each(function() {
                $(this).timeEntry({
                    show24Hours: ( resourceallocator.timeformat.match( "[GH]" ) !== null ),
                    spinnerImage: ( "" )
                    }
                );
            });

        }); // document ready 

    /**
     * helper function to format time
     * @param {int} hours
     * @param {int} minutes
     * @returns {String}
     */
    function resourceallocator_time ( hours, minutes ) {
        return (("0" + hours).slice(-2) + ":" + ("0" + minutes).slice(-2)); 
    }

    /**
     * helper function to call ajax request to show the current resource allocations
     * @param {String} display_json
     * @returns {undefined}
     */ 
    function resourceallocator_show( display_json ) {
        $.ajax({
            url: resourceallocator.base_url + '/show/',
            method: 'GET', //POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', resourceallocator.nonce);
            },
            data: {
                display: display_json
            },
            success: function (response) {
                var top = $('#resourceallocator-title-' + response.id).scrollTop();
                $('#resourceallocator-contents-' + response.id).html(response.html);
                $('#resourceallocator-title-' + response.id).scrollTop(top);
            },
            error: function (jqXHR, exception, errorThrown) {
                alert (resourceallocator_error (jqXHR, exception, errorThrown));
            }
        });
    }

    /**
     * helper function to call ajax request to save created or modified resource allocation
     * @param {String} display_json
     * @param {String} allocation_json
     * @param {String} values_json
     * @returns {undefined}
     */ 
    function resourceallocator_save( display_json, allocation_json, values_json ) {
        self.parent.tb_remove();
        $.ajax({
            url: resourceallocator.base_url + '/save/',
            method: 'POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', resourceallocator.nonce);
            },
            data: {
                display: display_json,
                allocation: allocation_json,
                values: values_json
            },
            success: function (response) {
                var top = $('#resourceallocator-title-' + response.id).scrollTop();
                $('#resourceallocator-contents-' + response.id).html(response.html);
                $('#resourceallocator-title-' + response.id).scrollTop(top);
            },
            error: function (jqXHR, exception, errorThrown) {
                alert (resourceallocator_error (jqXHR, exception, errorThrown));
            }
        });
    }

    /**
     * helper function to call ajax request to delete resource allocation
     * @param {String} display_json
     * @param {String} allocation_json
     * @returns {undefined}
     */ 
    function resourceallocator_delete( display_json, allocation_json) {
        self.parent.tb_remove();
        $.ajax({
            url: resourceallocator.base_url + '/delete/',
            method: 'POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', resourceallocator.nonce);
            },
            data: {
                display: display_json,
                allocation: allocation_json
            },
            success: function (response) {
                var top = $('#resourceallocator-title-' + response.id).scrollTop();
                $('#resourceallocator-contents-' + response.id).html(response.html);
                $('#resourceallocator-title-' + response.id).scrollTop(top);
            },
            error: function (jqXHR, exception, errorThrown) {
                alert (resourceallocator_error (jqXHR, exception, errorThrown));
            }
        });
    }

    /**
     * helper function to retrieve error text
     * @param {Object} jqXHR
     * @param {String} exception
     * @param {String} errorThrown
     * @returns {String}
     */
    function resourceallocator_error (jqXHR, exception, errorThrown) {
        if (exception === "error") {
            var response = JSON.parse (jqXHR.responseText);
            return (response.message);
        } else if (exception === 'parsererror') {
            return ('Requested JSON parse failed.');
        } else if (exception === 'timeout') {
            return ('Time out error.');
        } else if (exception === 'abort') {
            return ('Ajax request aborted.');
        } else {
            return ('Uncaught Error.\n' + jqXHR.responseText);
        }
    }

})( jQuery );
