<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptt_timeline_default_template', function ( $timeline_items = [], $post_id = 0 ) {
		if ( empty( $timeline_items ) || ! is_array( $timeline_items ) ) {
			return;
		}
		?>
        <section class="abptt_timeline abptt_timeline_default">
            <div class="_panel_head _fj_between">
                <h4 class="abp_gap_xs"><i class="fas fa-stream" aria-hidden="true"></i><?php esc_html_e( 'Tour Timeline', 'abp-tour-travel' ); ?></h4>
            </div>
            <div class="_panel_body_xs">
                <div class="abptt_tl_list">
					<?php foreach ( $timeline_items as $index => $item ) {
						$time  = $item['time'] ?? '';
						$title = $item['title'] ?? '';
						$des   = $item['des'] ?? '';
						?>
                        <div class="abptt_tl_item">
                            <div class="abptt_tl_dot">
                                <span class="abptt_tl_dot_core"></span>
                            </div>
                            <div class="abptt_tl_content">
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
            </div>
        </section>
		<?php
	}, 10, 2 );