(function($){
  $(document).ready(function(){

    $('input[type="password"]').hidePassword(true);

    // Handle Function - Other option required in Registration form
    if($('#signup_form')) {
      $('#field_287 input').on( 'change', function(){
        if( $('#option_383').is(':checked') ) {
          $('.field_367').addClass('required-field').removeClass('optional-field');
          $('#field_367').attr('required', 'true').attr('aria-required', 'true');
        } else {
          $('.field_367').removeClass('required-field').addClass('optional-field');
          $('#field_367').removeAttr('required').attr('aria-required', 'false');
        }
      })
    }

  });
})(jQuery);
