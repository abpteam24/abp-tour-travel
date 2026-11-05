<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTT_Function' ) ) {
		class ABPTT_Function {
			public function __construct() {
			}

			public static function get_cpt(): string { return 'abptt_post'; }

			public static function get_post_info( $post_id, $key, $default = '' ) {
				$data = get_post_meta( $post_id, $key, true ) ?: $default;

				return self::data_sanitize( $data );
			}

			public static function data_sanitize( $data ) {
				$data = maybe_unserialize( $data );
				if ( is_string( $data ) ) {
					$data = maybe_unserialize( $data );
					if ( is_array( $data ) ) {
						$data = self::data_sanitize( $data );
					} else {
						$data = sanitize_text_field( stripslashes( wp_strip_all_tags( $data ) ) );
					}
				} elseif ( is_array( $data ) ) {
					foreach ( $data as &$value ) {
						if ( is_array( $value ) ) {
							$value = self::data_sanitize( $value );
						} else {
							$value = sanitize_text_field( stripslashes( wp_strip_all_tags( $value ) ) );
						}
					}
				}

				return $data;
			}

			public static function get_all_meta( $post_id = 0 ): array {
				$all_data = [];
				if ( $post_id > 0 ) {
					$all_data['post_title'] = get_the_title( $post_id );
					$all_data['post_id']    = $post_id;
					$metas                  = get_post_meta( $post_id );
					if ( ! empty( $metas ) && sizeof( $metas ) > 0 ) {
						foreach ( $metas as $key => $meta ) {
							$all_data[ $key ] = self::data_sanitize( $meta[0] );
						}
					}
					$all_data['capacity'] = self::get_total_qty( $post_id, $all_data );
				}

				return $all_data;
			}

			/**
			 * Places You'll See rows for a tour.
			 *
			 * Read straight from post meta on purpose: get_all_meta() runs every scalar
			 * through wp_strip_all_tags(), which would drop the markup that the WP editor
			 * produces. Descriptions are therefore read raw and kses filtered here.
			 *
			 * @param int $post_id Tour ID.
			 *
			 * @return array<int,array{place_id:int,name:string,des:string}>
			 */
			public static function get_places_you_see( $post_id = 0 ): array {
				$post_id = absint( $post_id );
				$places  = $post_id > 0 ? get_post_meta( $post_id, 'abptt_places_you_see', true ) : [];
				$places  = is_array( $places ) ? $places : [];
				$result  = [];
				foreach ( $places as $place ) {
					if ( ! is_array( $place ) ) {
						continue;
					}
					$place_id = absint( $place['place_id'] ?? 0 );
					$name     = trim( (string) ( $place['name'] ?? '' ) );
					if ( $place_id > 0 ) {
						$term = get_term( $place_id, 'abptt_location' );
						if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
							$name = $term->name;
						}
					} elseif ( $name !== '' ) {
						$term = get_term_by( 'name', $name, 'abptt_location' );
						if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
							$place_id = absint( $term->term_id );
						}
					}
					if ( $place_id <= 0 && $name === '' ) {
						continue;
					}
					$result[] = [
						'place_id' => $place_id,
						'name'     => $name,
						'des'      => wp_kses_post( (string) ( $place['des'] ?? '' ) ),
					];
				}

				return $result;
			}

			public static function get_taxonomy( $name ) {
				return get_terms( array( 'taxonomy' => $name, 'hide_empty' => false ) );
			}

			public static function get_all_term_data( $term_name ): array {
				$all_data   = [];
				$taxonomies = self::get_taxonomy( $term_name );
				if ( ! empty( $taxonomies ) && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$all_data[ $taxonomy->term_id ] = $taxonomy->name;
					}
				}

				return $all_data;
			}

			public static function get_option( $option, $default = [] ) {
				$option_data = get_option( sanitize_key( $option ) );

				return ! empty( $option_data ) ? $option_data : $default;
			}

			public static function get_options( $option, $key, $default = '' ) {
				$options = get_option( sanitize_key( $option ) );
				if ( isset( $options[ $key ] ) && $options[ $key ] ) {
					$default = $options[ $key ];
				}

				return $default;
			}

			public static function booking_status() { return ( ABPTT_Configuration['booked_status'] ?? null ) ?: 'wc-processing,wc-completed'; }

			/**
			 * Order statuses that count as booked and reserve stock/seats.
			 *
			 * The admin controls this from Configuration → Booked Status. Availability,
			 * sold counts and stock checks use exactly the selected statuses; no status
			 * is added implicitly, so the setting is the single source of truth.
			 */
			public static function booking_status_sold() {
				return self::booking_status();
			}

			public static function label() { return ( ABPTT_Configuration['label'] ?? null ) ?: __( 'Tours', 'abp-tour-travel' ); }

			public static function slug() { return ( ABPTT_Configuration['slug'] ?? null ) ?: 'tour-ticket'; }

			public static function icon_wp() { return ( ABPTT_Configuration['icon'] ?? null ) ?: 'dashicons-palmtree'; }

			public static function icon() { return ( ABPTT_Configuration['brand_icon'] ?? null ) ?: '🏟️'; }

			public static function feature_label() { return ( ABPTT_Configuration['feature_label'] ?? null ) ?: __( 'Feature', 'abp-tour-travel' ); }

			public static function category_label() { return ( ABPTT_Configuration['category_label'] ?? null ) ?: __( 'Category', 'abp-tour-travel' ); }

			public static function category_slug() {
				$slug = ( ABPTT_Configuration['cat_slug'] ?? null ) ?: 'tour-category';

				return 'category' === $slug ? 'tour-category' : $slug;
			}

			public static function category_value( $id ) { return ( ABPTT_Category[ $id ]['label'] ?? null ) ?: $id; }

			public static function organizer_label() { return ( ABPTT_Configuration['organizer_label'] ?? null ) ?: __( 'Operator', 'abp-tour-travel' ); }

			public static function organizer_slug() { return ( ABPTT_Configuration['org_slug'] ?? null ) ?: 'organizer'; }

			public static function organizer_value( $id ) { return ( ABPTT_Organizer[ $id ]['label'] ?? null ) ?: $id; }

			public static function location_label() { return ( ABPTT_Configuration['location_label'] ?? null ) ?: __( 'Location', 'abp-tour-travel' ); }

			public static function location_slug() { return ( ABPTT_Configuration['location_slug'] ?? null ) ?: 'location'; }

			public static function location_value( $id ) { return ( ABPTT_Location[ $id ]['label'] ?? null ) ?: $id; }

			public static function route_directions_url( array $stops ): string {
				$points = [];
				foreach ( $stops as $stop ) {
					$lat = (float) ( $stop['lat'] ?? 0 );
					$lng = (float) ( $stop['lng'] ?? 0 );
					if ( $lat && $lng ) {
						$points[] = $lat . ',' . $lng;
					}
				}
				if ( count( $points ) < 2 ) {
					return '';
				}
				$origin      = array_shift( $points );
				$destination = array_pop( $points );
				$url         = 'https://www.google.com/maps/dir/?api=1&origin=' . rawurlencode( $origin ) . '&destination=' . rawurlencode( $destination );
				if ( ! empty( $points ) ) {
					$url .= '&waypoints=' . rawurlencode( implode( '|', $points ) );
				}

				return $url;
			}

			public static function guide_label() { return ( ABPTT_Configuration['guide_label'] ?? null ) ?: __( 'Guide', 'abp-tour-travel' ); }

			public static function guide_slug() { return ( ABPTT_Configuration['guide_slug'] ?? null ) ?: 'guide'; }

			public static function guide_value( $id ) { return ( ABPTT_Guide[ $id ]['label'] ?? null ) ?: $id; }

			public static function label_place_you_see(): ?string { return __( "Places You'll See", 'abp-tour-travel' ); }

			/**
			 * Format a short rich-text field (term description, timeline note,
			 * FAQ answer, terms text) for output.
			 *
			 * These values are not post content, so they must not run the full
			 * `the_content` filter chain. Themes and plugins routinely hook
			 * `the_content` to inject page-level markup around the main post;
			 * running that chain on a nested field leaks the injected markup
			 * into the field. Paragraph formatting and shortcodes are still
			 * applied, and the result can be extended with the
			 * `abptt_format_content` filter.
			 *
			 * @param string $content Raw field value.
			 *
			 * @return string Formatted HTML.
			 */
			public static function format_content( $content ): string {
				$content = (string) $content;
				if ( '' === trim( $content ) ) {
					return '';
				}
				$content = wptexturize( $content );
				$content = wpautop( $content );
				$content = shortcode_unautop( $content );
				$content = do_shortcode( $content );

				/**
				 * Filter the formatted value of a nested rich-text field.
				 *
				 * @param string $content Formatted HTML.
				 */
				return (string) apply_filters( 'abptt_format_content', $content );
			}

			/**
			 * Icon/label map for the social networks a guide can link to.
			 *
			 * @return array
			 */
			public static function guide_social_defs(): array {
				return [
					'twitter'   => [ 'icon' => 'fab fa-twitter', 'label' => 'Twitter/X' ],
					'linkedin'  => [ 'icon' => 'fab fa-linkedin', 'label' => 'LinkedIn' ],
					'facebook'  => [ 'icon' => 'fab fa-facebook', 'label' => 'Facebook' ],
					'instagram' => [ 'icon' => 'fab fa-instagram', 'label' => 'Instagram' ],
				];
			}

			/**
			 * Resolve a guide photo to a URL that is guaranteed to exist.
			 *
			 * wp_get_attachment_image_url() returns false when the requested size
			 * was never generated, which happens on servers with no GD/Imagick, for
			 * formats WordPress cannot resize, and for images smaller than the size
			 * threshold. Templates gated on that return value silently dropped the
			 * photo, so fall back through the other sizes and then the original.
			 *
			 * @param int $photo_id Attachment ID.
			 * @param string $size Preferred image size.
			 *
			 * @return string URL, or an empty string when no file exists.
			 */
			public static function guide_photo_url( $photo_id, string $size = 'medium_large' ): string {
				$photo_id = absint( $photo_id );
				if ( $photo_id <= 0 || ! get_post( $photo_id ) ) {
					return '';
				}
				// Reject attachments whose file is gone or was never written, e.g. an
				// upload the server refused. wp_get_attachment_url() still returns a
				// URL for those, which would render as a broken image.
				$file = get_attached_file( $photo_id );
				if ( ! $file || ! file_exists( $file ) ) {
					return '';
				}
				$full = wp_get_attachment_url( $photo_id );
				$full = is_string( $full ) ? $full : '';
				// WordPress does not resize SVGs and image_downsize() can hand back a
				// URL without the filename for them, so always use the original file.
				if ( 'image/svg+xml' === get_post_mime_type( $photo_id ) ) {
					return $full;
				}
				foreach ( array_unique( array_filter( [ $size, 'large', 'medium', 'thumbnail', 'full' ] ) ) as $candidate ) {
					$url = wp_get_attachment_image_url( $photo_id, $candidate );
					if ( is_string( $url ) && '' !== $url ) {
						return $url;
					}
				}

				return $full;
			}

			/**
			 * Build the guide photo <img> tag.
			 *
			 * Width/height attributes are only emitted when WordPress actually
			 * measured the file. Without an image library it reports 1x1, and
			 * browsers honour those attributes, so the photo renders as a single
			 * pixel and looks like a missing image.
			 *
			 * @param int $photo_id Attachment ID.
			 * @param string $size Preferred image size.
			 * @param string $alt Alt text, usually the guide name.
			 * @param string $class Optional class attribute.
			 *
			 * @return string Img markup, or an empty string when no file exists.
			 */
			public static function guide_photo_img( $photo_id, string $size = 'medium_large', string $alt = '', string $class = '' ): string {
				$photo_id = absint( $photo_id );
				$url      = self::guide_photo_url( $photo_id, $size );
				if ( '' === $url ) {
					return '';
				}
				$meta   = wp_get_attachment_metadata( $photo_id );
				$width  = ! empty( $meta['width'] ) ? (int) $meta['width'] : 0;
				$height = ! empty( $meta['height'] ) ? (int) $meta['height'] : 0;
				$html   = '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '"';
				if ( '' !== $class ) {
					$html .= ' class="' . esc_attr( $class ) . '"';
				}
				$html .= ' loading="lazy" decoding="async"';
				if ( $width > 1 && $height > 1 ) {
					$html .= ' width="' . $width . '" height="' . $height . '"';
				}

				return $html . ' />';
			}

			/**
			 * Build the render-ready guide list for a tour.
			 *
			 * Reads the `abptt_guide` term relations first and falls back to the
			 * `guide` post meta, so tours saved before the taxonomy relation was
			 * synced still resolve. Returns an empty array when nothing is linked.
			 *
			 * @param int $post_id Tour post ID.
			 *
			 * @return array List of normalised guide items.
			 */
			public static function guide_items( $post_id ): array {
				$post_id = absint( $post_id );
				if ( $post_id <= 0 ) {
					return [];
				}
				$terms = get_the_terms( $post_id, 'abptt_guide' );
				if ( empty( $terms ) || is_wp_error( $terms ) ) {
					$raw_ids = self::get_post_info( $post_id, 'guide' );
					$raw_ids = array_filter( array_map( 'absint', explode( ',', (string) $raw_ids ) ) );
					if ( empty( $raw_ids ) ) {
						return [];
					}
					$terms = [];
					foreach ( $raw_ids as $raw_id ) {
						$term = get_term( $raw_id, 'abptt_guide' );
						if ( $term instanceof WP_Term ) {
							$terms[] = $term;
						}
					}
				}
				$items = [];
				foreach ( $terms as $term ) {
					$items[] = self::guide_profile( $term );
				}

				return $items;
			}

			/**
			 * Normalise a single guide term into a render-ready profile array.
			 *
			 * @param WP_Term|int $term Speaker term or term ID.
			 *
			 * @return array Profile array, or an empty array when the term is invalid.
			 */
			public static function guide_profile( $term ): array {
				if ( is_numeric( $term ) ) {
					$term = get_term( absint( $term ), 'abptt_guide' );
				}
				if ( ! ( $term instanceof WP_Term ) ) {
					return [];
				}
				$social_defs = self::guide_social_defs();
				$meta        = get_term_meta( $term->term_id, '_abptt_guide_meta', true );
				$meta        = is_array( $meta ) ? $meta : [];
				$photo       = isset( $meta['photo'] ) ? absint( $meta['photo'] ) : 0;
				$stored      = isset( $meta['social'] ) && is_array( $meta['social'] ) ? $meta['social'] : [];
				$social      = [];
				foreach ( $social_defs as $key => $def ) {
					$url = isset( $stored[ $key ] ) ? esc_url_raw( $stored[ $key ] ) : '';
					if ( ! empty( $url ) ) {
						$social[] = [
							'key'   => $key,
							'icon'  => $def['icon'],
							'label' => $def['label'],
							'url'   => $url,
						];
					}
				}
				$website     = isset( $meta['website'] ) ? esc_url_raw( $meta['website'] ) : '';
				$designation = isset( $meta['designation'] ) ? sanitize_text_field( $meta['designation'] ) : '';
				$company     = isset( $meta['company'] ) ? sanitize_text_field( $meta['company'] ) : '';
				$term_link   = get_term_link( $term );
				$profile     = [
					'id'          => (int) $term->term_id,
					'name'        => $term->name,
					'slug'        => $term->slug,
					'description' => $term->description,
					'designation' => $designation,
					'company'     => $company,
					'website'     => $website,
					'social'      => $social,
					'photo_id'    => $photo,
					'photo_url'   => self::guide_photo_url( $photo ),
					'link'        => is_wp_error( $term_link ) ? '' : $term_link,
				];

				/**
				 * Filters the normalised guide profile.
				 *
				 * Add fields stored outside the built-in term meta, or reshape the
				 * values, without replacing the templates that consume this array.
				 *
				 * @param array $profile Profile array.
				 * @param WP_Term $term Speaker term.
				 */
				return (array) apply_filters( 'abptt_guide_profile', $profile, $term );
			}

			/**
			 * Build the complete view model for a single guide page.
			 *
			 * Every value the template prints is assembled here and passed through a
			 * filter before the template sees it, so a site can surface data it
			 * stores itself (pronouns, session title, a second link, custom term
			 * meta) by adding to these arrays, with no template override needed.
			 *
			 * Filters, each receiving the profile so callbacks can key off it:
			 *  - abptt_guide_page_data  the finished view model
			 *  - abptt_guide_initials   fallback avatar initials
			 *  - abptt_guide_role       designation/company line parts
			 *  - abptt_guide_facts      key/value rows shown in the hero card
			 *  - abptt_guide_socials    social icon links
			 *  - abptt_guide_photo_img  hero photo markup
			 *
			 * @param WP_Term|int $term Speaker term or term ID.
			 *
			 * @return array View model, or an empty array when the term is not a guide.
			 */
			public static function guide_page_data( $term ): array {
				if ( is_numeric( $term ) ) {
					$term = get_term( absint( $term ), 'abptt_guide' );
				}
				if ( ! ( $term instanceof WP_Term ) ) {
					return [];
				}
				$profile = self::guide_profile( $term );
				if ( empty( $profile ) ) {
					return [];
				}
				$name = (string) ( $profile['name'] ?? '' );
				$bio  = (string) ( $profile['description'] ?? '' );
				// Up to two letters for the avatar placeholder, taken from the first
				// two words so "Dr. Sarah Chen" reads as SC rather than DS.
				$plain_name = trim( wp_strip_all_tags( $name ) );
				$initials   = '';
				foreach ( preg_split( '/\s+/', $plain_name ) as $name_part ) {
					if ( '' !== $name_part ) {
						$initials .= mb_substr( $name_part, 0, 1 );
						if ( mb_strlen( $initials ) >= 2 ) {
							break;
						}
					}
				}
				$initials   = (string) apply_filters( 'abptt_guide_initials', mb_strtoupper( $initials ), $name, $profile );
				$role_parts = array_map( 'esc_html', array_filter( [ $profile['designation'] ?? '', $profile['company'] ?? '' ] ) );
				/** This filter is documented in the method docblock. */
				$role_parts = (array) apply_filters( 'abptt_guide_role', $role_parts, $profile );
				// Only rows the admin actually filled in, so a sparse profile stays
				// compact rather than showing a wall of empty labels.
				$rows  = [
					'designation' => [ 'icon' => 'fas fa-user-tie', 'label' => __( 'Designation', 'abp-tour-travel' ) ],
					'company'     => [ 'icon' => 'fas fa-building', 'label' => __( 'Company', 'abp-tour-travel' ) ],
				];
				$facts = [];
				foreach ( $rows as $key => $row ) {
					$value = (string) ( $profile[ $key ] ?? '' );
					if ( '' !== $value ) {
						$facts[] = [
							'icon'  => $row['icon'],
							'label' => $row['label'],
							'value' => $value,
							'url'   => '',
						];
					}
				}
				$website = (string) ( $profile['website'] ?? '' );
				if ( '' !== $website ) {
					$facts[] = [
						'icon'  => 'fas fa-globe',
						'label' => __( 'Website', 'abp-tour-travel' ),
						// Host only: the full URL duplicates the link text underneath it.
						'value' => (string) ( wp_parse_url( $website, PHP_URL_HOST ) ?: $website ),
						'url'   => $website,
					];
				}
				/** This filter is documented in the method docblock. */
				$facts = (array) apply_filters( 'abptt_guide_facts', $facts, $profile );
				/** This filter is documented in the method docblock. */
				$socials   = (array) apply_filters( 'abptt_guide_socials', (array) ( $profile['social'] ?? [] ), $profile );
				$photo_id  = (int) ( $profile['photo_id'] ?? 0 );
				$photo_img = self::guide_photo_img( $photo_id, 'medium_large', $name, 'attachment-medium_large size-medium_large' );
				/** This filter is documented in the method docblock. */
				$photo_img = (string) apply_filters( 'abptt_guide_photo_img', $photo_img, $profile );
				$data      = [
					'id'         => (int) ( $profile['id'] ?? 0 ),
					'name'       => $name,
					'slug'       => (string) ( $profile['slug'] ?? '' ),
					'link'       => (string) ( $profile['link'] ?? '' ),
					'photo_id'   => $photo_id,
					'photo_img'  => $photo_img,
					'initials'   => $initials,
					'role_parts' => $role_parts,
					'facts'      => $facts,
					'socials'    => $socials,
					'bio'        => $bio,
					'has_bio'    => '' !== trim( wp_strip_all_tags( $bio ) ),
					'profile'    => $profile,
				];

				/** This filter is documented in the method docblock. */
				return (array) apply_filters( 'abptt_guide_page_data', $data, $profile );
			}

			public static function ticket_name( $id ) { return ( ABPTT_Ticket[ $id ]['label'] ?? null ) ?: __( 'Ticket/Seat', 'abp-tour-travel' ); }

			public static function ticket_icon( $id ) { return ( ABPTT_Ticket[ $id ]['icon'] ?? null ) ?: ''; }

			public static function ticket_color( $id ) { return ( ABPTT_Ticket[ $id ]['color'] ?? null ) ?: 'inherit'; }

			public static function decor_icon( $id ) { return ( ABPTT_Decor[ $id ]['icon'] ?? null ) ?: ''; }

			public static function decor_color( $id ) { return ( ABPTT_Decor[ $id ]['color'] ?? null ) ?: 'inherit'; }

			public static function sp_label( $post_id, $id ) {
				if ( ! empty( $post_id ) && $post_id > 0 && ! empty( $id ) ) {
					$sp_infos = self::get_post_info( $post_id, 'sp_infos', [] );
					if ( ! empty( $sp_infos ) && sizeof( $sp_infos ) > 1 ) {
						foreach ( $sp_infos as $sp_info ) {
							if ( $sp_info['id'] == $id ) {
								return $sp_info['name'] ?? $id;
							}
						}
					}
				}

				return $id;
			}

			public static function on_off( $key ): bool {
				$value = ( ABPTT_On_Off[ $key ] ?? 'on' ) ?: 'on';

				return $value !== 'off';
			}

			public static function event_schedule_url( int $post_id, string $tour_date, string $session_time = '' ): string {
				$args = [ 'tour_date' => $tour_date ];
				if ( $session_time !== '' ) {
					$args['session_time'] = $session_time;
				}

				return add_query_arg( $args, get_permalink( $post_id ) );
			}

			public static function event_schedule_items( int $post_id, array $dates, array $time_infos ): array {
				$items = [];
				foreach ( $dates as $date ) {
					$date = gmdate( 'Y-m-d', strtotime( (string) $date ) );
					if ( isset( $items[ $date ] ) ) {
						continue;
					}
					$times          = ABPTT_Function::time( $time_infos, $date );
					$items[ $date ] = [
						'date'  => $date,
						'times' => array_values( array_unique( array_filter( array_map( 'sanitize_text_field', (array) $times ) ) ) ),
					];
				}

				return array_values( $items );
			}

			public static function event_schedule_selection( array $dates, array $time_infos, int $post_id = 0 ): array {
				$dates      = array_values( array_unique( array_map( static function ( $date ): string {
					return gmdate( 'Y-m-d', strtotime( (string) $date ) );
				}, $dates ) ) );
				$start_date = (string) ( $dates[0] ?? '' );
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only URL pre-select for display; no state change.
				$requested_date = isset( $_GET['tour_date'] ) ? sanitize_text_field( wp_unslash( $_GET['tour_date'] ) ) : '';
				if ( $requested_date && in_array( $requested_date, $dates, true ) ) {
					$start_date = $requested_date;
				}
				$all_times  = self::available_times( $post_id, $time_infos, $start_date );
				$start_time = (string) ( $all_times[0] ?? '' );
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only URL pre-select for display; no state change.
				$requested_time = isset( $_GET['session_time'] ) ? sanitize_text_field( wp_unslash( $_GET['session_time'] ) ) : '';
				if ( $requested_time && in_array( $requested_time, $all_times, true ) ) {
					$start_time = $requested_time;
				}

				return [ $dates, $start_date, $all_times, $start_time ];
			}

			public static function array_to_string( $array ) {
				$ids = '';
				if ( sizeof( $array ) > 0 ) {
					foreach ( $array as $data ) {
						if ( $data ) {
							$ids = $ids ? $ids . ',' . $data : $data;
						}
					}
				}

				return $ids;
			}

			public static function build_url( $value = '', $extra_args = [] ): string {
				$default_args = [
					'page'         => ABPTT_Function::slug(),
					'tab'          => $value,
					'_abptt_nonce' => wp_create_nonce( 'abptt_url_action' ),
				];
				$final_args   = array_merge( $default_args, $extra_args );

				return add_query_arg( $final_args, admin_url( 'admin.php' ) );
			}

			public static function get_image_url( $post_id = '', $image_id = '', $size = 'full' ) {
				$image_id = $post_id && $post_id > 0 ? get_post_thumbnail_id( $post_id ) : $image_id;

				return wp_get_attachment_image_url( $image_id, $size );
			}

			public static function get_page_by_slug( $slug ) {
				if ( $pages = get_pages() ) {
					foreach ( $pages as $page ) {
						if ( $slug === $page->post_name ) {
							return $page;
						}
					}
				}

				return false;
			}

			public static function get_id_by_slug( $page_slug ): ?int {
				$page = get_page_by_path( $page_slug );

				return $page ? $page->ID : null;
			}

			public static function check_wc(): int {
				if ( class_exists( 'WooCommerce' ) ) {
					return 2;
				}
				if ( ! function_exists( 'is_plugin_active' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}
				if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					return 2;
				}
				$wc_dir = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce';
				if ( is_dir( $wc_dir ) ) {
					return 1;
				}

				return 0;
			}

			public static function polylang_query_args(): array {
				if ( is_admin() || ! function_exists( 'pll_current_language' ) ) {
					return [];
				}
				$language = pll_current_language( 'slug' );

				return $language ? [ 'lang' => sanitize_key( $language ) ] : [];
			}

			public static function get_user_role( $user_ID ): string {
				global $wp_roles;
				$user_role_list = '';
				$user_data      = get_userdata( $user_ID );
				$user_role_slug = $user_data->roles;
				if ( is_array( $user_role_slug ) && sizeof( $user_role_slug ) > 0 ) {
					$user_count = 0;
					foreach ( $user_role_slug as $user_role ) {
						$user_count ++;
						if ( $user_count > 1 ) {
							$user_role_list .= ", ";
						}
						$user_role_list .= translate_user_role( $wp_roles->roles[ $user_role ]['name'] );
					}
				}

				return $user_role_list;
			}

			//=========== Template Related==================//
			public static function details_template_path( $post_id ): string {
				$post_id       = $post_id ?? get_the_id();
				$template_name = sanitize_key( self::get_post_info( $post_id, 'abptt_template', 'default' ) );
				$template_name = in_array( $template_name, [ 'default', 'light', 'modern' ], true ) ? $template_name : 'default';
				$file_name     = 'details_theme/' . $template_name . '.php';
				$dir           = ABPTT_DIR . 'tt_templates/' . $file_name;
				if ( ! file_exists( $dir ) ) {
					$file_name = 'details_theme/default.php';
				}

				return self::template_path( $file_name );
			}

			public static function template_path( $file_name ): string {
				$file_path   = wp_normalize_path( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . '/tt_templates/' . $file_name );
				$default_dir = wp_normalize_path( ABPTT_DIR . 'tt_templates/' . $file_name );

				return file_exists( $file_path ) ? $file_path : $default_dir;
			}

			//============== Tour Function===============//
			public static function checkout_validation( $booking_infos ) {
				if ( empty( $booking_infos ) ) {
					return false;
				}
				$booking_info = $booking_infos['booking_infos'] ?? [];
				$post_id      = $booking_infos['post_id'] ?? '';
				// Validate post existence and post type.
				if ( empty( $booking_info ) || empty( $post_id ) || get_post_type( $post_id ) !== ABPTT_Function::get_cpt() ) {
					return false;
				}
				$post_infos    = ABPTT_Function::get_all_meta( $post_id );
				$sale_continue = $post_infos['sale_continue'] ?? 'on';
				if ( 'on' !== $sale_continue ) {
					return false;
				}
				$all_dates           = self::date( $post_id );
				$display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
				$display_ticket_type = ABPTT_Function::on_off( 'ticket_type' ) ? $display_ticket_type : 'off';
				foreach ( $booking_info as $cart_item ) {
					if ( empty( $cart_item ) ) {
						continue;
					}
					$tour_date      = ! empty( $cart_item['tour_date'] ) ? gmdate( 'Y-m-d', strtotime( $cart_item['tour_date'] ) ) : '';
					$session_time   = ! empty( $cart_item['session_time'] ) ? gmdate( 'H:i:s', strtotime( '1970-01-01 ' . $cart_item['session_time'] ) ) : '';
					$seat_type      = $cart_item['seat_type'] ?? '';
					$time_infos     = self::schedule_time_infos( $post_infos );
					$all_start_time = ABPTT_Function::time( $time_infos, $tour_date );
					$ticket_infos   = $cart_item['info'] ?? [];
					// Early return if schedule or date validation fails.
					$valid_session = false;
					foreach ( $all_start_time as $available_time ) {
						if ( gmdate( 'H:i', strtotime( '1970-01-01 ' . $available_time ) ) === gmdate( 'H:i', strtotime( '1970-01-01 ' . $session_time ) ) ) {
							$valid_session = true;
							break;
						}
					}
					if ( empty( $ticket_infos ) || ! in_array( $tour_date, $all_dates, true ) || ! $valid_session ) {
						return false;
					}
					$form_data = [
						'post_id'      => $post_id,
						'tour_date'    => $tour_date,
						'session_time' => $session_time,
					];
					// Specific Seat ('sp') Validation Branch.
					if ( 'sp' === $seat_type ) {
						$sp_id = $cart_item['sp_id'] ?? '';
						if ( empty( $sp_id ) ) {
							return false;
						}
						$form_data['sp_id'] = $sp_id;
						$sold_seat          = ABPTT_Query::get_sold_seat( $form_data );
						$all_seat           = self::get_sp_seat( $sp_id );
						foreach ( $ticket_infos as $ticket_info ) {
							$name = $ticket_info['name'] ?? '';
							if ( empty( $name ) || ! in_array( $name, $all_seat, true ) || in_array( $name, $sold_seat, true ) ) {
								return false;
							}
						}
						continue;
					}
					// Standard Ticket Validation Branch.
					$_ticket_infos = $post_infos['ticket_infos'] ?? [];
					if ( empty( $_ticket_infos ) || ! is_array( $_ticket_infos ) ) {
						return false;
					}
					$order_tickets = $cart_item['info'] ?? [];
					if ( 'off' === $display_ticket_type ) {
						$key           = array_key_first( $_ticket_infos );
						$requested_qty = 0;
						foreach ( $order_tickets as $order_ticket ) {
							$requested_qty += intval( $order_ticket['qty'] ?? 0 );
						}
						$order_tickets = [ $key => [ 'qty' => $requested_qty ] ];
					}
					if ( empty( $order_tickets ) ) {
						return false;
					}
					$sold_infos      = ABPTT_Query::get_sold_ticket( $form_data );
					$requested_total = 0;
					foreach ( $order_tickets as $tic_id => $order_ticket ) {
						if ( ! isset( $_ticket_infos[ $tic_id ] ) ) {
							return false;
						}
						$_ticket_info  = $_ticket_infos[ $tic_id ];
						$qty           = intval( $order_ticket['qty'] ?? 1 );
						$sold          = $sold_infos[ $tic_id ] ?? 0;
						$reserve       = intval( $_ticket_info['reserve'] ?? 0 );
						$total_qty     = intval( $_ticket_info['qty'] ?? 0 );
						$max_qty_raw   = $_ticket_info['max_qty'] ?? '';
						$available_qty = $total_qty - $sold - $reserve;
						$max_qty       = ( '' !== $max_qty_raw && intval( $max_qty_raw ) <= $available_qty ) ? intval( $max_qty_raw ) : $available_qty;
						$min_qty       = intval( $_ticket_info['min_qty'] ?? 1 );
						if ( $qty < $min_qty || $qty > $max_qty ) {
							return false;
						}
						$requested_total += $qty;
					}
					$slot_available = self::slot_available( (int) $post_id, $time_infos, $tour_date, $session_time );
					if ( null !== $slot_available && $requested_total > $slot_available ) {
						return false;
					}
				}

				return true;
			}

			//============= Date function================//
			public static function date_all( $post_ids = [], $_date = '' ): array {
				$all_dates = [];
				$post_ids  = ! empty( $post_ids ) ? $post_ids : ABPTT_ids;
				if ( ! empty( $post_ids ) ) {
					foreach ( $post_ids as $post_id ) {
						if ( ! empty( $post_id ) && $post_id > 0 ) {
							$date_infos = self::get_post_info( $post_id, 'abptt_dates', [] );
							$dates      = self::date( $post_id, $date_infos, $_date );
							$all_dates  = array_merge( $all_dates, $dates );
						}
					}
					$all_dates = array_unique( $all_dates );
					usort( $all_dates, "ABPTT_Function::sort_date" );
				}

				return $all_dates;
			}

			public static function date( $post_id, $date_infos = [], $_date = '' ): array {
				$all_dates = [];
				if ( ! empty( $post_id ) && $post_id > 0 ) {
					if ( empty( $date_infos ) ) {
						$date_infos = self::get_post_info( $post_id, 'abptt_dates', [] );
					}
					$date_type = $date_infos['date_type'] ?? 'particular';
					$now       = current_time( 'Y-m-d' );
					if ( $date_type == 'particular' ) {
						foreach ( ( $date_infos['particular_dates'] ?? [] ) as $particular_date ) {
							if ( ! is_array( $particular_date ) || empty( $particular_date['checkin_date'] ) ) {
								continue;
							}
							if ( ! self::registration_open( $particular_date, $now ) ) {
								continue;
							}
							$date_item = gmdate( 'Y-m-d', strtotime( $particular_date['checkin_date'] ) );
							if ( strtotime( $date_item ) >= strtotime( $now ) ) {
								$all_dates[] = $date_item;
							}
						}
					} else {
						$anchor      = $date_infos['repeat_start_date'] ?? '';
						$anchor      = $anchor ? gmdate( 'Y-m-d', strtotime( $anchor ) ) : '';
						$start_date  = ! empty( $_date ) ? gmdate( 'Y-m-d', strtotime( $_date ) ) : $anchor;
						$start_date  = $start_date ?: $now;
						$active_days = ( ABPTT_Date_Config['advance_date_number'] ?? null ) ?: 28;
						$repeat      = max( (int) ( $date_infos['repeat_after'] ?? 1 ), 1 );
						if ( strtotime( $now ) > strtotime( $start_date ) ) {
							$day_diff   = (int) floor( ( strtotime( $now ) - strtotime( $start_date ) ) / DAY_IN_SECONDS );
							$steps      = (int) ceil( $day_diff / $repeat );
							$start_date = gmdate( 'Y-m-d', strtotime( $start_date . ' +' . ( $steps * $repeat ) . ' day' ) );
						}
						$end_date  = gmdate( 'Y-m-d', strtotime( $start_date . ' +' . $active_days . ' day' ) );
						$end_date  = self::repeat_end_date( $date_infos, $start_date, $end_date );
						$all_dates = self::date_list_modify( $start_date, $end_date, $date_infos );
					}
					$all_dates = sizeof( $all_dates ) > 1 ? array_unique( $all_dates ) : $all_dates;
					usort( $all_dates, "ABPTT_Function::sort_date" );
					$current_date = current( $all_dates );
					$now          = strtotime( current_time( 'Y-m-d' ) );
					if ( $current_date && $now == strtotime( $current_date ) ) {
						$time_info = self::date_session_times( $post_id, $date_infos, $current_date );
						if ( ! empty( $time_info ) ) {
							$exit        = false;
							$buffer_time = (int) ( ABPTT_Date_Config['sale_close_before'] ?? 0 ) * 60;
							$now_time    = strtotime( current_time( 'Y-m-d H:i' ) );
							foreach ( $time_info as $_time ) {
								$date = $current_date . ' ' . $_time;
								$date = $buffer_time > 0 ? strtotime( $date ) - $buffer_time : strtotime( $date );
								if ( $now_time < $date ) {
									$exit = true;
								}
							}
							if ( ! $exit ) {
								unset( $all_dates[ array_key_first( $all_dates ) ] );
							}
						}
					}
				}

				return array_values( $all_dates );
			}

			public static function registration_open( array $particular_date, string $now = '' ): bool {
				$reg_end_date = $particular_date['reg_end_date'] ?? '';
				$reg_end_time = $particular_date['reg_end_time'] ?? '';
				if ( empty( $reg_end_date ) ) {
					return true;
				}
				$now     = $now ?: current_time( 'Y-m-d' );
				$reg_end = $reg_end_date . ( $reg_end_time ? ' ' . $reg_end_time : ' 23:59' );
				$reg_end = strtotime( $reg_end );
				if ( ! $reg_end ) {
					return true;
				}

				return strtotime( current_time( 'Y-m-d H:i' ) ) < $reg_end || $now < $reg_end_date;
			}

			public static function repeat_end_date( array $date_infos, string $start_date, string $default_end ): string {
				if ( ! empty( $date_infos['repeat_end_date'] ) ) {
					$repeat_end = gmdate( 'Y-m-d', strtotime( $date_infos['repeat_end_date'] ) );
					if ( $repeat_end && $repeat_end < $default_end ) {
						$default_end = $repeat_end;
					}
				}

				return $default_end;
			}

			public static function date_session_times( $post_id, array $date_infos = [], string $current_date = '' ): array {
				$date_type = $date_infos['date_type'] ?? 'particular';
				if ( 'particular' === $date_type && ! empty( $current_date ) ) {
					// A date can have more than one particular session configured. Collect
					// all of them instead of returning the first one and ignoring the rest.
					$particular_times = [];
					foreach ( ( $date_infos['particular_dates'] ?? [] ) as $particular_date ) {
						if ( ! is_array( $particular_date ) || empty( $particular_date['checkin_date'] ) ) {
							continue;
						}
						$checkin = gmdate( 'Y-m-d', strtotime( $particular_date['checkin_date'] ) );
						if ( $checkin === $current_date && self::is_valid_time( $particular_date['checkin_time'] ?? '' ) ) {
							$particular_times[] = (string) $particular_date['checkin_time'];
						}
					}
					if ( ! empty( $particular_times ) ) {
						return array_values( array_unique( $particular_times ) );
					}
				}
				$time_infos = self::get_post_info( $post_id, 'time_infos', [] );

				return self::time( $time_infos, $current_date );
			}

			public static function is_valid_time( $time ): bool {
				return (bool) ( $time && preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) $time ) );
			}

			public static function slot_times( $slots ): array {
				$times = [];
				foreach ( (array) $slots as $slot ) {
					$time = is_array( $slot ) ? ( $slot['time'] ?? '' ) : $slot;
					$time = is_scalar( $time ) ? (string) $time : '';
					if ( self::is_valid_time( $time ) ) {
						$times[] = $time;
					}
				}

				return array_values( array_unique( $times ) );
			}

			public static function schedule_time_infos( array $post_infos ): array {
				$time_infos = is_array( $post_infos['time_infos'] ?? null ) ? $post_infos['time_infos'] : array();
				$date_infos = is_array( $post_infos['abptt_dates'] ?? null ) ? $post_infos['abptt_dates'] : array();
				if ( ( $date_infos['date_type'] ?? '' ) !== 'particular' ) {
					return $time_infos;
				}
				$particular_dates = $date_infos['particular_dates'] ?? array();
				if ( empty( $particular_dates ) || ! is_array( $particular_dates ) ) {
					return $time_infos;
				}
				if ( ! isset( $time_infos['date_times'] ) || ! is_array( $time_infos['date_times'] ) ) {
					$time_infos['date_times'] = array();
				}
				foreach ( $particular_dates as $particular_date ) {
					if ( ! is_array( $particular_date ) || empty( $particular_date['checkin_date'] ) || ! self::is_valid_time( $particular_date['checkin_time'] ?? '' ) ) {
						continue;
					}
					$checkin_date = gmdate( 'Y-m-d', strtotime( $particular_date['checkin_date'] ) );
					$time_key     = 'abptt_p_' . $checkin_date;
					if ( ! isset( $time_infos['date_times'][ $time_key ] ) || ! is_array( $time_infos['date_times'][ $time_key ] ) ) {
						$time_infos['date_times'][ $time_key ] = [
							'date' => $checkin_date,
							'time' => array(),
						];
					}
					$time_infos['date_times'][ $time_key ]['time'][] = (string) $particular_date['checkin_time'];
					$time_infos['date_times'][ $time_key ]['time']   = array_values( array_unique( $time_infos['date_times'][ $time_key ]['time'] ) );
				}

				return $time_infos;
			}

			public static function normalize_time( $time ): string {
				$time = is_scalar( $time ) ? (string) $time : '';
				if ( '' === trim( $time ) ) {
					return '';
				}
				$timestamp = strtotime( '1970-01-01 ' . $time );

				return $timestamp ? gmdate( 'H:i', $timestamp ) : '';
			}

			public static function date_slots( array $time_infos, string $date ): array {
				$date = '' !== $date ? gmdate( 'Y-m-d', strtotime( $date ) ) : '';
				if ( '' !== $date ) {
					foreach ( (array) ( $time_infos['date_times'] ?? [] ) as $date_time ) {
						if ( ! is_array( $date_time ) ) {
							continue;
						}
						$slot_date = (string) ( $date_time['date'] ?? '' );
						if ( '' !== $slot_date && gmdate( 'Y-m-d', strtotime( $slot_date ) ) === $date ) {
							return (array) ( $date_time['time'] ?? [] );
						}
					}
					$day_name  = strtolower( gmdate( 'l', strtotime( $date ) ) );
					$day_times = (array) ( $time_infos['day_time'] ?? [] );
					foreach ( $day_times as $day_key => $slots ) {
						if ( strtolower( (string) $day_key ) === $day_name ) {
							return (array) $slots;
						}
					}
				}

				return (array) ( $time_infos['time'] ?? [] );
			}

			public static function slot_stock( array $time_infos, string $date, string $time ): ?int {
				if ( ( $time_infos['timewise_stock'] ?? 'off' ) !== 'on' ) {
					return null;
				}
				$time = self::normalize_time( $time );
				if ( '' === $time ) {
					return null;
				}
				foreach ( self::date_slots( $time_infos, $date ) as $slot ) {
					if ( ! is_array( $slot ) || self::normalize_time( $slot['time'] ?? '' ) !== $time ) {
						continue;
					}
					$stock = $slot['stock'] ?? '';

					return ( '' === $stock || null === $stock ) ? null : max( 0, (int) $stock );
				}

				return null;
			}

			public static function slot_available( int $post_id, array $time_infos, string $date, string $time ): ?int {
				if ( '' === $date || '' === self::normalize_time( $time ) ) {
					return null;
				}
				$stock = self::slot_stock( $time_infos, $date, $time );
				if ( null === $stock ) {
					return null;
				}
				if ( $post_id <= 0 ) {
					return $stock;
				}
				$sold = ABPTT_Query::get_sold_ticket( [
					'post_id'      => $post_id,
					'tour_date'    => gmdate( 'Y-m-d', strtotime( $date ) ),
					'session_time' => $time,
				] );

				return max( 0, $stock - (int) ( $sold['total'] ?? 0 ) );
			}

			public static function available_times( int $post_id, array $time_infos, string $date ): array {
				$times = array_values( array_unique( array_filter( array_map( 'sanitize_text_field', (array) self::time( $time_infos, $date ) ) ) ) );
				if ( $post_id <= 0 || empty( $times ) ) {
					return $times;
				}
				$available = [];
				foreach ( $times as $time ) {
					$remaining = self::slot_available( $post_id, $time_infos, $date, $time );
					if ( null === $remaining || $remaining > 0 ) {
						$available[] = $time;
					}
				}

				return $available;
			}

			public static function date_list_modify( $start_date, $end_date, $date_infos ): array {
				$start_date = is_scalar( $start_date ) ? (string) $start_date : '';
				$end_date   = is_scalar( $end_date ) ? (string) $end_date : '';
				$all_dates  = [];
				if ( $start_date !== '' && $end_date !== '' && strtotime( $start_date ) <= strtotime( $end_date ) ) {
					$now             = current_time( 'Y-m-d' );
					$off_dates       = [];
					$date_rule       = $date_infos['date_rule'] ?? '';
					$date_rule_array = $date_rule ? explode( ',', $date_rule ) : [];
					if ( in_array( 'off_date_range', $date_rule_array ) ) {
						$off_date_range = $date_infos['off_date_range'] ?? [];
						if ( is_array( $off_date_range ) && sizeof( $off_date_range ) > 0 ) {
							foreach ( $off_date_range as $off_date ) {
								if ( is_array( $off_date ) && isset( $off_date['from'], $off_date['to'] ) && is_string( $off_date['from'] ) && is_string( $off_date['to'] ) && $off_date['from'] !== '' && $off_date['to'] !== '' ) {
									$from_date      = gmdate( 'Y-m-d', strtotime( $off_date['from'] ) );
									$to_date        = gmdate( 'Y-m-d', strtotime( $off_date['to'] ) );
									$off_date_lists = self::date_separate_period( $from_date, $to_date );
									foreach ( $off_date_lists as $off_date_list ) {
										$off_dates[] = $off_date_list->format( 'Y-m-d' );
									}
								}
							}
						}
					}
					if ( in_array( 'specific_off_dates', $date_rule_array ) ) {
						$particular_off_dates = $date_infos['specific_off_dates'] ?? [];
						if ( is_array( $particular_off_dates ) && sizeof( $particular_off_dates ) > 0 ) {
							foreach ( $particular_off_dates as $particular_off_date ) {
								if ( is_scalar( $particular_off_date ) && (string) $particular_off_date !== '' ) {
									$particular_off_date = gmdate( 'Y-m-d', strtotime( (string) $particular_off_date ) );
									$off_dates[]         = $particular_off_date;
								}
							}
						}
					}
					$off_dates     = array_unique( $off_dates );
					$off_day_array = [];
					if ( in_array( 'weekend', $date_rule_array ) ) {
						$off_days      = $date_infos['weekend'] ?? '';
						$off_day_array = is_string( $off_days ) && $off_days ? explode( ',', $off_days ) : [];
					}
					$repeat = max( (int) ( $date_infos['repeat_after'] ?? 1 ), 1 );
					$dates  = self::date_separate_period( $start_date, $end_date, $repeat );
					foreach ( $dates as $date ) {
						$date = $date->format( 'Y-m-d' );
						if ( strtotime( $date ) >= strtotime( $now ) ) {
							$day = strtolower( gmdate( 'l', strtotime( $date ) ) );
							if ( ! in_array( $date, $off_dates ) && ! in_array( $day, $off_day_array ) ) {
								$all_dates[] = $date;
							}
						}
					}
					//==================//
					if ( in_array( 'special_on_dates', $date_rule_array ) ) {
						$special_on_dates = $date_infos['special_on_dates'] ?? [];
						if ( is_array( $special_on_dates ) && sizeof( $special_on_dates ) > 0 ) {
							foreach ( $special_on_dates as $date_item ) {
								$date_item = is_array( $date_item ) && isset( $date_item['date'] ) ? $date_item['date'] : $date_item;
								if ( is_string( $date_item ) && $date_item !== '' ) {
									$date_item = gmdate( 'Y-m-d', strtotime( $date_item ) );
									if ( strtotime( $date_item ) >= strtotime( $now ) ) {
										$all_dates[] = $date_item;
									}
								}
							}
						}
					}
				}

				return array_values( array_unique( $all_dates ) );
			}

			public static function time( $time_infos, $date ) {
				$time_infos   = is_array( $time_infos ) ? $time_infos : [];
				$day_times    = $time_infos['day_time'] ?? [];
				$date_times   = $time_infos['date_times'] ?? [];
				$default_time = self::slot_times( $time_infos['time'] ?? [] );
				$time_info    = [];
				if ( ! empty( $day_times ) && is_array( $day_times ) ) {
					foreach ( $day_times as $day_key => $day_time ) {
						$day_time = self::slot_times( $day_time );
						if ( ! empty( $day_time ) ) {
							$time_info[ strtolower( (string) $day_key ) ] = $day_time;
						}
					}
				}
				if ( ! empty( $date_times ) && is_array( $date_times ) ) {
					foreach ( $date_times as $date_time ) {
						if ( ! is_array( $date_time ) ) {
							continue;
						}
						$_date = $date_time['date'] ?? '';
						$_time = self::slot_times( $date_time['time'] ?? [] );
						if ( ! empty( $_date ) && ! empty( $_time ) && strtotime( $_date ) !== false ) {
							$time_info[ gmdate( 'Y-m-d', strtotime( $_date ) ) ] = $_time;
						}
					}
				}
				$date = is_scalar( $date ) ? (string) $date : '';
				if ( $date !== '' && ! empty( $time_info[ gmdate( 'Y-m-d', strtotime( $date ) ) ] ) ) {
					return $time_info[ gmdate( 'Y-m-d', strtotime( $date ) ) ];
				}
				$day_name = $date !== '' ? strtolower( gmdate( 'l', strtotime( $date ) ) ) : '';
				if ( $day_name && ! empty( $time_info[ $day_name ] ) ) {
					return $time_info[ $day_name ];
				}
				if ( ( $time_infos['enable_time'] ?? 'on' ) === 'off' && ! empty( $default_time ) ) {
					return array_slice( $default_time, 0, 1 );
				}

				return $default_time;
			}

			public static function time_difference( $start_time, $end_time ): string {
				$text = '';
				if ( ! empty( $end_time ) ) {
					$start_time   = $start_time ?? 0;
					$totalMinutes = $end_time - $start_time;
					if ( $totalMinutes <= 0 ) {
						return __( '0 Min', 'abp-tour-travel' );
					}
					$days    = floor( $totalMinutes / 1440 );
					$hours   = floor( ( $totalMinutes % 1440 ) / 60 );
					$minutes = $totalMinutes % 60;
					if ( $days > 0 ) {
						$text .= sprintf(
						/* translators: %s = Days */
							_n( ' %s Day', ' %s Days', $days, 'abp-tour-travel' ), $days );
					}
					if ( $hours > 0 ) {
						$text .= sprintf(
						/* translators: %s = Hours */
							_n( ' %s Hour', ' %s Hours', $hours, 'abp-tour-travel' ), $hours );
					}
					if ( $minutes > 0 ) {
						$text .= sprintf(
						/* translators: %s = Minutes */
							_n( ' %s Minute', ' %s Minutes', $minutes, 'abp-tour-travel' ), $minutes );
					}
				}

				return $text;
			}

			public static function date_format( $date, $format = '' ): string {
				if ( ! empty( $date ) ) {
					if ( empty( $format ) ) {
						$format = ABPTT_Function::check_time_exit_date( $date ) ? 'full' : 'date';
					}
					$date_format = self::date_format_php();
					$time_format = ABPTT_Time_Format;
					$wp_settings = $date_format . '  ' . $time_format;
					//$timezone = wp_timezone_string();
					$timestamp = strtotime( $date );
					if ( $format == 'date' ) {
						$date = date_i18n( $date_format, $timestamp );
					} elseif ( $format == 'time' ) {
						$date = date_i18n( $time_format, $timestamp );
					} elseif ( $format == 'full' ) {
						$date = date_i18n( $wp_settings, $timestamp );
					} elseif ( $format == 'day' ) {
						$date = date_i18n( 'd', $timestamp );
					} elseif ( $format == 'month' ) {
						$date = date_i18n( 'M', $timestamp );
					} elseif ( $format == 'year' ) {
						$date = date_i18n( 'Y', $timestamp );
					} else {
						$date = date_i18n( $format, $timestamp );
					}
				}

				return $date;
			}

			public static function date_format_php(): string {
				$formats = [
					'yy/mm/dd'   => 'Y/m/d',
					'yy-dd-mm'   => 'Y-d-m',
					'yy/dd/mm'   => 'Y/d/m',
					'dd-mm-yy'   => 'd-m-Y',
					'dd/mm/yy'   => 'd/m/Y',
					'mm-dd-yy'   => 'm-d-Y',
					'mm/dd/yy'   => 'm/d/Y',
					'd M , yy'   => 'j M , Y',
					'D d M , yy' => 'D j M , Y',
					'M d , yy'   => 'M  j, Y',
					'D M d , yy' => 'D M  j, Y',
				];

				return $formats[ ABPTT_JS_Date_Format ] ?? 'Y-m-d';
			}

			public static function date_format_js() { return ( ABPTT_Date_Config['date_format'] ?? null ) ?: 'D d M , yy'; }

			public static function date_separate_period( $start_date, $end_date, $repeat = 1 ): DatePeriod {
				$start_date = is_scalar( $start_date ) ? (string) $start_date : '';
				$end_date   = is_scalar( $end_date ) ? (string) $end_date : '';
				$repeat     = max( (int) $repeat, 1 );
				$_interval  = 'P' . $repeat . 'D';
				$end_date   = gmdate( 'Y-m-d', strtotime( $end_date . ' +1 day' ) );

				return new DatePeriod( new DateTime( $start_date ), new DateInterval( $_interval ), new DateTime( $end_date ) );
			}

			public static function check_time_exit_date( $date ): bool {
				if ( $date ) {
					$parse_date = date_parse( $date );
					if ( ( $parse_date['hour'] && $parse_date['hour'] > 0 ) || ( $parse_date['minute'] && $parse_date['minute'] > 0 ) || ( $parse_date['second'] && $parse_date['second'] > 0 ) ) {
						return true;
					}
				}

				return false;
			}

			public static function sort_date( $a, $b ): int { return strtotime( $a ) - strtotime( $b ); }

			public static function sort_date_array( $a, $b ): int {
				$dateA = strtotime( $a['time'] );
				$dateB = strtotime( $b['time'] );
				if ( $dateA == $dateB ) {
					return 0;
				} elseif ( $dateA > $dateB ) {
					return 1;
				} else {
					return - 1;
				}
			}

			public static function get_date_time_difference( $start_time, $end_time ) {
				$text = '';
				if ( ! empty( $start_time ) && ! empty( $end_time ) && strtotime( $start_time ) <= strtotime( $end_time ) ) {
					$date1   = date_create( $start_time );
					$date2   = date_create( $end_time );
					$diff    = date_diff( $date1, $date2 );
					$years   = $diff->y;
					$months  = $diff->m;
					$days    = $diff->d;
					$hours   = $diff->h;
					$minutes = $diff->i;
					$seconds = $diff->s;
					if ( $years > 0 ) {
						$text .= sprintf(
						/* translators: %s =Years */
							_n( ' %s Year', ' %s Years', $years, 'abp-tour-travel' ), $years );
					}
					if ( $months > 0 ) {
						$text .= sprintf(
						/* translators: %s = Months */
							_n( ' %s Month', ' %s Months', $months, 'abp-tour-travel' ), $months );
					}
					if ( $days > 0 ) {
						$text .= sprintf(
						/* translators: %s = Days */
							_n( ' %s Day', ' %s Days', $days, 'abp-tour-travel' ), $days );
					}
					if ( $hours > 0 ) {
						$text .= sprintf(
						/* translators: %s = Hours */
							_n( ' %s Hour', ' %s Hours', $hours, 'abp-tour-travel' ), $hours );
					}
					if ( $minutes > 0 ) {
						$text .= sprintf(
						/* translators: %s = Minutes */
							_n( ' %s Minute', ' %s Minutes', $minutes, 'abp-tour-travel' ), $minutes );
					}
					if ( $seconds > 0 ) {
						$text .= sprintf(
						/* translators: %s = Seconds */
							_n( ' %s Second', ' %s Seconds', $seconds, 'abp-tour-travel' ), $seconds );
					}
				}

				return $text;
			}

			public static function check_time_slot_exit( $main_slots, $input_slots ): bool {
				if ( ! empty( $main_slots ) && ! empty( $input_slots ) ) {
					$main_slots = explode( '-', $main_slots );
					if ( isset( $main_slots[0] ) && isset( $main_slots[1] ) ) {
						$main_start  = strtotime( $main_slots[0] );
						$main_end    = strtotime( $main_slots[1] );
						$input_slots = strtotime( $input_slots );
						if ( $main_start <= $input_slots && $main_end >= $input_slots ) {
							return true;
						}
					}
				}

				return false;
			}

			public static function booking_buffer( $time, $end = '' ): string {
				$date_infos = ABPTT_Date_Config;
				if ( ! empty( $end ) ) {
					$buffer_time = (int) ( $date_infos['sale_close_after'] ?? 0 ) * 60;
					$time        = gmdate( 'Y-m-d H:i', strtotime( $time ) + $buffer_time );
				} else {
					$buffer_time = (int) ( $date_infos['sale_close_before'] ?? 0 ) * 60;
					$time        = gmdate( 'Y-m-d H:i', strtotime( $time ) - $buffer_time );
				}

				return $time;
			}

			//=============Price Function================//
			public static function tax_with_price( $post_id, $price ): string {
				$num_of_decimal = get_option( 'woocommerce_price_num_decimals', 2 );
				$_product       = self::get_post_info( $post_id, 'link_wc_id', $post_id );
				$product        = wc_get_product( $_product );
				$tax_display    = get_option( 'woocommerce_tax_display_shop' );
				if ( '' === $price ) {
					return '';
				}
				$return_price = (float) $price;
				if ( $product && $product->is_taxable() ) {
					$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
					if ( ! empty( $tax_rates ) ) {
						$taxes     = WC_Tax::calc_tax( $return_price, $tax_rates, false );
						$tax_total = 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ? array_sum( $taxes ) : array_sum( array_map( 'wc_round_tax_total', $taxes ) );
						if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() ) {
							$return_price = round( $return_price, $num_of_decimal );
						} else {
							$return_price = $tax_display === 'excl' ? round( $return_price, $num_of_decimal ) : round( $return_price + $tax_total, $num_of_decimal );
						}
					}
				}

				return $return_price;
			}

			public static function get_price( $post_infos, $type = 'price', $date = '' ) {
				if ( ! empty( $type ) && ! empty( $post_infos ) ) {
					$price   = 0;
					$post_id = absint( $post_infos['post_id'] ?? 0 );
					if ( ! empty( $post_id ) && $post_id > 0 ) {
						$display_ticket_type = $post_infos['display_ticket_type'] ?? ABPTT_Function::get_post_info( $post_id, 'display_ticket_type', 'on' );
						$display_ticket_type = ABPTT_Function::on_off( 'ticket_type' ) ? $display_ticket_type : 'off';
						$type                = $display_ticket_type == 'on' ? $type : 'price';
						$price_infos         = $post_infos['ticket_infos'] ?? ABPTT_Function::get_post_info( $post_id, 'ticket_infos', [] );
						if ( ! empty( $price_infos ) && array_key_exists( $type, $price_infos ) ) {
							$price = $price_infos[ $type ]['price'] ?? ( current( $price_infos['price'] ) ?? 0 );
							$price = $price > 0 ? apply_filters( 'abptt_filter_price', $price, $date ) : $price;
						}
					}

					return $price > 0 ? self::tax_with_price( $post_id, $price ) : 0;
				}

				return null;
			}

			public static function get_additional_price( $post_infos = [], $i_d = '' ) {
				$services = ABPTT_Function::additional_data( $post_infos );
				$price    = 0;
				$post_id  = $post_infos['post_id'] ?? '';
				if ( ! empty( $services ) && is_array( $services ) && ! empty( $i_d ) ) {
					foreach ( $services as $id => $service ) {
						if ( $id == $i_d ) {
							$price = $service['price'] ?? 0;
							break;
						}
					}
				}

				return $price > 0 ? self::tax_with_price( $post_id, $price ) : 0;
			}

			public static function get_min_price( $post_id = 0 ) {
				$price = 0;
				if ( ! empty( $post_id ) && $post_id > 0 ) {
					$price_infos = ABPTT_Function::get_post_info( $post_id, 'ticket_infos', [] );
					$all_price   = [];
					if ( ! empty( $price_infos ) ) {
						foreach ( $price_infos as $id => $price_info ) {
							$all_price[] = $price_info['price'] ?? 0;
						};
					}
					// min() throws a ValueError on an empty array in PHP 8, which used to
					// crash tour lists for tours with no ticket configured yet.
					$price = ! empty( $all_price ) ? min( $all_price ) : 0;
				}

				return $price > 0 ? self::tax_with_price( $post_id, $price ) : 0;
			}

			//=============================//
			public static function get_total_qty( $post_id, $post_infos = [] ) {
				$total_qty = 0;
				if ( ! empty( $post_id ) && $post_id > 0 ) {
					if ( ! empty( $post_infos ) ) {
						$seat_type    = $post_infos['seat_type'] ?? 'sp';
						$ticket_infos = $post_infos['ticket_infos'] ?? [];
						$sp_infos     = $post_infos['sp_infos'] ?? [];
					} else {
						$seat_type    = ABPTT_Function::get_post_info( $post_id, 'seat_type', 'sp' );
						$ticket_infos = ABPTT_Function::get_post_info( $post_id, 'ticket_infos', [] );
						$sp_infos     = ABPTT_Function::get_post_info( $post_id, 'sp_infos', [] );
					}
					$seat_type = ABPTT_Function::on_off( 'sp' ) ? $seat_type : 'ticket';
					if ( $seat_type === 'ticket' ) {
						if ( ! empty( $ticket_infos ) ) {
							foreach ( $ticket_infos as $ticket_info ) {
								$total_qty += $ticket_info['qty'] ?? 0;
							}
						}
					} else {
						if ( ! empty( $sp_infos ) ) {
							$ids = [];
							foreach ( $sp_infos as $ticket_info ) {
								$id = $ticket_info['id'] ?? '';
								if ( ! empty( $id ) ) {
									$ids[] = $id;
								}
							}
							$total_qty = self::get_sp_ticket( $ids, true );
						}
					}
				}

				return $total_qty;
			}

			//=============Rating Function================//
			public static function get_rating_product_id( $post_id = 0 ): int {
				if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_product' ) || empty( $post_id ) ) {
					return 0;
				}
				$product_id = absint( self::get_post_info( $post_id, 'link_wc_id', $post_id ) );
				if ( empty( $product_id ) || ! wc_get_product( $product_id ) ) {
					return 0;
				}

				return $product_id;
			}

			public static function get_rating( $post_id = 0 ): array {
				$product_id = self::get_rating_product_id( $post_id );
				if ( empty( $product_id ) ) {
					return [];
				}
				$product = wc_get_product( $product_id );
				if ( ! $product ) {
					return [];
				}
				$rating = (float) $product->get_average_rating();
				$count  = (int) $product->get_review_count();

				return $rating > 0 ? [ 'rating' => $rating, 'count' => $count ] : [];
			}

			public static function get_reviews( $post_id = 0, $limit = 0 ): array {
				$product_id = self::get_rating_product_id( $post_id );
				if ( empty( $product_id ) ) {
					return [];
				}
				$args = [
					'post_type'     => 'product',
					'post_id'       => $product_id,
					'status'        => 'approve',
					'type'          => 'review',
					'orderby'       => 'comment_date_gmt',
					'order'         => 'DESC',
					'no_found_rows' => true,
					'count'         => false,
				];
				if ( $limit > 0 ) {
					$args['number'] = $limit;
				}

				return get_comments( $args );
			}

			//=============Why Book With Us================//
			public static function why_book_items_raw(): array {
				$why_book = ABPTT_Function::get_option( 'abptt_why_book' );
				$raw      = $why_book['items'] ?? [];
				$items    = [];
				if ( is_array( $raw ) ) {
					foreach ( $raw as $key => $item ) {
						if ( ! is_array( $item ) ) {
							continue;
						}
						$icon  = trim( (string) ( $item['icon'] ?? '' ) );
						$title = sanitize_text_field( $item['title'] ?? '' );
						$desc  = sanitize_text_field( $item['description'] ?? '' );
						if ( $icon === '' && $title === '' && $desc === '' ) {
							continue;
						}
						$items[ (string) $key ] = [
							'icon'        => $icon,
							'title'       => $title,
							'description' => $desc,
						];
					}
				} elseif ( is_string( $raw ) && $raw !== '' ) {
					// Backward compatibility: legacy pipe-delimited "icon | title | description" text.
					$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) );
					foreach ( $lines as $line ) {
						$parts = array_map( 'trim', explode( '|', $line ) );
						if ( ( $parts[0] ?? '' ) === '' && ( $parts[1] ?? '' ) === '' ) {
							continue;
						}
						$items[] = [
							'icon'        => $parts[0] ?? '',
							'title'       => sanitize_text_field( $parts[1] ?? '' ),
							'description' => sanitize_text_field( $parts[2] ?? '' ),
						];
					}
				}

				return $items;
			}

			public static function why_book_us_items(): array {
				return array_values( self::why_book_items_raw() );
			}

			public static function get_sp_ticket( array $ids = [], bool $count = false ) {
				if ( empty( $ids ) ) {
					return $count ? 0 : [];
				}
				$tickets = [];
				$total   = 0;
				foreach ( $ids as $id ) {
					$seat_infos = ABPTT_Ticket_SP[ $id ] ?? null;
					if ( empty( $seat_infos ) ) {
						continue;
					}
					if ( $count ) {
						$total += $seat_infos['total'] ?? 0;
					} else {
						$seat_info = $seat_infos['type'] ?? [];
						if ( ! empty( $seat_info ) ) {
							$tickets = array_merge( $tickets, array_keys( $seat_info ) );
						}
					}
				}

				return $count ? $total : array_values( array_unique( $tickets ) );
			}

			public static function get_sp_seat( $sp_id = '' ): array {
				$all_seat = [];
				if ( ! empty( $sp_id ) && $sp_id > 0 ) {
					$row     = ABPTT_Query::get_sp( $sp_id );
					$sp_info = [];
					if ( ! empty( $row ) ) {
						$sp_info = current( $row );
					}
					if ( ! empty( $sp_info ) ) {
						$layout = json_decode( $sp_info['layout_data'] ?? '', true ) ?: [];
						foreach ( $layout as $cell ) {
							if ( ( $cell['type'] ?? '' ) == 'seat' ) {
								$all_seat[] = $cell['name'] ?? '';
							}
						}
					}
				}

				return $all_seat;
			}

			//=============================//
			public static function client_data( $post_infos = [] ) {
				$data = [];
				if ( ABPTT_Function::on_off( 'client_info' ) && ! empty( $post_infos ) ) {
					$display = $post_infos['display_client_form'] ?? 'on';
					if ( $display === 'on' ) {
						$active_global = $post_infos['active_global_form'] ?? 'on';
						if ( $active_global === 'on' ) {
							$data = ABPTT_Function::get_option( 'abptt_form' );
						} else {
							$data = $post_infos['abptt_form'] ?? [];
						}
					}
				}

				return $data;
			}

			public static function additional_data( $post_infos = [] ) {
				$data = [];
				if ( ABPTT_Function::on_off( 'additional_info' ) && ! empty( $post_infos ) ) {
					$display = $post_infos['display_additional_services'] ?? 'on';
					if ( $display === 'on' ) {
						$active_global = $post_infos['active_global_additional'] ?? 'on';
						if ( $active_global === 'on' ) {
							$data = ABPTT_Function::get_option( 'abptt_additional' );
						} else {
							$data = $post_infos['additional_services'] ?? [];
						}
					}
				}

				return $data;
			}

			//=============================//
			public static function related_info_js( $_post_id = '' ): array {
				$all_info = [];
				if ( ! empty( $_post_id ) && $_post_id > 0 ) {
					if ( ! empty( ABPTT_ids ) ) {
						$active_icon = ABPTT_Function::on_off( 'post_icon' );
						foreach ( ABPTT_ids as $post_id ) {
							if ( $post_id !== $_post_id ) {
								$icon       = $active_icon ? ABPTT_Function::get_post_info( $post_id, 'post_icon' ) : '';
								$all_info[] = [ 'id' => $post_id, 'icon' => $icon, 'label' => get_the_title( $post_id ) ];
							}
						}
					}
				}

				return $all_info;
			}

			public static function option_js( $post_id, $option ): array {
				$data = [];
				// The option payload is global and does not depend on $post_id; requiring a
				// post id here used to return an empty list on the global popup screens.
				if ( ! empty( $option ) ) {
					$options = ABPTT_Function::get_option( $option );
					$options = is_array( $options ) ? $options : [];
					if ( sizeof( $options ) > 0 ) {
						foreach ( $options as $key => $item ) {
							$data[] = [ 'id' => $key, 'icon' => ( $item['icon'] ?? '' ), 'label' => ( $item['label'] ?? '' ), 'value' => ( $item['value'] ?? '' ) ];
						}
					}
				}

				return $data;
			}
		}
		new ABPTT_Function();
	}
