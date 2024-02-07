<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Custom_Payment_Gateways_Settings_Section' ) ) :
	class Alg_WC_Custom_Payment_Gateways_Settings_Section {

		public function __construct() {
			add_filter( 'woocommerce_get_sections_alg_wc_custom_payment_gateways', array( $this, 'settings_section' ) );
			add_filter( 'woocommerce_get_settings_alg_wc_custom_payment_gateways_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
		}

		public function settings_section( $sections ) {
			$sections[ $this->id ] = $this->desc;
			return $sections;
		}

	}

endif;
