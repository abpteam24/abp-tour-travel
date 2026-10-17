<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptt_details_default_template', function ( $post_id, $form_data = [] ) {
		if ( ! empty( $post_id ) && $post_id > 0 && get_post_type( $post_id ) == ABPTT_Function::get_cpt() && ( get_post_status( $post_id ) == 'publish' || is_admin() ) ) {
			$post_infos           = ABPTT_Function::get_all_meta( $post_id );
			$form_data['form']    = 'inline';
			$form_data['post_id'] = $post_id;
			$content              = get_post_field( 'post_content', $post_id );
			$all_dates            = ABPTT_Function::date( $post_id );
			$time_infos           = $post_infos['time_infos'] ?? [];
			[ $all_dates, $start_date, $all_times, $start_time ] = ABPTT_Function::event_schedule_selection( $all_dates, $time_infos );
			$show_date_list = ABPTT_Function::on_off( 'tour_date_list' ) && count( ABPTT_Function::event_schedule_items( $post_id, $all_dates, $time_infos ) ) > 1;
			$upcoming_date  = '';
			if ( ! empty( $start_date ) ) {
				$upcoming_date = ! empty( $start_time ) ? gmdate( 'Y-m-d H:i', strtotime( $start_date . ' ' . $start_time ) ) : $start_date;
			}
			$form_data['all_dates']    = $all_dates;
			$form_data['all_times']    = $all_times;
			$form_data['start_date']   = $start_date;
			$form_data['start_time']   = $start_time;
			$form_data['session_time'] = $start_time;
			$form_data['tour_date']   = $upcoming_date;
			$sale_continue             = $post_infos['sale_continue'] ?? 'on';
			$is_on_sale                = ( $sale_continue === 'on' );
			?>
            <div id="abptt_area" class="abptt_area default_details_page">
                <div class="abp_container">
                    <section class="abptt_default_hero">
                        <div class="abptt_default_hero_media">
							<?php if ( ! empty( $post_infos['abptt_slider'] ) ) {
								do_action( 'abptt_slider', $post_infos['abptt_slider'], [ 'slider_style' => 'slider' ] );
							} else {
								ABPTT_Layout::image( $post_id );
							} ?>
                        </div>
                        <div class="abptt_default_hero_body">
                            <div class="abptt_default_hero_kicker">
								<?php if ( ! empty( $upcoming_date ) ) { ?>
                                    <span class="abptt_default_date_chip">
										<?php ABPTT_Static::icon_svg( 'date_1' ); ?>
										<?php echo esc_html( ABPTT_Function::date_format( $upcoming_date ) ); ?>
                                    </span>
								<?php } ?>
								<?php if ( $is_on_sale ) { ?>
                                    <span class="abptt_default_badge_live">
										<i class="fas fa-check-circle" aria-hidden="true"></i>
										<?php esc_html_e( 'Booking Open', 'abp-tour-travel' ); ?>
                                    </span>
								<?php } else { ?>
                                    <span class="abptt_default_badge_off">
										<i class="fas fa-times-circle" aria-hidden="true"></i>
										<?php esc_html_e( 'Booking Closed', 'abp-tour-travel' ); ?>
                                    </span>
								<?php } ?>
								<?php ABPTT_Layout::top_pick( $post_infos, 'top_pick default_top_pick' ); ?>
                            </div>
                            <h1 class="abptt_details_title"><?php ABPTT_Layout::title( $post_infos ); ?></h1>
							<?php ABPTT_Layout::sub_title( $post_infos, 'sub_title abptt_default_subtitle' ); ?>
                            <div class="abptt_default_hero_meta _gap_xs_mar_t_xs">
								<?php ABPTT_Layout::capacity( $post_infos );
									ABPTT_Layout::category( $post_infos );
									ABPTT_Layout::organizer( $post_infos, 'publish' );
									ABPTT_Layout::location( $post_infos );
									ABPTT_Layout::tour_duration( $post_infos );
									ABPTT_Layout::tour_night( $post_infos );
									ABPTT_Layout::max_people( $post_infos );
									ABPTT_Layout::min_age( $post_infos );
									ABPTT_Layout::start_place( $post_infos );
									ABPTT_Layout::rating( $post_infos );
								?>
                            </div>
                            <div class="abptt_default_hero_content">
								<?php if ( ABPTT_Function::on_off( 'feature' ) ) {
									ABPTT_Layout::item_feature( $post_infos['post_feature'] ?? '' );
								}
									ABPTT_Layout::description( $post_infos );
								?>
								<?php if ( $show_date_list ) { ?>
                                    <div class="abptt_default_schedule">
										<?php do_action( 'abptt_event_schedule_list', $post_id, $all_dates, $time_infos, $start_date, $start_time, 'dropdown' ); ?>
                                    </div>
								<?php } ?>
								<?php if ( ! empty( $content ) ) { ?>
                                    <div class="the_post_content abptt_default_content">
										<?php echo wp_kses_post( apply_filters( 'the_content', $content ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter 'the_content'. ?>
                                    </div>
								<?php } ?>
                            </div>
                        </div>
                    </section>
                    <section class="abptt_default_info_strip">
                        <div class="abptt_default_info_card">
                            <span class="abptt_default_info_icon"><i class="far fa-calendar-alt" aria-hidden="true"></i></span>
                            <div class="abptt_default_info_text">
                                <span class="abptt_default_info_label"><?php esc_html_e( 'Date', 'abp-tour-travel' ); ?></span>
                                <strong><?php echo ! empty( $start_date ) ? esc_html( ABPTT_Function::date_format( $start_date ) ) : esc_html__( 'TBA', 'abp-tour-travel' ); ?></strong>
                            </div>
                        </div>
                        <div class="abptt_default_info_card">
                            <span class="abptt_default_info_icon"><i class="far fa-clock" aria-hidden="true"></i></span>
                            <div class="abptt_default_info_text">
                                <span class="abptt_default_info_label"><?php esc_html_e( 'Time', 'abp-tour-travel' ); ?></span>
                                <strong><?php echo ! empty( $start_time ) ? esc_html( ABPTT_Function::date_format( $start_date . ' ' . $start_time ) ) : esc_html__( 'TBA', 'abp-tour-travel' ); ?></strong>
                            </div>
                        </div>
						<?php
							$location_enabled = ABPTT_Function::on_off( 'location' );
							$display_location = ( $post_infos['display_location'] ?? ABPTT_Function::get_post_info( $post_id, 'display_location', 'on' ) ) === 'on';
							if ( $location_enabled && $display_location ) {
								?>
                                <div class="abptt_default_info_card">
                                    <span class="abptt_default_info_icon"><i class="fas fa-map-marker-alt" aria-hidden="true"></i></span>
                                    <div class="abptt_default_info_text">
                                        <span class="abptt_default_info_label"><?php echo esc_html( ABPTT_Function::location_label() ); ?></span>
                                        <strong><?php
												$locations = ABPTT_Function::get_post_info( $post_id, 'abptt_location' );
												if ( ! empty( $locations ) ) {
													$loc_values = array_filter( array_map( 'trim', explode( ',', $locations ) ) );
													$labels     = [];
													foreach ( $loc_values as $loc ) {
														$labels[] = ABPTT_Function::location_value( $loc );
													}
													echo esc_html( implode( ', ', $labels ) );
												} else {
													esc_html_e( 'Online / TBA', 'abp-tour-travel' );
												}
											?></strong>
                                    </div>
                                </div>
							<?php } ?>
						<?php
							$capacity_enabled = ABPTT_Function::on_off( 'display_capacity' );
							$display_capacity = ( $post_infos['display_capacity'] ?? ABPTT_Function::get_post_info( $post_id, 'display_capacity', 'on' ) ) === 'on';
							if ( $capacity_enabled && $display_capacity ) {
								?>
                                <div class="abptt_default_info_card">
                                    <span class="abptt_default_info_icon"><i class="fas fa-users" aria-hidden="true"></i></span>
                                    <div class="abptt_default_info_text">
                                        <span class="abptt_default_info_label"><?php esc_html_e( 'Guests', 'abp-tour-travel' ); ?></span>
                                        <strong><?php echo esc_html( ABPTT_Function::get_total_qty( $post_id, $post_infos ) ?: esc_html__( 'Open', 'abp-tour-travel' ) ); ?></strong>
                                    </div>
                                </div>
							<?php } ?>
                    </section>
                    <div class="abptt_default_body">
                        <div class="abptt_default_main">
							<?php do_action( 'abptt_timeline', $post_infos, $post_id, 'default' ); ?>
							<?php do_action( 'abptt_map', $post_infos, $post_id, 'default' ); ?>
							<?php do_action( 'abptt_places_you_see', $post_infos, $post_id, 'default' ); ?>
							<?php do_action( 'abptt_guide', $post_infos, $post_id, 'default' ); ?>
							<?php do_action( 'abptt_faq', $post_infos ); ?>
							<?php do_action( 'abptt_review_section', $post_infos ); ?>
							<?php do_action( 'abptt_why_book_us', $post_infos ); ?>
							<?php do_action( 'abptt_term_condition', $post_infos ); ?>
                        </div>
                        <aside class="abptt_default_sidebar">
                            <div class="abptt_default_booking_sticky">
                                <div class="abptt_booking">
									<?php
										if ( $is_on_sale ) {
											?>
                                            <div class="abptt_default_booking_head">
                                                <i class="fas fa-ticket-alt" aria-hidden="true"></i>
                                                <span><?php esc_html_e( 'Get Tickets', 'abp-tour-travel' ); ?></span>
                                            </div>
                                            <div class="post_top_filter">
												<?php ABPTT_Layout::start_date( $all_dates, $start_date );
													ABPTT_Layout::start_time( $form_data ); ?>
                                            </div>
											<?php
											do_action( 'abptt_registration', $post_infos, $form_data );
										} else {
											ABPTT_Layout::layout_warning_info( 'sale_close_msg' );
										}
									?>
                                </div>
                            </div>
                        </aside>
                    </div>
                    <div class="abp_row">
                        <div class="_col_12"> <?php do_action( 'abptt_related_item', ( $post_infos['related_item'] ?? '' ), $post_id ); ?></div>
                    </div>
                </div>
            </div>
			<?php
		}
	}, 10, 2 );
