<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABPTT_Location' ) ) {
        class ABPTT_Location {
            public function __construct() {
                add_action( 'abptt_global_location', [ $this, 'global_location' ] );
                add_action( 'wp_ajax_abptt_add_tax_location', [ $this, 'add_tax_location' ] );
                add_action( 'wp_ajax_abptt_save_tax_location', [ $this, 'save_tax_location' ] );
                add_action( 'wp_ajax_abptt_delete_tax_location', [ $this, 'delete_tax_location' ] );
                add_action( 'abptt_location_update', [ $this, 'update_location' ] );
                add_action( 'abptt_post_content', [ $this, 'post_places_you_see' ] );
                add_filter( 'abptt_meta_info_update', [ $this, 'save_places_you_see' ], 10, 2 );
            }

            public function global_location(): void {
                $label = ABPTT_Function::location_label(); ?>
                <div class="_fj_between">
                    <h4 class="abp_color_theme_gap_xs"><span class="fas fa-map-marker-alt"></span><?php echo esc_html( __( 'Global', 'abp-tour-travel' ) . ' ' . $label . ' ' . __( 'Configuration', 'abp-tour-travel' ) ); ?></h4>
                    <?php ABPTT_Layout::button_global_popup( 'tax_location', __( 'Add New', 'abp-tour-travel' ) . ' ' . $label ); ?>
                </div>
                <?php ABPTT_Layout::info_text( 'abptt_location' ); ?>
                <div class="_divider_xxs"></div>
                <div class="tax_location _ov_auto">
                    <?php $this->location_list(); ?>
                </div>
                <?php
            }

            public function add_tax_location(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $term_id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : '';
                ob_start();
                $name      = $slug = $des = '';
                $map_data  = [];
                $label     = ABPTT_Function::location_label();
                $btn_label = __( 'Save', 'abp-tour-travel' ) . ' ' . $label;
                $title     = __( 'Add new ', 'abp-tour-travel' ) . ' ' . $label;
                if ( ! empty( $term_id ) && $term_id > 0 ) {
                    $term = get_term( $term_id );
                    if ( ! empty( $term ) ) {
                        $name      = $term->name;
                        $slug      = $term->slug;
                        $des       = $term->description;
                        $btn_label = __( 'Update', 'abp-tour-travel' ) . ' ' . $label . ' ' . $name;
                        $title     = __( 'Edit ', 'abp-tour-travel' ) . ' ' . $label . ' ' . $name;
                        $map_data  = get_term_meta( $term_id, '_abptt_map', true );
                        $map_data  = is_array( $map_data ) ? $map_data : [];
                    }
                }
                $google_map_key = ABPTT_Function::get_options( 'abptt_configuration', 'google_map_key', '' );
                $show_map       = ABPTT_Function::on_off( 'google_map' ) && ! empty( $google_map_key );
                ?>
                <div class="abp_form">
                    <h5 class="abp_color_theme_gap_xs">📍<?php echo esc_html( $title ); ?></h5>
                    <div class="_divider_xs"></div>
                    <input type="hidden" name="id" value="<?php echo esc_attr( $term_id ); ?>"/>
                    <div class="group_setting">
                        <div class="setting_item">
                            <label>
                                <span><?php echo esc_html( $label ); ?><sup class="_color_required">*</sup></span>
                                <input class="_form_control" name="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'abp-tour-travel' ); ?>" required/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'loc_name' ); ?>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php echo esc_html( $label ) . ' ' . esc_html_e( 'Slug (Optional)', 'abp-tour-travel' ); ?></span>
                                <input class="_form_control" name="slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'abp-tour-travel' ); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'loc_slug' ); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php esc_html_e( 'Full Address(optional)', 'abp-tour-travel' ); ?></span>
                                <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e( 'Address', 'abp-tour-travel' ); ?>"><?php echo esc_html( $des ); ?></textarea>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'loc_des' ); ?>
                        </div>
                        <?php if ( $show_map ) {
                            $map_lat     = $map_data['lat'] ?? '';
                            $map_lng     = $map_data['lng'] ?? '';
                            $map_place   = $map_data['place'] ?? '';
                            $map_address = $map_data['address'] ?? '';
                            ?>
                            <div class="setting_item full_width abptt_gmap_wrap <?php echo esc_attr( ! empty( $map_lat ) && ! empty( $map_lng ) ? 'abp_active' : '' ); ?>">
                                <span class="abp_label"><span class="fas fa-map-marker-alt"></span> <?php esc_html_e( 'Set Location On Google Map', 'abp-tour-travel' ); ?></span>
                                <div class="_divider_xxs"></div>
                                <label class="_f_equal_f_wrap">
                                    <input type="text" class="_form_control abptt_gmap_search" placeholder="<?php esc_attr_e( 'Search address / place on the map', 'abp-tour-travel' ); ?>" autocomplete="off"/>
                                </label>
                                <div class="abptt_gmap_canvas"></div>
                                <div class="_group_content">
                                    <input type="hidden" name="map_lat" class="abptt_gmap_lat" value="<?php echo esc_attr( $map_lat ); ?>"/>
                                    <input type="hidden" name="map_lng" class="abptt_gmap_lng" value="<?php echo esc_attr( $map_lng ); ?>"/>
                                    <input type="hidden" name="map_place" class="abptt_gmap_place" value="<?php echo esc_attr( $map_place ); ?>"/>
                                    <input type="hidden" name="map_address" class="abptt_gmap_address" value="<?php echo esc_attr( $map_address ); ?>"/>
                                    <span class="abptt_gmap_show_name"></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <?php ABPTT_Layout::button_global_save( 'tax_location', $btn_label ); ?>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success( [ 'html' => $html, 'type' => 'success', 'msg' => $label . ' ' . __( 'Form Loaded Successfully .....! ', 'abp-tour-travel' ) ] );
            }

            public function save_tax_location(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $post_int    = fn( $key, $default = 0 ) => isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
                $post_val    = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
                $post_slug   = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_title( wp_unslash( $_POST[ $key ] ) ) : $default;
                $tax_id      = $post_int( 'id' );
                $name        = $post_val( 'name' );
                $slug        = $post_slug( 'slug' );
                $description = $post_val( 'description' );
                $post_id     = $post_int( 'post_id' );
                if ( empty( $name ) ) {
                    ob_start();
                    $html = '';
                    if ( empty( $post_id ) || $post_id <= 0 ) {
                        ob_start();
                        $this->location_list();
                        $html = ob_get_clean();
                    }
                    wp_send_json_error( [ 'html' => $html, 'msg' => ABPTT_Function::location_label() . ' ' . __( 'Name cannot be blank!', 'abp-tour-travel' ), 'type' => 'warn' ] );
                }
                if ( $tax_id > 0 ) {
                    $result = wp_update_term( $tax_id, 'abptt_location', [
                        'name'        => $name,
                        'slug'        => $slug,
                        'description' => $description,
                    ] );
                } else {
                    $result = wp_insert_term( $name, 'abptt_location', [
                        'slug'        => $slug,
                        'description' => $description,
                    ] );
                }
                if ( is_wp_error( $result ) ) {
                    wp_send_json_error( [ 'html' => '', 'msg' => $result->get_error_message() ] );
                }
                $term_id = absint( $result['term_id'] ?? 0 );
                if ( $term_id <= 0 ) {
                    wp_send_json_error( [ 'html' => '', 'msg' => __( 'Failed to resolve location context.', 'abp-tour-travel' ), 'type' => 'warn' ] );
                }
                $map_lat     = $post_val( 'map_lat' );
                $map_lng     = $post_val( 'map_lng' );
                $map_place   = $post_val( 'map_place' );
                $map_address = $post_val( 'map_address' );
                if ( ABPTT_Function::on_off( 'google_map' ) && ! empty( $map_lat ) && ! empty( $map_lng ) ) {
                    update_term_meta( $term_id, '_abptt_map', [
                        'lat'     => $map_lat,
                        'lng'     => $map_lng,
                        'place'   => $map_place,
                        'address' => $map_address,
                    ] );
                } else {
                    delete_term_meta( $term_id, '_abptt_map' );
                }
                $this->update_location();
                ob_start();
                $html = '';
                if ( empty( $post_id ) || $post_id <= 0 ) {
                    ob_start();
                    $this->location_list();
                    $html = ob_get_clean();
                }
                wp_send_json_success( [
                    'html' => $html,
                    'type' => 'success',
                    'msg'  => ABPTT_Function::location_label() . ' ' . __( 'Saved Successfully !', 'abp-tour-travel' ),
                    'js'   => ABPTT_Function::option_js( $post_id, 'abptt_location' ),
                ] );
            }

            public function delete_tax_location(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $tax_id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : '';
                if ( empty( $tax_id ) || ! is_numeric( $tax_id ) ) {
                    ob_start();
                    $this->location_list();
                    $html = ob_get_clean();
                    wp_send_json_error( [ 'html' => $html, 'msg' => ABPTT_Function::location_label() . ' ' . __( 'id Invalid...!', 'abp-tour-travel' ), 'type' => 'warn' ] );
                }
                $result = wp_delete_term( $tax_id, 'abptt_location' );
                $this->update_location();
                ob_start();
                $this->location_list();
                $html = ob_get_clean();
                if ( is_wp_error( $result ) ) {
                    wp_send_json_error( [ 'html' => $html, 'msg' => $result->get_error_message(), 'type' => 'warn' ] );
                }
                wp_send_json_success( [ 'html' => $html, 'type' => 'success', 'msg' => ABPTT_Function::location_label() . ' ' . __( 'Deleted Successfully !', 'abp-tour-travel' ) ] );
            }

            public function update_location(): void {
                $taxonomies = ABPTT_Function::get_taxonomy( 'abptt_location' );
                $taxonomies = is_array( $taxonomies ) ? $taxonomies : [];
                $location   = [];
                if ( count( $taxonomies ) > 0 ) {
                    foreach ( $taxonomies as $taxonomy ) {
                        $term_id                             = $taxonomy->term_id;
                        $location[ $term_id ]['label']       = $taxonomy->name;
                        $location[ $term_id ]['description'] = $taxonomy->description;
                        $location[ $term_id ]['slug']        = $taxonomy->slug;
                        $map                                 = get_term_meta( $term_id, '_abptt_map', true );
                        if ( is_array( $map ) && ! empty( $map ) ) {
                            $location[ $term_id ]['map'] = $map;
                        }
                    }
                }
                ksort( $location );
                update_option( 'abptt_location', $location );
            }

            public function location_list(): void {
                $options = ABPTT_Function::get_option( 'abptt_location' );
                $options = is_array( $options ) ? $options : [];
                $count   = 1;
                if ( count( $options ) > 0 ) { ?>
                    <table class="abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'SI', 'abp-tour-travel' ) ?></th>
                            <th><?php esc_html_e( 'ID', 'abp-tour-travel' ) ?></th>
                            <th class="_min_150"><?php echo esc_html( ABPTT_Function::location_label() ); ?></th>
                            <th><?php esc_html_e( 'Full Address', 'abp-tour-travel' ) ?></th>
                            <th><?php esc_html_e( 'Shortcode Post', 'abp-tour-travel' ) ?></th>
                            <th><?php esc_html_e( 'Action', 'abp-tour-travel' ) ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $options as $term_id => $option ) {
                            $name = $option['label'] ?? $option['name'] ?? ''; ?>
                            <tr>
                                <th><?php echo esc_html( $count ); ?>.</th>
                                <th><?php echo esc_html( $term_id ); ?></th>
                                <th class="_text_left"><a href="<?php echo esc_url( get_term_link( (int) $term_id ) ); ?>" target="_blank" class="abp_fs_h5_color_theme"><?php echo esc_html( $name ); ?></a></th>
                                <td><?php echo esc_html( $option['description'] ?? '' ); ?></td>
                                <th class="_text_nowrap"><code> [abptt-post loc_id="<?php echo esc_attr( $term_id ); ?>"]</code></th>
                                <td>
                                    <div class="_fj_center">
                                        <div class="_group_content">
                                            <button type="button" class="_btn_light_yellow_xxs" onclick="abptt_popup_open_global('tax_location','<?php echo esc_attr( $term_id ); ?>')" title="<?php echo esc_attr__( 'Edit : ', 'abp-tour-travel' ) . ' ' . esc_attr( $name ); ?>"><?php ABPTT_Static::svg( 'edit' ); ?></button>
                                            <button type="button" class="_btn_light_danger_xxs" onclick="abptt_delete_global('tax_location','<?php echo esc_attr( $term_id ); ?>')" title="<?php echo esc_attr__( 'Trash : ', 'abp-tour-travel' ) . ' ' . esc_attr( $name ); ?>"><?php ABPTT_Static::svg( 'close_2' ); ?></button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php $count ++;
                        } ?>
                        </tbody>
                    </table>
                <?php } else {
                    ABPTT_Layout::layout_warning_info( 'no_location' );
                }
            }

            public function post_places_you_see( $post_infos = [] ): void {
                if ( ! ABPTT_Function::on_off( 'places_you_see' ) ) {
                    return;
                }
                $post_id  = absint( $post_infos['post_id'] ?? 0 );
                $display  = $post_infos['display_places_you_see'] ?? '';
                $display  = $display === 'off' ? 'off' : 'on';
                $places   = ABPTT_Function::get_places_you_see( $post_id );
                $selected = [];
                foreach ( $places as $place ) {
                    if ( $place['place_id'] > 0 ) {
                        $selected[] = (string) $place['place_id'];
                    }
                }
                ?>
                <div class="tab_item configuration_content abptt_place" data-tabs="#abptt_places_you_see">
                    <h5 class="abp_color_theme_gap_xs">📍<?php echo esc_html( ABPTT_Function::label_place_you_see() ); ?> <?php esc_html_e( 'Configuration', 'abp-tour-travel' ); ?></h5>
                    <?php ABPTT_Layout::info_text( 'place_you_see_item' ); ?>
                    <div class="_divider_xxs"></div>
                    <div class="setting_item">
                        <div class="_fj_between_fa_center">
                            <label>
                                <?php ABPTT_Layout::switch_checkbox( 'display_places_you_see', $display ); ?>
                                <span><?php esc_html_e( 'Active Places You\'ll See ?', 'abp-tour-travel' ); ?></span>
                            </label>
                            <div data-collapse="#display_places_you_see" class=" _mar_t_xs <?php echo esc_attr( $display == 'on' ? 'abp_active' : '' ); ?>">
                                <div class="_group_content">
                                    <?php ABPTT_Layout::selection_area(); ?>
                                    <?php ABPTT_Layout::button_global_popup( 'tax_location', __( 'Add New', 'abp-tour-travel' ) . ' ' . ABPTT_Function::location_label() ); ?>
                                </div>
                            </div>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php ABPTT_Layout::info_text( 'display_places_you_see' ); ?>
                        <div data-collapse="#display_places_you_see" class=" _mar_t_xs <?php echo esc_attr( $display == 'on' ? 'abp_active' : '' ); ?>">
                            <?php ABPTT_Layout::selected_area( 'abptt_place', implode( ',', $selected ) ); ?>
                        </div>
                    </div>
                    <div data-collapse="#display_places_you_see" class=" _mar_t_xs <?php echo esc_attr( $display == 'on' ? 'abp_active' : '' ); ?>">

                        <div class="insertable_area sortable_area place_you_see_list">
                            <?php
                                foreach ( $places as $place ) {
                                    $this->place_you_see_item( $place );
                                }
                            ?>
                        </div>
                        <div class="abp_hidden">
                            <div class="hidden_content">
                                <?php $this->place_you_see_item(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }

            public function place_you_see_item( $place = [] ): void {
                $place_id    = absint( $place['place_id'] ?? 0 );
                $name        = trim( (string) ( $place['name'] ?? '' ) );
                $name        = $name ?: __( 'NEW PLACE', 'abp-tour-travel' );
                $description = (string) ( $place['des'] ?? '' );
                $editor_id   = uniqid( 'abptt_place_editor_' );
                ?>
                <div class="delete_area place_you_see_item <?php echo esc_attr( empty( $place ) ? 'active' : '' ); ?>" data-place-id="<?php echo esc_attr( $place_id ); ?>">
                    <div class="place_you_see_head">
                        <h6 class="abp edit_hook"><?php echo esc_html( $name ); ?></h6>
                        <?php ABPTT_Layout::button_delete_sort_edit(); ?>
                    </div>
                    <div class="edit_area">
                        <div class="place_you_see_body">
                            <input type="hidden" name="place_you_see_id[]" value="<?php echo esc_attr( $place_id ); ?>"/>
                            <?php
                                wp_editor(
                                    $description,
                                    $editor_id,
                                    array(
                                        'textarea_name' => 'place_you_see_des[]',
                                        'textarea_rows' => 5,
                                        'media_buttons' => true,
                                        'teeny'         => false,
                                        'quicktags'     => true
                                    )
                                );
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
            public function save_places_you_see( array $meta_info, $post_id ): array {
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
                if ( get_post_type( $post_id ) !== ABPTT_Function::get_cpt() ) {
                    return $meta_info;
                }
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
                $display = isset( $_POST['display_places_you_see'] ) ? sanitize_text_field( wp_unslash( $_POST['display_places_you_see'] ) ) : 'on';
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
                if ( isset( $_POST['place_you_see_id'] ) && is_array( $_POST['place_you_see_id'] ) ) {
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
                    $ids = array_map( 'absint', wp_unslash( $_POST['place_you_see_id'] ) );
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
                    $names = ( isset( $_POST['place_you_see_name'] ) && is_array( $_POST['place_you_see_name'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['place_you_see_name'] ) ) : [];
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
                    $descriptions = ( isset( $_POST['place_you_see_des'] ) && is_array( $_POST['place_you_see_des'] ) ) ? array_map( 'wp_kses_post', wp_unslash( $_POST['place_you_see_des'] ) ) : [];
                    $places       = [];
                    foreach ( $ids as $key => $place_id ) {
                        $place_id = absint( $place_id );
                        $name     = trim( (string) ( $names[ $key ] ?? '' ) );
                        $term     = $place_id > 0 ? get_term( $place_id, 'abptt_location' ) : null;
                        if ( $place_id > 0 && ! empty( $term ) && ! is_wp_error( $term ) ) {
                            $name = $term->name;
                        } else {
                            // The stored id is missing or its global term was deleted after the
                            // tour was saved - a tour written by an older version, or a location
                            // removed from the global list. Resolve by name so that saving a tour
                            // never silently drops a place or its description.
                            $place_id = 0;
                            if ( $name === '' ) {
                                continue;
                            }
                            $found = get_term_by( 'name', $name, 'abptt_location' );
                            if ( ! empty( $found ) && ! is_wp_error( $found ) ) {
                                $place_id = (int) $found->term_id;
                                $name     = $found->name;
                            } else {
                                $created = wp_insert_term( $name, 'abptt_location' );
                                if ( is_wp_error( $created ) ) {
                                    continue;
                                }
                                $place_id = (int) $created['term_id'];
                            }
                        }
                        $places[] = [
                            'place_id' => $place_id,
                            'name'     => $name,
                            'des'      => trim( (string) ( $descriptions[ $key ] ?? '' ) ),
                        ];
                    }
                    $meta_info['abptt_places_you_see'] = array_values( $places );
                    $term_ids                          = wp_list_pluck( $places, 'place_id' );
                    wp_set_object_terms( $post_id, $term_ids, 'abptt_location', false );
                    // Mirrors the assigned location terms into post meta so the details-page
                    // map, the location display and the location filter all have data to read.
                    $meta_info['abptt_location'] = implode( ',', $term_ids );
                }
                $meta_info['display_places_you_see'] = $display;

                return $meta_info;
            }
        }
        new ABPTT_Location();
    }