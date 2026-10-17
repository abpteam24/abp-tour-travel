<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptt_map_modern_template', function ( $map_locations = [], $post_id = 0, $map_mode = 'js' ) {
		if ( empty( $map_locations ) || ! is_array( $map_locations ) ) {
			return;
		}
		$title = ABPTT_Function::location_label();
		$stops = [];
		foreach ( $map_locations as $map_loc ) {
			$stops[] = [
				'lat'     => (float) ( $map_loc['lat'] ?? 0 ),
				'lng'     => (float) ( $map_loc['lng'] ?? 0 ),
				'label'   => (string) ( $map_loc['label'] ?? '' ),
				'address' => (string) ( $map_loc['address'] ?? '' ),
			];
		}
		$stops      = array_values( array_filter( $stops, static function ( $s ) {
			return $s['lat'] && $s['lng'];
		} ) );
		$stops_json = wp_json_encode( $stops );
		$dir_url    = ABPTT_Function::route_directions_url( $stops );
		?>
        <section class="abptt_map abptt_map_modern">
            <div class="abptt_map_modern_head">
                <span class="abptt_map_modern_icon"><i class="fas fa-route" aria-hidden="true"></i></span>
                <div class="abptt_map_modern_heading">
                    <span class="abptt_map_eyebrow"><?php esc_html_e( 'Journey', 'abp-tour-travel' ); ?></span>
                    <h4><?php echo esc_html( $title . ' ' . __( 'Route', 'abp-tour-travel' ) ); ?></h4>
                </div>
                <?php if ( $dir_url ) { ?>
                    <a class="abptt_map_modern_dir" href="<?php echo esc_url( $dir_url ); ?>" target="_blank" rel="noopener">
                        <span class="fas fa-diamond-turn-right" aria-hidden="true"></span><?php esc_html_e( 'Directions', 'abp-tour-travel' ); ?>
                    </a>
                <?php } ?>
            </div>
            <div class="abptt_map_modern_body">
                <div class="abptt_route_map" data-abptt-stops="<?php echo esc_attr( $stops_json ); ?>">
                    <span class="abptt_route_map_load"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i><?php esc_html_e( 'Loading route map...', 'abp-tour-travel' ); ?></span>
                </div>
                <?php if ( ! empty( $stops ) ) { ?>
                    <div class="abptt_map_stop_list">
                        <?php foreach ( $stops as $i => $stop ) { ?>
                            <div class="abptt_map_modern_item">
                                <span class="abptt_map_stop_no"><?php echo esc_html( $i + 1 ); ?></span>
                                <span class="abptt_map_modern_caption_text">
                                    <strong><?php echo esc_html( $stop['label'] ); ?></strong>
                                    <?php if ( ! empty( $stop['address'] ) ) { ?>
                                        <span><?php echo esc_html( $stop['address'] ); ?></span>
                                    <?php } ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </section>
		<?php
	}, 10, 3 );
