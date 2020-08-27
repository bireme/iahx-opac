$(document).ready(function(){

    $('#btnOpenWizard').click(function(e){
        e.preventDefault();
        var wizard_id = $(this).data('wizard');

        // SmartWizard initialize
        $('#smartwizard').smartWizard({
            theme: 'dots',
            toolbarSettings: {
                showNextButton: false,
                showPreviousButton: false,
            },
            anchorSettings: {
                enableAllAnchors: true,
            },
        });
        // show modal
        $('#modal-wizard-' + wizard_id).modal('show');
        // show loading
        $('#smartwizard').smartWizard("loader", "show");
    });


    $('#smartwizard').on('change', '.step-option-list', function(){
        current_step = $(this).data('step');
        last_step = parseInt($('#smartwizard').data('total'));
        next_step = current_step+1;

        // if last step add all filters of wizard steps to form_clusters and submit the query
        if (current_step == last_step){
            $(".step-option-list").each(function(){
                var filter_name = $(this).attr('name');
                var filter_value = $(this).val();

                if (filter_name == 'wizard_option_group'){
                    // skip internal wizard filter
                }else{
                    // special treatment for filter_query
                    if(filter_name == 'filter_query'){
                        // check for multiples filters. Ex db:LILACS|type:article
                        var filters_to_apply = filter_value.split("|");
                        // split values in filter name e value
                        filters_to_apply.forEach(function(entry) {
                            var filter_name_value = entry.split(":");
                            filter_name = filter_name_value[0];
                            filter_value = filter_name_value[1];
                        });
                    }
                    add_wizard_filter(filter_name, filter_value);
                }
            });

            $('#smartwizard').hide();
            $("#form_clusters").submit();
            return true;
        }else{
            // clear content of next step div
            $('#step-' + next_step + '-title').html('');
            $('#step-' + next_step).html('');
            // load next step
            $('#smartwizard').smartWizard('next');
        }
    });


    $("#smartwizard").on("stepContent", function(e, anchorObject, stepIndex, stepDirection, stepPosition) {
        var time = 500;
        var current_step = stepIndex+1;
        var last_step = parseInt($(this).data('total'));
        var wizard_id = $(this).data('wizard');
        var ajaxURL = 'wizard/' + wizard_id + '?step=' + current_step;

        if ($('#step-' + current_step).contents().length > 0){
            return true;
        }

        if (current_step > 1){
            // get previous step option selected to update title and filter next step
            var previous_step = current_step-1;
            var previous_filter = $('#option-step-' + previous_step).attr('name');
            var previous_option_text = $('#option-step-' + previous_step + ' option:selected').text();
            var previous_option_value = $('#option-step-' + previous_step + ' option:selected').val();

            // update previous step title
            $('#step-' + previous_step + '-title').html(previous_option_text);
            // filter next step with previous values
            ajaxURL += '&filter_name=' + previous_filter + '&filter_value=' + previous_option_value + '&filter_label=' + previous_option_text;
        }

        // Return a promise object
        return new Promise((resolve, reject) => {

            // Ajax call to fetch your content
            $.ajax({
                method  : "GET",
                url     : ajaxURL,
                beforeSend: function( xhr ) {
                    // Show the loader
                    $('#smartwizard').smartWizard("loader", "show");
                }
            }).done(function( res ) {
                //console.log(res);
                var html = res;
                // Resolve the Promise with the tab content
                resolve(html);
                // Hide the loader
                $('#smartwizard').smartWizard("loader", "hide");
            }).fail(function(err) {
                // Reject the Promise with error message to show as tab content
                reject( "An error loading the resource" );
                // Hide the loader
                $('#smartwizard').smartWizard("loader", "hide");
            });

        });
        return new Promise((resolve, reject) => {
                            setTimeout( function() {
                                resolve("Success!" + (stepIndex + 1))
                                $('#smartwizard').smartWizard("loader", "hide");
                            }, time)
                            });

        return true;
    });


});
