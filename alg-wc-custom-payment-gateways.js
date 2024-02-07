jQuery(
	function() {
		jQuery( 'body' ).on(
			'change',
			'input[name="payment_method"]',
			function() {
				jQuery( 'body' ).trigger( 'update_checkout' );
			}
		);
	}
);
