jQuery('document').ready(function(){

	$ = jQuery;

	var form = $('form#userswitch_usearch_form');

	form.submit(function(e) {

		e.preventDefault();

		var user = $('#userswitch_username').val();
		var nonce = $('input[name="userswitch_search_nonce"]').val();

		$.ajax({
	        type : 'POST',
	        url : ScriptData.ajaxurl,
	        data : {
		        action : 'userswitch_user_search',
		        username : user,
		        nonce : nonce
	        },
	        beforeSend : function() {
		        $('#userswitch_username').prop('disabled',true);
	        },
	        success : function( response ) {
		        $('#userswitch_username').prop( 'disabled', false );
		        $('#userswitch_usearch_result').html( response );
	        }
        });

		return false;
	});

	$('#wp-admin-bar-tikemp_impresonate_user').click(function(){
		$('input[id="userswitch_username"]').focus();
	});

	$('#switchuser_usearch_result').niceScroll({
		autohidemode:'leave'
	});

});