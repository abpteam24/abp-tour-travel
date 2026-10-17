<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	add_action( 'abptt_related_item_template', function ( $related_item = '', $current_post_id = 0 ) {
		if ( ABPTT_Function::on_off( 'related' ) && ! empty( $related_item ) ) {
			$post_ids = is_array( $related_item ) ? $related_item : explode( ',', (string) $related_item );
			$post_ids = array_values( array_unique( array_filter( array_map( 'absint', $post_ids ) ) ) );
			$post_ids = array_values( array_filter( $post_ids, static function ( $post_id ) use ( $current_post_id ) {
				return $post_id !== absint( $current_post_id ) && get_post_type( $post_id ) === ABPTT_Function::get_cpt() && get_post_status( $post_id ) === 'publish';
			} ) );
			if ( empty( $post_ids ) || ! is_array( $post_ids ) ) {
				return;
			}
			$params['all_post'] = $post_ids;
			$params['related']  = 'yes';
            $brand_icon=ABPTT_Function::icon();
			?>
            <div class="abp_panel related_item_area">
                <div class="_panel_head _fj_between">
                    <h4 class="abp"><?php ABPTT_Layout::image_icon($brand_icon);  ?><?php esc_html_e( 'Related Tour', 'abp-tour-travel' ); ?></h4>
                    <div class="_group_content">
                        <h3 class="related_prev">🔙</h3>
                        <h3 class="related_next">🔜</h3>
                    </div>
                </div>
                <div class="_panel_body_xs ">
					<?php
						include_once ABPTT_Function::template_path( 'list/default.php' );
						do_action( 'abptt_default_template', $params );
					?>
                </div>
            </div>
			<?php
		}
	}, 10, 2 );