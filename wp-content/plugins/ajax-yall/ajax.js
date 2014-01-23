jQuery(document).ready(function($){

	$('button.ajax-yall').click(function(){

        var $el = $(this);

		$.get(
            ajaxYall.ajaxurl,
            {
                action	: ajaxYall.action,
                nonce  	: ajaxYall.nonce,
                postID 	: $el.attr('data-postid')
            },
            function( response, textStatus, jqXHR ){
                if( 200 == jqXHR.status && 'success' == textStatus ) {
                    if( 'success' == response.status ){
                        $el.after( '<p style="color:green;">' + response.message + '</p>' );
                    } else {
                        $el.after( '<p style="color:red;">' + response.message + '</p>' );
                    }
                    console.log( response, textStatus, jqXHR );
                }
            },
            'json'
		);

	});

});