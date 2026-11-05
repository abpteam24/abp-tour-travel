<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    add_action( 'abptt_review_section_template', function ( $post_infos = [], $type = '' ) {
        if ( ! ABPTT_Function::on_off( 'rating' ) ) {
            return;
        }
        $post_id = absint( $post_infos['post_id'] ?? 0 );
        $display = $post_infos['abptt_display_rating'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_display_rating', 'on' );
        if ( $display === 'off' || $post_id <= 0 ) {
            return;
        }
        $rating_data = ABPTT_Function::get_rating( $post_id );
        $reviews     = ABPTT_Function::get_reviews( $post_id, 10 );
        if ( empty( $rating_data ) && empty( $reviews ) ) {
            return;
        }
        ?>
        <div class="_section_card_xs_w_full review_section_area abptt_review_section_panel">
            <h4 class="abp_gap_xxs"> ⭐ <?php esc_html_e( 'Ratings & Reviews', 'abp-tour-travel' ); ?>            </h4>
            <div class="_divider_xs"></div>
            <?php if ( ! empty( $rating_data ) ) { ?>
                <div class="review_summary">
                    <span class="review_summary_stars">
                        <?php
                            $rounded = round( $rating_data['rating'] * 2 ) / 2;
                            for ( $i = 1; $i <= 5; $i ++ ) :
                                if ( $rounded >= $i ) { ?>
                                    <i class="fas fa-star" aria-hidden="true"></i>
                                <?php } elseif ( $rounded >= $i - 0.5 ) { ?>
                                    <i class="fas fa-star-half-alt" aria-hidden="true"></i>
                                <?php } else { ?>
                                    <i class="far fa-star" aria-hidden="true"></i>
                                <?php } ?>
                            <?php endfor;
                        ?>
                    </span>
                    <span class="review_summary_value"><?php echo esc_html( number_format_i18n( $rating_data['rating'], 1 ) ); ?></span>
                    <span class="review_summary_count"><?php
                            // translators: %s: number of reviews.
                            echo esc_html( sprintf( _n( '(%s Review)', '(%s Reviews)', $rating_data['count'], 'abp-tour-travel' ), number_format_i18n( $rating_data['count'] ) ) );
                        ?></span>
                </div>
            <?php } ?>
            <?php if ( ! empty( $reviews ) ) { ?>
                <div class="review_list">
                    <?php foreach ( $reviews as $review ) { ?>
                        <div class="review_item">
                            <div class="review_item_head">
                                <strong class="review_author"><?php echo esc_html( $review->comment_author ); ?></strong>
                                <span class="review_date"><?php echo esc_html( get_comment_date( '', $review ) ); ?></span>
                            </div>
                            <?php
                                $review_rating = get_comment_meta( $review->comment_ID, 'rating', true );
                                if ( ! empty( $review_rating ) ) { ?>
                                    <span class="review_item_stars"><?php echo wp_kses_post( wc_get_rating_html( (float) $review_rating ) ); ?></span>
                                <?php } ?>
                            <div class="review_content"><?php echo esc_html( $review->comment_content ); ?></div>
                        </div>
                        <?php
                    } ?>
                </div>
            <?php } ?>
        </div>
        <?php
    }, 10, 2 );