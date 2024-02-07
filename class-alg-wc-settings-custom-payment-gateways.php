<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Settings_Custom_Payment_Gateways' ) ) :

	class Alg_WC_Settings_Custom_Payment_Gateways extends WC_Settings_Page {

		public function __construct() {
			$this->id    = 'alg_wc_custom_payment_gateways';
			$this->label = __( 'Custom Payment Gateways', 'custom-payment-gateways-woocommerce' );
			parent::__construct();
			add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'maybe_unsanitize_option' ), PHP_INT_MAX, 3 );
			// Sections.
			require_once 'class-alg-wc-custom-payment-gateways-settings-section.php';
			require_once 'class-alg-wc-custom-payment-gateways-settings-general.php';
			require_once 'class-alg-wc-custom-payment-gateways-settings-input-fields.php';
			require_once 'class-alg-wc-custom-payment-gateways-settings-fees.php';
			require_once 'class-alg-wc-custom-payment-gateways-settings-advanced.php';
		}

		public function maybe_unsanitize_option( $value, $option, $raw_value ) {
			return ( ! empty( $option['alg_wc_cpg_raw'] ) ? $raw_value : $value );
		}

		public function get_settings() {
			global $current_section;
			return array_merge(
				apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() ),
				array(
					array(
						'title' => __( 'Reset Settings', 'custom-payment-gateways-woocommerce' ),
						'type'  => 'title',
						'id'    => $this->id . '_' . $current_section . '_reset_options',
					),
					array(
						'title'   => __( 'Reset section settings', 'custom-payment-gateways-woocommerce' ),
						'desc'    => '<strong>' . __( 'Reset', 'custom-payment-gateways-woocommerce' ) . '</strong>',
						'id'      => $this->id . '_' . $current_section . '_reset',
						'default' => 'no',
						'type'    => 'checkbox',
					),
					array(
						'type' => 'sectionend',
						'id'   => $this->id . '_' . $current_section . '_reset_options',
					),
				)
			);
		}

		public function maybe_reset_settings() {
			global $current_section;
			if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
				foreach ( $this->get_settings() as $value ) {
					if ( isset( $value['id'] ) ) {
						$id = explode( '[', $value['id'] );
						delete_option( $id[0] );
					}
				}
				add_action( 'admin_notices', array( $this, 'admin_notice_settings_reset' ) );
			}
		}

		public function admin_notice_settings_reset() {
			echo '<div class="notice notice-warning is-dismissible"><p><strong>' .
			esc_attr__( 'Your settings have been reset.', 'custom-payment-gateways-woocommerce' ) . '</strong></p></div>';
		}

		public function save() {
			parent::save();
			$this->maybe_reset_settings();
		}

	}

endif;

return new Alg_WC_Settings_Custom_Payment_Gateways();
