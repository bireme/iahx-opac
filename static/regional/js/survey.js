(function( $ ) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    $(function() {

        // Suppressing the default validation bubbles
        // document.querySelector( "form#feedbackForm" )
        //     .addEventListener( "invalid", function( event ) {
        //         event.preventDefault();
        //     }, true );

        $(document).on('click','#iconFeedback',function(e){
            $('#boxFeedback').toggleClass("boxFeedback");
        });

        $(document).on('click','#feedbackFechar',function(e){
            $('#boxFeedback').removeClass("boxFeedback");
        })

        /* Add */
        $(document).on('mouseenter','.star-rating',function(e){
            $(this).addClass("fas");
            $(this).prevAll().addClass("fas");
        })

        /* Remove */
        $(document).on('mouseleave','.star-rating',function(e){
            $(this).removeClass("fas", "fa-star");
            $(this).prevAll().removeClass("fas", "fa-star");
        })

        /* Click */
        $(document).on('click','.star-rating',function(e){
            var rating = $(this).data('rating');
            $('#rating').val(rating);
            $('.star-rating').removeClass("fa-star, starClick");
            $(this).addClass("fa-star, starClick");
            $(this).prevAll().addClass("fa-star, starClick");
        })

        $(document).on('click','label.btn-yesno',function(e){
            $(this).find(':radio').attr('checked', 'checked');
        })
        
        $(document).on('click','div.rowQuestion input:radio, label.btn-yesno, .star-rating',function(e){
            // Check if all questions have been answered
            var len = $('div.rowQuestion:not(:has(:radio:checked,:hidden[value!=""]))').length;
            if ( len == 0 ) $('#formdata-submit').attr("disabled", false);
        });

        // Attach a submit handler to the form
        $(document).on('click','#formdata-submit',function(e){
            // Stop form from submitting normally
            e.preventDefault();

            var datastring = [];
            var url = $('#feedbackForm').attr("action");
         
            // Get some values from elements on the page
            $("#conteudoFeedback form").each(function(i){
                var $form = $( this );
                datastring[i] = $form.serialize();
            });

            // Send the data using post
            var posting = $.post( url, datastring.join('&') );
         
            // Put the results in a div
            posting.done(function( data ) {
                $(".im-questions").hide();
                $(".im-formdata-submit").hide();

                if ( data == 'True' ) {       
                    $(".feedback-message").find('.result-error').hide();
                } else {
                    $(".feedback-message").find('.result-ok').hide();
                }

                $(".feedback-message").show();
            });
        });

    });

})( jQuery );
