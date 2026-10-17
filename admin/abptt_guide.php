<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABPTT_Guide' ) ) {
        class ABPTT_Guide {
            public function __construct() {
                add_action( 'abptt_global_guide', array( $this, 'global_guide' ) );
                add_action( 'abptt_post_content', array( $this, 'post_guide' ) );
                add_action( 'wp_ajax_abptt_add_tax_guide', array( $this, 'add_tax_guide' ) );
                add_action( 'wp_ajax_abptt_save_tax_guide', array( $this, 'save_tax_guide' ) );
                add_action( 'wp_ajax_abptt_delete_tax_guide', array( $this, 'delete_tax_guide' ) );
                add_action( 'abptt_guide_update', array( $this, 'update_guide' ) );
            }

            public function global_guide(): void {
                if ( ABPTT_Function::on_off( 'guide' ) ) {
                    $label = ABPTT_Function::guide_label(); ?>
                    <div class="_fj_between">
                        <h5 class="abp"><span class="_mar_r_xs">🎤</span><?php echo esc_html( $label ); ?></h5>
                        <?php ABPTT_Layout::button_global_popup( 'tax_guide', __( 'Add New', 'abp-tour-travel' ) . ' ' . $label ); ?>
                    </div>
                    <?php ABPTT_Layout::info_text( 'abptt_guide' ); ?>
                    <div class="tax_guide _ov_auto_mar_t_xs">
                        <?php $this->guide_list(); ?>
                    </div>
                    <?php
                }
            }

            public function post_guide( $post_infos = [] ): void {
                if ( ABPTT_Function::on_off( 'guide' ) ) {
                    $display=$post_infos['display_guide'] ?? 'on';
                    $display       = $display === 'off' ? 'off' : 'on';
                    $switch_active = $display === 'on' ? 'abp_active' : '';
                    ?>
                    <div class="tab_item" data-tabs="#abptt_guide">
                        <div class="group_setting">
                            <div class="setting_item post_guide">
                                <div class="_fj_between_fa_center">
                                    <label>
                                        <?php ABPTT_Layout::switch_checkbox( 'display_guide', $display ); ?>
                                        <span><?php esc_html_e( 'Active Guide?', 'abp-tour-travel' ); ?></span>
                                    </label>
                                    <div data-collapse="#display_guide" class="<?php echo esc_attr( $switch_active ); ?>">
                                        <div class="_group_content">
                                            <?php ABPTT_Layout::selection_area(); ?>
                                            <?php ABPTT_Layout::button_global_popup( 'tax_guide', __( 'Add New', 'abp-tour-travel' ) . ' ' . ABPTT_Function::guide_label() ); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="_divider_xs"></div>
                                <?php ABPTT_Layout::info_text( 'display_guide' ); ?>
                                <div data-collapse="#display_guide" class="<?php echo esc_attr( $switch_active ); ?>">
                                    <?php ABPTT_Layout::selected_area( 'guide', ( $post_infos['guide'] ?? '' ) ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }

            public function add_tax_guide(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $term_id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
                ob_start();
                $name      = $slug = $des = '';
                $meta      = [];
                $label     = ABPTT_Function::guide_label();
                $btn_label = __( 'Save', 'abp-tour-travel' ) . ' ' . $label;
                $title     = __( 'Add new ', 'abp-tour-travel' ) . ' ' . $label;
                if ( ! empty( $term_id ) ) {
                    $term = get_term( $term_id, 'abptt_guide' );
                    if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
                        $name      = $term->name;
                        $slug      = $term->slug;
                        $des       = $term->description;
                        $btn_label = __( 'Update', 'abp-tour-travel' ) . ' ' . $label . ' ' . $name;
                        $title     = __( 'Edit ', 'abp-tour-travel' ) . ' ' . $label . ' ' . $name;
                        $meta      = get_term_meta( $term_id, '_abptt_guide_meta', true );
                        $meta      = is_array( $meta ) ? $meta : [];
                    }
                }
                ?>
                <div class="abp_form">
                    <h5 class="abp"><span class="_mar_r_xs">🎤</span><?php echo esc_html( $title ); ?></h5>
                    <div class="_divider_xs"></div>
                    <input type="hidden" name="id" value="<?php echo esc_attr( $term_id ); ?>"/>
                    <div class="group_setting">
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Name', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></span>
                                <input class="_form_control" name="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'abp-tour-travel' ); ?>" required/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'guide_name' ); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Slug (Optional)', 'abp-tour-travel' ); ?></span>
                                <input class="_form_control" name="slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'abp-tour-travel' ); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'guide_slug' ); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Bio', 'abp-tour-travel' ); ?></span>
                                <?php
                                    $editor_id = 'abptt_guide_bio_' . $term_id;
                                    wp_editor(
                                        $des,
                                        $editor_id,
                                        array(
                                            'textarea_name' => 'description',
                                            'textarea_rows' => 6,
                                            'media_buttons' => true,
                                            'teeny'         => false,
                                            'quicktags'     => true,
                                        )
                                    );
                                ?>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'guide_bio' ); ?>
                        </div>
                        <div class="setting_item full_width">
                            <span class="abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Photo', 'abp-tour-travel' ); ?></span>
                            <div class="guide_photo_selection">
                                <?php do_action( 'abptt_image_selection', 'meta[photo]', absint( $meta['photo'] ?? 0 ) ); ?>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'guide_photo' ); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Designation', 'abp-tour-travel' ); ?></span>
                                <input class="_form_control" name="meta[designation]" value="<?php echo esc_attr( $meta['designation'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'e.g., CEO, CTO, Professor', 'abp-tour-travel' ); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'guide_designation' ); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Company', 'abp-tour-travel' ); ?></span>
                                <input class="_form_control" name="meta[company]" value="<?php echo esc_attr( $meta['company'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Company/Organization', 'abp-tour-travel' ); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'guide_company' ); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Website', 'abp-tour-travel' ); ?></span>
                                <input type="url" class="_form_control" name="meta[website]" value="<?php echo esc_attr( $meta['website'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'https://example.com', 'abp-tour-travel' ); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'guide_website' ); ?>
                        </div>
                        <div class="setting_item full_width">
                            <strong><?php esc_html_e( 'Social Links', 'abp-tour-travel' ); ?></strong>
                            <div class="_divider_xxs"></div>
                            <?php
                                $social        = $meta['social'] ?? [];
                                $social_fields = [
                                    'twitter'   => [ 'icon' => 'fab fa-twitter', 'label' => 'Twitter/X', 'info' => 'guide_social_twitter' ],
                                    'linkedin'  => [ 'icon' => 'fab fa-linkedin', 'label' => 'LinkedIn', 'info' => 'guide_social_linkedin' ],
                                    'facebook'  => [ 'icon' => 'fab fa-facebook', 'label' => 'Facebook', 'info' => 'guide_social_facebook' ],
                                    'instagram' => [ 'icon' => 'fab fa-instagram', 'label' => 'Instagram', 'info' => 'guide_social_instagram' ],
                                ];
                                foreach ( $social_fields as $key => $info ) { ?>
                                    <div class="setting_item full_width _mar_t_xxs">
                                        <label class="_f_equal_f_wrap">
                                            <span class="abp_label"><i class="<?php echo esc_attr( $info['icon'] ); ?>"></i> <?php echo esc_html( $info['label'] ); ?></span>
                                            <input type="url" class="_form_control" name="meta[social][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $social[ $key ] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Profile URL', 'abp-tour-travel' ); ?>"/>
                                        </label>
                                        <div class="_divider_xxs"></div>
                                        <?php ABPTT_Layout::info_text( $info['info'] ); ?>
                                    </div>
                                <?php } ?>
                        </div>
                    </div>
                    <div class="_divider_xs"></div>
                    <?php ABPTT_Layout::button_global_save( 'tax_guide', $btn_label ); ?>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success( [ 'html' => $html, 'type' => 'success', 'msg' => $label . ' ' . __( 'Form Loaded Successfully .....! ', 'abp-tour-travel' ) ] );
            }

            public function save_tax_guide(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $post_int      = fn( $key, $default = 0 ) => isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
                $post_val      = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
                $post_textarea = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? wp_kses_post( wp_unslash( $_POST[ $key ] ) ) : $default;
                $post_slug     = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_title( wp_unslash( $_POST[ $key ] ) ) : $default;
                $post_meta     = fn( $key, $default = '' ) => isset( $_POST['meta'] ) && is_array( $_POST['meta'] ) && isset( $_POST['meta'][ $key ] )
                    ? sanitize_text_field( wp_unslash( $_POST['meta'][ $key ] ) ) : $default;
                $post_meta_url = fn( $key, $default = '' ) => isset( $_POST['meta'] ) && is_array( $_POST['meta'] ) && isset( $_POST['meta'][ $key ] )
                    ? esc_url_raw( wp_unslash( $_POST['meta'][ $key ] ) ) : $default;
                $post_meta_int = fn( $key, $default = 0 ) => isset( $_POST['meta'] ) && is_array( $_POST['meta'] ) && isset( $_POST['meta'][ $key ] )
                    ? absint( $_POST['meta'][ $key ] ) : $default;
                $post_social   = fn( $key, $default = '' ) => isset( $_POST['meta'] ) && is_array( $_POST['meta'] )
                                                              && isset( $_POST['meta']['social'] ) && is_array( $_POST['meta']['social'] )
                                                              && isset( $_POST['meta']['social'][ $key ] )
                    ? esc_url_raw( wp_unslash( $_POST['meta']['social'][ $key ] ) ) : $default;
                $tax_id        = $post_int( 'id' );
                $name          = $post_val( 'name' );
                $slug          = $post_slug( 'slug' );
                $description   = $post_textarea( 'description' );
                $post_id       = $post_int( 'post_id' );
                $meta          = [
                    'photo'       => $post_meta_int( 'photo' ),
                    'designation' => $post_meta( 'designation' ),
                    'company'     => $post_meta( 'company' ),
                    'website'     => $post_meta_url( 'website' ),
                    'social'      => [],
                ];
                $social_keys   = [ 'twitter', 'linkedin', 'facebook', 'instagram' ];
                foreach ( $social_keys as $key ) {
                    $meta['social'][ $key ] = $post_social( $key );
                }
                if ( empty( $name ) ) {
                    ob_start();
                    if ( empty( $post_id ) || $post_id <= 0 ) {
                        $this->guide_list();
                    }
                    $html = ob_get_clean();
                    wp_send_json_error( [ 'html' => $html, 'type' => 'warn', 'msg' => ABPTT_Function::guide_label() . ' ' . __( 'Name cannot be blank!', 'abp-tour-travel' ) ], 400 );
                }
                if ( $tax_id > 0 ) {
                    $result = wp_update_term( $tax_id, 'abptt_guide', [
                        'name'        => $name,
                        'slug'        => $slug,
                        'description' => $description,
                    ] );
                } else {
                    $result = wp_insert_term( $name, 'abptt_guide', [
                        'slug'        => $slug,
                        'description' => $description,
                    ] );
                }
                if ( is_wp_error( $result ) ) {
                    wp_send_json_error( [ 'html' => '', 'msg' => $result->get_error_message() ] );
                }
                $term_id = absint( $result['term_id'] ?? 0 );
                if ( $term_id <= 0 ) {
                    wp_send_json_error( [ 'html' => '', 'msg' => __( 'Failed to resolve guide context.', 'abp-tour-travel' ), 'type' => 'warn' ] );
                }
                update_term_meta( $term_id, '_abptt_guide_meta', $meta );
                $this->update_guide();
                ob_start();
                if ( empty( $post_id ) || $post_id <= 0 ) {
                    $this->guide_list();
                }
                $html = ob_get_clean();
                wp_send_json_success( [ 'html' => $html, 'type' => 'success', 'js' => ABPTT_Function::option_js( $post_id, 'abptt_guide' ), 'msg' => ABPTT_Function::guide_label() . ' ' . __( 'Saved Successfully !', 'abp-tour-travel' ), ] );
            }

            public function delete_tax_guide(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $tax_id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : '';
                if ( empty( $tax_id ) || ! is_numeric( $tax_id ) ) {
                    ob_start();
                    $this->guide_list();
                    $html = ob_get_clean();
                    wp_send_json_error( [ 'html' => $html, 'msg' => ABPTT_Function::guide_label() . ' ' . __( 'id Invalid...!', 'abp-tour-travel' ), 'type' => 'warn' ] );
                }
                delete_term_meta( $tax_id, '_abptt_guide_meta' );
                $result = wp_delete_term( $tax_id, 'abptt_guide' );
                $this->update_guide();
                ob_start();
                $this->guide_list();
                $html = ob_get_clean();
                if ( is_wp_error( $result ) ) {
                    wp_send_json_error( [ 'html' => $html, 'msg' => $result->get_error_message(), 'type' => 'warn' ] );
                }
                wp_send_json_success( [ 'html' => $html, 'type' => 'success', 'msg' => ABPTT_Function::guide_label() . ' ' . __( 'Deleted Successfully !', 'abp-tour-travel' ) ] );
            }

            public function update_guide(): void {
                $taxonomies = ABPTT_Function::get_taxonomy( 'abptt_guide' );
                $guide      = [];
                if ( ! empty( $taxonomies ) && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
                    foreach ( $taxonomies as $taxonomy ) {
                        $meta                                       = get_term_meta( $taxonomy->term_id, '_abptt_guide_meta', true );
                        $meta                                       = is_array( $meta ) ? $meta : [];
                        $guide[ $taxonomy->term_id ]['label']       = $taxonomy->name;
                        $guide[ $taxonomy->term_id ]['description'] = $taxonomy->description;
                        $guide[ $taxonomy->term_id ]['meta']        = $meta;
                    }
                }
                ksort( $guide );
                update_option( 'abptt_guide', $guide );
            }

            public function guide_list(): void {
                $all_guide = ABPTT_Function::get_option( 'abptt_guide' );
                if ( ! empty( $all_guide ) && is_array( $all_guide ) && sizeof( $all_guide ) > 0 ) {
                    $count = 1; ?>
                    <table class="abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'SI', 'abp-tour-travel' ) ?></th>
                            <th class="_min_150"><?php echo esc_html( ABPTT_Function::guide_label() ); ?></th>
                            <th><?php esc_html_e( 'ID', 'abp-tour-travel' ) ?></th>
                            <th class="_min_150"><?php esc_html_e( 'Designation', 'abp-tour-travel' ); ?></th>
                            <th class="_min_150"><?php esc_html_e( 'Company', 'abp-tour-travel' ); ?></th>
                            <th class="_w_100"><?php esc_html_e( 'Action', 'abp-tour-travel' ) ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $all_guide as $id => $guide ) {
                            $meta      = $guide['meta'] ?? [];
                            $photo_url = '';
                            if ( ! empty( $meta['photo'] ) ) {
                                $photo_url = wp_get_attachment_url( $meta['photo'] );
                            } ?>
                            <tr>
                                <td><?php echo esc_html( $count ++ ); ?></td>
                                <td>
                                    <div class="_gap_xs">
                                        <?php if ( $photo_url ) { ?>
                                            <img src="<?php echo esc_url( $photo_url ); ?>" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;"/>
                                        <?php } ?>
                                        <strong><?php echo esc_html( $guide['label'] ); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo esc_html( $id ); ?></td>
                                <td><?php echo esc_html( $meta['designation'] ?? '' ); ?></td>
                                <td><?php echo esc_html( $meta['company'] ?? '' ); ?></td>
                                <td>
                                    <div class="_group_content">
                                        <button type="button" class="_btn_light_yellow_xxs" onclick="abptt_popup_open_global('tax_guide','<?php echo esc_attr( $id ); ?>')" title="<?php echo esc_attr__( 'Edit : ', 'abp-tour-travel' ) . ' ' . esc_attr( $guide['label'] ); ?>"><?php ABPTT_Static::icon_svg( 'edit' ); ?></button>
                                        <button type="button" class="_btn_light_danger_xxs" onclick="abptt_delete_global('tax_guide','<?php echo esc_attr( $id ); ?>')" title="<?php echo esc_attr__( 'Trash : ', 'abp-tour-travel' ) . ' ' . esc_attr( $guide['label'] ); ?>"><?php ABPTT_Static::icon_svg( 'close_1' ); ?></button>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <?php
                } else {
                    ABPTT_Layout::layout_warning_info( 'no_guide' );
                }
            }
        }
        new ABPTT_Guide();
    }