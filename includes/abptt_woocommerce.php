<?php
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	if (!class_exists('ABPTT_Woocommerce')) {
		class ABPTT_Woocommerce {
			public function __construct() {
				add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 90, 3);
				add_action('woocommerce_before_calculate_totals', array($this, 'before_calculate_totals'), 90);
				add_filter('woocommerce_cart_item_thumbnail', array($this, 'cart_item_thumbnail'), 90, 3);
				add_filter('woocommerce_get_item_data', array($this, 'get_item_data'), 90, 2);
				//=============================//
				add_action('woocommerce_after_checkout_validation', array($this, 'after_checkout_validation'), 90, 2);
				add_action('woocommerce_checkout_create_order_line_item', array($this, 'checkout_create_order_line_item'), 90, 4);
				add_action('woocommerce_checkout_order_processed', array($this, 'checkout_order_processed'));
				add_action('woocommerce_store_api_checkout_order_processed', array($this, 'api_checkout_order_processed'));
				add_filter('woocommerce_order_status_changed', array($this, 'order_status_changed'), 90, 4);
			}
			public function add_cart_item_data($cart_item, $product_id) {
				$linked_id = ABPTT_Function::get_post_info($product_id, 'abptt_link_id', $product_id);
				$post_id = is_string(get_post_status($linked_id)) ? $linked_id : $product_id;
				if (get_post_type($post_id) == ABPTT_Function::get_cpt() && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'abptt_registration_nonce')) {
					$post_infos = ABPTT_Function::get_all_meta($post_id);
					$booking_infos = [];
					$event_info = self::get_booking_info($post_infos);
					if (!empty($event_info)) {
						$booking_infos[] = $event_info;
					}
					$total_price = 0;
					if (!empty($booking_infos)) {
						foreach ($booking_infos as $booking_info) {
							$total_price += ($booking_info['total'] ?? 0);
						}
					}
					$cart_item['post_id'] = $post_id;
					$cart_item['booking_infos'] = $booking_infos;
					$cart_item['total_price'] = $total_price;
					$cart_item['line_total'] = $total_price;
					$cart_item['line_subtotal'] = $total_price;
					$cart_item = apply_filters('abptt_add_cart_item_data', $cart_item, $post_id);
					if ( function_exists( 'WC' ) && WC()->session ) {
						WC()->session->set( 'abptt_cart_success', get_the_title( $post_id ) . ' ' . __( 'Add to cart successfully!', 'abp-tour-travel' ) );
					}
				}
				//echo '<pre>';print_r($cart_item);echo '</pre>';die();
			return $cart_item;
			}
			public function before_calculate_totals($cart_object): void {
				foreach ($cart_object->cart_contents as $value) {
					$post_id = $value['post_id'] ?? 0;
					if (get_post_type($post_id) == ABPTT_Function::get_cpt()) {
						$total_price = $value['total_price'] ?? 0;
						$value['data']->set_price($total_price);
						$value['data']->set_regular_price($total_price);
						$value['data']->set_sale_price($total_price);
						$value['data']->set_sold_individually('yes');
						$value['data']->get_price();
					}
				}
			}
			public function cart_item_thumbnail($thumbnail, $cart_item) {
				$post_id = $cart_item['post_id'] ?? 0;
				if (get_post_type($post_id) == ABPTT_Function::get_cpt()) {
					$url = ABPTT_Function::get_image_url($post_id) ?: ABPTT_BLANK_IMG_URL;
					if (!empty($url)) {
						$thumbnail = '<div class="abptt_area"><img class="_img_control" src="' . $url . '" data-href="' . get_the_permalink($post_id) . '" alt="#"></div>';
					}
				}
				return $thumbnail;
			}
			public function get_item_data($item_data, $cart_item) {
				$post_id = $cart_item['post_id'] ?? 0;
				if (get_post_type($post_id) == ABPTT_Function::get_cpt()) {
					global $post;
					$is_block_cart = false;
					$is_block_checkout = false;
					if (is_a($post, 'WP_Post')) {
						$is_block_cart = has_block('woocommerce/cart', $post->ID);
						$is_block_checkout = has_block('woocommerce/checkout', $post->ID);
					}
					if (is_checkout() && $is_block_checkout) {
						$item_data = $this->display_cart_item_block($cart_item);
					} elseif (is_cart() && $is_block_cart) {
						$item_data = $this->display_cart_item_block($cart_item);
					} else {
						ob_start();
						do_action('abptt_display_cart_item', $cart_item);
						$content = ob_get_clean();
						if (!empty($content)) {
							$item_data[] = array(
								'name' => __('Booking Details', 'abp-tour-travel'),
								'value' => $content
							);
						}
					}
				}
				return $item_data;
			}
			public static function get_booking_info($post_infos = []) {
				$booking_info = [];
				if (isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'abptt_registration_nonce')) {
					$post_int_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('absint', wp_unslash($_POST[$key])) : [];
					$post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
					$post_int = fn($key, $default = '') => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
					$post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
					$post_id = $post_infos['post_id'] ?? '';
					$seat_type = $post_infos['seat_type'] ?? 'sp';
					$seat_type = ABPTT_Function::on_off('sp') ? $seat_type : 'ticket';
					$tour_date = $post_val('tour_date');
					$tour_date = $tour_date ?: $post_val('start_date');
					$session_time = $post_val('session_time');
					$session_time = $session_time ?: $post_val('start_time');
					if (empty($session_time)) {
						$session_time = ABPTT_Function::check_time_exit_date($tour_date) ? gmdate('H:i', strtotime($tour_date)) : '00:00';
					}
					$ticket_price = 0;
					if (!empty($tour_date) && !empty($session_time) && !empty($post_id)) {
						if ($seat_type == 'ticket') {
							$ticket_types = $post_array( 'item_check');
							$item_qty = $post_int_array( 'item_qty');
							if (!empty($ticket_types) && !empty($item_qty) && sizeof($ticket_types) > 0) {
								foreach ($ticket_types as $key => $ticket_type) {
									$qty = absint($item_qty[$key] ?? '');
									if (!empty($ticket_type) && $qty > 0) {
										$price = ABPTT_Function::get_price($post_infos, $ticket_type, $tour_date);
										$booking_info['info'][$ticket_type]['id'] = $ticket_type;
										$booking_info['info'][$ticket_type]['name'] = ABPTT_Function::ticket_name($ticket_type);
										$booking_info['info'][$ticket_type]['price'] = $price;
										$booking_info['info'][$ticket_type]['qty'] = $qty;
										$ticket_price = $ticket_price + $price * $qty;
									}
								}
							}
						} else {
							$seats = $post_val( 'sp_selected_seat');
							$seats = $seats ? explode(',', $seats) : [];
							$types = $post_val( 'sp_selected_seat_id');
							$types = $types ? explode(',', $types) : [];
							$sp_id = $post_int( 'sp_id');
							if (!empty($sp_id) && !empty($seats) && !empty($types)) {
								foreach ($types as $index => $type) {
									$seat = $seats[$index] ?? '';
									if (!empty($seat) && !empty($type)) {
										$price = ABPTT_Function::get_price($post_infos, $type, $session_time);
										$booking_info['info'][$index]['id'] = $type;
										$booking_info['info'][$index]['name'] = $seat;
										$booking_info['info'][$index]['sp_id'] = $sp_id;
										$booking_info['info'][$index]['price'] = $price;
										$booking_info['info'][$index]['qty'] = 1;
										$ticket_price = $ticket_price + $price * 1;
									}
								}
								$booking_info['sp_id'] = $sp_id;
							}
						}
						if (!empty($booking_info['info'])) {
							$additional_info = self::get_additional_info($post_infos);
							$additional_price = self::get_additional_price($additional_info);
							$booking_info['seat_type'] = $seat_type;
							$booking_info['tour_date'] = gmdate('Y-m-d', strtotime($tour_date));
							$booking_info['session_time'] = gmdate('H:i:s', strtotime('1970-01-01 ' . $session_time));
							$booking_info['pass_info'] = self::get_passenger_info($post_infos);
							$booking_info['additional_info'] = $additional_info;
							$booking_info['price'] = $ticket_price;
							$booking_info['ex_price'] = $additional_price;
							$booking_info['total'] = $ticket_price + $additional_price;
						}
					}
				}
				return apply_filters('abptt_cart_booking_info_filter', $booking_info);
			}
			public static function get_additional_price($services) {
				$price = 0;
				if (is_array($services) && sizeof($services) > 0) {
					foreach ($services as $service) {
						$qty = $service['qty'] ?? '';
						if (!empty($qty) && $qty > 0) {
							$ticket_price = $service['price'] ?? 0;
							$price = $price + $ticket_price * $qty;
						}
					}
				}
				return $price;
			}
			public static function get_additional_info($post_infos = []): array {
				$infos = array();
				if (isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'abptt_registration_nonce')) {
					$services = ABPTT_Function::additional_data($post_infos);
					if (!empty($services) && is_array($services)) {
						foreach ($services as $id => $service) {
							$name = isset($_POST['name_' . $id]) ? sanitize_text_field(wp_unslash($_POST[ 'name_' . $id])) : '';
							$quantity = isset($_POST[ 'qty_' . $id]) ? sanitize_text_field(wp_unslash($_POST[ 'qty_' . $id])) : '';
							if (!empty($name) && !empty($quantity) && $quantity > 0 && !empty($id)) {
								$infos[$id]['name'] = $name;
								$infos[$id]['qty'] = $quantity;
								$infos[$id]['price'] = ABPTT_Function::get_additional_price($post_infos, $id);
								$infos[$id]['icon'] = $service['icon'] ?? '';
								$infos[$id]['returnable'] = $service['returnable'] ?? 'no';
							}
						}
					}
				}
				return $infos;
			}
			public static function get_passenger_info($post_infos = []): array {
				$pass_info = [];
				if (ABPTT_Function::on_off('client_info') && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'abptt_registration_nonce')) {
					$forms = ABPTT_Function::client_data($post_infos);
					if (!empty($forms) && is_array($forms)) {
						foreach ($forms as $id => $form) {
							$infos = isset($_POST[$id]) ? array_map('sanitize_text_field', wp_unslash($_POST[$id])) : [];
							if (!empty($infos)) {
								foreach ($infos as $key => $info) {
									if (!empty($info)) {
										$pass_info[$key][$id]['label'] = $form['label'] ?? '';
										$pass_info[$key][$id]['value'] = $info;
									}
								}
							}
						}
					}
				}
				return $pass_info;
			}
			public function display_cart_item_block($booking_infos): array {
				$item_data = [];
				$booking_info = $booking_infos['booking_infos'] ?? [];
				$post_id = $booking_infos['post_id'] ?? '';
				if (!empty($booking_info) && sizeof($booking_info) > 0 && !empty($post_id) && get_post_type($post_id) == ABPTT_Function::get_cpt()) {
					$html = '';
					foreach ($booking_info as $cart_item) {
						if (!empty($cart_item)) {
							$ticket_infos = $cart_item['info'] ?? [];
							if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
								$tour_date = $cart_item['tour_date'] ?? '';
								$session_time = $cart_item['session_time'] ?? '';
								$seat_type = $cart_item['seat_type'] ?? '';
								$html .= esc_html__('Booking Information', 'abp-tour-travel') . '<br />';
								$html .= esc_html__('Tour Date', 'abp-tour-travel') . ' : ' . esc_html(ABPTT_Function::date_format($tour_date)) . '<br />';
								$html .= esc_html__('Session Time', 'abp-tour-travel') . ' : ' . esc_html(ABPTT_Function::date_format($tour_date . ' ' . $session_time, 'time')) . '<br />';
								$html .= esc_html__('Ticket Information', 'abp-tour-travel') . '<br />';
								foreach ($ticket_infos as $ticket_info) {
									$price = $ticket_info['price'] ?? 0;
									$qty = $ticket_info['qty'] ?? 1;
									$price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-tour-travel');
									$price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-tour-travel');
									$name = $ticket_info['name'] ?? '';
									if ($seat_type == 'sp') {
										$name = $name . ' - ' . ABPTT_Function::sp_label($post_id, ($ticket_info['sp_id'] ?? $cart_item['sp_id'] ?? ''));
									}
									$html .= esc_html($name) . ' : ' . wp_kses_post($price_text) . ' X ' . esc_html($qty) . ' = ' . wp_kses_post($price) . '<br />';
								}
								$additional_info = $cart_item['additional_info'] ?? [];
								if (ABPTT_Function::on_off('additional_info') && !empty($additional_info) && sizeof($additional_info) > 0) {
									$html .= esc_html__('Additional Information', 'abp-tour-travel') . '<br />';
									foreach ($additional_info as $additional) {
										if (is_array($additional)) {
											$qty = $additional['qty'] ?? 1;
											$price = $additional['price'] ?? 0;
											$price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-tour-travel');
											$ex_price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-tour-travel');
											$html .= esc_html($additional['name'] ?? '') . ' : ' . wp_kses_post($price_text) . ' X ' . esc_html($qty) . ' = ' . wp_kses_post($ex_price) . '<br />';
										}
									}
								}
								$attendee_infos = $cart_item['pass_info'] ?? [];
								if (ABPTT_Function::on_off('client_info') && !empty($attendee_infos) && sizeof($attendee_infos) > 0) {
									$html .= esc_html__('Client Information', 'abp-tour-travel') . '<br />';
									foreach ($attendee_infos as $attendee_info) {
										if (!empty($attendee_info)) {
											foreach ($attendee_info as $attendee) {
												$label = $attendee['label'] ?? '';
												$value = $attendee['value'] ?? '';
												if ($label && $value) {
													$html .= esc_html($label) . ' : ' . esc_html($value) . '<br />';
												}
											}
										}
									}
								}
							}
						}
					}
					if (!empty($html)) {
						$item_data[] = array('name' => __('Booking Details', 'abp-tour-travel'), 'value' => $html);
					}
				}
				return $item_data;
			}
			//=============================//
			public function after_checkout_validation( $data, $errors ): void {
				if ( ! WC()->cart ) {
					return;
				}
				foreach ( WC()->cart->get_cart() as $booking_infos ) {
					if ( ! ABPTT_Function::checkout_validation( $booking_infos ) ) {
						$errors->add(
							'abptt_booking_invalid',
							__( 'One or more tour tickets are no longer available. Please review your booking and try again.', 'abp-tour-travel' )
						);
						break;
					}
				}
			}
			public function checkout_create_order_line_item($item, $_key, $booking_infos, $order = null): void {
				$booking_info = $booking_infos['booking_infos'] ?? [];
				$post_id = $booking_infos['post_id'] ?? 0;
				if (!empty($booking_info) && sizeof($booking_info) > 0 && !empty($post_id) && get_post_type($post_id) == ABPTT_Function::get_cpt()) {
					$return = '';
					foreach ($booking_info as $cart_item) {
						if (!empty($cart_item)) {
							$ticket_infos = $cart_item['info'] ?? [];
							$seat_type = $cart_item['seat_type'] ?? '';
							if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
								$tour_date = $cart_item['tour_date'] ?? '';
								$session_time = $cart_item['session_time'] ?? '';
								$additional_infos = $cart_item['additional_info'] ?? [];
								$attendee_infos = $cart_item['pass_info'] ?? [];
								$item->add_meta_data(__('Booking Information', 'abp-tour-travel') . ' ' . $return, '');
								$item->add_meta_data(__('Tour Date', 'abp-tour-travel'), ABPTT_Function::date_format($tour_date));
								$item->add_meta_data(__('Session Time', 'abp-tour-travel'), ABPTT_Function::date_format($tour_date . ' ' . $session_time, 'time'));
								$item->add_meta_data(__('Ticket Information', 'abp-tour-travel'), '');
								foreach ($ticket_infos as $ticket_info) {
									$price = $ticket_info['price'] ?? 0;
									$qty = $ticket_info['qty'] ?? 1;
									$price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-tour-travel');
									$price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-tour-travel');
									$name = $ticket_info['name'] ?? '';
									if ($seat_type == 'sp') {
										$name = $name . ' - ' . ABPTT_Function::sp_label($post_id, ($ticket_info['sp_id'] ?? $cart_item['sp_id'] ?? ''));
									}
									$item->add_meta_data($name, ($price_text . ' X ' . $qty . '  = ' . $price));
								}
								if (ABPTT_Function::on_off('additional_info') && !empty($additional_infos) && sizeof($additional_infos) > 0) {
									$item->add_meta_data(__('Additional Information', 'abp-tour-travel'), '');
									foreach ($additional_infos as $additional) {
										$name = $additional['name'] ?? '';
										$qty = $additional['qty'] ?? 1;
										$price = $additional['price'] ?? 0;
										$price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-tour-travel');
										if (!empty($name) && $qty > 0) {
											$ex_price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-tour-travel');
											$item->add_meta_data($name, '  ( ' . $price_text . ' X ' . $qty . ') = ' . $ex_price);
										}
									}
								}
								if (ABPTT_Function::on_off('client_info') && !empty($attendee_infos) && sizeof($attendee_infos) > 0) {
									$item->add_meta_data(__('Client Information', 'abp-tour-travel'), '');
									foreach ($attendee_infos as $attendee_info) {
										if (!empty($attendee_info)) {
											foreach ($attendee_info as $attendee) {
												$label = $attendee['label'] ?? '';
												$value = $attendee['value'] ?? '';
												if (!empty($label) && !empty($value)) {
													$item->add_meta_data($label, $value);
												}
											}
										}
									}
								}
								//=============================//
							}
						}
					}
					$item_info = [
						'post_id' => $post_id,
						'user_id' => $order instanceof WC_Order ? $order->get_customer_id() : get_current_user_id(),
						'booking_infos' => $booking_info,
						'item_total' => $booking_infos['total_price'] ?? '',
					];
					$item_info = apply_filters('abptt_checkout_create_order_line_item', $item_info, $booking_infos);
					$item->add_meta_data('_abptt_items', $item_info, true);
				}
			}
			public static function save_custom_data($order_id): void {
				if ($order_id) {
					$order = wc_get_order($order_id);
					if (!$order) {
						return;
					}
					$order_status = $order->get_status();
					$payment_method = $order->get_payment_method_title();
					$user_id = $order->get_customer_id();
					$_billing_first_name = $order->get_billing_first_name();
					$_billing_last_name = $order->get_billing_last_name();
					$billing_email = $order->get_billing_email();
					$billing_phone = $order->get_billing_phone();
					$_billing_address_1 = $order->get_billing_address_1();
					$_billing_address_2 = $order->get_billing_address_2();
					$billing_name = $_billing_first_name . ' ' . $_billing_last_name;
					$billing_address = $_billing_address_1 . ' ' . $_billing_address_2;
					if ($order_status != 'failed') {
						global $wpdb;
						// Serialize concurrent order writes so availability is re-checked atomically and the last seat is never sold twice.
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching -- MySQL advisory lock, no user input.
						$got_lock = $wpdb->get_var("SELECT GET_LOCK('abptt_orders_write', 10)");
						if (!$got_lock) {
							$order->update_status('failed', __('Could not finalize the booking due to a temporary database lock. Please review and try again.', 'abp-tour-travel'));
							return;
						}
						try {
							$table_name = $wpdb->prefix . 'abptt_orders';
							$rows = array();
							$order_items = $order->get_items();
							if (!empty($order_items)) {
								foreach ($order_items as $item_id => $item) {
									$item_infos = wc_get_order_item_meta($item_id, '_abptt_items');
									if (!empty($item_infos) && is_array($item_infos) && sizeof($item_infos) > 0) {
										// Skip only the items that already have a booking row, whatever
										// the order status is. A retried request cannot insert the same
										// booking twice, while any remaining item of a partially
										// written order can still be completed.
										$existing_rows = ABPTT_Query::get_booking_query(['order_id' => $order_id, 'item_id' => $item_id, 'status' => 'all'], 0, 0, true);
										if (!empty($existing_rows)) {
											continue;
										}
										$post_id = $item_infos['post_id'] ?? '';
										$booking_info = $item_infos['booking_infos'] ?? [];
										if (!empty($post_id) && get_post_type($post_id) == ABPTT_Function::get_cpt() && !empty($booking_info) && sizeof($booking_info) > 0) {
											// Re-validate availability inside the lock to prevent overselling on concurrent checkouts.
											if (!ABPTT_Function::checkout_validation($item_infos)) {
												$order->update_status('failed', __('One or more seats became unavailable. The order has been cancelled.', 'abp-tour-travel'));
												return;
											}
											foreach ($booking_info as $item_info) {
												if (empty($item_info)) {
													continue;
												}
												$seat_type = $item_info['seat_type'] ?? ($item_infos['seat_type'] ?? 'ticket');
												$ticket_infos = $item_info['info'] ?? [];
												$additional_info = $item_info['additional_info'] ?? [];
												if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
													$ticket_id = $ex_id = [];
													$qty = 0;
													foreach ($ticket_infos as $ticket_info) {
														if ($seat_type == 'sp') {
															$ticket_id[] = $ticket_info['name'] ?? '';
														} else {
															$ticket_id[] = $ticket_info['id'] ?? '';
														}
														$qty = $qty + ($ticket_info['qty'] ?? 1);
													}
													if (!empty($additional_info) && sizeof($additional_info) > 0) {
														foreach ($additional_info as $key => $additional) {
															$ex_id[] = $key;
														}
													}
$money = static function ($value): ?string {
												return ('' === $value || null === $value) ? null : sanitize_text_field((string) $value);
											};
											$others = [];
											$_order_status = 'wc-' . $order_status;
											$rows[] = [
												'order_id' => intval($order_id),
												'item_id' => intval($item_id),
												'post_id' => intval($post_id),
												'user_id' => intval($user_id),
												'seat_type' => sanitize_text_field($seat_type),
												'tour_date' => sanitize_text_field($item_info['tour_date'] ?? ''),
												'session_time' => sanitize_text_field($item_info['session_time'] ?? ''),
												'sp_id' => intval($item_info['sp_id'] ?? ''),
												'ticket_info' => wp_json_encode($ticket_infos),
												'ticket_id' => wp_json_encode($ticket_id),
												'qty' => intval($qty),
												'price' => $money($item_info['price'] ?? ''),
												'ex_info' => wp_json_encode($additional_info),
												'ex_id' => wp_json_encode($ex_id),
												'ex_price' => $money($item_info['ex_price'] ?? ''),
												'total' => $money($item_info['total'] ?? ''),
												'pass_info' => wp_json_encode($item_info['pass_info'] ?? []),
												'checkin' => 0,
												'order_status' => sanitize_text_field($_order_status),
												'payment_method' => sanitize_text_field($payment_method),
												'billing_name' => sanitize_text_field($billing_name),
												'billing_email' => sanitize_text_field($billing_email),
												'billing_phone' => sanitize_text_field($billing_phone),
												'billing_address' => sanitize_text_field($billing_address),
												'payment_status' => null,
												'others' => wp_json_encode($others),
												'created_at' => current_time('Y-m-d H:i'),
												'updated_at' => current_time('Y-m-d H:i')
											];
												}
											}
										}
									}
								}
								foreach ($rows as $row) {
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
									$wpdb->insert($table_name, $row);
								}
								if (!empty($rows)) {
									ABPTT_Query::flush_cache();
								}
							}
						} finally {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching -- MySQL advisory lock, no user input.
							$wpdb->query("SELECT RELEASE_LOCK('abptt_orders_write')");
						}
					}
				}
			}
			public function checkout_order_processed($order_id): void {
				self::save_custom_data($order_id);
			}
			public function api_checkout_order_processed($order): void {
				$this->checkout_order_processed($order->get_id());
			}
			public function order_status_changed($order_id): void {
				if (!empty($order_id) && $order_id > 0) {
					global $wpdb;
					$table_name = $wpdb->prefix . 'abptt_orders';
					$order = wc_get_order($order_id);
					if (!$order) {
						return;
					}
					$order_status = $order->get_status();
					$updated = false;
					foreach ($order->get_items() as $item_id => $item_values) {
						if ($item_id) {
							// Status-independent lookup: rows created while the order was pending,
							// on-hold or failed must still be found when the status changes later.
							$order_infos = ABPTT_Query::get_booking_query(['order_id' => $order_id, 'item_id' => $item_id, 'status' => 'all']);
							if (!empty($order_infos) && sizeof($order_infos) > 0) {
								$order_info = current($order_infos);
								$others = $order_info['others'] ?? '';
								$others = !empty($others) ? json_decode($others, true) : [];
								$others = is_array($others) ? $others : [];
								$others['updated_by'] = get_current_user_id();
								$data = [
									'others' => wp_json_encode($others),
									'order_status' => 'wc-' . $order_status,
									'updated_at' => current_time('Y-m-d H:i')
								];
								$where = ['item_id' => $item_id, 'order_id' => $order_id];
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
								$wpdb->update($table_name, $data, $where, ['%s', '%s', '%s'], ['%d', '%d']);
								$updated = true;
								$mail_send = apply_filters('abptt_send_mail', false, $item_id);
							}
						}
					}
					// Backfill bookings for orders whose rows were never written, e.g. an order
					// created through a gateway that changed status before checkout completed.
					if (!$updated && in_array($order_status, ['pending', 'on-hold', 'processing', 'completed'], true)) {
						self::save_custom_data($order_id);
						return;
					}
					ABPTT_Query::flush_cache();
				}
			}
		}
		new ABPTT_Woocommerce();
	}
