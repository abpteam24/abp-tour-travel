<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
add_action( 'abptt_details_modern_template', function ( $post_id, $form_data = [] ) {
	if ( ! $post_id || get_post_type( $post_id ) !== ABPTT_Function::get_cpt() || ( get_post_status( $post_id ) !== 'publish' && ! is_admin() ) ) {
		return;
	}
	$post_infos = ABPTT_Function::get_all_meta( $post_id );
	$all_dates  = ABPTT_Function::date( $post_id );
	$time_infos = $post_infos['time_infos'] ?? [];
	[ $all_dates, $start_date, $all_times, $start_time ] = ABPTT_Function::event_schedule_selection( $all_dates, $time_infos );
	$show_date_list = ABPTT_Function::on_off( 'tour_date_list' ) && count( ABPTT_Function::event_schedule_items( $post_id, $all_dates, $time_infos ) ) > 1;
	$content = get_post_field( 'post_content', $post_id );
	$sale_continue = $post_infos['sale_continue'] ?? 'on';
	$form_data  = array_merge( $form_data, [
		'form' => 'inline',
		'post_id' => $post_id,
		'all_dates' => $all_dates,
		'all_times' => $all_times,
		'start_date' => $start_date,
		'start_time' => $start_time,
		'tour_date' => $start_date,
		'session_time' => $start_time,
	] );
	?>
	<div id="abptt_area" class="abptt_area abptt_modern_details">
		<div class="abptt_modern_banner">
			<div class="abp_container">
				<div class="abptt_modern_split">
					<div class="abptt_modern_sticky_media">
						<?php if ( ! empty( $post_infos['abptt_slider'] ) ) {
							do_action( 'abptt_slider', $post_infos['abptt_slider'], [ 'slider_style' => 'slider' ] );
						} else {
							ABPTT_Layout::image( $post_id );
						} ?>
					</div>
					<div class="abptt_modern_copy">
						<div class="abptt_modern_kicker">
							<?php if ( ! empty( $form_data['tour_date'] ) ) { ?>
								<span class="abptt_modern_kicker_date"><?php ABPTT_Static::icon_svg( 'date_1' ); ?><?php echo esc_html( ABPTT_Function::date_format( $form_data['tour_date'] ) ); ?></span>
							<?php } ?>
							<?php if ( $sale_continue === 'on' ) { ?>
								<span class="abptt_modern_kicker_live"><?php esc_html_e( 'Booking Open', 'abp-tour-travel' ); ?></span>
							<?php } ?>
							<?php ABPTT_Layout::top_pick( $post_infos, 'top_pick modern_top_pick' ); ?>
						</div>
						<h1 class="abptt_details_title"><?php ABPTT_Layout::title( $post_infos ); ?></h1>
						<?php ABPTT_Layout::sub_title( $post_infos, 'sub_title abptt_modern_subtitle' ); ?>
						<div class="abptt_modern_meta _gap_xs_mar_t_xs">
							<?php ABPTT_Layout::capacity( $post_infos ); ABPTT_Layout::category( $post_infos ); ABPTT_Layout::organizer( $post_infos, 'publish' ); ABPTT_Layout::location( $post_infos ); ABPTT_Layout::tour_duration( $post_infos ); ABPTT_Layout::tour_night( $post_infos ); ABPTT_Layout::max_people( $post_infos ); ABPTT_Layout::min_age( $post_infos ); ABPTT_Layout::start_place( $post_infos ); ABPTT_Layout::rating( $post_infos ); ?>
						</div>
						<?php if ( ABPTT_Function::on_off( 'feature' ) ) { ABPTT_Layout::item_feature( $post_infos['post_feature'] ?? '' ); } ?>
						<?php ABPTT_Layout::description( $post_infos, 'abptt_modern_description' ); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="abp_container">
			<div class="abptt_modern_booking_card abptt_booking">
				<?php if ( $show_date_list ) { ?>
					<div class="abptt_modern_schedule">
						<?php do_action( 'abptt_event_schedule_list', $post_id, $all_dates, $time_infos, $start_date, $start_time, 'scroll' ); ?>
					</div>
				<?php } ?>
				<?php if ( $sale_continue === 'on' ) { ?>
					<div class="post_top_filter"><?php ABPTT_Layout::start_date( $all_dates, $start_date ); ABPTT_Layout::start_time( $form_data ); ?></div>
					<?php do_action( 'abptt_registration', $post_infos, $form_data ); ?>
				<?php } else { ABPTT_Layout::layout_warning_info( 'sale_close_msg' ); } ?>
			</div>
			<?php do_action( 'abptt_map', $post_infos, $post_id, 'modern' ); ?>
			<?php do_action( 'abptt_timeline', $post_infos, $post_id, 'modern' ); ?>
			<?php do_action( 'abptt_places_you_see', $post_infos, $post_id, 'modern' ); ?>
			<?php do_action( 'abptt_guide', $post_infos, $post_id, 'modern' ); ?>
			<?php do_action( 'abptt_review_section', $post_infos ); ?>
			<?php do_action( 'abptt_why_book_us', $post_infos ); ?>
			<?php if ( ! empty( $content ) ) { ?>
				<div class="abp_row">
					<div class="_col_12"><div class="the_post_content"><?php echo wp_kses_post( apply_filters( 'the_content', $content ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter 'the_content'. ?></div></div>
				</div>
			<?php } ?>
			<div class="abp_row">
				<div class="_f_equal_f_wrap_gap_w_full">
					<div class="abptt_details_column">

						<?php do_action( 'abptt_faq', $post_infos ); ?>
					</div>
					<div class="abptt_details_column">
						<?php do_action( 'abptt_term_condition', $post_infos ); ?>
					</div>
				</div>
			</div>
			<?php if ( empty( $post_infos['abptt_slider'] ) ) { ?>
				<div class="abp_row"><div class="_col_12"><?php do_action( 'abptt_slider', $post_infos['abptt_slider'] ?? [] ); ?></div></div>
			<?php } ?>
			<div class="abp_row"><div class="_col_12"><?php do_action( 'abptt_related_item', $post_infos['related_item'] ?? '', $post_id ); ?></div></div>
		</div>
	</div>
	<?php
}, 10, 2 );
