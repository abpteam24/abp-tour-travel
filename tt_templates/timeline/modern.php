<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptt_timeline_modern_template', function ( $timeline_items = [], $post_id = 0 ) {
		if ( empty( $timeline_items ) || ! is_array( $timeline_items ) ) {
			return;
		}
		?>
        <section class="abptt_timeline abptt_timeline_modern">
            <div class="abptt_tl_modern_head">
                <span class="abptt_tl_modern_icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
                <div class="abptt_tl_modern_heading">
                    <span><?php esc_html_e( 'Schedule', 'abp-tour-travel' ); ?></span>
                    <h4><?php esc_html_e( 'Tour Timeline', 'abp-tour-travel' ); ?></h4>
                </div>
            </div>
            <div class="abptt_tl_list">
				<?php foreach ( $timeline_items as $index => $item ) {
					$time  = $item['time'] ?? '';
					$title = $item['title'] ?? '';
					$des   = $item['des'] ?? '';
					?>
                    <div class="abptt_tl_item">
                        <div class="abptt_tl_badge">
                            <span class="abptt_tl_badge_core"></span>
                        </div>
                        <div class="abptt_tl_card">
							<?php if ( ! empty( $time ) ) { ?>
                                <div class="abptt_tl_time">
                                    <i class="far fa-clock" aria-hidden="true"></i>
                                    <span><?php echo esc_html( $time ); ?></span>
                                </div>
							<?php } ?>
                            <h5 class="abptt_tl_title"><?php echo esc_html( $title ); ?></h5>
							<?php if ( ! empty( $des ) ) { ?>
                                <div class="abptt_tl_des">
									<?php
										// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
										echo wp_kses_post( apply_filters( 'the_content', $des ) );
									?>
                                </div>
							<?php } ?>
                        </div>
                    </div>
				<?php } ?>
            </div>
        </section>
		<?php
	}, 10, 2 );