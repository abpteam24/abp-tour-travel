<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTT_Dashboard' ) ) {
		class ABPTT_Dashboard {
			public function __construct() {
				add_action( 'abptt_load_dashboard', array( $this, 'load_dashboard' ) );
				add_action( 'wp_ajax_abptt_journey_popup', array( $this, 'journey_popup' ) );
			}
			public function load_dashboard( $abptt_info ): void {
				$label = ABPTT_Function::label();
				$kpi   = self::kpi_data();
				?>
                <div class="abptt_dashboard">
					<?php $this->hero( $label ); ?>
					<?php $this->kpi_cards( $abptt_info, $kpi ); ?>
                    <div class="dash_columns">
                        <div class="dash_main">
							<?php $this->today_events( $kpi['upcoming'] ?? array() ); ?>
							<?php $this->orders_summary(); ?>
                        </div>
                        <aside class="dash_side">
							<?php do_action( 'abptt_dashboard_sidebar' ); ?>
							<?php $this->quick_actions(); ?>
							<?php $this->system_status( $abptt_info, $label ); ?>
							<?php $this->content_breakdown( $abptt_info ); ?>
                        </aside>
                    </div>
                </div>
				<?php
			}
			//=============================//
			private function hero( $label ): void {
				$user = wp_get_current_user();
				$name = $user && ! empty( $user->display_name ) ? $user->display_name : __( 'Admin', 'abp-tour-travel' );
				?>
                <div class="dash_hero">
                    <div class="dash_hero_main">
                        <div class="dash_hero_text">
                            <span class="dash_hero_date"><i class="far fa-calendar-alt"></i> <?php echo esc_html( ABPTT_Function::date_format( current_time( 'Y-m-d' ) ) ); ?></span>
                            <h2>
								<?php
									/* translators: %s: current user display name. */
									printf( esc_html__( 'Welcome back, %s!', 'abp-tour-travel' ), esc_html( $name ) );
								?>
                            </h2>
                            <p>
								<?php
									/* translators: %s: tour label. */
									printf( esc_html__( 'Here is what is happening with your %s business today.', 'abp-tour-travel' ), esc_html( $label ) );
								?>
                            </p>
                        </div>
                    </div>
                    <div class="dash_hero_side">
                        <div class="dash_hero_actions">
                            <a class="dash_btn dash_btn_solid" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . ABPTT_Function::get_cpt() ) ); ?>">
                                <i class="fas fa-plus"></i> <?php echo esc_html( $label ); ?>
                            </a>
                            <a class="dash_btn dash_btn_ghost" href="<?php echo esc_url( ABPTT_Function::build_url( 'orders' ) ); ?>">
                                <i class="fas fa-file-invoice"></i> <?php esc_html_e( 'Orders', 'abp-tour-travel' ); ?>
                            </a>
                        </div>
                    </div>
                </div>
				<?php
			}
			private function kpi_cards( $abptt_info, $kpi ): void {
				$currency = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';
				$revenue  = $currency . number_format_i18n( (float) ( $kpi['revenue'] ?? 0 ), 2 );
				$cards    = array(
					array( 'theme', 'fas fa-calendar-days', (string) ( $abptt_info['total_post'] ?? 0 ), __( 'Total Tours', 'abp-tour-travel' ), ABPTT_Function::build_url( 'posts' ) ),
					array( 'navy', 'fas fa-file-invoice', (string) ( $abptt_info['total_order'] ?? 0 ), __( 'Total Orders', 'abp-tour-travel' ), ABPTT_Function::build_url( 'orders' ) ),
					array( 'success', 'fas fa-ticket-alt', (string) ( $kpi['tickets'] ?? 0 ), __( 'Tickets Sold', 'abp-tour-travel' ), ABPTT_Function::build_url( 'orders' ) ),
					array( 'purple', 'fas fa-money-bill-wave', $revenue, __( 'Total Revenue', 'abp-tour-travel' ), ABPTT_Function::build_url( 'orders' ) ),
					array( 'warning', 'fas fa-calendar-day', (string) ( $kpi['today'] ?? 0 ), __( 'Booked Today', 'abp-tour-travel' ), ABPTT_Function::build_url( 'orders' ) ),
				);
				?>
                <div class="dash_kpi_grid">
					<?php foreach ( $cards as $card ) { ?>
                        <a class="dash_kpi" href="<?php echo esc_url( $card[4] ); ?>">
                            <span class="dash_kpi_icon <?php echo esc_attr( $card[0] ); ?>"><i class="<?php echo esc_attr( $card[1] ); ?>"></i></span>
                            <span class="dash_kpi_body">
                                <span class="dash_kpi_value"><?php echo esc_html( $card[2] ); ?></span>
                                <span class="dash_kpi_label"><?php echo esc_html( $card[3] ); ?></span>
                            </span>
                        </a>
					<?php } ?>
                </div>
				<?php
			}
			private function today_events( $upcoming ): void {
				?>
                <div class="dash_card">
                    <div class="dash_card_head">
                        <h4 class="abp"><i class="fas fa-calendar-day _color_theme"></i> <?php esc_html_e( "Today's Sessions", 'abp-tour-travel' ); ?></h4>
                        <a class="_btn_light_theme_xs" href="<?php echo esc_url( ABPTT_Function::build_url( 'orders' ) ); ?>"><?php esc_html_e( 'View All', 'abp-tour-travel' ); ?> <i class="fas fa-angle-right"></i></a>
                    </div>
                    <div class="dash_card_body dash_card_body_plain">
						<?php if ( ! empty( $upcoming ) ) { ?>
                            <div class="dash_rows">
								<?php foreach ( $upcoming as $row ) {
									$post_id    = (int) ( $row['post_id'] ?? 0 );
									$start_time = $row['start_time'] ?? '';
									$started    = ! empty( $row['started'] );
									$sold       = (int) ( $row['sold'] ?? 0 );
									$available  = (int) ( $row['available'] ?? 0 );
									$total      = (int) ( $row['total'] ?? 0 );
									$reserve    = (int) ( $row['reserve'] ?? 0 );
									?>
                                    <div class="dash_row dash_journey_row" data-journey data-post="<?php echo esc_attr( $post_id ); ?>" data-start="<?php echo esc_attr( $start_time ); ?>" data-direction="up" title="<?php esc_attr_e( 'Click to view session bookings', 'abp-tour-travel' ); ?>">
                                        <div class="dash_row_main">
                                            <h6 class="abp_gap_xs"><?php ABPTT_Layout::title( array( 'post_id' => $post_id ) ); ?></h6>
                                            <small>
                                                <i class="far fa-calendar-alt"></i>
												<?php echo esc_html( ABPTT_Function::date_format( $start_time ) ); ?>
												<?php if ( $started ) { ?>
                                                    <span class="dash_trip_state dash_trip_state_started"><i class="fas fa-play-circle"></i> <?php esc_html_e( 'Session Started', 'abp-tour-travel' ); ?></span>
												<?php } ?>
                                            </small>
                                        </div>
                                        <div class="dash_row_meta dash_journey_meta">
                                            <span class="dash_chip dash_chip_sold"><i class="fas fa-ticket-alt"></i> <?php /* translators: %d: number of tickets sold */ echo esc_html( sprintf( __( 'Sold %d', 'abp-tour-travel' ), $sold ) ); ?></span>
                                            <span class="dash_chip dash_chip_avail"><i class="fas fa-chair"></i> <?php /* translators: %d: number of tickets available */ echo esc_html( sprintf( __( 'Avail %d', 'abp-tour-travel' ), $available ) ); ?></span>
                                            <span class="dash_chip dash_chip_total"><i class="fas fa-users"></i> <?php /* translators: %d: total number of tickets */ echo esc_html( sprintf( __( 'Total %d', 'abp-tour-travel' ), $total ) ); ?></span>
											<?php if ( $reserve > 0 ) { ?>
                                                <span class="dash_chip dash_chip_reserve"><i class="fas fa-lock"></i> <?php /* translators: %d: number of reserved tickets */ echo esc_html( sprintf( __( 'Reserve %d', 'abp-tour-travel' ), $reserve ) ); ?></span>
											<?php } ?>
                                        </div>
                                    </div>
								<?php } ?>
                            </div>
                            <div class="dash_journey_hint"><i class="fas fa-mouse-pointer"></i> <?php esc_html_e( 'Click a session to see its booking details.', 'abp-tour-travel' ); ?></div>
						<?php } else { ?>
                            <div class="dash_empty">
                                <i class="fas fa-calendar-check"></i>
                                <p><?php esc_html_e( 'No sessions scheduled today.', 'abp-tour-travel' ); ?></p>
                            </div>
						<?php } ?>
                    </div>
                </div>
				<?php
			}
			private function quick_actions(): void {
				$actions = array(
					array( 'fas fa-toggle-on', __( 'ON/OFF Configuration', 'abp-tour-travel' ), __( 'Enable & disable features', 'abp-tour-travel' ), ABPTT_Function::build_url( 'configuration', [ 'configuration' => 'on_off' ] ), 'warning' ),
					array( 'fas fa-calendar-days', __( 'Global Date', 'abp-tour-travel' ), __( 'Manage global dates', 'abp-tour-travel' ), ABPTT_Function::build_url( 'global', [ 'global' => 'dates' ] ), 'theme' ),
				);
				if ( ABPTT_Function::on_off( 'additional_info' ) ) {
					$actions[] = array( 'fas fa-list-check', __( 'Additional Services', 'abp-tour-travel' ), __( 'Manage additional services', 'abp-tour-travel' ), ABPTT_Function::build_url( 'global', [ 'global' => 'additional' ] ), 'success' );
				}
				if ( ABPTT_Function::on_off( 'client_info' ) ) {
					$actions[] = array( 'fas fa-user', __( 'Traveler Forms', 'abp-tour-travel' ), __( 'Manage Traveler Forms fields', 'abp-tour-travel' ), ABPTT_Function::build_url( 'global', [ 'global' => 'client_form' ] ), 'purple' );
				}
				$actions[] = array( 'fas fa-route', __( 'Location Configuration', 'abp-tour-travel' ), __( 'Manage  locations', 'abp-tour-travel' ), ABPTT_Function::build_url( 'global', [ 'global' => 'location' ] ), 'navy' );
				if ( defined( 'ABPTT_DIR_PRO' ) ) {
					if ( ABPTT_Function::on_off( 'partial_payment' ) ) {
						$actions[] = array( 'fas fa-hand-holding-dollar', __( 'Partial Payment', 'abp-tour-travel' ), __( 'Configure partial payment', 'abp-tour-travel' ), ABPTT_Function::build_url( 'global', [ 'global' => 'partial_payment' ] ), 'success' );
					}
					if ( ( ABPTT_Function::on_off( 'seasonal' ) && ABPTT_Function::on_off( 'seasonal_global' ) ) || ( ABPTT_Function::on_off( 'early_bird' ) && ABPTT_Function::on_off( 'early_bird_global' ) ) ) {
						$actions[] = array( 'fas fa-percent', __( 'Global Discount', 'abp-tour-travel' ), __( 'Configure global discounts', 'abp-tour-travel' ), ABPTT_Function::build_url( 'global', [ 'global' => 'discount' ] ), 'info' );
					}
					if ( ABPTT_Function::on_off( 'cancel_request' ) ) {
						$actions[] = array( 'fas fa-ban', __( 'Cancel Request', 'abp-tour-travel' ), __( 'Manage cancellation requests', 'abp-tour-travel' ), ABPTT_Function::build_url( 'cancel_requests' ), 'warning' );
					}
				}
				?>
                <div class="dash_card">
                    <div class="dash_card_head">
                        <h4><i class="fas fa-bolt"></i> <?php esc_html_e( 'Quick Actions', 'abp-tour-travel' ); ?></h4>
                    </div>
                    <div class="dash_card_body">
                        <div class="dash_action_grid">
							<?php foreach ( $actions as $action ) { ?>
                                <a class="dash_action" href="<?php echo esc_url( $action[3] ); ?>">
                                    <span class="dash_action_icon <?php echo esc_attr( $action[4] ); ?>"><i class="<?php echo esc_attr( $action[0] ); ?>"></i></span>
                                    <span class="dash_action_text">
                                        <b><?php echo esc_html( $action[1] ); ?></b>
                                        <small><?php echo esc_html( $action[2] ); ?></small>
                                    </span>
                                    <i class="fas fa-angle-right dash_action_arrow"></i>
                                </a>
							<?php } ?>
                        </div>
                    </div>
                </div>
				<?php
			}
			private function orders_summary(): void {
				$last  = ABPTT_Query::get_booking_query( array(), 10 );
				$today = ABPTT_Query::get_booking_query( array( 'order_date' => current_time( 'Y-m-d' ) ) );
				?>
                <div class="dash_card dash_orders_card">
                    <div class="dash_card_head ">
                        <h4 class="abp_gap_xs"><i class="fas fa-receipt"></i> <?php esc_html_e( 'Recent Orders', 'abp-tour-travel' ); ?></h4>
                        <div class="_group_content">
                            <button type="button" class="_btn_light_theme_xs abp_active dash_order_tab" data-dtab="recent"><i class="fas fa-list-ul"></i> <?php esc_html_e( 'Last 10 Orders', 'abp-tour-travel' ); ?></button>
                            <button type="button" class="_btn_light_theme_xs dash_order_tab" data-dtab="today"><i class="fas fa-calendar-day"></i> <?php esc_html_e( "Today's Orders", 'abp-tour-travel' ); ?></button>
                        </div>
                        <a class="dash_card_link" href="<?php echo esc_url( ABPTT_Function::build_url( 'orders' ) ); ?>"><?php esc_html_e( 'All Orders', 'abp-tour-travel' ); ?> <i class="fas fa-angle-right"></i></a>
                    </div>
                    <div class="dash_orders_body">
                        <div class="dash_orders_pane abp_active" data-dpane="recent">
							<?php $this->order_table( $last, false ); ?>
                        </div>
                        <div class="dash_orders_pane" data-dpane="today">
							<?php $this->today_orders_pane( $today ); ?>
                        </div>
                    </div>
                </div>
				<?php
			}
			private function today_orders_pane( $items ): void {
				$total = count( $items );
				$step  = 10;
				?>
                <div class="dash_orders_meta"><?php echo esc_html( $total ); ?><?php echo esc_html( _n( 'order', 'orders', $total, 'abp-tour-travel' ) ); ?></div>
				<?php $this->order_table( $items, false, $step ); ?>
				<?php if ( $total > $step ) { ?>
                    <div class="dash_pane_more">
                        <button type="button" class="_btn_light_theme_xs dash_orders_loadmore" data-step="<?php echo esc_attr( $step ); ?>"><?php esc_html_e( 'Load More', 'abp-tour-travel' ); ?></button>
                    </div>
				<?php } ?>
				<?php
			}
			private function order_table( $items, $with_total = true, $limit = 0 ): void {
				$totals = array( 'total' => 0 );
				?>
                <table class="dash_order_table">
                    <thead>
                    <tr>
                        <th><?php esc_html_e( 'Order / Tour', 'abp-tour-travel' ); ?></th>
                        <th><?php esc_html_e( 'Session', 'abp-tour-travel' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'abp-tour-travel' ); ?></th>
                        <th class="dash_num"><?php esc_html_e( 'Total', 'abp-tour-travel' ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php if ( ! empty( $items ) ) { ?>
						<?php foreach ( $items as $index => $item ) {
							$order_id        = (int) ( $item['order_id'] ?? 0 );
							$tour_date      = $item['tour_date'] ?? '';
							$session_time    = $item['session_time'] ?? '';
							$total           = (float) ( $item['total'] ?? 0 );
							$totals['total'] += $total;
							$row_class       = ( $limit > 0 && $index >= $limit ) ? 'dash_row_hidden' : '';
							?>
                            <tr class="<?php echo esc_attr( $row_class ); ?>">
                                <td data-label="<?php esc_attr_e( 'Order / Tour', 'abp-tour-travel' ); ?>">
                                    <b class="dash_oid">#<?php echo esc_html( $order_id ); ?></b>
									<?php ABPTT_Layout::title( array( 'post_id' => (int) ( $item['post_id'] ?? 0 ) ) ); ?>
                                </td>
                                <td data-label="<?php esc_attr_e( 'Session', 'abp-tour-travel' ); ?>"><span class="dash_route"><?php echo ! empty( $tour_date ) ? esc_html( ABPTT_Function::date_format( $tour_date . ' ' . $session_time ) ) : '&mdash;'; ?></span></td>
                                <td data-label="<?php esc_attr_e( 'Status', 'abp-tour-travel' ); ?>"><span class="dash_pill <?php echo esc_attr( $item['order_status'] ?? '' ); ?>"><?php echo esc_html( ABPTT_Layout::status_text( $item['order_status'] ?? '' ) ); ?></span></td>
                                <td class="dash_num" data-label="<?php esc_attr_e( 'Total', 'abp-tour-travel' ); ?>">
									<?php
										if ( $total > 0 ) {
											echo function_exists( 'wc_price' ) ? wp_kses_post( wc_price( $total ) ) : esc_html( number_format_i18n( $total, 2 ) );
										} else {
											esc_html_e( 'FREE', 'abp-tour-travel' );
										}
									?>
                                </td>
                            </tr>
						<?php } ?>
						<?php if ( $with_total ) { ?>
                            <tr class="dash_order_total_row">
                                <td colspan="3"><?php esc_html_e( 'Total', 'abp-tour-travel' ); ?></td>
                                <td class="dash_num"><?php echo function_exists( 'wc_price' ) ? wp_kses_post( wc_price( $totals['total'] ) ) : esc_html( number_format_i18n( $totals['total'], 2 ) ); ?></td>
                            </tr>
						<?php } ?>
					<?php } else { ?>
                        <tr>
                            <td colspan="4" class="dash_order_empty"><?php esc_html_e( 'No orders found.', 'abp-tour-travel' ); ?></td>
                        </tr>
					<?php } ?>
                    </tbody>
                </table>
				<?php
			}
			//=============================//
			public function journey_popup(): void {
				if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
				}
				$post_id    = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
				$start_time = isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '';
				if ( $post_id <= 0 || empty( $start_time ) ) {
					wp_send_json_error( [ 'msg' => __( 'Invalid session.', 'abp-tour-travel' ), 'type' => 'warn' ], 400 );
				}
				$meta      = self::journey_meta( $post_id, $start_time );
				$seat_type = $meta['seat_type'] ?? 'ticket';
				ob_start();
				?>
                <div class="dash_journey_popup">
                    <div class="dash_journey_pop_head">
                        <div>
                            <h4><i class="fas fa-calendar-day"></i> <?php echo esc_html( $meta['title'] ?? __( 'Tour', 'abp-tour-travel' ) ); ?></h4>
                            <small><i class="far fa-calendar-alt"></i> <?php echo esc_html( ABPTT_Function::date_format( $start_time ) ); ?>
								<?php if ( ! empty( $meta['started'] ) ) { ?>
                                    <span class="dash_trip_state dash_trip_state_started"><i class="fas fa-play-circle"></i> <?php esc_html_e( 'Session Started', 'abp-tour-travel' ); ?></span>
								<?php } ?>
                            </small>
                        </div>
                        <span class="dash_chip dash_chip_total"><?php echo esc_html( ( 'sp' === $seat_type ) ? __( 'Seat Plan', 'abp-tour-travel' ) : __( 'Ticket Type', 'abp-tour-travel' ) ); ?></span>
                    </div>
                    <div class="dash_journey_stats">
                        <span class="dash_chip dash_chip_sold"><i class="fas fa-ticket-alt"></i> <?php /* translators: %d: number of tickets sold */ echo esc_html( sprintf( __( 'Sold %d', 'abp-tour-travel' ), $meta['sold'] ) ); ?></span>
                        <span class="dash_chip dash_chip_avail"><i class="fas fa-chair"></i> <?php /* translators: %d: number of tickets available */ echo esc_html( sprintf( __( 'Available %d', 'abp-tour-travel' ), $meta['available'] ) ); ?></span>
                        <span class="dash_chip dash_chip_total"><i class="fas fa-users"></i> <?php /* translators: %d: total number of tickets */ echo esc_html( sprintf( __( 'Total %d', 'abp-tour-travel' ), $meta['total'] ) ); ?></span>
						<?php if ( $meta['reserve'] > 0 ) { ?>
                            <span class="dash_chip dash_chip_reserve"><i class="fas fa-lock"></i> <?php /* translators: %d: number of reserved tickets */ echo esc_html( sprintf( __( 'Reserve %d', 'abp-tour-travel' ), $meta['reserve'] ) ); ?></span>
						<?php } ?>
                    </div>
					<?php if ( 'sp' === $seat_type ) { ?>
                        <div class="dash_journey_section">
                            <h5><i class="fas fa-chair"></i> <?php esc_html_e( 'Seat Map', 'abp-tour-travel' ); ?></h5>
							<?php $this->journey_seat_map( $post_id, $start_time ); ?>
                        </div>
					<?php } else { ?>
                        <div class="dash_journey_section">
                            <h5><i class="fas fa-tags"></i> <?php esc_html_e( 'Ticket Type Breakdown', 'abp-tour-travel' ); ?></h5>
							<?php $this->journey_ticket_table( $post_id, $start_time ); ?>
                        </div>
					<?php } ?>
                    <div class="dash_journey_section">
                        <h5><i class="fas fa-file-invoice"></i> <?php esc_html_e( 'Bookings', 'abp-tour-travel' ); ?> (<?php echo esc_html( $meta['order_count'] ); ?>)</h5>
						<?php $this->today_bookings( $post_id, $start_time ); ?>
                    </div>
                </div>
				<?php
				$html = ob_get_clean();
				wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Session loaded.', 'abp-tour-travel' ), 'type' => 'success' ] );
			}
			//=============================//
			private function journey_ticket_table( $post_id, $start_time ): void {
				$post_infos   = ABPTT_Function::get_all_meta( $post_id );
				$ticket_infos = $post_infos['ticket_infos'] ?? array();
				$filters      = array( 'post_id' => $post_id, 'start_time' => $start_time, 'cache' => false );
				$sold         = ABPTT_Query::get_sold_ticket( $filters, true );
				if ( empty( $ticket_infos ) || ! is_array( $ticket_infos ) ) {
					echo '<div class="dash_empty"><i class="fas fa-tag"></i><p>' . esc_html__( 'No ticket configuration found.', 'abp-tour-travel' ) . '</p></div>';
					return;
				}
				?>
                <table class="dash_journey_table">
                    <thead>
                    <tr>
                        <th><?php esc_html_e( 'Type', 'abp-tour-travel' ); ?></th>
                        <th><?php esc_html_e( 'Sold', 'abp-tour-travel' ); ?></th>
                        <th><?php esc_html_e( 'Available', 'abp-tour-travel' ); ?></th>
                        <th><?php esc_html_e( 'Total', 'abp-tour-travel' ); ?></th>
                        <th><?php esc_html_e( 'Reserve', 'abp-tour-travel' ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php foreach ( $ticket_infos as $tic_id => $ticket_info ) {
						$qty      = (int) ( $ticket_info['qty'] ?? 0 );
						$reserve  = (int) ( $ticket_info['reserve'] ?? 0 );
						$sold_qty = (int) ( $sold[ $tic_id ] ?? 0 );
						$avail    = max( 0, $qty - $sold_qty - $reserve );
						?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( ABPTT_Function::ticket_name( $tic_id ) ); ?></strong>
                            </td>
                            <td><?php echo esc_html( $sold_qty ); ?></td>
                            <td><?php echo esc_html( $avail ); ?></td>
                            <td><?php echo esc_html( $qty ); ?></td>
                            <td><?php echo esc_html( $reserve ); ?></td>
                        </tr>
					<?php } ?>
                    </tbody>
                </table>
				<?php
			}
			//=============================//
			private function journey_seat_map( $post_id, $start_time ): void {
				$sp_infos = ABPTT_Function::get_post_info( $post_id, 'sp_infos', [] );
				if ( empty( $sp_infos ) || ! is_array( $sp_infos ) ) {
					echo '<div class="dash_empty"><i class="fas fa-chair"></i><p>' . esc_html__( 'No seat plan assigned.', 'abp-tour-travel' ) . '</p></div>';
					return;
				}
				$filters   = array( 'post_id' => $post_id, 'start_time' => $start_time, 'cache' => false );
				$sold_seat = ABPTT_Query::get_sold_seat( $filters );
				foreach ( $sp_infos as $sp_item ) {
					$sp_id = (int) ( $sp_item['id'] ?? 0 );
					if ( $sp_id <= 0 ) {
						continue;
					}
					$this->render_seat_plan( $sp_id, $sold_seat );
				}
			}
			//=============================//
			private function render_seat_plan( $sp_id, $sold_seat ): void {
				$row     = ABPTT_Query::get_sp( $sp_id );
				$sp_info = ! empty( $row ) ? current( $row ) : array();
				if ( empty( $sp_info ) ) {
					return;
				}
				$others      = json_decode( $sp_info['others'] ?? '', true ) ?: array();
				$cell_width  = $others['width'] ?? 40;
				$cell_height = $others['height'] ?? 40;
				$gap         = $others['gap'] ?? 0;
				$radius      = $others['radius'] ?? 0;
				$cols        = intval( $others['column'] ?? 10 );
				$bg_image    = $others['bg_image'] ?? '';
				$img_url     = ! empty( $bg_image ) && $bg_image > 0 ? ABPTT_Function::get_image_url( '', $bg_image ) : '';
				$bg_color    = $others['bg_color'] ?? '#fff';
				$layout      = json_decode( $sp_info['layout_data'] ?? '', true ) ?: array();
				$sp_name     = $sp_info['name'] ?? '';
				// Multi-row/column cells must hide their "occupied" grid slots.
				$hidden_cells = array();
				foreach ( $layout as $index => $cell ) {
					$c_span = intval( $cell['width_ratio'] ?? 1 );
					$r_span = intval( $cell['height_ratio'] ?? 1 );
					if ( $c_span > 1 || $r_span > 1 ) {
						for ( $r = 0; $r < $r_span; $r ++ ) {
							for ( $c = 0; $c < $c_span; $c ++ ) {
								if ( $r === 0 && $c === 0 ) {
									continue;
								}
								$target_idx                  = $index + ( $r * $cols ) + $c;
								$hidden_cells[ $target_idx ] = true;
							}
						}
					}
				}
				?>
                <div class="dash_sp_block">
					<?php if ( ! empty( $sp_name ) ) { ?>
                        <div class="dash_sp_title"><?php echo esc_html( $sp_name ); ?></div>
					<?php } ?>
                    <div class="sp_canvas dash_sp_canvas" style="grid-template-columns: repeat(<?php echo esc_attr( $cols ); ?>, 1fr); background-image: url('<?php echo esc_url( $img_url ); ?>'); background-color: <?php echo esc_attr( $bg_color ); ?>;gap: <?php echo esc_attr( $gap ); ?>px;">
						<?php foreach ( $layout as $index => $cell ) {
							if ( isset( $hidden_cells[ $index ] ) ) {
								continue;
							}
							$type_id         = $cell['id'] ?? '';
							$name            = $cell['name'] ?? '';
							$c_span          = intval( $cell['width_ratio'] ?? 1 );
							$r_span          = intval( $cell['height_ratio'] ?? 1 );
							$rotate          = intval( $cell['rotate'] ?? 0 );
							$fs              = $cell['fs'] ?? 12;
							$is_seat         = ( 'seat' === ( $cell['type'] ?? '' ) );
							$seat_type_class = $is_seat ? 'available' : '';
							$seat_type_class = $is_seat && in_array( $name, $sold_seat, true ) ? 'sold' : $seat_type_class;
							$class           = $is_seat ? 'sp_cell ' . $seat_type_class : 'sp_decor';
							$color           = $is_seat ? ABPTT_Function::ticket_color( $type_id ) : ABPTT_Function::decor_color( $type_id );
							$icon_image      = $is_seat ? ABPTT_Function::ticket_icon( $type_id ) : ABPTT_Function::decor_icon( $type_id );
							$width           = $cell_width * $c_span;
							$height          = $cell_height * $r_span;
							if ( $gap > 0 ) {
								$width  = $c_span > 1 ? $width + ( $c_span - 1 ) * $gap : $width;
								$height = $r_span > 1 ? $height + ( $r_span - 1 ) * $gap : $height;
							}
							$style = "color: {$color}; grid-column: span {$c_span}; grid-row: span {$r_span}; width:{$width}px;height:{$height}px; border:1px solid  {$color};font-size:{$fs}px;border-radius:{$radius}px;";
							$image = '';
							if ( ! empty( $icon_image ) && is_numeric( $icon_image ) ) {
								$image = ABPTT_Function::get_image_url( '', $icon_image );
							}
							?>
                            <div class="<?php echo esc_attr( $class ); ?>" style="<?php echo esc_attr( $style ); ?>">
                                <div class="cell_content <?php echo esc_attr( $rotate ? "rotate-{$rotate}" : '' ); ?>" style="background-image: url('<?php echo esc_url( $image ); ?>');">
									<?php ABPTT_Layout::image_icon( $icon_image ); ?>
                                    <span class="cell_label"><?php echo esc_html( $name ); ?></span>
                                </div>
                            </div>
						<?php } ?>
                    </div>
                </div>
				<?php
			}
			//=============================//
			private function today_bookings( $post_id, $start_time ): void {
				$bookings = ABPTT_Query::get_booking_query( array(
					'post_id'    => $post_id,
					'start_time' => $start_time,
					'status'     => ABPTT_Function::booking_status_sold(),
					'cache'      => false,
				) );
				if ( empty( $bookings ) ) {
					echo '<div class="dash_empty"><i class="fas fa-file-invoice"></i><p>' . esc_html__( 'No bookings for this journey yet.', 'abp-tour-travel' ) . '</p></div>';
					return;
				}
				?>
                <div class="dash_journey_bookings">
					<?php foreach ( $bookings as $booking ) {
						$name         = $booking['billing_name'] ?? '';
						$qty          = (int) ( $booking['qty'] ?? 0 );
						$status       = $booking['order_status'] ?? '';
						$ticket_infos = json_decode( $booking['ticket_info'] ?? '', true ) ?: array();
						$ticket_text  = array();
						if ( is_array( $ticket_infos ) ) {
							foreach ( $ticket_infos as $ticket_info ) {
								$tic_qty = (int) ( $ticket_info['qty'] ?? 1 );
								$label   = $ticket_info['name'] ?? ABPTT_Function::ticket_name( $ticket_info['id'] ?? 0 );
								if ( ! empty( $label ) ) {
									$ticket_text[] = esc_html( $label ) . ' x ' . $tic_qty;
								}
							}
						}
						?>
                        <div class="dash_journey_booking">
                            <div class="dash_journey_booking_main">
                                <strong><?php echo esc_html( $name ? $name : '#' . esc_html( $booking['order_id'] ?? '' ) ); ?></strong>
								<?php if ( ! empty( $ticket_text ) ) { ?>
                                    <small><?php echo esc_html( implode( ', ', $ticket_text ) ); ?></small>
								<?php } ?>
                            </div>
                            <div class="dash_journey_booking_side">
                                <span class="dash_pill <?php echo esc_attr( $status ); ?>"><?php echo esc_html( ABPTT_Layout::status_text( $status ) ); ?></span>
                                <span class="dash_chip dash_chip_total"><?php echo esc_html( 'x' . $qty ); ?></span>
                            </div>
                        </div>
					<?php } ?>
                </div>
				<?php
			}
			private function system_status( $abptt_info, $label ): void {
				$total       = (int) ( $abptt_info['total_post'] ?? 0 );
				$dummy_total = sizeof( ABPTT_Query::dummy_ids() );
				?>
                <div class="dash_card">
                    <div class="dash_card_head">
                        <h4 class="abp_gap_xs"><i class="fas fa-server"></i> <?php esc_html_e( 'System Status', 'abp-tour-travel' ); ?></h4>
                    </div>
                    <div class="dash_card_body dash_card_body_plain">
                        <div class="dash_kv_list">
                            <div class="dash_kv">
                                <span><?php esc_html_e( 'Plugin', 'abp-tour-travel' ); ?></span>
                                <b><?php echo esc_html( ABPTT_VERSION ); ?></b>
                            </div>
                            <div class="dash_kv">
                                <span><?php esc_html_e( 'WordPress', 'abp-tour-travel' ); ?></span>
                                <b><?php echo esc_html( get_bloginfo( 'version' ) ); ?></b>
                            </div>
                            <div class="dash_kv">
                                <span><?php esc_html_e( 'PHP', 'abp-tour-travel' ); ?></span>
                                <b><?php echo esc_html( phpversion() ); ?></b>
                            </div>
                            <div class="dash_kv">
                                <span><?php esc_html_e( 'WooCommerce', 'abp-tour-travel' ); ?></span>
								<?php if ( ABPTT_WC >= 2 && defined( 'WC_VERSION' ) ) { ?>
                                    <b class="<?php echo esc_attr( version_compare( WC_VERSION, '8.0', '>' ) ? '' : 'dash_status_warn' ); ?>"><?php echo esc_html( WC_VERSION ); ?></b>
								<?php } else { ?>
                                    <b class="dash_status_warn"><?php esc_html_e( 'Not active', 'abp-tour-travel' ); ?></b>
								<?php } ?>
                            </div>
							<?php if ( ABPTT_WC >= 2 ) {
								$wc_name  = get_option( 'woocommerce_email_from_name' );
								$wc_email = get_option( 'woocommerce_email_from_address' );
								if ( ! empty( $wc_name ) ) { ?>
                                    <div class="dash_kv">
                                        <span><?php esc_html_e( 'WC Name', 'abp-tour-travel' ); ?></span>
                                        <b><?php echo esc_html( $wc_name ); ?></b>
                                    </div>
								<?php }
								if ( ! empty( $wc_email ) ) { ?>
                                    <div class="dash_kv">
                                        <span><?php esc_html_e( 'WC Email', 'abp-tour-travel' ); ?></span>
                                        <b><?php echo esc_html( $wc_email ); ?></b>
                                    </div>
								<?php }
							} ?>
                            <div class="dash_status_checklist">
								<?php $this->checklist_item( __( 'WooCommerce Plugin', 'abp-tour-travel' ), ABPTT_WC >= 2, ABPTT_WC == 1 ? 'wc_active' : 'wc_install_active' ); ?>
								<?php $this->checklist_item( __( 'Booking Page', 'abp-tour-travel' ), (bool) ABPTT_Function::get_page_by_slug( 'tf_booking' ), 'tf_booking' ); ?>
								<?php $this->checklist_item(
								/* translators: %s: transport label. */
									sprintf( esc_html__( '%s List Page', 'abp-tour-travel' ), esc_html( $label ) ),
									(bool) ABPTT_Function::get_page_by_slug( 'tf_post' ),
									'tf_post'
								); ?>
								<?php $this->checklist_item( __( 'Gallery Page', 'abp-tour-travel' ), (bool) ABPTT_Function::get_page_by_slug( 'tf_gallery' ), 'tf_gallery' ); ?>
								<?php if ( ABPTT_WC > 1 ) {
									do_action( 'abptt_add_page' );
									do_action( 'abptt_add_tools' );
								} ?>
								<?php $this->checklist_item( __( 'First Transport Added', 'abp-tour-travel' ), $total > 0, 'add_new' ); ?>
                                <div class="dash_kv">
                                    <span class="dash_status_item is_todo">
                                        <i class="far fa-circle"></i>
                                        <?php esc_html_e( 'Number of Post', 'abp-tour-travel' ); ?>
                                    </span>
                                    <b class="dash_status_ready"><?php echo esc_html( $total ); ?></b>
                                </div>
                                <div class="dash_kv">
                                    <span class="dash_status_item is_todo">
                                        <i class="far fa-circle"></i>
                                        <?php esc_html_e( 'Dummy Import', 'abp-tour-travel' ); ?>
                                    </span>
                                    <div class="_group_content">
                                        <button class="_btn_light_theme_xxs" onclick="abptt_import_global('dummy', this)" type="button"><span class="fas fa-plus"></span><?php esc_html_e( 'Dummy ', 'abp-tour-travel' ); ?></button>
										<?php if ( $dummy_total > 0 ) { ?>
                                            <button class="_btn_light_warning_xxs" onclick="abptt_import_global('remove_dummy', this)" type="button"><span class="fas fa-trash"></span><?php echo esc_html( $dummy_total ); ?> <?php esc_html_e( 'Dummy', 'abp-tour-travel' ); ?></button>
										<?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			private function checklist_item( $label, $done, $fix_type = '' ): void {
				?>
                <div class="dash_kv">
                    <span class="dash_status_item <?php echo esc_attr( $done ? 'is_done' : 'is_todo' ); ?>">
                        <i class="<?php echo esc_attr( $done ? 'fas fa-check-circle' : 'far fa-circle' ); ?>"></i>
                        <?php echo esc_html( $label ); ?>
                    </span>
					<?php if ( $done ) { ?>
                        <b class="dash_status_ready"><?php esc_html_e( 'Ready', 'abp-tour-travel' ); ?></b>
					<?php } elseif ( $fix_type === 'add_new' ) { ?>
                        <a class="dash_status_fix" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . ABPTT_Function::get_cpt() ) ); ?>"><?php esc_html_e( 'Fix', 'abp-tour-travel' ); ?></a>
					<?php } elseif ( in_array( $fix_type, array( 'wc_active', 'wc_install_active' ), true ) ) { ?>
                        <button class="dash_status_fix" onclick="abptt_wc_config('<?php echo esc_attr( $fix_type ); ?>', this)" type="button">
							<?php echo esc_html( $fix_type === 'wc_active' ? __( 'Active Now', 'abp-tour-travel' ) : __( 'Install & Activate', 'abp-tour-travel' ) ); ?>
                        </button>
					<?php } elseif ( ! empty( $fix_type ) ) { ?>
                        <button class="dash_status_fix" onclick="abptt_create_page('<?php echo esc_attr( $fix_type ); ?>', this)" type="button"><?php esc_html_e( 'Fix', 'abp-tour-travel' ); ?></button>
					<?php } ?>
                </div>
				<?php
			}
			private function content_breakdown( $abptt_info ): void {
				$rows = array(
					array( 'publish', __( 'Published', 'abp-tour-travel' ), (int) ( $abptt_info['total_publish'] ?? 0 ) ),
					array( 'draft', __( 'Draft', 'abp-tour-travel' ), (int) ( $abptt_info['total_draft'] ?? 0 ) ),
					array( 'private', __( 'Private', 'abp-tour-travel' ), (int) ( $abptt_info['total_private'] ?? 0 ) ),
					array( 'trash', __( 'Trash', 'abp-tour-travel' ), (int) ( $abptt_info['total_trash'] ?? 0 ) ),
				);
				$sum  = 0;
				foreach ( $rows as $row ) {
					$sum += $row[2];
				}
				?>
                <div class="dash_card">
                    <div class="dash_card_head">
                        <h4><i class="fas fa-layer-group"></i> <?php esc_html_e( 'Content Breakdown', 'abp-tour-travel' ); ?></h4>
                    </div>
                    <div class="dash_card_body">
                        <div class="dash_bar">
							<?php foreach ( $rows as $row ) {
								$width = $sum > 0 ? (int) round( ( $row[2] / $sum ) * 100 ) : 0;
								if ( $row[2] > 0 && $width < 1 ) {
									$width = 1;
								}
								?>
                                <span class="<?php echo esc_attr( $row[0] ); ?>" style="width:<?php echo esc_attr( $width ); ?>%;" title="<?php echo esc_attr( $row[1] . ': ' . $row[2] ); ?>"></span>
							<?php } ?>
                        </div>
                        <ul class="dash_legend">
							<?php foreach ( $rows as $row ) { ?>
                                <li>
                                    <span class="dash_dot <?php echo esc_attr( $row[0] ); ?>"></span>
									<?php echo esc_html( $row[1] ); ?>
                                    <b><?php echo esc_html( $row[2] ); ?></b>
                                </li>
							<?php } ?>
                        </ul>
                    </div>
                </div>
				<?php
			}
			//=============================//
			public static function kpi_data(): array {
				$cache_key = 'abptt_dashboard_kpi';
				$cached    = wp_cache_get( $cache_key, 'abptt_dashboard' );
				if ( is_array( $cached ) ) {
					return $cached;
				}
				global $wpdb;
				$table_name = $wpdb->prefix . 'abptt_orders';
				$data       = array( 'revenue' => 0, 'tickets' => 0, 'today' => 0, 'upcoming' => array() );
				$statuses   = array_filter( array_map( 'sanitize_text_field', explode( ',', (string) ABPTT_Function::booking_status() ) ) );
				if ( ! empty( $statuses ) && current( $statuses ) !== 'all' ) {
					$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
					$sql          = "SELECT COALESCE(SUM(total), 0) AS revenue, COALESCE(SUM(qty), 0) AS tickets FROM %i WHERE order_status IN ($placeholders)";
					$params       = array_merge( array( $table_name ), $statuses );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
					$row = $wpdb->get_row( $wpdb->prepare( $sql, $params ), ARRAY_A );
					if ( ! empty( $row ) ) {
						$data['revenue'] = (float) $row['revenue'];
						$data['tickets'] = (int) $row['tickets'];
					}
				}
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$data['today']    = (int) $wpdb->get_var(
					$wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE DATE(created_at) = %s', $table_name, current_time( 'Y-m-d' ) )
				);
				$data['upcoming'] = self::journey_list();
				wp_cache_set( $cache_key, $data, 'abptt_dashboard' );
				return $data;
			}
			//=============================//
			/**
			 * Build today's session list (a session = a tour on a specific start time).
			 * Only today's date is considered; every session scheduled today is shown,
			 * including sessions whose start time has already passed.
			 */
			public static function journey_list(): array {
				$post_ids = defined( 'ABPTT_ids' ) && ! empty( ABPTT_ids ) ? ABPTT_ids : ABPTT_Query::get_post_id();
				if ( empty( $post_ids ) || ! is_array( $post_ids ) ) {
					return array();
				}
				$today    = current_time( 'Y-m-d' );
				$journeys = array();
				foreach ( $post_ids as $post_id ) {
					$post_id = (int) $post_id;
					if ( $post_id <= 0 ) {
						continue;
					}
					$journeys = array_merge( $journeys, self::journey_today( $post_id, $today ) );
				}
				if ( count( $journeys ) > 1 ) {
					usort( $journeys, function ( $a, $b ) {
						return strtotime( $a['start_time'] ?? '' ) <=> strtotime( $b['start_time'] ?? '' );
					} );
				}
				return $journeys;
			}
			//=============================//
			private static function journey_today( $post_id, $today ): array {
				$post_infos = ABPTT_Function::get_all_meta( $post_id );
				if ( empty( $post_infos ) ) {
					return array();
				}
				// Is today among the post's scheduled dates?
				$dates             = ABPTT_Function::date( $post_id, array(), $today );
				$today_is_schedule = is_array( $dates ) && in_array( $today, $dates, true );
				// Keep listing transports whose trips have already started today.
				if ( ! $today_is_schedule && ! self::is_schedule_day( $post_infos, $today ) ) {
					return array();
				}
				$journeys = array();
				// Outbound trips: every scheduled departure time today (multi schedule = several times).
				$time_infos = $post_infos['time_infos'] ?? [];
				$times      = ABPTT_Function::time( $time_infos, $today );
				if ( ! empty( $times ) && is_array( $times ) ) {
					foreach ( $times as $_time ) {
						if ( '' === (string) $_time ) {
							continue;
						}
						$journeys[] = self::journey_meta( $post_id, $today . ' ' . $_time, - 1, - 1 );
					}
				}
				return $journeys;
			}
			//=============================//
			private static function is_schedule_day( $post_infos, $today ): bool {
				$date_infos = $post_infos['abptt_dates'] ?? array();
				if ( empty( $date_infos ) || ! is_array( $date_infos ) ) {
					return false;
				}
				$date_type = $date_infos['date_type'] ?? 'periodic_date';
				if ( 'specific_date' === $date_type ) {
					$specific_dates = $date_infos['specific_dates'] ?? array();
					if ( is_array( $specific_dates ) ) {
						foreach ( $specific_dates as $date_item ) {
							if ( ! empty( $date_item ) && gmdate( 'Y-m-d', strtotime( $date_item ) ) === $today ) {
								return true;
							}
						}
					}
					return false;
				}
				$start_date = $date_infos['periodic_start_date'] ?? '';
				if ( empty( $start_date ) || strtotime( $today ) < strtotime( $start_date ) ) {
					return false;
				}
				$end_date     = $date_infos['periodic_end_date'] ?? '';
				$end_date     = ! empty( $end_date ) ? gmdate( 'Y-m-d', strtotime( $end_date ) ) : '';
				$advance_days = ( ABPTT_Date_Config['advance_date_number'] ?? null ) ?: 28;
				$calc_end     = gmdate( 'Y-m-d', strtotime( $start_date . ' +' . $advance_days . ' day' ) );
				if ( ! empty( $end_date ) && strtotime( $end_date ) < strtotime( $calc_end ) ) {
					$calc_end = $end_date;
				}
				return in_array( $today, ABPTT_Function::date_list_modify( $start_date, $calc_end, $date_infos ), true );
			}
			//=============================//
			/**
			 * Compute the stats (total, sold, available, reserve, seat_type) for a single session.
			 *
			 * @param int $post_id Tour post ID.
			 * @param string $start_time Exact session datetime.
			 * @param int $sold_qty Pre-computed sold quantity (optional).
			 * @param int $order_count Number of distinct orders (optional).
			 */
			public static function journey_meta( $post_id, $start_time, $sold_qty = - 1, $order_count = - 1 ): array {
				$post_id    = (int) $post_id;
				$post_infos = ABPTT_Function::get_all_meta( $post_id );
				$seat_type  = $post_infos['seat_type'] ?? 'sp';
				$seat_type  = ABPTT_Function::on_off( 'sp' ) ? $seat_type : 'ticket';
				$total      = (int) ( ABPTT_Function::get_total_qty( $post_id, $post_infos ) );
				$filters    = array( 'post_id' => $post_id, 'start_time' => $start_time, 'cache' => false );
				// Sold quantity.
				if ( $sold_qty < 0 ) {
					$sold     = ABPTT_Query::get_sold_ticket( $filters );
					$sold_qty = (int) ( $sold['total'] ?? 0 );
				}
				if ( $order_count < 0 ) {
					$order_count = count( ABPTT_Query::get_booking_query( array(
						'post_id'    => $post_id,
						'start_time' => $start_time,
						'status'     => ABPTT_Function::booking_status_sold(),
						'cache'      => false,
					) ) );
				}
				// Reserve quantity (only meaningful for ticket-type seating).
				$reserve = 0;
				if ( 'ticket' === $seat_type ) {
					$ticket_infos = $post_infos['ticket_infos'] ?? array();
					if ( is_array( $ticket_infos ) ) {
						foreach ( $ticket_infos as $ticket_info ) {
							$reserve += (int) ( $ticket_info['reserve'] ?? 0 );
						}
					}
				}
				$available = $total - $sold_qty - $reserve;
				if ( $available < 0 ) {
					$available = 0;
				}
				return array(
					'post_id'     => $post_id,
					'start_time'  => $start_time,
					'title'       => get_the_title( $post_id ),
					'seat_type'   => $seat_type,
					'started'     => strtotime( $start_time ) <= current_time( 'timestamp' ),
					'sold'        => (int) $sold_qty,
					'available'   => $available,
					'total'       => $total,
					'reserve'     => $reserve,
					'order_count' => (int) $order_count,
				);
			}
		}
		new ABPTT_Dashboard();
	}
