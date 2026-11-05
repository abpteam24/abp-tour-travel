<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    add_action( 'abptt_timeline_light_template', function ( $timeline_items = [], $post_id = 0 ) {
        if ( empty( $timeline_items ) || ! is_array( $timeline_items ) ) {
            return;
        }
        ?>
        <section class="abptt_timeline abptt_timeline_light">
            <div class="_panel_head _fj_between">
                <h4 class="abp_gap_xs"><i class="fas fa-history" aria-hidden="true"></i><?php esc_html_e( 'Tour Timeline', 'abp-tour-travel' ); ?></h4>
            </div>
            <div class="_panel_body_xs">
                <div class="abptt_tl_list">
                    <?php foreach ( $timeline_items as $index => $item ) {
                        $time  = $item['time'] ?? '';
                        $title = $item['title'] ?? '';
                        $des   = $item['des'] ?? '';
                        ?>
                        <div class="abptt_tl_item">
                            <div class="abptt_tl_marker">
                                <span class="abptt_tl_marker_core"></span>
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
                                        <?php echo wp_kses_post( ABPTT_Function::format_content( $des ) ); ?>
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