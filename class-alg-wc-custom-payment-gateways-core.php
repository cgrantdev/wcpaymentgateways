<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Custom_Payment_Gateways_Core' ) ) :
	class Alg_WC_Custom_Payment_Gateways_Core {
		public function __construct() {
			if ( 'yes' === get_option( 'alg_wc_custom_payment_gateways_enabled', 'yes' ) ) {
				// Include custom payment gateways class.
				require_once 'class-wc-gateway-alg-custom.php';
				// Input fields.
				if ( 'yes' === get_option( 'alg_wc_cpg_input_fields_enabled', 'yes' ) ) {
					require_once 'class-alg-wc-custom-payment-gateways-input-fields.php';
				}
				// Fees.
				if ( 'yes' === get_option( 'alg_wc_cpg_fees_enabled', 'yes' ) ) {
					require_once 'class-alg-wc-custom-payment-gateways-fees.php';
				}
			}
		}

	}

endif;
return new Alg_WC_Custom_Payment_Gateways_Core();
