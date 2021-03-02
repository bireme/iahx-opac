$(document).ready(function(){

    const queryString = window.location.search;
    const url_params = new URLSearchParams(queryString);

    $('#btnOpenWizard').click(function(e){
        e.preventDefault();
        var wizard_id = $(this).data('wizard');

        $('#modal-wizard-' + wizard_id).modal({
            backdrop: 'static',
            keyboard: false
        });

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
    });


    $('#smartwizard').on('change', '.step-option-list', function(){
        current_step = $(this).data('step');
        last_step = parseInt($('#smartwizard').data('total'));
        next_step = current_step+1;

        // register current step info at Google Analytics
        var user_selected_options = $(".step-option-list");

        user_selected_options.each(function(index){
            var current_step_index = parseInt(current_step)-1;
            if (index == current_step_index){
                var step_selected_option = $("option:selected", this);
                var step_filter_label = step_selected_option.text();

                // fire GA event
                gtag('event', 'Step ' + current_step, {'event_category': 'Wizard', 'event_label': step_filter_label});
            }
        });


        // if last step add all filters of wizard steps to form_clusters and submit the query
        if (current_step == last_step){
            // remove all previous interface applied filters
            $("#form_clusters input[name^='filter']").remove();

            var user_selected_options = $(".step-option-list");

            user_selected_options.each(function(index){
                var filter_name = $(this).attr('name');
                var filter_value = $(this).val();
                var filter_label = $(this).text();

                if (filter_name == 'wizard_option_group'){
                    // skip internal wizard filter
                }else{

                    // special treatment for filter_query (last step of wizard)
                    if(filter_name == 'filter_query'){
                        // check for multiples filters. Ex db:LILACS|type:article
                        var filters_to_apply = filter_value.split("|");
                        // split values in filter name e value
                        filters_to_apply.forEach(function(entry) {
                            var filter_name_value = entry.split(":");
                            filter_name = filter_name_value[0];
                            filter_value = filter_name_value[1];

                            // add filter to search form
                            add_wizard_filter(filter_name, filter_value);
                            // add last step filter to session
                            $.post("wizard-session/add", {"filter_name": filter_name, "filter_value": filter_value, "filter_label": filter_label, "filter_step": current_step});
                        });

                    }else{
                        // add filter to search form
                        add_wizard_filter(filter_name, filter_value);
                    }
                }
            });

            $('#smartwizard').hide();
            $("#form_clusters").submit();
            return true;
        }else{
            // clear content of nexts steps div
            for (s = next_step; s <= last_step; s++){
                $('#step-' + s + '-title').html('');
                $('#step-' + s).html('');
            }
            // load next step
            $('#smartwizard').smartWizard('next');
        }
    });


    $("#smartwizard").on("stepContent", function(e, anchorObject, stepIndex, stepDirection, stepPosition) {
        var time = 500;
        var current_step = stepIndex+1;
        var last_step = parseInt($(this).data('total'));
        var wizard_id = $(this).data('wizard');
        var ajaxURL = 'wizard/' + wizard_id + '?step=' + current_step + '&lang=' + LANG;

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
            if (previous_option_text != ''){
                $('#step-' + previous_step + '-title').html(previous_option_text);
            }
            // filter next step with previous values
            ajaxURL += '&previous_filter_name=' + previous_filter + '&previous_filter_value=' + previous_option_value + '&previous_filter_label=' + previous_option_text;
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

    // check if url has parameter to show wizard popup on page  load
    if (url_params.has('display_wizard')){
        $('#btnOpenWizard').click();
    }

});
