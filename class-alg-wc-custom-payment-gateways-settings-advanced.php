<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Custom_Payment_Gateways_Settings_Advanced' ) ) :
	class Alg_WC_Custom_Payment_Gateways_Settings_Advanced extends Alg_WC_Custom_Payment_Gateways_Settings_Section {

		public $id = '';

		public $desc = '';

		public function __construct() {
			$this->id   = 'advanced';
			$this->desc = __( 'Advanced', 'custom-payment-gateways-woocommerce' );
			parent::__construct();
		}

		public function get_settings() {
			$settings = array(
				array(
					'title' => __( 'Advanced Options', 'custom-payment-gateways-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_wc_cpg_advanced_options',
				),
				array(
					'title'    => __( 'Shipping methods', 'custom-payment-gateways-woocommerce' ),
					'desc_tip' => __( 'Used in "Enable for shipping methods" custom payment gateway\'s option.', 'custom-payment-gateways-woocommerce' ),
					'type'     => 'select',
					'class'    => 'chosen_select',
					'id'       => 'alg_wc_cpg_load_shipping_method_instances',
					'default'  => 'yes',
					'options'  => array(
						'yes'     => __( 'Load shipping methods and instances', 'custom-payment-gateways-woocommerce' ),
						'no'      => __( 'Load shipping methods only', 'custom-payment-gateways-woocommerce' ),
						'disable' => __( 'Do not load', 'custom-payment-gateways-woocommerce' ),
					),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_cpg_advanced_options',
				),
			);
			return $settings;
		}

	}

endif;

return new Alg_WC_Custom_Payment_Gateways_Settings_Advanced();
