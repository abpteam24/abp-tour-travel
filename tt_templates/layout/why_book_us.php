<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    add_action( 'abptt_why_book_us_template', function ( $post_infos = [], $type = '' ) {
        if ( ! ABPTT_Function::on_off( 'why_book_us' ) ) {
            return;
        }
        $items = ABPTT_Function::why_book_us_items();
        if ( empty( $items ) ) {
            return;
        }
        $why_book = ABPTT_Function::get_option( 'abptt_why_book' );
        $title    = $why_book['title'] ?? __( 'Why Book With Us', 'abp-tour-travel' );
        ?>
        <div class="_section why_book_us_area">
            <h4 class="abp_gap_xs"> 👍 <?php echo esc_html( $title ); ?>            </h4>
            <div class="_divider_xs"></div>
            <div class="why_book_us_list ">
                <?php foreach ( $items as $item ) { ?>
                    <div class="why_book_us_item">
                        <span class="why_book_us_icon"><?php ABPTT_Layout::image_icon( $item['icon'] ); ?></span>
                        <?php if ( ! empty( $item['title'] ) ) { ?>
                            <h6 class="abp why_book_us_title"><?php echo esc_html( $item['title'] ); ?></h6>
                        <?php } ?>
                        <?php if ( ! empty( $item['description'] ) ) { ?>
                            <p class="why_book_us_description"><?php echo esc_html( $item['description'] ); ?></p>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
    }, 10, 2 );