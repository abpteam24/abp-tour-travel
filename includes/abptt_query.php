<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly
	if (!class_exists('ABPTT_Query')) {
		class ABPTT_Query {
			public function __construct() {
			}
			public static function get_info() {
				global $wpdb;
				$cache_key = 'abptt_info';
				$abptt_info = wp_cache_get($cache_key);
				if (false !== $abptt_info) {
					return $abptt_info;
				}
				$order_table = $wpdb->prefix . 'abptt_orders';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$total_order = (int)$wpdb->get_var(
					$wpdb->prepare("SELECT COUNT(*) FROM %i", $order_table)
				);
				$abptt_info = array();
				$post_ids = self::get_post_id(['status' => ['publish', 'draft', 'private', 'trash']]);
				$post_counts = wp_count_posts(ABPTT_Function::get_cpt());
				$total_publish = $post_counts->publish ?? 0;
				$total_draft = $post_counts->draft ?? 0;
				$total_private = $post_counts->private ?? 0;
				$total_trash = $post_counts->trash ?? 0;
				$abptt_info['post_ids'] = $post_ids;
				$abptt_info['total_post'] = sizeof($post_ids);
				$abptt_info['total_publish'] = $total_publish;
				$abptt_info['total_draft'] = $total_draft;
				$abptt_info['total_private'] = $total_private;
				$abptt_info['total_trash'] = $total_trash;
				$abptt_info['total_order'] = $total_order;
				wp_cache_set($cache_key, $abptt_info);
				return $abptt_info;
			}
			public static function query_post_type($post_type, $show = -1, $page = 1): WP_Query {
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => $show,
					'paged' => $page,
					'post_status' => 'publish'
				) + ABPTT_Function::polylang_query_args();
				return new WP_Query($args);
			}
			public static function dummy_ids(): array {
				$args = array(
					'post_type' => ABPTT_Function::get_cpt(),
					'post_status' => 'any',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'meta_key' => 'dummy',
					'meta_value' => 'on',
				);
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				$posts = get_posts($args);
				return is_array($posts) ? $posts : array();
			}
			public static function get_post_id($filters = []): array {
				$post_type = ($filters['cpt'] ?? null) ?: ABPTT_Function::get_cpt();
				$show = ($filters['posts_per_page'] ?? null) ?: -1;
				$page = ($filters['paged'] ?? null) ?: 1;
				$status = ($filters['status'] ?? null) ?: 'publish';
				$cat_id = $filters['cat_id'] ?? null;
				$loc_id = $filters['loc_id'] ?? null;
				$organizer_id = $filters['organizer_id'] ?? null;
				$guide_id = $filters['guide_id'] ?? null;
				$meta_query = ['relation' => 'AND'];
				// Category query
				if (!empty($cat_id)) {
					$meta_query[] = ['key' => 'abptt_category', 'value' => '(^|,)' . absint($cat_id) . '(,|$)', 'compare' => 'REGEXP'];
				}
				if (!empty($loc_id)) {
					$meta_query[] = ['key' => 'abptt_location', 'value' => '(^|,)' . absint($loc_id) . '(,|$)', 'compare' => 'REGEXP'];
				}
				if (!empty($organizer_id)) {
					$meta_query[] = ['key' => 'abptt_organizer', 'value' => '(^|,)' . absint($organizer_id) . '(,|$)', 'compare' => 'REGEXP'];
				}
				if (!empty($guide_id)) {
					$meta_query[] = ['key' => 'guide', 'value' => '(^|,)' . absint($guide_id) . '(,|$)', 'compare' => 'REGEXP'];
				}
				$top_pick = $filters['top_pick'] ?? null;
				if (!empty($top_pick)) {
					$meta_query[] = ['key' => 'abptt_top_pick', 'value' => 'on', 'compare' => '='];
				}
				$order = strtoupper((string) ($filters['sort'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
				$all_data = get_posts(array(
					'fields' => 'ids',
					'post_type' => $post_type,
					'posts_per_page' => $show,
					'paged' => $page,
					'post_status' => $status,
					'orderby' => 'date',
					'order' => $order,
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Meta query keys are fixed plugin strings.
					'meta_query' => $meta_query
				) + ABPTT_Function::polylang_query_args());
				return array_unique($all_data);
			}
			public static function get_booking_query($filters = array(), $limit = 0, $offset = 0, $count = false) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'abptt_orders';
				$conditions = array();
				$params = array();
				// Order Status Filter
				$status = !empty($filters['status']) ? sanitize_text_field($filters['status']) : null;
				$booked_status = $status ?: ABPTT_Function::booking_status();
				$booked_status = $booked_status ? explode(',', $booked_status) : array();
				$is_all_status = (!empty($booked_status) && current($booked_status) === 'all');
				if (!empty($booked_status) && !$is_all_status) {
					$placeholders = implode(',', array_fill(0, count($booked_status), '%s'));
					$conditions[] = "order_status IN ($placeholders)";
					$params = array_merge($params, $booked_status);
				}
				// Integer ID Filters
				$user_email = $filters['user_email'] ?? '';
				$int_keys = array('id', 'post_id', 'user_id', 'item_id', 'order_id', 'sp_id');
				foreach ($int_keys as $key) {
					if (!empty($filters[$key])) {
						if ('user_id' === $key && !empty($user_email)) {
							continue;
						}
						$conditions[] = "{$key} = %d";
						$params[] = absint($filters[$key]);
					}
				}
				// Account lookup: match by user id OR the customer's billing email (covers guest checkouts).
				if (!empty($user_email)) {
					$conditions[] = '(user_id = %d OR billing_email = %s)';
					$params[] = absint($filters['user_id'] ?? 0);
					$params[] = sanitize_email($user_email);
				}
				// Tour date/session filters.
				$start_time = $filters['start_time'] ?? '';
				if (!empty($start_time) && empty($filters['tour_date']) && empty($filters['session_time'])) {
					$start_st   = sanitize_text_field( $start_time );
					$conditions[] = 'tour_date = %s AND TIME_FORMAT(session_time, "%%H:%%i") = %s';
					$params[]   = gmdate( 'Y-m-d', strtotime( $start_st ) );
					$params[]   = gmdate( 'H:i', strtotime( $start_st ) );
				}
				$tour_date = $filters['tour_date'] ?? '';
				if (!empty($tour_date)) {
					$conditions[] = 'tour_date = %s';
					$params[] = gmdate('Y-m-d', strtotime(sanitize_text_field($tour_date)));
				}
				$session_time = $filters['session_time'] ?? '';
				if (!empty($session_time)) {
					$conditions[] = "TIME_FORMAT(session_time, '%%H:%%i') = %s";
					$params[] = gmdate('H:i', strtotime('1970-01-01 ' . sanitize_text_field($session_time)));
				}
				// JSON Fields
				if (!empty($filters['ticket_id'])) {
					$conditions[] = 'JSON_CONTAINS(ticket_id, %s)';
					$params[] = wp_json_encode(sanitize_text_field($filters['ticket_id']));
				}
				if (!empty($filters['ex_id'])) {
					$conditions[] = 'JSON_CONTAINS(ex_id, %s)';
					$params[] = wp_json_encode(sanitize_text_field($filters['ex_id']));
				}
				// Date Range Filters
				if (!empty($filters['order_date'])) {
					$conditions[] = 'DATE(created_at) = %s';
					$params[] = gmdate('Y-m-d', strtotime($filters['order_date']));
				}
				$tour_date_from = $filters['tour_date_from'] ?? '';
				$tour_date_to = $filters['tour_date_to'] ?? '';
				if (!empty($tour_date_from) && !empty($tour_date_to)) {
					$conditions[] = 'tour_date BETWEEN %s AND %s';
					$params[] = gmdate('Y-m-d', strtotime($tour_date_from));
					$params[] = gmdate('Y-m-d', strtotime($tour_date_to));
				}
				if (!empty($filters['order_date_from']) && !empty($filters['order_date_to'])) {
					$conditions[] = 'DATE(created_at) BETWEEN %s AND %s';
					$params[] = gmdate('Y-m-d', strtotime($filters['order_date_from']));
					$params[] = gmdate('Y-m-d', strtotime($filters['order_date_to']));
				}
				// Billing Info (LIKE search)
				$like_keys = array('billing_name', 'billing_email', 'billing_phone');
				foreach ($like_keys as $like_key) {
					if (!empty($filters[$like_key])) {
						$conditions[] = "{$like_key} LIKE %s";
						$params[] = '%' . $wpdb->esc_like(sanitize_text_field($filters[$like_key])) . '%';
					}
				}
				// SQL Query Assembly
				$select = $count ? 'SELECT COUNT(*)' : 'SELECT *';
				$sql = "{$select} FROM {$table_name}";
				if (!empty($conditions)) {
					$sql .= ' WHERE ' . implode(' AND ', $conditions);
				}
				if (!$count) {
					$allowed_columns = array('id', 'post_id', 'order_id', 'tour_date', 'session_time', 'order_status', 'created_at');
					$raw_order_by = !empty($filters['order_by']) ? sanitize_key($filters['order_by']) : 'order_id';
					$order_by = in_array($raw_order_by, $allowed_columns, true) ? $raw_order_by : 'order_id';
					$order_dir = (!empty($filters['order_dir']) && strtoupper($filters['order_dir']) === 'ASC') ? 'ASC' : 'DESC';
					// phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter -- ORDER BY column/direction are whitelisted constants.
					$sql .= " ORDER BY {$order_by} {$order_dir}";
					if ($limit > 0) {
						$sql .= ' LIMIT %d OFFSET %d';
						$params[] = absint($limit);
						$params[] = absint($offset);
					}
				}
				if ($count) {
					if (!empty($params)) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
						$results = $wpdb->get_var(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
							$wpdb->prepare($sql, ...$params)
						);
					} else {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
						$results = $wpdb->get_var($sql);
					}
				} else {
					if (!empty($params)) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
						$results = $wpdb->get_results(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
							$wpdb->prepare($sql, ...$params),
							ARRAY_A
						);
					} else {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
						$results = $wpdb->get_results($sql, ARRAY_A);
					}
				}
				$results = $results ?: ($count ? 0 : array());
				return $results;
			}
			public static function get_sold_qty_ex($filters = []) {
				$sold_qty = 0;
				$booking_lists = self::get_booking_query($filters);
				if (empty($booking_lists)) {
					return $sold_qty;
				}
				$id = $filters['ex_id'] ?? '';
				foreach ($booking_lists as $booking_list) {
					$ex_ids = json_decode($booking_list['ex_id'] ?? '', true) ?: [];
					$additional_infos = json_decode($booking_list['ex_info'] ?? '', true) ?: [];
					if (!empty($id)) {
						if (in_array($id, $ex_ids, true) && isset($additional_infos[$id])) {
							$sold_qty += $additional_infos[$id]['qty'] ?? 1;
						}
					} else {
						foreach ($additional_infos as $additional_info) {
							$sold_qty += $additional_info['qty'] ?? 1;
						}
					}
				}
				return $sold_qty;
			}
			public static function flush_cache(int $sp_id = 0): void {
				wp_cache_delete('abptt_info');
				wp_cache_delete('abptt_sp_' . md5('_all'));
				wp_cache_delete('abptt_sp_' . md5('_count'));
				if ($sp_id > 0) {
					wp_cache_delete('abptt_sp_' . md5((string) $sp_id . '_all'));
					wp_cache_delete('abptt_sp_' . md5((string) $sp_id . '_count'));
				}
			}
			public static function get_sp($id = '', $count = false) {
				global $wpdb;
				$cache_key = 'abptt_sp_' . md5($id . ($count ? '_count' : '_all'));
				$abptt_sp = wp_cache_get($cache_key);
				if (false !== $abptt_sp) {
					return $abptt_sp;
				}
				$table_name = $wpdb->prefix . 'abptt_sp';
				if ($count) {
					if (!empty($id)) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe table name variable; $id is prepared.
						$results = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE id = %d", (int)$id));
					} else {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe table name variable with no user input.
						$results = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
					}
				} else {
					if (!empty($id)) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe table name variable; $id is prepared.
						$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d ORDER BY id ASC", (int)$id), ARRAY_A);
					} else {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe table name variable with no user input.
						$results = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY id ASC", ARRAY_A);
					}
				}
				wp_cache_set($cache_key, $results);
				return $results;
			}
			public static function get_sold_ticket($filters = []): array {
				$sold_qty = [];
				$booking_lists = self::get_booking_query($filters);
				if (empty($booking_lists)) {
					return $sold_qty;
				}
				foreach ($booking_lists as $booking_list) {
					$ticket_infos = json_decode($booking_list['ticket_info'] ?? '', true) ?: [];
					if (!empty($ticket_infos)) {
						foreach ($ticket_infos as $ticket_info) {
							if (!empty($ticket_info)) {
								$qty = $ticket_info ['qty'] ?? 1;
								$id = $ticket_info ['id'] ?? 'price';
								$sold_qty [$id] = ($sold_qty [$id] ?? 0) + $qty;
								$sold_qty ['total'] = ($sold_qty ['total'] ?? 0) + $qty;
							}
						}
					}
				}
				return $sold_qty;
			}
			public static function get_sold_seat($filters = []): array {
				$sold_seats = [];
				$booking_lists = self::get_booking_query($filters);
				if (empty($booking_lists)) {
					return $sold_seats;
				}
				foreach ($booking_lists as $booking_list) {
					$ticket_infos = json_decode($booking_list['ticket_info'] ?? '', true) ?: [];
					if (!empty($ticket_infos)) {
						foreach ($ticket_infos as $ticket_info) {
							if (!empty($ticket_info)) {
								$sold_seats [] = $ticket_info ['name'] ?? '';
							}
						}
					}
				}
				return array_values(array_unique($sold_seats));
			}
		}
		new ABPTT_Query();
	}