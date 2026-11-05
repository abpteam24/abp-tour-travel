<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	add_action( 'abptt_post_filter_template', function ( $params ) {
		if ( ! is_array( $params ) ) {
			return;
		}
		$style    = $params['style'] ?? 'grid';
		$post_ids = $params['all_post'] ?? [];

		/**
		 * Taxonomy filter definitions: post meta key, shortcode attribute that pins
		 * a single term, global ON/OFF key and the term label map.
		 */
		$taxonomies = [
			'category'  => [
				'meta'     => 'abptt_category',
				'request'  => 'cat_id',
				'constant' => defined( 'ABPTT_Category' ) ? ABPTT_Category : [],
				'label'    => ABPTT_Function::category_label(),
			],
			'location'  => [
				'meta'     => 'abptt_location',
				'request'  => 'loc_id',
				'constant' => defined( 'ABPTT_Location' ) ? ABPTT_Location : [],
				'label'    => ABPTT_Function::location_label(),
			],
			'organizer' => [
				'meta'     => 'abptt_organizer',
				'request'  => 'organizer_id',
				'constant' => defined( 'ABPTT_Organizer' ) ? ABPTT_Organizer : [],
				'label'    => ABPTT_Function::organizer_label(),
			],
			'guide'     => [
				'meta'     => 'guide',
				'request'  => 'guide_id',
				'constant' => defined( 'ABPTT_Guide' ) ? ABPTT_Guide : [],
				'label'    => ABPTT_Function::guide_label(),
			],
		];

		// The shortcode `filter` attribute decides which dropdowns may render;
		// the global Feature ON/OFF switches can always remove one.
		$filter_keys = $params['filter_taxonomies'] ?? array_keys( $taxonomies );
		$filter_keys = is_array( $filter_keys ) ? array_map( 'sanitize_key', $filter_keys ) : [];

		$lists = [];
		foreach ( $taxonomies as $key => $data ) {
			if ( ! in_array( $key, $filter_keys, true ) || ! ABPTT_Function::on_off( $key ) ) {
				continue;
			}
			// A shortcode that already pins this taxonomy to one term needs no dropdown.
			if ( ! empty( $params[ $data['request'] ] ) ) {
				continue;
			}
			$term_ids = [];
			if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
				foreach ( $post_ids as $post_id ) {
					$value = ABPTT_Function::get_post_info( $post_id, $data['meta'] );
					if ( empty( $value ) ) {
						continue;
					}
					foreach ( explode( ',', (string) $value ) as $term_id ) {
						$term_id = trim( $term_id );
						if ( '' !== $term_id ) {
							$term_ids[] = $term_id;
						}
					}
				}
			}
			$lists[ $key ] = array_values( array_unique( $term_ids ) );
		}

		$has_select = false;
		foreach ( $lists as $term_ids ) {
			if ( count( $term_ids ) > 1 ) {
				$has_select = true;
				break;
			}
		}
		$show_toggle = $style === 'grid' || $style === 'list';

		// Nothing to filter and no view switch means there is no bar to render.
		if ( ! $has_select && ! $show_toggle ) {
			return;
		}
		?>
        <div class="post_top_filter">
			<?php
				foreach ( $taxonomies as $key => $data ) {
					$term_ids = $lists[ $key ] ?? [];
					if ( count( $term_ids ) <= 1 || ! is_array( $data['constant'] ) || empty( $data['constant'] ) ) {
						continue;
					} ?>
                    <label>
                        <select class="_form_control abptt_tax_filter" name="<?php echo esc_attr( $data['request'] ); ?>" aria-label="<?php echo esc_attr( $data['label'] ); ?>">
                            <option value="" selected><?php
								/* translators: %s: taxonomy label, for example "Category". */
								echo esc_html( sprintf( __( 'All %s', 'abp-tour-travel' ), $data['label'] ) );
							?></option>
							<?php foreach ( $term_ids as $current_term_id ) {
								$name = $data['constant'][ $current_term_id ]['label'] ?? '';
								if ( '' === $name ) {
									continue;
								} ?>
                                <option value="<?php echo esc_attr( $current_term_id ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php } ?>
                        </select>
                    </label>
					<?php
				}
				if ( $show_toggle ) { ?>
                    <div class="_group_content">
                        <button type="button" class="_btn_light_info_xs_fs_h6 grid_view <?php echo esc_attr( $style === 'grid' ? 'abp_active' : '' ); ?>" aria-label="<?php esc_attr_e( 'Grid view', 'abp-tour-travel' ); ?>" aria-pressed="<?php echo esc_attr( $style === 'grid' ? 'true' : 'false' ); ?>"><span class="fas fa-table-cells" aria-hidden="true"></span></button>
                        <button type="button" class="_btn_light_info_xs_fs_h6 list_view <?php echo esc_attr( $style === 'list' ? 'abp_active' : '' ); ?>" aria-label="<?php esc_attr_e( 'List view', 'abp-tour-travel' ); ?>" aria-pressed="<?php echo esc_attr( $style === 'list' ? 'true' : 'false' ); ?>"><span class="fas fa-list" aria-hidden="true"></span></button>
                    </div>
				<?php } ?>
        </div>
		<?php
	} );
