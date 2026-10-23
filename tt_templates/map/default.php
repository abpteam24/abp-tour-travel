<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    add_action( 'abptt_map_default_template', function ( $map_locations = [], $post_id = 0, $map_mode = 'js' ) {
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
        <section class="abptt_map abptt_map_default _section">
            <div class="_fj_between_gap_xs">
                <h4 class="abp_gap_xs"><i class="fas fa-route" aria-hidden="true"></i><?php echo esc_html( __( 'Tour', 'abp-tour-travel' ) . ' ' . $title . ' ' . __( 'Route', 'abp-tour-travel' ) ); ?></h4>
                <?php if ( count( $stops ) > 1 ) { ?>
                    <span class="abptt_map_count"><?php echo esc_html( count( $stops ) ); ?><?php echo esc_html( $title ); ?></span>
                <?php } ?>
            </div>
            <div class="_divider_xs"></div>
            <div class="abptt_route_map" data-abptt-stops="<?php echo esc_attr( $stops_json ); ?>">
                <span class="abptt_route_map_load"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i><?php esc_html_e( 'Loading route map...', 'abp-tour-travel' ); ?></span>
            </div>
        </section>
        <?php
    }, 10, 3 );
