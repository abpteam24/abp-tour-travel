<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTT_Dummy_Import' ) ) {
		class ABPTT_Dummy_Import {
			public function __construct() {
				add_action( 'wp_ajax_abptt_create_page', array( $this, 'create_page' ) );
				add_action( 'wp_ajax_abptt_import_dummy', array( $this, 'import_dummy' ) );
				add_action( 'wp_ajax_abptt_import_remove_dummy', array( $this, 'remove_dummy' ) );
			}

			/* =========================================================
			 * Security
			 * ========================================================= */
			private function verify_request(): void {
				if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( array( 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ), 403 );
				}
			}

			/* =========================================================
			 * Pages
			 * ========================================================= */
			private function page_map(): array {
				return array(
					'tf_booking' => array( 'name' => __( 'Booking', 'abp-tour-travel' ), 'slug' => 'tf_booking', 'code' => '[abptt-booking]' ),
					'tf_post'    => array( 'name' => sprintf( '%s List', ABPTT_Function::label() ), 'slug' => 'tf_post', 'code' => '[abptt-post]' ),
					'tf_gallery' => array( 'name' => __( 'Gallery', 'abp-tour-travel' ), 'slug' => 'tf_gallery', 'code' => '[abptt-gallery]' ),
				);
			}

			public function create_page(): void {
				$this->verify_request();
				$page_type = '';
				if ( isset( $_POST['type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request().
					$page_type = sanitize_text_field( wp_unslash( $_POST['type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request().
				} elseif ( isset( $_POST['page_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request().
					$page_type = sanitize_text_field( wp_unslash( $_POST['page_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request().
				}
				if ( $page_type === '' ) {
					wp_send_json_error( array( 'type' => 'warn', 'msg' => esc_html__( 'Page type is missing.', 'abp-tour-travel' ) ) );
				}
				$pages = ABPTT_Function::get_option( 'abptt_page', array() );
				$pages = is_array( $pages ) ? $pages : array();
				$map   = $this->page_map();
				$page  = isset( $pages[ $page_type ] ) && is_array( $pages[ $page_type ] ) ? $pages[ $page_type ] : ( $map[ $page_type ] ?? array() );
				if ( empty( $page ) ) {
					wp_send_json_error( array( 'type' => 'warn', 'msg' => esc_html__( 'Page Type not found!', 'abp-tour-travel' ) ) );
				}
				$slug = ! empty( $page['slug'] ) ? sanitize_title( $page['slug'] ) : sanitize_title( $page_type );
				if ( ABPTT_Function::get_page_by_slug( $slug ) ) {
					wp_send_json_error( array( 'type' => 'warn', 'msg' => esc_html__( 'Page already exists.', 'abp-tour-travel' ) ) );
				}
				$post_id = wp_insert_post( array(
					'post_type'    => 'page',
					'post_name'    => $slug,
					'post_title'   => $page['name'] ?? ucfirst( $page_type ),
					'post_content' => $page['code'] ?? '',
					'post_status'  => 'publish',
				) );
				if ( is_wp_error( $post_id ) || 0 === $post_id ) {
					wp_send_json_error( array( 'type' => 'warn', 'msg' => esc_html__( 'Failed to create page.', 'abp-tour-travel' ) ) );
				}
				flush_rewrite_rules();
				/* translators: %s: page name. */
				$msg = sprintf( esc_html__( '%s Page Created successfully.....', 'abp-tour-travel' ), $page['name'] ?? '' );
				wp_send_json_success( array( 'type' => 'success', 'msg' => $msg ) );
			}

			/* =========================================================
			 * Import
			 * ========================================================= */
			public function import_dummy(): void {
				$this->verify_request();
				// Start from a clean slate so repeated imports never duplicate data.
				$this->reset_data();
				$term_map = $this->create_terms();
				$this->seed_options();
				$post_ids = $this->create_posts( $term_map );
				$this->set_related( $post_ids );
				$this->seed_reviews( $post_ids );
				ABPTT_Query::flush_cache();
				flush_rewrite_rules();
				wp_send_json_success( array(
					'type' => 'success',
					/* translators: %d: number of demo posts created. */
					'msg'  => sprintf( esc_html__( 'Demo data imported successfully! (%d items)', 'abp-tour-travel' ), count( $post_ids ) ),
				) );
			}

			/* =========================================================
			 * Remove (full reset of this plugin's data only)
			 * ========================================================= */
			public function remove_dummy(): void {
				$this->verify_request();
				$this->reset_data();
				ABPTT_Query::flush_cache();
				flush_rewrite_rules();
				wp_send_json_success( array( 'type' => 'success', 'msg' => esc_html__( 'All ABP Tour and Travel data removed successfully.', 'abp-tour-travel' ) ) );
			}

			/* =========================================================
			 * Reset helpers
			 * ========================================================= */
			private function reset_options(): array {
				return array(
					'abptt_configuration',
					'abptt_on_off',
					'abptt_date_config',
					'abptt_dates',
					'abptt_category',
					'abptt_location',
					'abptt_organizer',
					'abptt_guide',
					'abptt_feature',
					'abptt_ticket',
					'abptt_ticket_sp',
					'abptt_decor',
					'abptt_additional',
					'abptt_form',
					'abptt_faq',
					'abptt_tc',
					'abptt_place',
					'abptt_color',
					'abptt_css_var',
					'abptt_slider',
					'abptt_contact',
					'abptt_per_page_item',
					'abptt_dummy_registry',
					'abptt_orders_schema_version',
					'abptt_activation_redirect',
					'abptt_page',
					'abptt_why_book',
					'abptt_my_account_flushed',
					'abptt_mail',
					'abptt_pdf',
					'abptt_pdf_list',
					'abptt_csv',
				);
			}

			private function reset_data(): void {
				// 1. Tour posts (and their linked hidden WooCommerce products).
				$post_ids = get_posts( array(
					'post_type'      => ABPTT_Function::get_cpt(),
					'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
					'posts_per_page' => - 1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
				) );
				foreach ( $post_ids as $post_id ) {
					$link_wc_id = absint( ABPTT_Function::get_post_info( $post_id, 'link_wc_id' ) );
					if ( $link_wc_id > 0 ) {
						wp_delete_post( $link_wc_id, true );
					}
					wp_delete_post( (int) $post_id, true );
				}
				// 2. Taxonomy terms.
				foreach ( array( 'abptt_category', 'abptt_location', 'abptt_organizer', 'abptt_guide', 'abptt_place' ) as $taxonomy ) {
					if ( ! taxonomy_exists( $taxonomy ) ) {
						continue;
					}
					$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'fields' => 'ids' ) );
					if ( is_wp_error( $terms ) ) {
						continue;
					}
					foreach ( $terms as $term_id ) {
						wp_delete_term( (int) $term_id, $taxonomy );
					}
				}
				// 3. Plugin options.
				foreach ( $this->reset_options() as $option ) {
					delete_option( $option );
				}
				// 4. Seat-plan rows (keep the schema so ABPTT_Static::sp() can regenerate them).
				global $wpdb;
				$table = $wpdb->prefix . 'abptt_sp';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Existence check for a plugin-owned table.
				if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is a trusted prefix.
					$wpdb->query( "DELETE FROM {$table}" );
				}
			}

			/* =========================================================
			 * Terms
			 * ========================================================= */
			private function ensure_taxonomy( string $taxonomy ): bool {
				if ( taxonomy_exists( $taxonomy ) ) {
					return true;
				}
				$cpt = ABPTT_Function::get_cpt();
				if ( ! post_type_exists( $cpt ) ) {
					return false;
				}
				register_taxonomy( $taxonomy, $cpt, array(
					'hierarchical' => true,
					'public'       => true,
					'show_ui'      => false,
					'query_var'    => true,
					'rewrite'      => false,
				) );

				return taxonomy_exists( $taxonomy );
			}

			private function create_terms(): array {
				$map  = array(
					'abptt_location'  => array(),
					'abptt_category'  => array(),
					'abptt_organizer' => array(),
					'abptt_guide'     => array(),
				);
				$sets = array(
					'abptt_location'  => ABPTT_Static::location(),
					'abptt_category'  => ABPTT_Static::category(),
					'abptt_organizer' => ABPTT_Static::organizer(),
					'abptt_guide'     => ABPTT_Static::guide(),
				);
				foreach ( $sets as $taxonomy => $items ) {
					if ( ! $this->ensure_taxonomy( $taxonomy ) ) {
						continue;
					}
					foreach ( $items as $item ) {
						$name = sanitize_text_field( $item['name'] ?? '' );
						if ( $name === '' ) {
							continue;
						}
						$description = isset( $item['description'] ) ? wp_kses_post( $item['description'] ) : '';
						$args        = $description !== '' ? array( 'description' => $description ) : array();
						$term        = wp_insert_term( $name, $taxonomy, $args );
						if ( is_wp_error( $term ) ) {
							$existing = get_term_by( 'name', $name, $taxonomy );
							$term_id  = $existing && ! is_wp_error( $existing ) ? (int) $existing->term_id : 0;
						} else {
							$term_id = (int) $term['term_id'];
						}
						if ( $term_id <= 0 ) {
							continue;
						}
						$map[ $taxonomy ][ $name ] = $term_id;
						if ( $taxonomy === 'abptt_guide' && ! empty( $item['meta'] ) && is_array( $item['meta'] ) ) {
							update_term_meta( $term_id, '_abptt_guide_meta', $item['meta'] );
						}
					}
				}
				// Seed real map coordinates for every location so the details-page map renders.
				// This must run before the option mirrors are rebuilt below.
				foreach ( $map['abptt_location'] as $location_name => $location_term_id ) {
					$coords = $this->location_coords( $location_name );
					if ( ! empty( $coords ) ) {
						update_term_meta( $location_term_id, '_abptt_map', $coords );
					}
				}
				// Rebuild the option mirrors (label/description/meta) from the created terms.
				do_action( 'abptt_location_update' );
				do_action( 'abptt_category_update' );
				do_action( 'abptt_organizer_update' );
				do_action( 'abptt_guide_update' );

				return $map;
			}

			/* =========================================================
			 * Options
			 * ========================================================= */
			private function seed_options(): void {
				$options = array(
					'abptt_ticket'     => ABPTT_Static::ticket(),
					'abptt_decor'      => ABPTT_Static::decoration(),
					'abptt_additional' => ABPTT_Static::additional(),
					'abptt_form'       => ABPTT_Static::form(),
					'abptt_faq'        => ABPTT_Static::faq(),
					'abptt_tc'         => ABPTT_Static::tc(),
					'abptt_feature'    => ABPTT_Static::feature(),
				);
				foreach ( $options as $key => $value ) {
					if ( false === get_option( $key ) && ! empty( $value ) ) {
						update_option( $key, $value );
					}
				}
				// Global date configuration (saved separately from configuration_data()).
				if ( false === get_option( 'abptt_date_config' ) ) {
					update_option( 'abptt_date_config', array(
						'date_format'         => 'D d M , yy',
						'time_format'         => get_option( 'time_format' ) ?: 'H:i',
						'advance_date_number' => '28',
						'sale_close_before'   => '0',
					) );
				}
				// Seed the general / ON-OFF / slider / contact / why-book / css defaults
				// from the plugin's own field definitions so the site looks complete.
				if ( class_exists( 'ABPTT_Configuration' ) ) {
					$instance = new ABPTT_Configuration();
					$data     = $instance->configuration_data();
					if ( is_array( $data ) ) {
						foreach ( $data as $option_id => $fields ) {
							if ( ! is_string( $option_id ) || strpos( $option_id, 'abptt_' ) !== 0 ) {
								continue;
							}
							if ( false !== get_option( $option_id ) || ! is_array( $fields ) ) {
								continue;
							}
							$built = array();
							foreach ( $fields as $field ) {
								$name = $field['name'] ?? '';
								if ( $name === '' || in_array( $name, array( 'collapse_start', 'collapse_end' ), true ) ) {
									continue;
								}
								$built[ $name ] = $field['default'] ?? '';
							}
							if ( ! empty( $built ) ) {
								update_option( $option_id, $built );
							}
						}
					}
				}
				// Seed the global "Why Book With Us" reasons (structured icon/title/description items).
				if ( false === get_option( 'abptt_why_book' ) ) {
					$why_book_items = array(
						array( 'icon' => '💰', 'title' => 'Best Price', 'description' => 'No hidden charges and the lowest fares every single time.' ),
						array( 'icon' => '🎧', 'title' => '24/7 Support', 'description' => 'Real people on call around the clock, ready to help.' ),
						array( 'icon' => '⭐', 'title' => 'Certified Guides', 'description' => 'Hand-picked licensed guides who know every story.' ),
						array( 'icon' => '🗺️', 'title' => 'Flexible Itinerary', 'description' => 'Personalize your trip before you go.' ),
					);
					$why_book_data = array( 'title' => 'Why Book With Us', 'items' => array() );
					foreach ( $why_book_items as $why_book_item ) {
						$why_book_data['items'][ uniqid() ] = $why_book_item;
					}
					update_option( 'abptt_why_book', $why_book_data );
				}
				// Generate the demo seat plans (writes wp_abptt_sp + option abptt_ticket_sp).
				ABPTT_Query::flush_cache();
				ABPTT_Static::sp();
			}

			/* =========================================================
			 * Posts
			 * ========================================================= */
			private function post_specs(): array {
				// seat: ticket|sp | date: particular|repeated | types: number of ticket types
				// form: display guest form | single: one form for all tickets | global_form: use the global form
				return array(
					array( 'seat' => 'ticket', 'date' => 'particular', 'types' => 2, 'form' => 'on', 'single' => 'on', 'global_form' => 'on' ),
					array( 'seat' => 'sp', 'date' => 'repeated', 'types' => 1, 'form' => 'on', 'single' => 'off', 'global_form' => 'on' ),
					array( 'seat' => 'ticket', 'date' => 'particular', 'types' => 3, 'form' => 'off', 'single' => 'on', 'global_form' => 'off' ),
					array( 'seat' => 'sp', 'date' => 'repeated', 'types' => 1, 'form' => 'on', 'single' => 'off', 'global_form' => 'off' ),
					array( 'seat' => 'ticket', 'date' => 'repeated', 'types' => 1, 'form' => 'on', 'single' => 'on', 'global_form' => 'on' ),
					array( 'seat' => 'sp', 'date' => 'particular', 'types' => 1, 'form' => 'off', 'single' => 'off', 'global_form' => 'on' ),
					array( 'seat' => 'ticket', 'date' => 'repeated', 'types' => 2, 'form' => 'on', 'single' => 'on', 'global_form' => 'on' ),
					array( 'seat' => 'sp', 'date' => 'particular', 'types' => 1, 'form' => 'on', 'single' => 'off', 'global_form' => 'off' ),
					array( 'seat' => 'ticket', 'date' => 'particular', 'types' => 3, 'form' => 'on', 'single' => 'on', 'global_form' => 'on' ),
					array( 'seat' => 'sp', 'date' => 'repeated', 'types' => 1, 'form' => 'on', 'single' => 'off', 'global_form' => 'off' ),
				);
			}

			private function post_titles(): array {
				return array(
					'City Lights Walking Tour',
					'Grand Cultural Heritage Tour',
					'Mountain Adventure Expedition',
					'Coastal Sunset Cruise',
					'Wildlife Safari Discovery',
					'Historic Old Town Journey',
					'Desert Dune Safari Experience',
					'River Valley Hiking Trip',
					'Island Beach Holiday Escape',
					'National Museum Guided Visit',
				);
			}

			private function post_subtitles(): array {
				return array(
					'Discover the city skyline and its hidden gems.',
					'Walk through centuries of art, history and tradition.',
					'An unforgettable trek across breathtaking landscapes.',
					'Sail into the sunset along a stunning coastline.',
					'Meet the wildlife in their natural habitat.',
					'Step back in time through charming cobbled streets.',
					'Feel the thrill of the dunes and desert breeze.',
					'Explore lush valleys, rivers and scenic trails.',
					'Relax on golden sands and turquoise waters.',
					'Uncover remarkable stories from the past.',
				);
			}

			private function post_descriptions(): array {
				return array(
					'Join a knowledgeable local guide for a relaxed walking tour that takes in the best viewpoints, landmarks and stories of the city, ideal for first-time visitors and photographers alike.',
					'Immerse yourself in the region\'s rich heritage with visits to historic monuments, museums and cultural quarters, complete with expert commentary and plenty of time to explore.',
					'Challenge yourself on a guided mountain expedition featuring dramatic scenery, well-marked trails and plenty of photo stops, suitable for travellers with a moderate level of fitness.',
					'Spend a magical evening on the water with a scenic sunset cruise, enjoying panoramic coastal views, a calm atmosphere and refreshments served on board.',
					'Get closer to nature on a guided safari that introduces you to remarkable wildlife and ecosystems, with safe transport and experienced rangers throughout the day.',
					'Discover the charm of the old town as you wander its narrow lanes, historic squares and traditional markets, learning the local legends behind every corner.',
					'Experience the desert at its most spectacular with dune drives, camel rides and a traditional camp, all led by guides who know the terrain intimately.',
					'Breathe in the fresh air on a river valley hike that combines gentle climbs, woodland paths and riverside picnic spots perfect for the whole family.',
					'Unwind on a relaxing beach holiday with time to swim, sunbathe and sample local cuisine, with optional water activities available for the more adventurous.',
					'Explore the finest collections and exhibitions at the national museum on a guided visit that brings history, science and art vividly to life.',
				);
			}

			private function post_icons(): array {
				return array(
					'🏙️',
					'🏛️',
					'🏔️',
					'🚢',
					'🐾',
					'🕰️',
					'☀️',
					'🥾',
					'🏖️',
					'🖼️',
				);
			}

			private function ticket_row( int $index, $type ): array {
				$type_bonus = is_numeric( $type ) ? (int) $type : 0;

				return array(
					'qty'         => 40 + ( $index * 3 ),
					'price'       => 30 + ( ( $index * 7 ) % 50 ) + $type_bonus,
					'reserve'     => 5,
					'min_qty'     => 1,
					'max_qty'     => 10,
					'description' => __( 'Includes guided access and standard facilities.', 'abp-tour-travel' ),
				);
			}

			private function ticket_config( array $spec, int $index, array $ticket_keys, array $sp_ids, array $sp_option ): array {
				if ( 'sp' === $spec['seat'] && ! empty( $sp_ids ) ) {
					$sp_id = $sp_ids[ $index % count( $sp_ids ) ];
					$types = array_keys( (array) ( $sp_option[ $sp_id ]['type'] ?? array() ) );
					if ( empty( $types ) ) {
						$types = array( '1' );
					}
					$tickets = array();
					foreach ( $types as $type_id ) {
						$tickets[ $type_id ] = $this->ticket_row( $index, $type_id );
					}

					return array(
						'seat_type'           => 'sp',
						'display_ticket_type' => 'on',
						'ticket_infos'        => $tickets,
						/* translators: %d: seat plan number. */
						'sp_infos'            => array( array( 'id' => $sp_id, 'name' => sprintf( __( 'Seat Plan %d', 'abp-tour-travel' ), $index + 1 ) ) ),
						'all_ticket_type'     => array_values( $types ),
					);
				}
				$total = count( $ticket_keys );
				$want  = max( 1, min( (int) ( $spec['types'] ?? 1 ), max( 1, $total ) ) );
				$pick  = array();
				for ( $k = 0; $k < $want && $total > 0; $k ++ ) {
					$pick[] = $ticket_keys[ ( $index + $k ) % $total ];
				}
				$pick    = array_values( array_unique( $pick ) );
				$tickets = array();
				foreach ( $pick as $type_id ) {
					$tickets[ $type_id ] = $this->ticket_row( $index, $type_id );
				}

				return array(
					'seat_type'           => 'ticket',
					'display_ticket_type' => 'on',
					'ticket_infos'        => $tickets,
					'sp_infos'            => array(),
					'all_ticket_type'     => $pick,
				);
			}

			private function date_config( string $type, int $index ): array {
				$base = current_time( 'Y-m-d' );
				$plus = static function ( int $days ) use ( $base ): string {
					return gmdate( 'Y-m-d', strtotime( $base . ' +' . $days . ' days' ) );
				};
				if ( 'repeated' === $type ) {
					$start = 3 + $index;
					$end   = $start + 90 + ( $index * 5 );

					return array(
						'date_type'         => 'repeated',
						'repeat_after'      => (string) array( 1, 7, 30 )[ $index % 3 ],
						'repeat_start_date' => $plus( $start ),
						'repeat_end_date'   => $plus( $end ),
						'enable_time'       => 'on',
						'date_rule'         => '',
						'off_date_range'    => array(),
					);
				}
				$first = 5 + $index;
				$dates = array();
				foreach ( array( $first, $first + 14, $first + 35 ) as $offset ) {
					$dates[] = array(
						'checkin_date'  => $plus( $offset ),
						'checkin_time'  => '09:00',
						'checkout_date' => $plus( $offset + 1 + ( $index % 3 ) ),
						'checkout_time' => '18:00',
						'reg_end_date'  => $plus( $offset - 2 ),
						'reg_end_time'  => '23:59',
					);
				}

				return array(
					'date_type'        => 'particular',
					'particular_dates' => $dates,
				);
			}

			private function time_config( int $index ): array {
				$all = array(
					array( 'name' => __( 'Morning', 'abp-tour-travel' ), 'time' => '09:00' ),
					array( 'name' => __( 'Late Morning', 'abp-tour-travel' ), 'time' => '10:30' ),
					array( 'name' => __( 'Noon', 'abp-tour-travel' ), 'time' => '12:00' ),
					array( 'name' => __( 'Afternoon', 'abp-tour-travel' ), 'time' => '14:30' ),
					array( 'name' => __( 'Evening', 'abp-tour-travel' ), 'time' => '18:00' ),
					array( 'name' => __( 'Night', 'abp-tour-travel' ), 'time' => '20:30' ),
				);
				$total = count( $all );
				$slots = array();
				for ( $k = 0; $k < 3; $k ++ ) {
					$slot    = $all[ ( $index + ( $k * 2 ) ) % $total ];
					$slots[] = array( 'name' => $slot['name'], 'time' => $slot['time'], 'stock' => '' );
				}
				usort( $slots, static fn( $a, $b ) => strcmp( $a['time'], $b['time'] ) );

				return array(
					'timewise_stock' => 'off',
					'time'           => $slots,
				);
			}

			private function create_posts( array $term_map ): array {
				$specs        = $this->post_specs();
				$titles       = $this->post_titles();
				$subtitles    = $this->post_subtitles();
				$descriptions = $this->post_descriptions();
				$icons        = $this->post_icons();
				$ages         = array( '5 - 50 Years', '10 - 60 Years', '18+ Years', 'All Ages' );
				$start_places = array( 'Downtown Meeting Point', 'Central Station', 'City Main Square', 'Harbor Front', 'Old Town Gate' );

				$feature_keys = array_keys( (array) get_option( 'abptt_feature' ) );
				$ticket_keys  = array_keys( (array) get_option( 'abptt_ticket' ) );
				$sp_option    = (array) get_option( 'abptt_ticket_sp' );
				$sp_ids       = array_keys( $sp_option );

				$locations  = array_values( $term_map['abptt_location'] );
				$categories = array_values( $term_map['abptt_category'] );
				$organizers = array_values( $term_map['abptt_organizer'] );
				$guides     = array_values( $term_map['abptt_guide'] );

				$created = array();
				foreach ( $specs as $index => $spec ) {
					$guide_on = ! empty( $guides );
					$ticket   = $this->ticket_config( $spec, $index, $ticket_keys, $sp_ids, $sp_option );
					$dates    = $this->date_config( $spec['date'], $index );
					$time     = $this->time_config( $index );

					// Features.
					$feature_total = count( $feature_keys );
					$included      = array();
					if ( $feature_total > 0 ) {
						for ( $f = 0; $f < 3; $f ++ ) {
							$included[] = $feature_keys[ ( $index + $f ) % $feature_total ];
						}
						$included = array_values( array_unique( $included ) );
					}
					$excluded = array();
					if ( $feature_total > 0 ) {
						for ( $f = 0; $f < 2; $f ++ ) {
							$candidate = $feature_keys[ ( $index + $f + 5 ) % $feature_total ];
							if ( ! in_array( $candidate, $included, true ) ) {
								$excluded[] = $candidate;
							}
						}
						$excluded = array_values( array_unique( $excluded ) );
					}

					// Places you'll see (location terms).
					$places    = array();
					$loc_total = count( $locations );
					if ( $loc_total > 0 ) {
						$want = min( 2 + ( $index % 3 ), $loc_total );
						for ( $p = 0; $p < $want; $p ++ ) {
							$term_id = $locations[ ( ( $index * 2 ) + $p ) % $loc_total ];
							$term    = get_term( $term_id, 'abptt_location' );
							if ( empty( $term ) || is_wp_error( $term ) ) {
								continue;
							}
							$places[] = array(
								'place_id' => (int) $term_id,
								'name'     => $term->name,
								'des'      => '<p>' . $term->name . ' ' . __( 'is one of the must-visit places included in this tour.', 'abp-tour-travel' ) . '</p>',
							);
						}
					}

					$cat_id = ! empty( $categories ) ? $categories[ $index % count( $categories ) ] : '';
					$org_id = ! empty( $organizers ) ? $organizers[ $index % count( $organizers ) ] : '';
					$guide_ids = $guide_on ? array( $guides[ $index % count( $guides ) ] ) : array();

					// Guest form.
					$form_display = 'on' === $spec['form'] ? 'on' : 'off';
					$form_global  = 'off';
					$form_custom  = array();
					if ( 'on' === $form_display ) {
						$form_global = 'on' === $spec['global_form'] ? 'on' : 'off';
						if ( 'off' === $form_global ) {
							$form_custom = ABPTT_Static::form();
						}
					}

					// Additional services.
					$add_global          = ( 3 === $index ) ? 'off' : 'on';
					$additional_services = ( 'off' === $add_global ) ? ABPTT_Static::additional() : array();

					// FAQ.
					$faq_global = ( 5 === $index ) ? 'off' : 'on';
					$faq_custom = ( 'off' === $faq_global ) ? ABPTT_Static::faq() : array();

					// Terms & conditions.
					$tc_global = ( 7 === $index ) ? 'off' : 'on';
					$tc_custom = ( 'off' === $tc_global ) ? (string) ABPTT_Static::tc() : '';

					$meta = array(
						'sale_continue'                => 'on',
						'abptt_template'               => ( [ 'default', 'light', 'modern' ] )[ $index % 3 ],
						'display_sku'                  => 'on',
						'post_sku'                     => 'SKU-' . sprintf( '%03d', 100 + $index ),
						'post_icon'                    => $icons[ $index % count( $icons ) ],
						'sub_title'                    => $subtitles[ $index ],
						'post_description'             => $descriptions[ $index ],
						'display_organizer'            => 'on',
						'abptt_organizer'              => $org_id,
						'display_capacity'             => 'on',
						'abptt_duration'               => 1 + ( $index % 10 ),
						'abptt_duration_type'          => ( 0 === $index % 2 ) ? 'day' : 'hour',
						'abptt_display_duration'       => 'on',
						'abptt_duration_night'         => $index % 4,
						'abptt_display_duration_night' => 'on',
						'abptt_max_people'             => 10 + ( $index * 5 ),
						'abptt_display_max_people'     => 'on',
						'abptt_min_age'                => $ages[ $index % count( $ages ) ],
						'abptt_display_min_age'        => 'on',
						'abptt_start_place'            => $start_places[ $index % count( $start_places ) ],
						'abptt_display_start_place'    => 'on',
						'abptt_top_pick'               => ( 0 === $index % 3 ) ? 'on' : 'off',
						'abptt_display_rating'         => 'on',
						'display_category'             => 'on',
						'abptt_category'               => $cat_id,
						'related_item'                 => '',
						'post_feature'                 => implode( ',', $included ),
						'post_feature_excluded'        => implode( ',', $excluded ),
						'display_feature_included'     => 'on',
						'display_feature_excluded'     => 'on',
						'abptt_slider'                 => implode( ',', range( 900001 + ( $index * 10 ), 900005 + ( $index * 10 ) ) ),
						'seat_type'                    => $ticket['seat_type'],
						'display_ticket_type'          => $ticket['display_ticket_type'],
						'min_qty'                      => 1,
						'max_qty'                      => 4 + ( $index % 8 ),
						'ticket_infos'                 => $ticket['ticket_infos'],
						'sp_infos'                     => $ticket['sp_infos'],
						'all_ticket_type'              => $ticket['all_ticket_type'],
						'abptt_dates'                  => $dates,
						'time_infos'                   => $time,
						'display_additional_services'  => 'on',
						'active_global_additional'     => $add_global,
						'additional_services'          => $additional_services,
						'display_client_form'          => $form_display,
						'active_global_form'           => $form_global,
						'display_single_form'          => 'on' === $spec['single'] ? 'on' : 'off',
						'abptt_form'                   => $form_custom,
						'display_faq'                  => 'on',
						'active_global_faq'            => $faq_global,
						'abptt_faq'                    => $faq_custom,
						'display_tc'                   => 'on',
						'active_global_tc'             => $tc_global,
						'abptt_tc'                     => $tc_custom,
						'guide'                        => implode( ',', $guide_ids ),
						'display_guide'                => $guide_on ? 'on' : 'off',
						'abptt_places_you_see'         => $places,
						'display_places_you_see'       => 'on',
						'display_timeline'             => 'on',
						'abptt_timeline'               => $this->timeline_items( $index ),
						'dummy'                        => 'on',
					);

					$post_id = wp_insert_post( array(
						'post_title'   => $titles[ $index ],
						'post_status'  => 'publish',
						'post_type'    => ABPTT_Function::get_cpt(),
						'post_content' => '',
					) );
					if ( is_wp_error( $post_id ) || ! $post_id ) {
						continue;
					}
					foreach ( $meta as $meta_key => $meta_value ) {
						update_post_meta( $post_id, sanitize_key( $meta_key ), $meta_value );
					}
					if ( get_option( 'woocommerce_calc_taxes' ) === 'yes' ) {
						update_post_meta( $post_id, '_tax_status', 'none' );
						update_post_meta( $post_id, '_tax_class', '' );
					}
					$this->sync_taxonomies( (int) $post_id, $meta );
					$created[] = (int) $post_id;
				}

				return $created;
			}

			private function sync_taxonomies( int $post_id, array $meta ): void {
				foreach ( array(
					'abptt_category'  => 'abptt_category',
					'abptt_organizer' => 'abptt_organizer',
					'guide'           => 'abptt_guide',
				) as $meta_key => $taxonomy ) {
					if ( ! taxonomy_exists( $taxonomy ) ) {
						continue;
					}
					$value = $meta[ $meta_key ] ?? '';
					$ids   = is_array( $value ) ? $value : explode( ',', (string) $value );
					$ids   = array_values( array_filter( array_map( 'absint', $ids ) ) );
					wp_set_object_terms( $post_id, $ids, $taxonomy, false );
				}
				if ( taxonomy_exists( 'abptt_location' ) ) {
					$location_ids = array();
					foreach ( (array) ( $meta['abptt_places_you_see'] ?? array() ) as $place ) {
						if ( is_array( $place ) && ! empty( $place['place_id'] ) ) {
							$location_ids[] = absint( $place['place_id'] );
						}
					}
					$location_ids = array_values( array_filter( $location_ids ) );
					wp_set_object_terms( $post_id, $location_ids, 'abptt_location', false );
					// The details-page map, the location display and the location filter all
					// read this comma separated list from post meta, so it must exist too.
					update_post_meta( $post_id, 'abptt_location', implode( ',', $location_ids ) );
				}
			}

			/* =========================================================
			 * Google Map coordinates for the demo locations
			 * ========================================================= */
			private function location_coords( string $name ): array {
				$coords = array(
					'New York City' => array( 'lat' => '40.7128', 'lng' => '-74.0060', 'place' => 'New York, USA', 'address' => 'New York, NY, USA' ),
					'Los Angeles'   => array( 'lat' => '34.0522', 'lng' => '-118.2437', 'place' => 'Los Angeles, USA', 'address' => 'Los Angeles, CA, USA' ),
					'Chicago'       => array( 'lat' => '41.8781', 'lng' => '-87.6298', 'place' => 'Chicago, USA', 'address' => 'Chicago, IL, USA' ),
					'Las Vegas'     => array( 'lat' => '36.1699', 'lng' => '-115.1398', 'place' => 'Las Vegas, USA', 'address' => 'Las Vegas, NV, USA' ),
					'San Francisco' => array( 'lat' => '37.7749', 'lng' => '-122.4194', 'place' => 'San Francisco, USA', 'address' => 'San Francisco, CA, USA' ),
					'Miami'         => array( 'lat' => '25.7617', 'lng' => '-80.1918', 'place' => 'Miami, USA', 'address' => 'Miami, FL, USA' ),
					'Orlando'       => array( 'lat' => '28.5383', 'lng' => '-81.3792', 'place' => 'Orlando, USA', 'address' => 'Orlando, FL, USA' ),
					'Boston'        => array( 'lat' => '42.3601', 'lng' => '-71.0589', 'place' => 'Boston, USA', 'address' => 'Boston, MA, USA' ),
					'Nashville'     => array( 'lat' => '36.1627', 'lng' => '-86.7816', 'place' => 'Nashville, USA', 'address' => 'Nashville, TN, USA' ),
					'Austin'        => array( 'lat' => '30.2672', 'lng' => '-97.7431', 'place' => 'Austin, USA', 'address' => 'Austin, TX, USA' ),
				);
				$key = trim( $name );

				return isset( $coords[ $key ] ) ? $coords[ $key ] : array();
			}

			/* =========================================================
			 * Tour itinerary (Timeline) for the demo posts
			 * ========================================================= */
			private function timeline_items( int $index ): array {
				$day = ( $index % 3 ) + 1;

				return array(
					array(
						'time'  => '08:30 AM',
						'title' => __( 'Arrival & Check-in', 'abp-tour-travel' ),
						'des'   => __( 'Meet your guide at the hotel lobby, drop your bags and go over the plan for the day.', 'abp-tour-travel' ),
					),
					array(
						'time'  => '10:00 AM',
						/* translators: %d: day number. */
						'title' => sprintf( __( 'Day %d - Signature Stops', 'abp-tour-travel' ), $day ),
						'des'   => __( 'Guided sightseeing of the landmark stops that define this tour, with time for photos at each one.', 'abp-tour-travel' ),
					),
					array(
						'time'  => '01:00 PM',
						'title' => __( 'Lunch Break', 'abp-tour-travel' ),
						'des'   => __( 'Enjoy an included local lunch at a hand picked restaurant close to the route.', 'abp-tour-travel' ),
					),
					array(
						'time'  => '03:30 PM',
						'title' => __( 'Free Time & Shopping', 'abp-tour-travel' ),
						'des'   => __( 'A relaxed window to explore on your own, pick up souvenirs and grab a coffee.', 'abp-tour-travel' ),
					),
					array(
						'time'  => '06:30 PM',
						'title' => __( 'Sunset Finale', 'abp-tour-travel' ),
						'des'   => __( 'End the day at the best viewpoint in town before the transfer back to your hotel.', 'abp-tour-travel' ),
					),
				);
			}

			/* =========================================================
			 * WooCommerce product reviews (Ratings & Reviews section)
			 * ========================================================= */
			private function seed_reviews( array $post_ids ): void {
				if ( ! function_exists( 'wc_get_product' ) ) {
					return;
				}
				$review_names  = array( 'Sarah Mitchell', 'Daniel Brooks', 'Priya Sharma', 'Marcus Lee' );
				$review_emails = array(
					'sarah.mitchell@example.com',
					'daniel.brooks@example.com',
					'priya.sharma@example.com',
					'marcus.lee@example.com',
				);
				$review_texts = array(
					'Amazing tour - our guide brought every stop to life and the pace worked perfectly for the whole family.',
					'Well organised from start to finish. The itinerary gave us plenty of time at each place.',
					'Booking was simple and the day ran exactly as described. Would happily book again.',
					'Friendly staff, comfortable transport and genuinely interesting stops. Great value for money.',
				);
				foreach ( $post_ids as $index => $post_id ) {
					$product_id = absint( ABPTT_Function::get_post_info( $post_id, 'link_wc_id', 0 ) );
					if ( ! $product_id || ! wc_get_product( $product_id ) ) {
						continue;
					}
					$review_count = 3 + ( $index % 2 );
					for ( $i = 0; $i < $review_count; $i ++ ) {
						$key         = ( $index + $i ) % count( $review_names );
						$comment_id  = wp_insert_comment( array(
							'comment_post_ID'      => $product_id,
							'comment_content'      => $review_texts[ ( $index + $i ) % count( $review_texts ) ],
							'comment_author'       => $review_names[ $key ],
							'comment_author_email' => $review_emails[ $key ],
							'comment_author_IP'    => '127.0.0.1',
							'comment_agent'        => 'Mozilla/5.0 (compatible; ABP Demo)',
							'comment_date'         => current_time( 'mysql' ),
							'comment_date_gmt'     => current_time( 'mysql', 1 ),
							'comment_type'         => 'review',
							'comment_approved'     => '1',
							'comment_parent'       => 0,
						) );
						if ( $comment_id ) {
							add_comment_meta( $comment_id, 'rating', 4 + ( ( $index + $i ) % 2 ) );
						}
					}
					// Recompute the product's average rating / review count from the new reviews.
					if ( function_exists( 'wc_update_product_rating' ) ) {
						wc_update_product_rating( $product_id );
					}
				}
			}

			private function set_related( array $post_ids ): void {
				$total = count( $post_ids );
				if ( $total < 2 ) {
					return;
				}
				foreach ( $post_ids as $index => $post_id ) {
					$related = array();
					for ( $k = 1; $k <= 2 && $k < $total; $k ++ ) {
						$related[] = $post_ids[ ( $index + $k ) % $total ];
					}
					update_post_meta( $post_id, 'related_item', implode( ',', $related ) );
				}
			}
		}
	}
