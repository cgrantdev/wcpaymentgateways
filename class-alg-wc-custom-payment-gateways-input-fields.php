<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\Utilities\OrderUtil;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

if ( ! class_exists( 'Alg_WC_Custom_Payment_Gateways_Input_Fields' ) ) :
	class Alg_WC_Custom_Payment_Gateways_Input_Fields {
		public function __construct() {
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'check_required_input_fields' ), PHP_INT_MAX, 2 );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'add_input_fields_to_order_meta' ), PHP_INT_MAX, 2 );
			add_action( 'add_meta_boxes', array( $this, 'add_input_fields_meta_box' ), 10, 2 );
			add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_input_fields_to_order_details' ) );
			add_action( 'woocommerce_email_after_order_table', array( $this, 'add_input_fields_to_emails' ), 10, 4 );
			if ( 'yes' === get_option( 'alg_wc_cpg_input_fields_woe_enabled', 'no' ) ) {
				add_filter( 'woe_get_order_value__alg_wc_cpg_input_fields', array( $this, 'woe_process_input_fields' ), 10, 3 );
			}
		}

		public function woe_process_input_fields( $value, $order, $field ) {
			$order_id = $order->get_id();
			if ( $order_id ) {
				$input_fields = $order->get_meta( '_alg_wc_cpg_input_fields', true );
				//$input_fields = get_post_meta( $order_id, '_alg_wc_cpg_input_fields', true );
				if ( is_array( $input_fields ) ) {
					$template = get_option( 'alg_wc_cpg_input_fields_woe_template', '%title%: %value%' );
					$glue     = get_option( 'alg_wc_cpg_input_fields_woe_glue', ' | ' );
					$output   = array();
					foreach ( $input_fields as $field_title => $field_value ) {
						$output[] = str_replace( array( '%title%', '%value%' ), array( $field_title, $field_value ), $template );
					}
					return implode( $glue, $output );
				} else {
					return $input_fields;
				}
			}
			return $value;
		}

		public function get_input_fields_output( $fields, $templates ) {
			$fields_html = '';
			foreach ( $fields as $title => $value ) {
				$fields_html .= str_replace( array( '%title%', '%value%' ), array( $title, wpautop( $value ) ), $templates['item'] );
			}
			return $templates['start'] . $fields_html . $templates['end'];
		}

		public function add_input_fields_to_emails( $order, $sent_to_admin, $plain_text, $email ) {
			if ( 'no' === get_option( 'alg_wc_cpg_input_fields_add_to_emails', 'no' ) ) {
				return;
			}
			if (
			'customer' === get_option( 'alg_wc_cpg_input_fields_add_to_emails_sent_to', 'all' ) && $sent_to_admin ||
			'admin' === get_option( 'alg_wc_cpg_input_fields_add_to_emails_sent_to', 'all' ) && ! $sent_to_admin
			) {
				return;
			}
			$input_fields_meta = $order->get_meta( '_alg_wc_cpg_input_fields', true );
			//$input_fields_meta = get_post_meta( $order->get_id(), '_alg_wc_cpg_input_fields', true );
			if ( ! empty( $input_fields_meta ) ) {
				$templates = ( $plain_text ?
				get_option( 'alg_wc_cpg_input_fields_add_to_emails_template_plain', array() ) :
				get_option( 'alg_wc_cpg_input_fields_add_to_emails_template', array() ) );
				$start     = ( isset( $templates['header'] ) ? $templates['header'] : '' );
				$item      = ( isset( $templates['field'] ) ? $templates['field'] : ( $plain_text ? '%title%: %value%' . "\n" : '<p>%title%: %value%</p>' ) );
				$end       = ( isset( $templates['footer'] ) ? $templates['footer'] : '' );
				echo $this->get_input_fields_output(
					$input_fields_meta,
					array(
						'start' => $start,
						'item'  => $item,
						'end'   => $end,
					)
				);
			}
		}

		public function add_input_fields_to_order_details( $order ) {
			if ( 'no' === get_option( 'alg_wc_cpg_input_fields_add_to_order_details', 'no' ) ) {
				return;
			}
			$input_fields_meta = $order->get_meta( '_alg_wc_cpg_input_fields', true );
			//$input_fields_meta = get_post_meta( $order->get_id(), '_alg_wc_cpg_input_fields', true );
			if ( ! empty( $input_fields_meta ) ) {
				$templates = get_option( 'alg_wc_cpg_input_fields_add_to_order_details_template', array() );
				$start     = ( isset( $templates['header'] ) ? $templates['header'] : '<table class="widefat striped"><tbody>' );
				$item      = ( isset( $templates['field'] ) ? $templates['field'] : '<tr><th>%title%</th><td>%value%</td></tr>' );
				$end       = ( isset( $templates['footer'] ) ? $templates['footer'] : '</tbody></table>' );
				echo $this->get_input_fields_output(
					$input_fields_meta,
					array(
						'start' => $start,
						'item'  => $item,
						'end'   => $end,
					)
				);
			}
		}

		public function check_required_input_fields( $data, $errors ) {
			if ( ! empty( $data['payment_method'] ) ) {
				if ( isset( $_POST['alg_wc_cpg_input_fields_required'][ $data['payment_method'] ] ) ) {
					foreach ( $_POST['alg_wc_cpg_input_fields_required'][ $data['payment_method'] ] as $required_field_name => $is_required ) {
						if (
						! isset( $_POST['alg_wc_cpg_input_fields'][ $data['payment_method'] ][ $required_field_name ] ) ||
						'' === $_POST['alg_wc_cpg_input_fields'][ $data['payment_method'] ][ $required_field_name ]
						) {
							$errors->add(
								'alg_wc_custom_payment_gateways',
								// translators: %s Required field name.
								sprintf( __( '%s is a required field.', 'custom-payment-gateways-woocommerce' ), '<strong>' . $required_field_name . '</strong>' )
							);
						}
					}
				}
			}
		}

		public function add_input_fields_meta_box( $post_type, $post ) {
			if ( 'woocommerce_page_wc-orders' === $post_type || 'shop_order' === $post_type ) {
				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					// HPOS usage is enabled.
					global $theorder;
					if ( ! $theorder ) {
						return;
					}
					$input_fields_meta = $theorder->get_meta( '_alg_wc_cpg_input_fields', true );
				} else {
					// Traditional CPT-based orders are in use.
					$input_fields_meta = get_post_meta( get_the_ID(), '_alg_wc_cpg_input_fields', true );
				}

				if ( ! empty( $input_fields_meta ) ) {
					$screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
							? wc_get_page_screen_id( 'shop-order' )
							: 'shop_order';

					add_meta_box(
						'alg-wc-cpg-input-fields',
						__( 'Payment gateway input fields', 'custom-payment-gateways-woocommerce' ),
						array( $this, 'display_input_fields_meta_box' ),
						$screen,
						'side'
					);
				}
			}
		}

		public function display_input_fields_meta_box( $post_or_order_object ) {
			$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

			$input_fields_meta = $order->get_meta( '_alg_wc_cpg_input_fields', true );

			echo $this->get_input_fields_output(
				//get_post_meta( get_the_ID(), '_alg_wc_cpg_input_fields', true ),
				$input_fields_meta,
				array(
					'start' => '<table class="widefat striped"><tbody>',
					'item'  => '<tr><th>%title%</th><td>%value%</td></tr>',
					'end'   => '</tbody></table>',
				)
			);
		}

		public function add_input_fields_to_order_meta( $order_id, $posted ) {
			if ( ! empty( $_POST['payment_method'] ) && isset( $_POST['alg_wc_cpg_input_fields'][ $_POST['payment_method'] ] ) ) {
				$values = array_map( 'sanitize_textarea_field', $_POST['alg_wc_cpg_input_fields'][ $_POST['payment_method'] ] );
				$order = wc_get_order( $order_id );
				$order->update_meta_data( '_alg_wc_cpg_input_fields', $values );
				$order->save();
				//update_post_meta( $order_id, '_alg_wc_cpg_input_fields', $values );
				if ( 'yes' === get_option( 'alg_wc_cpg_input_fields_add_order_note', 'no' ) ) {
					$note   = array();
					$note[] = __( 'Payment gateway input fields', 'custom-payment-gateways-woocommerce' ) . ':';
					//$order  = wc_get_order( $order_id );
					foreach ( $values as $title => $value ) {
						$note[] = ( $title . ': ' . $value );
					}
					$order->add_order_note( implode( PHP_EOL, $note ) );
				}
			}
		}

	}

endif;

return new Alg_WC_Custom_Payment_Gateways_Input_Fields();
