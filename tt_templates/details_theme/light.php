<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptt_details_light_template', function ( $post_id, $form_data = [] ) {
		if ( ! empty( $post_id ) && $post_id > 0 && get_post_type( $post_id ) == ABPTT_Function::get_cpt() && ( get_post_status( $post_id ) == 'publish' || is_admin() ) ) {
			$post_infos        = ABPTT_Function::get_all_meta( $post_id );
			$form_data['form'] = 'inline';
			$content           = get_post_field( 'post_content', $post_id );
			$all_dates         = ABPTT_Function::date( $post_id );
			$time_infos        = $post_infos['time_infos'] ?? [];
			[ $all_dates, $start_date, $all_times, $start_time ] = ABPTT_Function::event_schedule_selection( $all_dates, $time_infos );
			$show_date_list            = ABPTT_Function::on_off( 'tour_date_list' ) && count( ABPTT_Function::event_schedule_items( $post_id, $all_dates, $time_infos ) ) > 1;
			$upcoming_date             = ! empty( $start_date ) && ! empty( $start_time ) ? gmdate( 'Y-m-d H:i', strtotime( $start_date . ' ' . $start_time ) ) : $start_date;
			$form_data['post_id']      = $post_id;
			$form_data['all_dates']    = $all_dates;
			$form_data['all_times']    = $all_times;
			$form_data['start_date']   = $start_date;
			$form_data['start_time']   = $start_time;
			$form_data['session_time'] = $start_time;
			$form_data['tour_date']   = $upcoming_date;
			?>
            <div id="abptt_area" class="abptt_area abptt_light_details">
                <div class="abp_container">
                    <div class="abp_row">
                        <div class="_f_equal_f_wrap_gap_section_15 abptt_light_lead">
                            <div class="abptt_details_column abptt_light_lead_body">
                                <div class="abptt_light_lead_kicker">
									<?php ABPTT_Static::icon_svg( 'location_1' ); ?>
                                    <span><?php esc_html_e( 'Tour Highlights', 'abp-tour-travel' ); ?></span>
                                </div>
                                <h1 class="abptt_details_title"><?php ABPTT_Layout::title( $post_infos ); ?></h1>
								<?php ABPTT_Layout::sub_title( $post_infos ); ?>
                                <div class="abptt_light_lead_meta _gap_xs_mar_t_xs">
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
								<?php if ( ! empty( $upcoming_date ) ) { ?>
                                    <div class="abptt_light_lead_date">
										<?php ABPTT_Static::icon_svg( 'date_1' ); ?>
                                        <span><?php echo esc_html( ABPTT_Function::date_format( $upcoming_date ) ); ?></span>
                                    </div>
								<?php } ?>
								<?php if ( ( $post_infos['sale_continue'] ?? 'on' ) === 'on' ) { ?>
                                    <span class="abptt_light_badge_live">
										<i class="fas fa-check-circle" aria-hidden="true"></i>
										<?php esc_html_e( 'Booking Open', 'abp-tour-travel' ); ?>
                                    </span>
								<?php } else { ?>
                                    <span class="abptt_light_badge_off">
										<i class="fas fa-times-circle" aria-hidden="true"></i>
										<?php esc_html_e( 'Booking Closed', 'abp-tour-travel' ); ?>
                                    </span>
								<?php } ?>
								<?php ABPTT_Layout::top_pick( $post_infos, 'top_pick light_top_pick' ); ?>
                                <div class="abptt_details_column abptt_light_main">
									<?php if ( ABPTT_Function::on_off( 'feature' ) ) {
										ABPTT_Layout::item_feature( $post_infos['post_feature'] ?? '' );
									}
										ABPTT_Layout::description( $post_infos ); ?>
                                </div>
                            </div>
                            <div class="abptt_details_column abptt_light_lead_media">
								<?php if ( ! empty( $post_infos['abptt_slider'] ) ) {
									do_action( 'abptt_slider', $post_infos['abptt_slider'], [ 'slider_style' => 'slider' ] );
								} else {
									ABPTT_Layout::image( $post_id );
								} ?>
                            </div>
                        </div>
                    </div>
					<?php if ( ! empty( $content ) ) { ?>
                        <div class="abp_row">
                            <div class="_col_12">
                                <div class="the_post_content">
									<?php echo wp_kses_post( apply_filters( 'the_content', $content ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter 'the_content'. ?>
                                </div>
                            </div>
                        </div>
					<?php } ?>
					<?php if ( $show_date_list ) { ?>
                        <div class="abp_row">
                            <div class="_col_12">
                                <div class="abptt_light_schedule_scroll">
									<?php do_action( 'abptt_event_schedule_list', $post_id, $all_dates, $time_infos, $start_date, $start_time, 'scroll' ); ?>
                                </div>
                            </div>
                        </div>
					<?php } ?>
                    <div class="abp_row">
                        <div class="_col_12">
                            <div class="abptt_light_booking_card">
                                <div class="abptt_light_booking_head">
                                    <i class="fas fa-ticket-alt" aria-hidden="true"></i>
                                    <span><?php esc_html_e( 'Book Your Ticket', 'abp-tour-travel' ); ?></span>
                                </div>
                                <div class="abptt_booking">
									<?php if ( ( $post_infos['sale_continue'] ?? 'on' ) === 'on' ) { ?>
                                        <div class="post_top_filter">
											<?php ABPTT_Layout::start_date( $all_dates, $start_date );
												ABPTT_Layout::start_time( $form_data ); ?>
                                        </div>
										<?php do_action( 'abptt_registration', $post_infos, $form_data ); ?>
									<?php } else {
										ABPTT_Layout::layout_warning_info( 'sale_close_msg' );
									} ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="abp_row">
                        <div class="abptt_details_column _fd_column_gap_xs">
							<?php do_action( 'abptt_faq', $post_infos ); ?>
							<?php if ( empty( $post_infos['abptt_slider'] ) ) { ?>
								<?php do_action( 'abptt_slider', ( $post_infos['abptt_slider'] ?? [] ) ); ?>
							<?php } ?>
                        </div>
                    </div>
					<?php do_action( 'abptt_timeline', $post_infos, $post_id, 'light' ); ?>
					<?php do_action( 'abptt_map', $post_infos, $post_id, 'light' ); ?>
					<?php do_action( 'abptt_places_you_see', $post_infos, $post_id, 'light' ); ?>
					<?php do_action( 'abptt_guide', $post_infos, $post_id, 'light' ); ?>
                    <div class="abp_row">
                        <div class="_col_12">
							<?php do_action( 'abptt_review_section', $post_infos ); ?>
							<?php do_action( 'abptt_why_book_us', $post_infos ); ?>
                        </div>
                    </div>
                    <div class="abp_row">
                        <div class="_col_12">
							<?php do_action( 'abptt_term_condition', $post_infos ); ?>
                        </div>
                    </div>
                    <div class="abp_row">
                        <div class="_col_12"> <?php do_action( 'abptt_related_item', ( $post_infos['related_item'] ?? '' ), $post_id ); ?></div>
                    </div>
                </div>
            </div>
			<?php
		}
	} );
