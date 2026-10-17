<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptt_missionary_template', function ( $params = [] ) {
		//echo '<pre>';print_r($params);echo '</pre>';
		$global_order = $params['global_order'] ?? '';
		$post_ids     = $params['all_post'] ?? [];
		if ( ! empty( $post_ids ) && sizeof( $post_ids ) > 0 ) {
			$column            = $params['column'] ?? 3;
			$show_post         = absint( ( $params['show'] ?? 0 ) ?: ( $column * 3 ) );
			$post_count        = 0;
			$args['total']     = sizeof( $post_ids );
			$args['page_item'] = $show_post;
			asort( $post_ids );
			?>
            <div class=" abptt_missionary  item_<?php echo esc_attr( $column ); ?>">
				<?php foreach ( $post_ids as $post_id ) {
					$post_infos['post_id'] = $post_id;
					$cat_id                = ABPTT_Function::get_post_info( $post_id, 'abptt_category' );
					$loc_id                = ABPTT_Function::get_post_info( $post_id, 'abptt_location' );
					$post_count ++;
					$url = get_the_permalink( $post_id ); ?>
                    <div class="pagination_item item_box_1  <?php echo esc_attr( $show_post >= $post_count ? '' : 'abp_close' ); ?>" data-cat_id="<?php echo esc_attr( $cat_id ); ?>" data-loc_id="<?php echo esc_attr( $loc_id ); ?>">
                        <div class="item_head">
							<?php ABPTT_Layout::category( $post_infos, 'ribbon' ); ?>
							<?php ABPTT_Layout::image( $post_id ); ?>
							<?php ABPTT_Layout::rating( $post_infos, 'ribbon publish' ); ?>
							<?php ABPTT_Layout::top_pick( $post_infos, 'ribbon top_pick' ); ?>
                        </div>
                        <div class="item_body">
                            <div class="">
                                <a class="abp list_title" href="<?php echo esc_url( $url ); ?>" target="_blank">
									<?php ABPTT_Layout::title( $post_infos ); ?>
                                </a>
                                <div class="_divider_xxs"></div>
								<?php
									ABPTT_Layout::organizer( $post_infos, 'publish' );
									ABPTT_Layout::item_feature( $post_infos );
									ABPTT_Layout::description( $post_infos ); ?>
                            </div>
                            <div>
                                <div class="_divider_xxs"></div>
                                <div class="_fj_between">
									<?php $price = ABPTT_Function::get_min_price( $post_id ); ?>
                                    <span class="price_value">
                                                <?php esc_html_e( 'Min Price :', 'abp-tour-travel' );
	                                                echo $price > 0 ? wp_kses_post( wc_price( $price ) ) : esc_html__( 'Free', 'abp-tour-travel' ); ?>
                                            </span>
									<?php if ( ! empty( $global_order ) ) { ?>
                                        <button type="button" class="_btn_theme_xs select_post" data-post_id="<?php echo esc_attr( $post_id ); ?>"> <?php esc_html_e( 'Book Now', 'abp-tour-travel' ); ?> </button>
									<?php } else { ?>
                                        <button type="button" class="_btn_theme_xs" data-href="<?php echo esc_url( $url ); ?>" data-blank="_blank"> <?php esc_html_e( 'Book Now', 'abp-tour-travel' ); ?> </button>
									<?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
				<?php } ?>
            </div>
			<?php if ( ( $params['pagination'] ?? 'yes' ) !== 'no' ) {
				$args['style'] = $params['pagination_style'] ?? ( $params['pagination-style'] ?? 'live' );
				do_action( 'abptt_pagination', $args );
			} ?>
			<?php
		} else {
			ABPTT_Layout::layout_warning_info( 'not_found' );
		}
	}, 10, 2 );