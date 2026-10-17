<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	add_action( 'abptt_post_filter_template', function ( $params ) {
		if ( ! is_array( $params ) ) {
			return;
		}
		$style      = $params['style'] ?? 'grid';
		$post_ids   = $params['all_post'] ?? [];
		$cat_id     = $params['cat_id'] ?? null;
		$categories = [];
		if ( empty( $cat_id ) && is_array( $post_ids ) && ! empty( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				$category = ABPTT_Function::get_post_info( $post_id, 'abptt_category' );
				if ( ! empty( $category ) ) {
					$categories[] = $category;
				}
			}
			$categories = array_unique( $categories );
		}
		$cat_count = count( $categories );
		if ( $cat_count > 1 || $style === 'grid' || $style === 'list' || $style === 'minimal' ) {
			?>
            <div class="post_top_filter">
				<?php if ( $cat_count > 1 ) {
					if ( is_array( ABPTT_Category ) ) { ?>
                        <label>
                            <select class="_form_control" name="cat_id">
                                <option value="" selected><?php echo esc_html__( 'All ', 'abp-tour-travel' ) . ' ' . esc_html( ABPTT_Function::category_label() ); ?></option>
								<?php foreach ( $categories as $current_cat_id ) {
									$name = ABPTT_Category[ $current_cat_id ]['label'] ?? '';
									if ( $name !== '' ) { ?>
                                        <option value="<?php echo esc_attr( $current_cat_id ); ?>"><?php echo esc_html( $name ); ?></option>
										<?php
									}
								} ?>
                            </select>
                        </label>
					<?php }
				}
					if ( $style === 'grid' || $style === 'list' ) { ?>
                        <div class="_group_content">
                            <button type="button" class="_btn_light_info_xs_fs_h6 grid_view <?php echo esc_attr( $style === 'grid' ? 'abp_active' : '' ); ?>"><span class="fas fa-table-cells"></span></button>
                            <button type="button" class="_btn_light_info_xs_fs_h6 list_view <?php echo esc_attr( $style === 'list' ? 'abp_active' : '' ); ?>"><span class="fas fa-list"></span></button>
                        </div>
					<?php } ?>
            </div>
			<?php
		}
	} );