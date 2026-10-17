<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTT_Hooks' ) ) {
		class ABPTT_Hooks {
			public function __construct() {
				add_action( 'abptt_load_details_template', [ $this, 'details_template' ] );
				add_action( 'abptt_search_form', [ $this, 'search_form' ], 10, 2 );
				add_action( 'abptt_post_filter', [ $this, 'post_filter' ], 10, 2 );
				add_action( 'abptt_ticket_type', [ $this, 'ticket_type' ], 10, 3 );
				add_action( 'abptt_sp_type', [ $this, 'sp_type' ], 10, 3 );
				add_action( 'abptt_registration', [ $this, 'registration' ], 10, 2 );
				add_action( 'abptt_additional', [ $this, 'additional' ], 10, 2 );
				add_action( 'abptt_client_form', [ $this, 'client_form' ], 10, 2 );
				add_action( 'abptt_total_price', [ $this, 'total_price' ], 10, 2 );
				add_action( 'abptt_pagination', [ $this, 'pagination' ] );
				add_action( 'abptt_display_cart_item', [ $this, 'display_cart_item' ] );
				add_action( 'abptt_faq', [ $this, 'faq' ], 10, 2 );
				add_action( 'abptt_review_section', [ $this, 'review_section' ], 10, 2 );
				add_action( 'abptt_why_book_us', [ $this, 'why_book_us' ], 10, 2 );
				add_action( 'abptt_term_condition', [ $this, 'term_condition' ], 10, 2 );
				add_action( 'abptt_places_you_see', [ $this, 'places_you_see' ], 10, 3 );
				add_action( 'abptt_related_item', [ $this, 'related_item' ], 10, 2 );
				add_action( 'abptt_slider', [ $this, 'slider' ], 10, 3 );
				add_action( 'abptt_slider_popup', [ $this, 'slider_popup' ], 10, 3 );
				add_action( 'abptt_event_schedule_list', [ $this, 'event_schedule_list' ], 10, 6 );
				add_action( 'abptt_map', [ $this, 'map' ], 10, 3 );
				add_action( 'abptt_timeline', [ $this, 'timeline' ], 10, 3 );
				add_action( 'abptt_guide', [ $this, 'guide' ], 10, 3 );
			}
			public function details_template( $post_id ): void {
				require_once ABPTT_Function::details_template_path( $post_id );
				$template_name = sanitize_key( ABPTT_Function::get_post_info( $post_id, 'abptt_template', 'default' ) );
				$template_name = in_array( $template_name, [ 'default', 'light', 'modern' ], true ) ? $template_name : 'default';
				do_action( 'abptt_details_' . $template_name . '_template', $post_id );
			}
			public function search_form( $post_infos = [] ): void {
				include_once ABPTT_Function::template_path( 'layout/search_form.php' );
				do_action( 'abptt_search_form_template', $post_infos );
			}
			public function post_filter( $params = [] ): void {
				include_once ABPTT_Function::template_path( 'layout/post_filter.php' );
				do_action( 'abptt_post_filter_template', $params );
			}
			public function ticket_type( $post_infos, $form_data = [] ): void {
				include_once ABPTT_Function::template_path( 'layout/ticket_type.php' );
				do_action( 'abptt_ticket_type_template', $post_infos, $form_data );
			}
			public function sp_type( $post_infos, $form_data = [] ): void {
				include_once ABPTT_Function::template_path( 'layout/sp_type.php' );
				do_action( 'abptt_sp_type_template', $post_infos, $form_data );
			}
			public function registration( $post_infos = [], $form_data = [] ): void {
				include_once ABPTT_Function::template_path( 'layout/registration.php' );
				do_action( 'abptt_registration_template', $post_infos, $form_data );
			}
			public function additional( $post_infos = [] ): void {
				include_once ABPTT_Function::template_path( 'layout/additional_services.php' );
				do_action( 'abptt_additional_template', $post_infos );
			}
			public function client_form( $post_infos = [] ): void {
				include_once ABPTT_Function::template_path( 'layout/client_form.php' );
				do_action( 'abptt_client_form_template', $post_infos );
			}
			public function total_price( $post_infos = [], $form_data = [] ): void {
				include_once ABPTT_Function::template_path( 'layout/total_price.php' );
				do_action( 'abptt_total_price_template', $post_infos, $form_data );
			}
			public function pagination( $args ): void {
				include_once ABPTT_Function::template_path( 'layout/pagination.php' );
				do_action( 'abptt_pagination_template', $args );
			}
			public function display_cart_item( $cart_item = [] ): void {
				include_once ABPTT_Function::template_path( 'layout/display_cart_item.php' );
				do_action( 'abptt_display_cart_item_template', $cart_item );
			}
			public function faq( $post_infos = [], $type = '' ): void {
				include_once ABPTT_Function::template_path( 'layout/faq.php' );
				do_action( 'abptt_faq_template', $post_infos, $type );
			}
			public function review_section( $post_infos = [], $type = '' ): void {
				include_once ABPTT_Function::template_path( 'layout/review_section.php' );
				do_action( 'abptt_review_section_template', $post_infos, $type );
			}
			public function why_book_us( $post_infos = [], $type = '' ): void {
				include_once ABPTT_Function::template_path( 'layout/why_book_us.php' );
				do_action( 'abptt_why_book_us_template', $post_infos, $type );
			}
			public function term_condition( $post_infos = [], $type = '' ): void {
				include_once ABPTT_Function::template_path( 'layout/term_condition.php' );
				do_action( 'abptt_term_condition_template', $post_infos, $type );
			}
			public function places_you_see( $post_infos = [], $post_id = 0, $type = '' ): void {
				if ( empty( $post_id ) || $post_id <= 0 || get_post_type( $post_id ) !== ABPTT_Function::get_cpt() ) {
					return;
				}
				include_once ABPTT_Function::template_path( 'layout/places_you_see.php' );
				do_action( 'abptt_places_you_see_template', $post_infos, $type );
			}
			public function related_item( $related_item = '', int $current_post_id = 0 ): void {
				include_once ABPTT_Function::template_path( 'layout/related_item.php' );
				do_action( 'abptt_related_item_template', $related_item, $current_post_id );
			}
			public function slider( $img_ids = '', $params = [] ): void {
				if ( ! empty( $img_ids ) ) {
					$img_ids      = explode( ',', $img_ids );
					$style        = $params['slider_style'] ?? '';
					$image_column = $params['column'] ?? '';
					$abptt_slider = ABPTT_Function::get_option( 'abptt_slider' );
					//echo '<pre>';print_r($abptt_slider);echo '</pre>';
					if ( ! empty( $image_column ) ) {
						$abptt_slider['image_column'] = $image_column;
						$abptt_slider['show_item']    = ( $params['show'] ?? null ) ?: $image_column * 3;
					}
					if ( ! empty( $style ) ) {
						$slider_style = $style == 'gallery' ? 'gallery' : 'slider';
					} else {
						$slider_style = ( $abptt_slider['slider_style'] ?? null ) ?: 'slider';
					}
					include_once ABPTT_Function::template_path( 'layout/' . $slider_style . '.php' );
					do_action( 'abptt_' . $slider_style . '_template', $img_ids, $abptt_slider );
				}
			}
			public function slider_popup( $abptt_slider, $img_ids, $popup_id = '#abptt_slider_' ): void {
				include_once ABPTT_Function::template_path( 'layout/slider_popup.php' );
				do_action( 'abptt_slider_popup_template', $abptt_slider, $img_ids, $popup_id );
			}
			public function map( $post_infos = [], $post_id = 0, $style = 'default' ): void {
				if ( empty( $post_id ) || $post_id <= 0 || get_post_type( $post_id ) !== ABPTT_Function::get_cpt() ) {
					return;
				}
				if ( ! ABPTT_Function::on_off( 'google_map' ) || ! ABPTT_Function::on_off( 'location' ) ) {
					return;
				}
				$google_map_key = ABPTT_Function::get_options( 'abptt_configuration', 'google_map_key', '' );
				$map_mode       = empty( $google_map_key ) ? 'iframe' : 'js';
				$locations      = ABPTT_Function::get_post_info( $post_id, 'abptt_location' );
				$map_locations  = [];
				if ( ! empty( $locations ) ) {
					$loc_values = array_filter( array_map( 'trim', explode( ',', $locations ) ) );
					foreach ( $loc_values as $loc ) {
						$map_data      = ABPTT_Location[ $loc ]['map'] ?? [];
						$map_term_meta = is_numeric( $loc ) ? get_term_meta( (int) $loc, '_abptt_map', true ) : [];
						$map_data      = is_array( $map_term_meta ) && ! empty( $map_term_meta ) ? $map_term_meta : $map_data;
						if ( ! empty( $map_data['lat'] ) && ! empty( $map_data['lng'] ) ) {
							$map_locations[] = [
								'id'      => $loc,
								'label'   => ABPTT_Location[ $loc ]['label'] ?? $loc,
								'address' => $map_data['address'] ?? ABPTT_Location[ $loc ]['description'] ?? '',
								'lat'     => $map_data['lat'],
								'lng'     => $map_data['lng'],
								'place'   => $map_data['place'] ?? '',
							];
						}
					}
				}
				if ( empty( $map_locations ) ) {
					return;
				}
				$style = sanitize_key( $style );
				$style = in_array( $style, [ 'default', 'light', 'modern' ], true ) ? $style : 'default';
				include_once ABPTT_Function::template_path( 'map/' . $style . '.php' );
				do_action( 'abptt_map_' . $style . '_template', $map_locations, $post_id, $map_mode );
			}
			public function timeline( $post_infos = [], $post_id = 0, $style = 'default' ): void {
				if ( empty( $post_id ) || $post_id <= 0 || get_post_type( $post_id ) !== ABPTT_Function::get_cpt() ) {
					return;
				}
				if ( ! ABPTT_Function::on_off( 'timeline' ) ) {
					return;
				}
				if ( ( $post_infos['display_timeline'] ?? 'on' ) === 'off' ) {
					return;
				}
				$timeline_items = $post_infos['abptt_timeline'] ?? [];
				$timeline_items = is_array( $timeline_items ) ? $timeline_items : [];
				$timeline_items = array_values( array_filter( $timeline_items, static function ( $item ) {
					return ! empty( $item['title'] );
				} ) );
				if ( empty( $timeline_items ) ) {
					return;
				}
				$style = sanitize_key( $style );
				$style = in_array( $style, [ 'default', 'light', 'modern' ], true ) ? $style : 'default';
				include_once ABPTT_Function::template_path( 'timeline/' . $style . '.php' );
				do_action( 'abptt_timeline_' . $style . '_template', $timeline_items, $post_id );
			}
			public function guide( $post_infos = [], $post_id = 0, $style = 'default' ): void {
				if ( empty( $post_id ) || $post_id <= 0 || get_post_type( $post_id ) !== ABPTT_Function::get_cpt() ) {
					return;
				}
				if ( ! ABPTT_Function::on_off( 'guide' ) ) {
					return;
				}
				if ( ( $post_infos['display_guide'] ?? 'on' ) === 'off' ) {
					return;
				}
				$guides = ABPTT_Function::guide_items( $post_id );
				if ( empty( $guides ) ) {
					return;
				}
				$style = sanitize_key( $style );
				$style = in_array( $style, [ 'default', 'light', 'modern' ], true ) ? $style : 'default';
				include_once ABPTT_Function::template_path( 'guide/' . $style . '.php' );
				do_action( 'abptt_guide_' . $style . '_template', $guides, $post_id );
			}
			public function event_schedule_list( int $post_id, array $dates, array $time_infos, string $selected_date = '', string $selected_time = '', string $layout = 'dropdown' ): void {
				$items = ABPTT_Function::event_schedule_items( $post_id, $dates, $time_infos );
				if ( ! ABPTT_Function::on_off( 'tour_date_list' ) || count( $items ) < 2 ) {
					return;
				}
				$is_scroll_layout = 'scroll' === $layout;
				$dropdown_id      = 'abptt-schedule-dropdown-' . $post_id;
				?>
				<?php if ( ! $is_scroll_layout ) : ?>
                    <div class="abptt_schedule_picker">
                    <button class="_btn_theme_xs abptt_schedule_toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $dropdown_id ); ?>">
                        <span class="fas fa-calendar-alt" aria-hidden="true"></span>
						<?php esc_html_e( 'View More Date', 'abp-tour-travel' ); ?>
                    </button>
                    <div id="<?php echo esc_attr( $dropdown_id ); ?>" class="abptt_schedule_dropdown" hidden>
				<?php endif; ?>
                <div class="abptt_schedule_list<?php echo esc_attr( $is_scroll_layout ? ' abptt_schedule_list--scroll' : '' ); ?>">
                    <div class="abptt_schedule_list_heading">
                        <span class="fas fa-calendar-alt" aria-hidden="true"></span>
                        <strong><?php esc_html_e( 'Available Schedule', 'abp-tour-travel' ); ?></strong>
                    </div>
                    <div class="abptt_schedule_list_items">
						<?php foreach ( $items as $item ) : ?>
                            <div class="abptt_event_schedule_day">
								<?php
									$all_times = $item['times'] ?? [];
									if ( ! empty( $all_times ) ) {
										foreach ( $all_times as $time ) :
											$is_active = $item['date'] === $selected_date && $time === $selected_time;
											?>
                                            <a class="abp <?php echo esc_attr( $is_active ? 'is-active' : '' ); ?>" href="<?php echo esc_url( ABPTT_Function::event_schedule_url( $post_id, $item['date'], $time ) ); ?>">
                                                <time datetime="<?php echo esc_attr( $item['date'] . 'T' . $time ); ?>"><?php echo esc_html( ABPTT_Function::date_format( $item['date'] . ' ' . $time ) ); ?></time>
                                            </a>
										<?php endforeach;
									} else {
										$is_active = $item['date'] === $selected_date;
										?>
                                        <a class="abp <?php echo esc_attr( $is_active ? 'is-active' : '' ); ?>" href="<?php echo esc_url( ABPTT_Function::event_schedule_url( $post_id, $item['date'] ) ); ?>">
                                            <time datetime="<?php echo esc_attr( $item['date'] . 'T' ); ?>"><?php echo esc_html( ABPTT_Function::date_format( $item['date'] ) ); ?></time>
                                        </a>
										<?php
									} ?>
                            </div>
						<?php endforeach; ?>
                    </div>
                </div>
				<?php if ( ! $is_scroll_layout ) : ?>
                    </div>
                    </div>
				<?php endif; ?>
				<?php
			}
		}
		new ABPTT_Hooks();
	}
