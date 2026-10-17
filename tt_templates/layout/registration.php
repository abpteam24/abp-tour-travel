<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptt_registration_template', function ( $post_infos, $form_data = [] ) {
		if ( ! empty( $post_infos ) ) {
			$sale_continue = $post_infos['sale_continue'] ?? 'on';
			$seat_type     = $post_infos['seat_type'] ?? 'sp';
			$seat_type     = ABPTT_Function::on_off( 'sp' ) ? $seat_type : 'ticket';
			$post_id       = absint( $post_infos['post_id'] ?? 0 );
			//echo '<pre>';print_r($upcoming_date);echo '</pre>';
			if ( $sale_continue == 'on' ) { ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
                    <input type="hidden" name="tour_date" value="<?php echo esc_attr( $form_data['start_date'] ?? $form_data['tour_date'] ?? '' ); ?>">
                    <input type="hidden" name="session_time" value="<?php echo esc_attr( $form_data['session_time'] ?? $form_data['start_time'] ?? '' ); ?>">
                    <input type="hidden" name="seat_type" value="<?php echo esc_attr( $seat_type ); ?>">
                    <input type="hidden" name="same_attendee" value="<?php echo esc_attr( $post_infos['display_single_form'] ?? 'on' ); ?>">
                    <input type="hidden" name="min_qty" value="<?php echo esc_attr( $post_infos['min_qty'] ?? 1 ); ?>">
                    <input type="hidden" name="max_qty" value="<?php echo esc_attr( $post_infos['max_qty'] ?? '' ); ?>" data-msg="<?php echo esc_attr__( 'You can buy max ticket :', 'abp-tour-travel' ) . ' ' . esc_attr( ( $post_infos['max_qty'] ?? '' ) ); ?>">
					<?php wp_nonce_field( 'abptt_registration_nonce' );
						do_action( 'abptt_admin_order', $post_id ); ?>
                    <div class="booking_area">
                        <div class="ticket_left">
                            <div class="ticket_content">
								<?php if ( $seat_type === 'ticket' ) {
									do_action( 'abptt_ticket_type', $post_infos, $form_data );
								} else {
									do_action( 'abptt_sp_type', $post_infos, $form_data );
								} ?>
                            </div>

                        </div>
                        <div class="ticket_right">
	                        <?php do_action( 'abptt_additional', $post_infos ); ?>
	                        <?php do_action( 'abptt_client_form', $post_infos ); ?>
							<?php								if ( $seat_type === 'sp' ) {									?>
                                    <div class="seat_selection">
                                        <div class="_section_15_xs">
                                            <table class="abp">
                                                <thead>
                                                <tr>
                                                    <th><?php esc_html_e( 'Seat', 'abp-tour-travel' ); ?></th>
                                                    <th><?php esc_html_e( 'Price', 'abp-tour-travel' ); ?></th>
                                                    <th class="_text_center"><?php esc_html_e( 'Action', 'abp-tour-travel' ); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody class="insert_item ">
                                                </tbody>
                                                <tfoot>
                                                <tr class="_fs_h5">
                                                    <th><?php esc_html_e( 'Sub-Total', 'abp-tour-travel' ); ?></th>
                                                    <th class="_color_theme sub_total"></th>
                                                    <th></th>
                                                </tr>
                                                </tfoot>
                                            </table>
                                            <div class="abp_hidden">
                                                <table class="abp">
                                                    <tbody class="hidden_content">
                                                    <tr class="delete_area">
                                                        <th class="seat_name"></th>
                                                        <th class="seat_price"></th>
                                                        <th>
                                                            <div class="_all_center"><?php ABPTT_Layout::button_delete( 'seat_remove' ); ?></div>
                                                        </th>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
									<?php
								}
								do_action( 'abptt_total_price', $post_infos, $form_data );
							?>
                        </div>
                    </div>
                </form>
				<?php
			} else {
				ABPTT_Layout::layout_warning_info( 'sale_close_msg' );
			}
		}
		?>
		<?php
	}, 10, 3 );