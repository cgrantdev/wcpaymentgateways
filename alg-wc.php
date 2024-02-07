<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Custom_Payment_Gateways' ) ) :

	final class Alg_WC_Custom_Payment_Gateways {

		public $version = '1.8.0';
		protected static $_instance = null;
		protected $core = null;
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct() {

			// Check for active plugins.
			if (
			! $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ||
			( 'custom-payment-gateways-for-woocommerce.php' === basename( __FILE__ ) && $this->is_plugin_active( 'custom-payment-gateways-for-woocommerce-pro/custom-payment-gateways-for-woocommerce-pro.php' ) )
			) {
				return;
			}

			// Set up localisation.
			load_plugin_textdomain( 'custom-payment-gateways-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

			// Pro.
			if ( 'custom-payment-gateways-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
				require_once 'includes/pro/class-alg-wc-custom-payment-gateways-pro.php';
			}

			// Include required files.
			$this->includes();

			// Admin.
			if ( is_admin() ) {
				$this->admin();
			}
		}

		public function is_plugin_active( $plugin ) {
			return ( function_exists( 'is_plugin_active' ) ? is_plugin_active( $plugin ) :
			(
				in_array( $plugin, apply_filters( 'active_plugins', (array) get_option( 'active_plugins', array() ) ), true ) ||
				( is_multisite() && array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) ) )
			)
			);
		}

		public function includes() {
			// Functions.
			require_once 'includes/alg-wc-custom-payment-gateways-functions.php';
			// Core.
			$this->core = require_once 'includes/class-alg-wc-custom-payment-gateways-core.php';
		}

		public function admin() {
			// Action links.
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
			// Settings.
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			// Version update.
			if ( get_option( 'alg_wc_custom_payment_gateways_version', '' ) !== $this->version ) {
				add_action( 'admin_init', array( $this, 'version_updated' ) );
			}
			// HPOS compatibility
			add_action( 'before_woocommerce_init', array( $this, 'cpg_declare_hpos_compatibility' ) );
		}

		public function cpg_declare_hpos_compatibility() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}

		public function action_links( $links ) {
			$custom_links   = array();
			$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_custom_payment_gateways' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
			if ( 'custom-payment-gateways-for-woocommerce.php' === basename( __FILE__ ) ) {
				$custom_links[] = '<a target="_blank" href="https://imaginate-solutions.com/downloads/custom-payment-gateways-for-woocommerce/">' .
				__( 'Unlock All', 'custom-payment-gateways-woocommerce' ) . '</a>';
			}
			return array_merge( $custom_links, $links );
		}

		public function add_woocommerce_settings_tab( $settings ) {
			$settings[] = require_once 'includes/settings/class-alg-wc-settings-custom-payment-gateways.php';
			return $settings;
		}

		public function version_updated() {
			update_option( 'alg_wc_custom_payment_gateways_version', $this->version );
		}

		public function plugin_url() {
			return untrailingslashit( plugin_dir_url( __FILE__ ) );
		}

		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

	}

endif;

if ( ! function_exists( 'alg_wc_custom_payment_gateways' ) ) {

	function alg_wc_custom_payment_gateways() {
		return Alg_WC_Custom_Payment_Gateways::instance();
	}
}

alg_wc_custom_payment_gateways();
