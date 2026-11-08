<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABPTT_Post' ) ) {
        class ABPTT_Post {
            public function __construct() {
                add_action( 'abptt_load_posts', array( $this, 'load_posts' ) );
                add_action( 'add_meta_boxes', [ $this, 'settings_meta' ] );
                add_action( 'save_post', array( $this, 'save_settings' ) );
                add_action( 'admin_notices', array( $this, 'configuration_notice' ) );
                add_action( 'wp_ajax_abptt_post_permanent_remove', array( $this, 'post_permanent_remove' ) );
                add_action( 'wp_ajax_abptt_post_move_trash', array( $this, 'post_move_trash' ) );
                add_action( 'wp_ajax_abptt_post_restore', array( $this, 'post_restore' ) );
                add_action( 'wp_ajax_abptt_reload_post_list', array( $this, 'reload_post_list' ) );
            }

            public function load_posts( $abptt_info = [] ): void {
                $total_posts   = $abptt_info['total_post'] ?? 0;
                $total_publish = $abptt_info['total_publish'] ?? 0;
                $total_draft   = $abptt_info['total_draft'] ?? 0;
                $total_private = $abptt_info['total_private'] ?? 0;
                $total_trash   = $abptt_info['total_trash'] ?? 0;
                $status        = 'publish';
                if ( isset( $_GET['_abptt_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_abptt_nonce'] ) ), 'abptt_url_action' ) ) {
                    $status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'publish';
                }
                $status                = $status ?? 'publish';
                $filter_args['status'] = $status;
                ?>
                <div class="abptt_posts _section_card">
                    <div class="_fj_between_f_wrap">
                        <div class="_group_content">
                            <input type="hidden" name="select_hidden_post_status" value="<?php echo esc_attr( $status ); ?>"/>
                            <button type="button" class="_btn_light_active_xs <?php echo esc_attr( $status == 'all' ? 'abp_active' : '' ); ?>" data-href="<?php echo esc_url( ABPTT_Function::build_url( 'posts', [ 'status' => 'all' ] ) ); ?>"><?php esc_html_e( 'All', 'abp-tour-travel' ); ?> ( <?php echo esc_html( $total_posts ); ?> )</button>
                            <button type="button" class="_btn_light_active_xs <?php echo esc_attr( $status == 'publish' ? 'abp_active' : '' ); ?>" data-href="<?php echo esc_url( ABPTT_Function::build_url( 'posts', [ 'status' => 'publish' ] ) ); ?>"><?php esc_html_e( 'Published', 'abp-tour-travel' ); ?> ( <?php echo esc_html( $total_publish ); ?> )</button>
                            <button type="button" class="_btn_light_active_xs <?php echo esc_attr( $status == 'private' ? 'abp_active' : '' ); ?>" data-href="<?php echo esc_url( ABPTT_Function::build_url( 'posts', [ 'status' => 'private' ] ) ); ?>"><?php esc_html_e( 'Private', 'abp-tour-travel' ); ?> ( <?php echo esc_html( $total_private ); ?> )</button>
                            <button type="button" class="_btn_light_active_xs <?php echo esc_attr( $status == 'draft' ? 'abp_active' : '' ); ?>" data-href="<?php echo esc_url( ABPTT_Function::build_url( 'posts', [ 'status' => 'draft' ] ) ); ?>"><?php esc_html_e( 'Draft', 'abp-tour-travel' ); ?> ( <?php echo esc_html( $total_draft ); ?> )</button>
                            <button type="button" class="_btn_light_active_xs <?php echo esc_attr( $status == 'trash' ? 'abp_active' : '' ); ?>" data-href="<?php echo esc_url( ABPTT_Function::build_url( 'posts', [ 'status' => 'trash' ] ) ); ?>"><?php esc_html_e( 'Trash', 'abp-tour-travel' ); ?> ( <?php echo esc_html( $total_trash ); ?> )</button>
                        </div>
                        <a class="_btn_navy_blue_xs" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . ABPTT_Function::get_cpt() ) ); ?>">
                            <?php ABPTT_Static::svg( 'plus' );
                                esc_html_e( 'Add New Tour', 'abp-tour-travel' ); ?>
                        </a>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class=" post_list">
                        <?php $this->post_table( $filter_args ); ?>
                    </div>
                </div>
                <?php
            }

            public function settings_meta(): void {
                $label      = ABPTT_Function::label();
                $brand_icon = ABPTT_Function::icon();
                $label      = $label . ' ' . __( 'Configuration', 'abp-tour-travel' ) . get_the_title( get_the_id() );
                add_meta_box( 'abptt_configuration', '<span class="' . esc_attr( $brand_icon ?: '' ) . '"></span>' . esc_html( $label ), array( $this, 'settings' ), esc_attr( ABPTT_Function::get_cpt() ), 'normal', 'high' );
            }

            //=============================//
            public function post_table( $filter_args ): void {
                //echo '<pre>';print_r($filter_args);echo '</pre>';
                $status = $filter_args['status'] ?? '';
                if ( empty( $status ) || $status == 'all' ) {
                    $status = [ 'publish', 'draft', 'private', 'trash' ];
                }
                $page_number               = absint( $filter_args['page_number'] ?? 1 ) ?: 1;
                $limit                     = absint( ( $filter_args['page_item'] ?? 0 ) ?: ABPTT_Function::get_option( 'abptt_per_page_item', 20 ) );
                $count                     = ( $page_number - 1 ) * $limit + 1;
                $cpt                       = ABPTT_Function::get_cpt();
                $filters['status']         = $status;
                $filters['posts_per_page'] = $limit;
                $filters['paged']          = $page_number;
                //echo '<pre>';print_r($filters);echo '</pre>';
                $post_ids = ABPTT_Query::get_post_id( $filters );
                if ( ! empty( $post_ids ) && sizeof( $post_ids ) > 0 ) {
                    $total_post   = sizeof( ABPTT_Query::get_post_id( [ 'status' => $status ] ) );
                    $new_post_url = admin_url( 'post-new.php?post_type=' . $cpt );
                    ?>
                    <table class="abp">
                        <thead>
                        <tr>
                            <th class="_w_50"><?php esc_html_e( 'SI', 'abp-tour-travel' ); ?></th>
                            <th class="_w_100"><?php esc_html_e( 'Image', 'abp-tour-travel' ); ?></th>
                            <th><?php echo esc_html( ABPTT_Function::label() ); ?></th>
                            <th><?php esc_html_e( 'Ticket Type & Qty', 'abp-tour-travel' ); ?></th>
                            <th><?php esc_html_e( 'Shortcode', 'abp-tour-travel' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'abp-tour-travel' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                            foreach ( $post_ids as $post_id ) {
                                $post_infos    = ABPTT_Function::get_all_meta( $post_id );
                                $title         = $post_infos['post_title'] ?? '';
                                $seat_type     = $post_infos['seat_type'] ?? 'ticket';
                                $seat_type     = ABPTT_Function::on_off( 'sp' ) ? $seat_type : 'ticket';
                                $edit_link     = get_edit_post_link( $post_id );
                                $sale_continue = $post_infos['sale_continue'] ?? 'on';
                                $post_status   = get_post_status( $post_id );
                                $new_post_url  = add_query_arg( array( 'copy_post' => $post_id, '_abptt_nonce' => wp_create_nonce( 'abptt_copy_post_action' ), ), $new_post_url );
                                ?>
                                <tr>
                                    <th><?php echo esc_html( $count ); ?>.</th>
                                    <td><?php ABPTT_Layout::image( $post_id ); ?></td>
                                    <td>
                                        <div class="_mar_b_xxs">
                                            <?php if ( $post_status == 'trash' ) { ?>
                                                <h6 class="abp_color_warning"><?php ABPTT_Layout::title( $post_infos ); ?></h6>
                                            <?php } else { ?>
                                                <a href="<?php echo esc_url( $edit_link ); ?>" class="abp_fs_h6_color_theme"><?php ABPTT_Layout::title( $post_infos ); ?></a>
                                            <?php } ?>
                                        </div>
                                        <div class="_gap_xxs">
                                            <span class="abp_tag"><?php echo esc_html( __( 'ID : ', 'abp-tour-travel' ) . ' ' . $post_id ); ?></span>
                                            <span class=" abp_tag <?php echo esc_attr( $sale_continue == 'on' ? 'publish' : 'trash' ); ?>">
                                                <?php echo esc_html( $sale_continue == 'on' ? __( 'Sale On', 'abp-tour-travel' ) : __( 'Sale Off', 'abp-tour-travel' ) ); ?>
                                            </span>
                                            <span class="abp_tag <?php echo esc_attr( $post_status ); ?>"><?php echo esc_html( $post_status ); ?></span>
                                        </div>
                                    </td>
                                    <th><?php echo esc_html( ABPTT_Layout::ticket_type( $seat_type ) . ' - ' . ABPTT_Function::get_total_qty( $post_id, $post_infos ) ); ?></th>
                                    <th><code> [abptt-post post_id="<?php echo esc_attr( $post_id ); ?>"]</code></th>
                                    <th>
                                        <div class="_group_content">
                                            <button type="button" class="_btn_light_navy_blue_xxs" data-href="<?php echo esc_url( $new_post_url ); ?>" data-blank="_blank" title="<?php echo esc_html__( 'Copy/Clone : ', 'abp-tour-travel' ) . ' ' . esc_html( $title ); ?>"><?php ABPTT_Static::svg( 'clone_1' ); ?></button>
                                            <?php if ( $post_status == 'trash' ) { ?>
                                                <button type="button" class="_btn_light_success_xxs " onclick="abptt_post_action('restore','<?php echo esc_attr( $post_id ); ?>')" title="<?php echo esc_html__( 'Restore : ', 'abp-tour-travel' ) . ' ' . esc_html( $title ); ?>">♻️</button>
                                                <button type="button" class="_btn_light_danger_xxs" onclick="abptt_post_action('permanent_remove','<?php echo esc_attr( $post_id ); ?>')" title="<?php echo esc_html__( 'Permanent Remove : ', 'abp-tour-travel' ) . ' ' . esc_html( $title ); ?>"><?php ABPTT_Static::svg( 'close_2' ); ?></button>
                                            <?php } else { ?>
                                                <button type="button" class="_btn_light_yellow_xxs" data-href="<?php echo esc_url( $edit_link ); ?>" data-blank="_blank" title="<?php echo esc_html__( 'Edit : ', 'abp-tour-travel' ) . ' ' . esc_html( $title ); ?>"><?php ABPTT_Static::svg( 'edit' ); ?></button>
                                                <button type="button" class="_btn_light_theme_xxs" data-href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" data-blank="_blank" title="<?php echo esc_html__( 'View : ', 'abp-tour-travel' ) . ' ' . esc_html( $title ); ?>"><?php ABPTT_Static::svg( 'view_1' ); ?></button>
                                                <button type="button" class="_btn_light_danger_xxs" onclick="abptt_post_action('move_trash','<?php echo esc_attr( $post_id ); ?>')" title="<?php echo esc_html__( 'Move to Trash : ', 'abp-tour-travel' ) . ' ' . esc_html( $title ); ?>"><?php ABPTT_Static::svg( 'close_1' ); ?></button>
                                            <?php } ?>
                                        </div>
                                    </th>
                                </tr>
                                <?php
                                $count ++;
                            }
                        ?>
                        </tbody>
                    </table>
                    <?php
                    do_action( 'abptt_pagination', [ 'page_item' => $limit, 'page_number' => $page_number, 'total' => $total_post, 'style' => 'ajax' ] );
                } else {
                    ABPTT_Layout::layout_warning_info( 'not_found' );
                }
            }

            public function settings(): void {
                $post_id      = get_the_id();
                $copy_post_id = isset( $_GET['copy_post'] ) ? absint( $_GET['copy_post'] ) : '';
                if ( ! empty( $copy_post_id ) && isset( $_GET['_abptt_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_abptt_nonce'] ) ), 'abptt_copy_post_action' ) && current_user_can( 'edit_post', $copy_post_id ) ) {
                    ?>
                    <input type="hidden" name="abptt_copy_post" value="<?php echo esc_attr( $copy_post_id ); ?>"/>
                    <?php
                    $post_infos['copy_post_id'] = $copy_post_id;
                    $new_post_id                = $copy_post_id;
                } else {
                    $new_post_id = $post_id;
                }
                $post_infos = ABPTT_Function::get_all_meta( $new_post_id );
                wp_nonce_field( 'abptt_post_nonce', 'abptt_post_nonce' );
                ?>
                <div class="abptt_area abptt_admin abp_post_config">
                    <input type="hidden" name="abptt_post_id" value="<?php echo esc_attr( $post_id ); ?>"/>
                    <div class="abp_tabs tab_left">
                        <ul class="abp tab_lists">
                            <li data-tabs-target="#abptt_general"><span class="fas fa-rainbow"></span><?php esc_html_e( 'General', 'abp-tour-travel' ); ?></li>
                            <li data-tabs-target="#abptt_ticket">🎫 <?php esc_html_e( 'Ticket & Price', 'abp-tour-travel' ); ?></li>
                            <li data-tabs-target="#abptt_dates">🗓️<?php esc_html_e( 'Date and Times', 'abp-tour-travel' ); ?></li>
                            <?php if ( ABPTT_Function::on_off( 'additional_info' ) ) { ?>
                                <li data-tabs-target="#abptt_additional_service">💰<?php esc_html_e( 'Additional services', 'abp-tour-travel' ); ?></li>
                            <?php } ?>
                            <?php if ( ABPTT_Function::on_off( 'client_info' ) ) { ?>
                                <li data-tabs-target="#abptt_client_form">📋<?php esc_html_e( 'Traveler Forms', 'abp-tour-travel' ); ?></li>
                            <?php } ?>
                            <?php do_action( 'abptt_post_tab_menu', $post_infos ); ?>
                            <?php if ( ABPTT_Function::on_off( 'feature' ) ) { ?>
                                <li data-tabs-target="#abptt_feature">🔗<?php echo esc_html( ABPTT_Function::feature_label() ); ?></li>
                            <?php } ?>
                            <?php if ( ABPTT_Function::on_off( 'related' ) ) { ?>
                                <li data-tabs-target="#abptt_related">🔗<?php esc_html_e( 'Related Tour', 'abp-tour-travel' ); ?></li>
                            <?php } ?>
                            <?php if ( ABPTT_Function::on_off( 'guide' ) ) { ?>
                                <li data-tabs-target="#abptt_guide">🧑‍✈️<?php esc_html_e( 'Guide', 'abp-tour-travel' ); ?></li>
                            <?php } ?>
                            <li data-tabs-target="#abptt_gallery">🖼️<?php esc_html_e( 'Gallery', 'abp-tour-travel' ); ?></li>
                            <?php if ( ABPTT_Function::on_off( 'places_you_see' ) ) { ?>
                                <li data-tabs-target="#abptt_places_you_see">📍<?php echo esc_html( ABPTT_Function::label_place_you_see() ); ?></li>
                            <?php } ?>
                            <li data-tabs-target="#abptt_tax">🧾<?php esc_html_e( 'Tax', 'abp-tour-travel' ); ?></li>
                            <?php if ( ABPTT_Function::on_off( 'timeline' ) ) { ?>
                                <li data-tabs-target="#abptt_timeline">⏱️<?php esc_html_e( 'Timeline', 'abp-tour-travel' ); ?></li>
                            <?php } ?>
                            <?php if ( ABPTT_Function::on_off( 'faq' ) ) { ?>
                                <li data-tabs-target="#abptt_faq">❓<?php esc_html_e( 'FAQs', 'abp-tour-travel' ); ?></li>
                            <?php } ?>
                            <?php if ( ABPTT_Function::on_off( 'tc' ) ) { ?>
                                <li data-tabs-target="#abptt_tc">🤝<?php esc_html_e( 'Term & Conditions', 'abp-tour-travel' ); ?></li>
                            <?php } ?>
                        </ul>
                        <div class="tab_content">
                            <?php
                                $this->general_configuration( $post_infos );
                                $this->related_configuration( $post_infos );
                                $this->gallery_configuration( $post_infos );
                                $this->tax_configuration( $post_infos );
                                do_action( 'abptt_post_content', $post_infos );
                            ?>
                        </div>
                    </div>
                    <?php ABPTT_Layout::load_admin_globally(); ?>
                </div>
                <?php
            }

            public function general_configuration( $post_infos ): void {
                $abptt_template = $post_infos['abptt_template'] ?? 'default';
                ?>
                <div class="tab_item" data-tabs="#abptt_general">
                    <h4 class="abp_color_theme"><?php esc_html_e( 'General Configuration', 'abp-tour-travel' ); ?></h4>
                    <?php ABPTT_Layout::info_text( 'general_config' ); ?>
                    <div class="_divider_xxs"></div>
                    <div class="group_setting">
                        <div class="setting_item">
                            <label>
                                <?php ABPTT_Layout::switch_checkbox( 'sale_continue', ( $post_infos['sale_continue'] ?? 'on' ) ); ?>
                                <?php esc_html_e( 'Sale continue?', 'abp-tour-travel' ); ?>
                            </label>
                            <div class="_divider_xxs"></div>
                            <?php ABPTT_Layout::info_text( 'sale_continue' ); ?>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php esc_html_e( 'Template', 'abp-tour-travel' ); ?></span>
                                <select class="_form_control " name="abptt_template" required>
                                    <option disabled selected><?php esc_html_e( 'Please Select', 'abp-tour-travel' ); ?></option>
                                    <option value="default" <?php echo esc_attr( $abptt_template == 'default' ? 'selected' : '' ); ?>><?php esc_html_e( 'Default Template', 'abp-tour-travel' ); ?></option>
                                    <option value="light" <?php echo esc_attr( $abptt_template == 'light' ? 'selected' : '' ); ?>><?php esc_html_e( 'Light Template', 'abp-tour-travel' ); ?></option>
                                    <option value="modern" <?php echo esc_attr( $abptt_template == 'modern' ? 'selected' : '' ); ?>><?php esc_html_e( 'Modern Template', 'abp-tour-travel' ); ?></option>
                                </select>
                            </label>
                            <div class="_divider_xxs"></div>
                            <?php ABPTT_Layout::info_text( 'abptt_template' ); ?>
                        </div>
                        <?php if ( ABPTT_Function::on_off( 'sku' ) ) { ?>
                            <div class="setting_item">
                                <div class="_fj_between">
                                    <label>
                                        <?php ABPTT_Layout::switch_checkbox( 'display_sku', ( $post_infos['display_sku'] ?? 'off' ) ); ?>
                                        <?php esc_html_e( 'ID/SKU', 'abp-tour-travel' ); ?>
                                    </label>
                                    <label>
                                        <input class="_form_control" name="post_sku" value="<?php echo esc_attr( $post_infos['post_sku'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Tour ID', 'abp-tour-travel' ); ?>"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'post_sku' ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'post_icon' ) ) { ?>
                            <div class="setting_item">
                                <divl class="_fj_between">
                                    <span class="abp_label"><?php esc_html_e( 'Tour Icon', 'abp-tour-travel' ); ?></span>
                                    <?php do_action( 'abptt_add_icon', 'post_icon', ( $post_infos['post_icon'] ?? '' ) ); ?>
                                </divl>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'post_icon' ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'sub_title' ) ) { ?>
                            <div class="setting_item">
                                <div class="_f_equal_f_wrap">
                                    <span class="abp_label"><?php esc_html_e( 'Sub Title', 'abp-tour-travel' ); ?></span>
                                    <label>
                                        <textarea class="_form_control" name="sub_title" placeholder="<?php esc_attr_e( 'Tour Sub Title', 'abp-tour-travel' ); ?>"><?php echo esc_html( $post_infos['sub_title'] ?? '' ); ?></textarea>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'sub_title' ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'post_des' ) ) { ?>
                            <div class="setting_item">
                                <label class="_f_equal_f_wrap">
                                    <span class="abp_label"><?php esc_html_e( 'Short Description', 'abp-tour-travel' ); ?></span>
                                    <textarea class="_form_control" name="post_description" placeholder="<?php esc_attr_e( 'EX: Description', 'abp-tour-travel' ); ?>"><?php echo esc_html( $post_infos['post_description'] ?? '' ); ?></textarea>
                                </label>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'post_description' ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'category' ) ) { ?>
                            <div class="setting_item abptt_category">
                                <div class="_fj_between_fa_center">
                                    <label>
                                        <?php ABPTT_Layout::switch_checkbox( 'display_category', ( $post_infos['display_category'] ?? 'on' ) ); ?>
                                        <?php echo esc_html( ABPTT_Function::category_label() ); ?>
                                    </label>
                                    <div class="_group_content">
                                        <?php ABPTT_Layout::selection_area();
                                            ABPTT_Layout::button_global_popup( 'tax_category', __( 'Add New', 'abp-tour-travel' ) . ' ' . ABPTT_Function::category_label() ); ?>
                                    </div>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'display_category' );
                                    ABPTT_Layout::selected_area( 'abptt_category', ( $post_infos['abptt_category'] ?? '' ) ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'organizer' ) ) { ?>
                            <div class="setting_item abptt_organizer">
                                <div class="_fj_between_fa_center">
                                    <label>
                                        <?php ABPTT_Layout::switch_checkbox( 'display_organizer', ( $post_infos['display_organizer'] ?? 'off' ) ); ?>
                                        <?php echo esc_html( ABPTT_Function::organizer_label() ); ?>
                                    </label>
                                    <div class="_group_content">
                                        <?php ABPTT_Layout::selection_area();
                                            ABPTT_Layout::button_global_popup( 'tax_organizer', __( 'Add New', 'abp-tour-travel' ) . ' ' . ABPTT_Function::organizer_label() ); ?>
                                    </div>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'display_organizer' );
                                    ABPTT_Layout::selected_area( 'abptt_organizer', ( $post_infos['abptt_organizer'] ?? '' ) ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'duration' ) ) { ?>
                            <div class="setting_item">
                                <div class="_fj_between">
                                    <label>
                                        <?php ABPTT_Layout::switch_checkbox( 'abptt_display_duration', ( $post_infos['abptt_display_duration'] ?? 'on' ) ); ?>
                                        <?php esc_html_e( 'Tour Duration', 'abp-tour-travel' ); ?>
                                    </label>
                                    <label class="_group_content">
                                        <input class="_form_control" type="number" min="0" name="abptt_duration" value="<?php echo esc_attr( $post_infos['abptt_duration'] ?? '' ); ?>" placeholder="Ex: 3"/>
                                        <select class="_form_control" name="abptt_duration_type">
                                            <?php $abptt_duration_type = $post_infos['abptt_duration_type'] ?? 'day'; ?>
                                            <option value="day" <?php echo esc_attr( $abptt_duration_type == 'day' ? 'selected' : '' ); ?>><?php esc_html_e( 'Days', 'abp-tour-travel' ); ?></option>
                                            <option value="hour" <?php echo esc_attr( $abptt_duration_type == 'hour' ? 'selected' : '' ); ?>><?php esc_html_e( 'Hours', 'abp-tour-travel' ); ?></option>
                                            <option value="min" <?php echo esc_attr( $abptt_duration_type == 'min' ? 'selected' : '' ); ?>><?php esc_html_e( 'Minutes', 'abp-tour-travel' ); ?></option>
                                        </select>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'abptt_duration' ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'stay_night' ) ) { ?>
                            <div class="setting_item">
                                <div class="_fj_between">
                                    <label>
                                        <?php ABPTT_Layout::switch_checkbox( 'abptt_display_duration_night', ( $post_infos['abptt_display_duration_night'] ?? 'off' ) ); ?>
                                        <?php esc_html_e( 'Stay Night', 'abp-tour-travel' ); ?>
                                    </label>
                                    <label>
                                        <input class="_form_control" type="number" min="0" name="abptt_duration_night" value="<?php echo esc_attr( $post_infos['abptt_duration_night'] ?? '' ); ?>" placeholder="Ex: 3"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'abptt_duration_night' ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'display_capacity' ) ) { ?>
                            <div class="setting_item">
                                <label>
                                    <?php ABPTT_Layout::switch_checkbox( 'display_capacity', ( $post_infos['display_capacity'] ?? 'off' ) ); ?>
                                    <?php esc_html_e( 'Display Capacity', 'abp-tour-travel' ); ?>
                                </label>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'display_capacity' ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'max_people' ) ) { ?>
                            <div class="setting_item">
                                <div class="_fj_between">
                                    <label>
                                        <?php ABPTT_Layout::switch_checkbox( 'abptt_display_max_people', ( $post_infos['abptt_display_max_people'] ?? 'on' ) ); ?>
                                        <?php esc_html_e( 'Max Guests', 'abp-tour-travel' ); ?>
                                    </label>
                                    <label>
                                        <input class="_form_control" type="number" min="0" name="abptt_max_people" value="<?php echo esc_attr( $post_infos['abptt_max_people'] ?? '' ); ?>" placeholder="Ex: 50"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'abptt_max_people' ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'min_age' ) ) { ?>
                            <div class="setting_item">
                                <div class="_fj_between">
                                    <label>
                                        <?php ABPTT_Layout::switch_checkbox( 'abptt_display_min_age', ( $post_infos['abptt_display_min_age'] ?? 'on' ) ); ?>
                                        <?php esc_html_e( 'Age Range', 'abp-tour-travel' ); ?>
                                    </label>
                                    <label>
                                        <input class="_form_control" name="abptt_min_age" value="<?php echo esc_attr( $post_infos['abptt_min_age'] ?? '' ); ?>" placeholder="Ex: 5 - 50 Years"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'abptt_min_age' ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'start_place' ) ) { ?>
                            <div class="setting_item">
                                <div class="_fj_between">
                                    <label>
                                        <?php ABPTT_Layout::switch_checkbox( 'abptt_display_start_place', ( $post_infos['abptt_display_start_place'] ?? 'on' ) ); ?>
                                        <?php esc_html_e( 'Start Point', 'abp-tour-travel' ); ?>
                                    </label>
                                    <label>
                                        <input class="_form_control" name="abptt_start_place" value="<?php echo esc_attr( $post_infos['abptt_start_place'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Type Start Point...', 'abp-tour-travel' ); ?>"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'abptt_start_place' ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'top_pick' ) ) { ?>
                            <div class="setting_item">
                                <label>
                                    <?php ABPTT_Layout::switch_checkbox( 'abptt_top_pick', ( $post_infos['abptt_top_pick'] ?? 'off' ) ); ?>
                                    <?php esc_html_e( 'Mark as Top Pick', 'abp-tour-travel' ); ?>
                                </label>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'abptt_top_pick' ); ?>
                            </div>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'rating' ) ) { ?>
                            <div class="setting_item">
                                <label>
                                    <?php ABPTT_Layout::switch_checkbox( 'abptt_display_rating', ( $post_infos['abptt_display_rating'] ?? 'on' ) ); ?>
                                    <?php esc_html_e( 'Rating & Reviews', 'abp-tour-travel' ); ?>
                                </label>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'abptt_display_rating' ); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }

            public function related_configuration( $post_infos ): void {
                ?>
                <div class="tab_item" data-tabs="#abptt_related">
                    <div class="group_setting">
                        <?php if ( ABPTT_Function::on_off( 'related' ) ) { ?>
                            <div class="setting_item related_item">
                                <div class="_fj_between_fa_center">
                                    <span class="abp_label"><?php esc_html_e( 'Related Tour', 'abp-tour-travel' ); ?></span>
                                    <?php ABPTT_Layout::selection_area(); ?>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'related_item' );
                                    ABPTT_Layout::selected_area( 'related_item', ( $post_infos['related_item'] ?? '' ) ); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }

            public function gallery_configuration( $post_infos ): void {
                ?>
                <div class="tab_item" data-tabs="#abptt_gallery">
                    <span class="abp_label"><?php esc_html_e( 'Gallery', 'abp-tour-travel' ); ?></span>
                    <div class="_divider_xxs"></div>
                    <?php ABPTT_Layout::info_text( 'display_slider' ); ?>
                    <div class="_divider_xxs"></div>
                    <?php do_action( 'abptt_add_image_multiple', 'abptt_slider', ( $post_infos['abptt_slider'] ?? '' ) ); ?>
                </div>
                <?php
            }

            public function tax_configuration( $post_infos = [] ): void {
                $tax_status  = $post_infos['_tax_status'] ?? '';
                $tax_classes = WC_Tax::get_tax_rate_classes();
                $tax_class   = $post_infos['_tax_class'] ?? '';
                ?>
                <div class="tab_item" data-tabs="#abptt_tax">
                    <h5 class="abp_color_theme_gap_xs">🧾<?php esc_html_e( 'Tax Configuration', 'abp-tour-travel' ); ?></h5>
                    <div class="_divider_xxs"></div>
                    <?php if ( get_option( 'woocommerce_calc_taxes' ) != 'yes' ) {
                        ABPTT_Layout::layout_warning_info( 'enable_tax_msg' );
                    } else { ?>
                        <div class="group_setting">
                            <div class="setting_item">
                                <label class="_f_equal_f_wrap">
                                    <span><?php esc_html_e( 'Tax Status', 'abp-tour-travel' ); ?></span>
                                    <select class="_form_control" name="_tax_status">
                                        <option disabled selected><?php esc_html_e( 'Please Select', 'abp-tour-travel' ); ?></option>
                                        <option value="taxable" <?php echo esc_attr( $tax_status == 'taxable' ? 'selected' : '' ); ?>><?php esc_html_e( 'Taxable', 'abp-tour-travel' ); ?></option>
                                        <option value="shipping" <?php echo esc_attr( $tax_status == 'shipping' ? 'selected' : '' ); ?>><?php esc_html_e( 'Shipping only', 'abp-tour-travel' ); ?></option>
                                        <option value="none" <?php echo esc_attr( $tax_status == 'none' ? 'selected' : '' ); ?>><?php esc_html_e( 'None', 'abp-tour-travel' ); ?></option>
                                    </select>
                                </label>
                            </div>
                            <div class="setting_item">
                                <label class="_f_equal_f_wrap">
                                    <span><?php esc_html_e( 'Tax Class', 'abp-tour-travel' ); ?></span>
                                    <select class="_form_control" name="_tax_class">
                                        <option disabled selected><?php esc_html_e( 'Please Select', 'abp-tour-travel' ); ?></option>
                                        <option value="standard" <?php echo esc_attr( $tax_class == 'standard' ? 'selected' : '' ); ?>><?php esc_html_e( 'Standard', 'abp-tour-travel' ); ?></option>
                                        <?php if ( sizeof( $tax_classes ) > 0 ) { ?>
                                            <?php foreach ( $tax_classes as $class ) { ?>
                                                <option value="<?php echo esc_attr( $class->slug ); ?>" <?php echo esc_attr( $tax_class == $class->slug ? 'selected' : '' ); ?>><?php echo esc_html( $class->name ); ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                    <div class="_divider_xs"></div>
                                    <?php ABPTT_Layout::info_text( '_tax_class' ); ?>
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }

            //====================================//
            public function save_settings( $post_id ): void {
                static $validating_status = false;
                if ( ! isset( $_POST['abptt_post_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abptt_post_nonce'] ) ), 'abptt_post_nonce' ) ) {
                    return;
                }
                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                    return;
                }
                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                    return;
                }
                if ( get_post_type( $post_id ) == ABPTT_Function::get_cpt() ) {
                    $post_int            = fn( $key, $default = 0 ) => isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
                    $post_val            = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
                    $post_textarea       = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : $default;
                    $post_html           = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? wp_kses_post( wp_unslash( $_POST[ $key ] ) ) : $default;
                    $post_int_array      = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'absint', wp_unslash( $_POST[ $key ] ) ) : [];
                    $post_array          = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) ) : [];
                    $post_textarea_array = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_textarea_field', wp_unslash( $_POST[ $key ] ) ) : [];
                    $post_deep           = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? map_deep( wp_unslash( $_POST[ $key ] ), 'sanitize_text_field' ) : [];
                    $format_date         = fn( $date ) => $date ? gmdate( 'Y-m-d', strtotime( $date ) ) : '';
                    $post_time           = fn( $time ) => $time && preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) $time ) ? (string) $time : '';
                    $date_infos          = [];
                    $time_info           = [];
                    //$post_html_array     = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'wp_kses_post', wp_unslash( $_POST[ $key ] ) ) : [];
                    /***********************************/
                    $seat_type           = $post_val( 'seat_type' );
                    $display_ticket_type = $post_val( 'display_ticket_type' );
                    $ticket_infos        = [];
                    $sp_infos            = [];
                    $all_ticket_types    = [];
                    if ( $display_ticket_type == 'off' ) {
                        $all_ticket_types[] = 'price';
                    }
                    $ticket_ids = $post_array( 'ticket_name' );
                    $price_data = $post_array( 'ticket_price' );
                    if ( ! empty( $ticket_ids ) ) {
                        foreach ( $ticket_ids as $key => $id ) {
                            if ( ! empty( $id ) ) {
                                if ( $display_ticket_type == 'on' ) {
                                    $all_ticket_types[] = $id;
                                }
                                $ticket_infos[ $id ]['price'] = $price_data[ $key ] ?? 0;
                            }
                        }
                    }
                    if ( $seat_type == 'ticket' ) {
                        $ticket_qty         = $post_array( 'ticket_qty' );
                        $reserve_qty        = $post_array( 'reserve_qty' );
                        $ticket_min_qty     = $post_array( 'ticket_min_qty' );
                        $ticket_max_qty     = $post_array( 'ticket_max_qty' );
                        $ticket_description = $post_textarea_array( 'ticket_description' );
                        if ( ! empty( $ticket_ids ) ) {
                            foreach ( $ticket_ids as $key => $id ) {
                                if ( ! empty( $id ) ) {
                                    if ( $display_ticket_type == 'on' ) {
                                        $all_ticket_types[] = $id;
                                    }
                                    $ticket_infos[ $id ] = [
                                        'qty'         => $ticket_qty[ $key ] ?? 10,
                                        'price'       => $price_data[ $key ] ?? 0,
                                        'reserve'     => $reserve_qty[ $key ] ?? 0,
                                        'min_qty'     => $ticket_min_qty[ $key ] ?? 1,
                                        'max_qty'     => $ticket_max_qty[ $key ] ?? '',
                                        'description' => $ticket_description[ $key ] ?? '',
                                    ];
                                }
                            }
                        }
                    } else {
                        $sp_ids    = $post_int_array( 'sp_id' );
                        $sp_names  = $post_array( 'sp_name' );
                        $all_sp_id = [];
                        if ( ! empty( $sp_ids ) ) {
                            foreach ( $sp_ids as $key => $id ) {
                                if ( ! empty( $id ) ) {
                                    $all_sp_id[] = $id;
                                    $sp_infos[]  = [
                                        'id'   => $id,
                                        'name' => $sp_names[ $key ] ?? '',
                                    ];
                                }
                            }
                        }
                        if ( $display_ticket_type == 'on' ) {
                            $all_ticket_types = ABPTT_Function::get_sp_ticket( $all_sp_id );
                        }
                    }
                    /***********************************/
                    $date_type_options       = array_keys( ABPTT_Dates::date_types() );
                    $date_type               = $post_val( 'date_type', 'particular' );
                    $date_infos['date_type'] = in_array( $date_type, $date_type_options, true ) ? $date_type : 'particular';
                    //=================== Particular Dates ===================//
                    $checkin_dates    = $post_array( 'particular_checkin_date' );
                    $checkin_times    = $post_array( 'particular_checkin_time' );
                    $checkout_dates   = $post_array( 'particular_checkout_date' );
                    $checkout_times   = $post_array( 'particular_checkout_time' );
                    $reg_end_dates    = $post_array( 'particular_reg_end_date' );
                    $reg_end_times    = $post_array( 'particular_reg_end_time' );
                    $particular_dates = [];
                    foreach ( $checkin_dates as $p_key => $checkin_date ) {
                        $checkin_date = $format_date( $checkin_date );
                        if ( ! $checkin_date ) {
                            continue;
                        }
                        $particular_dates[] = [
                            'checkin_date'  => $checkin_date,
                            'checkin_time'  => $post_time( $checkin_times[ $p_key ] ?? '' ),
                            'checkout_date' => $format_date( $checkout_dates[ $p_key ] ?? '' ),
                            'checkout_time' => $post_time( $checkout_times[ $p_key ] ?? '' ),
                            'reg_end_date'  => $format_date( $reg_end_dates[ $p_key ] ?? '' ),
                            'reg_end_time'  => $post_time( $reg_end_times[ $p_key ] ?? '' ),
                        ];
                    }
                    usort( $particular_dates, fn( $a, $b ) => strcmp( $a['checkin_date'], $b['checkin_date'] ) );
                    $date_infos['particular_dates'] = $particular_dates;
                    //=================== Repeated Dates ===================//
                    $repeat_patterns                 = array_keys( ABPTT_Dates::repeat_patterns() );
                    $repeat_pattern                  = $post_val( 'repeat_pattern', '1' );
                    $repeat_after                    = 'custom' === $repeat_pattern ? max( 1, (int) $post_val( 'repeat_after_custom', '1' ) ) : ( in_array( $repeat_pattern, $repeat_patterns, true ) ? (int) $repeat_pattern : 1 );
                    $date_infos['repeat_after']      = (string) $repeat_after;
                    $date_infos['repeat_start_date'] = $format_date( $post_val( 'repeat_start_date' ) );
                    $date_infos['repeat_end_date']   = $format_date( $post_val( 'repeat_end_date' ) );
                    $date_infos['enable_time']       = 'off' === $post_val( 'enable_time', 'on' ) ? 'off' : 'on';
                    $date_rule                       = $post_val( 'date_rule' );
                    $date_infos['date_rule']         = $post_val( 'date_rule' );
                    $date_rule_array                 = $date_rule ? explode( ',', $date_rule ) : [];
                    if ( in_array( 'weekend', $date_rule_array ) ) {
                        $date_infos['weekend'] = $post_val( 'weekend' );
                    }
                    if ( in_array( 'specific_off_dates', $date_rule_array ) ) {
                        $specific_off_dates               = array_filter( $post_array( 'specific_off_dates' ) );
                        $date_infos['specific_off_dates'] = array_unique( array_map( fn( $d ) => gmdate( 'Y-m-d', strtotime( $d ) ), $specific_off_dates ) );
                    }
                    if ( in_array( 'off_date_range', $date_rule_array ) ) {
                        $off_schedules = [];
                        $from_dates    = $post_array( 'abptt_off_from' );
                        $to_dates      = $post_array( 'abptt_off_to' );
                        foreach ( $from_dates as $key => $from_date ) {
                            if ( $from_date && ! empty( $to_dates[ $key ] ) ) {
                                $off_schedules[] = [ 'from' => $from_date, 'to' => $to_dates[ $key ] ];
                            }
                        }
                        $date_infos['off_date_range'] = $off_schedules;
                    }
                    if ( in_array( 'special_on_dates', $date_rule_array ) ) {
                        $special_on_dates = $post_array( 'special_on_dates' );
                        $on_start         = $post_array( 'special_on_time_start' );
                        $on_end           = $post_array( 'special_on_time_end' );
                        $specific_on      = [];
                        foreach ( $special_on_dates as $key => $date ) {
                            if ( $date ) {
                                $specific_on[ $key ] = [ 'date' => $format_date( $date ), 'start' => $on_start[ $key ] ?? '', 'end' => $on_end[ $key ] ?? '' ];
                            }
                        }
                        $date_infos['special_on_dates'] = $specific_on;
                    }
                    /****************/
                    $read_slots                  = function ( $names, $times, $stocks ) {
                        $slots = [];
                        foreach ( (array) $times as $s_key => $time ) {
                            if ( ! preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) $time ) ) {
                                continue;
                            }
                            $stock   = (int) ( $stocks[ $s_key ] ?? 0 );
                            $slots[] = [
                                'name'  => sanitize_text_field( (string) ( $names[ $s_key ] ?? '' ) ),
                                'time'  => (string) $time,
                                'stock' => $stock > 0 ? $stock : '',
                            ];
                        }

                        return $slots;
                    };
                    $time_info['timewise_stock'] = 'on' === $post_val( 'timewise_stock', 'off' ) ? 'on' : 'off';
                    $time_info['time']           = $read_slots( $post_array( 'slot_name' ), $post_array( 'slot_time' ), $post_array( 'slot_stock' ) );
                    $opt_time                    = $post_val( 'operation_time_optional' );
                    $opt_time                    = ! empty( $opt_time ) ? explode( ',', $opt_time ) : [];
                    if ( in_array( 'day_wise_time', $opt_time, true ) ) {
                        $day_names  = $post_deep( 'day_slot_name' );
                        $day_times  = $post_deep( 'day_slot_time' );
                        $day_stocks = $post_deep( 'day_slot_stock' );
                        foreach ( ABPTT_Layout::week_day() as $key => $day ) {
                            $slots = $read_slots( $day_names[ $key ] ?? [], $day_times[ $key ] ?? [], $day_stocks[ $key ] ?? [] );
                            if ( ! empty( $slots ) ) {
                                $time_info['day_time'][ $key ] = $slots;
                            }
                        }
                    }
                    if ( in_array( 'date_wise_time', $opt_time, true ) ) {
                        $date_time_ids = $post_array( 'date_wise_time_id' );
                        $all_dates     = $post_deep( 'date_wise_date' );
                        $date_names    = $post_deep( 'date_slot_name' );
                        $date_times    = $post_deep( 'date_slot_time' );
                        $date_stocks   = $post_deep( 'date_slot_stock' );
                        if ( ! empty( $date_time_ids ) ) {
                            foreach ( $date_time_ids as $time_id ) {
                                $date_wise_dates = array_filter( $all_dates[ $time_id ] ?? [] );
                                $clean_date      = $date_wise_dates ? reset( $date_wise_dates ) : '';
                                $slots           = $read_slots( $date_names[ $time_id ] ?? [], $date_times[ $time_id ] ?? [], $date_stocks[ $time_id ] ?? [] );
                                if ( ! empty( $clean_date ) && ! empty( $slots ) ) {
                                    $time_info['date_times'][ $time_id ] = [ 'date' => $format_date( $clean_date ), 'time' => $slots ];
                                }
                            }
                        }
                    }
                    /***********************************/
                    $display_additional_services = $post_val( 'display_additional_services', 'off' );
                    $active_global_additional    = $display_additional_services == 'on' ? $post_val( 'active_global_additional', 'on' ) : 'off';
                    $additional_services         = ( $active_global_additional == 'on' || $display_additional_services == 'off' ) ? [] : apply_filters( 'abptt_get_additional_array', [] );
                    $display_client_form         = $post_val( 'display_client_form', 'off' );
                    $active_global_form          = $display_client_form == 'on' ? $post_val( 'active_global_form', 'on' ) : 'off';
                    $abptt_form                  = ( $active_global_form == 'on' || $display_client_form == 'off' ) ? [] : apply_filters( 'abptt_get_form_array', [] );
                    $display_faq                 = $post_val( 'display_faq', 'off' );
                    $active_global_faq           = $display_faq == 'on' ? $post_val( 'active_global_faq', 'on' ) : 'off';
                    $abptt_faq                   = ( $active_global_faq == 'on' || $display_faq == 'off' ) ? [] : apply_filters( 'abptt_get_faq_array', [] );
                    $display_tc                  = $post_val( 'display_tc', 'off' );
                    $active_global_tc            = $display_tc == 'on' ? $post_val( 'active_global_tc', 'on' ) : 'off';
                    $abptt_tc                    = ( $active_global_tc == 'on' || $display_tc == 'off' ) ? [] : $post_html( 'tc_content' );
                    $meta_info                   = [
                        'sale_continue'                => $post_val( 'sale_continue', 'on' ),
                        'abptt_template'               => $post_val( 'abptt_template', 'default' ),
                        'display_sku'                  => $post_val( 'display_sku', 'off' ),
                        'post_sku'                     => $post_val( 'post_sku' ),
                        'post_icon'                    => $post_val( 'post_icon' ),
                        'sub_title'                    => $post_textarea( 'sub_title' ),
                        'post_description'             => $post_textarea( 'post_description' ),
                        'display_organizer'            => $post_val( 'display_organizer', 'off' ),
                        'abptt_organizer'              => $post_val( 'abptt_organizer' ),
                        'display_capacity'             => $post_val( 'display_capacity', 'on' ),
                        'abptt_duration'               => $post_int( 'abptt_duration' ),
                        'abptt_duration_type'          => $post_val( 'abptt_duration_type', 'day' ),
                        'abptt_display_duration'       => $post_val( 'abptt_display_duration', 'on' ),
                        'abptt_duration_night'         => $post_int( 'abptt_duration_night' ),
                        'abptt_display_duration_night' => $post_val( 'abptt_display_duration_night', 'off' ),
                        'abptt_max_people'             => $post_int( 'abptt_max_people' ),
                        'abptt_display_max_people'     => $post_val( 'abptt_display_max_people', 'on' ),
                        'abptt_min_age'                => $post_val( 'abptt_min_age' ),
                        'abptt_display_min_age'        => $post_val( 'abptt_display_min_age', 'on' ),
                        'abptt_start_place'            => $post_val( 'abptt_start_place' ),
                        'abptt_display_start_place'    => $post_val( 'abptt_display_start_place', 'on' ),
                        'abptt_top_pick'               => $post_val( 'abptt_top_pick', 'off' ),
                        'abptt_display_rating'         => $post_val( 'abptt_display_rating', 'on' ),
                        'display_category'             => $post_val( 'display_category', 'on' ),
                        'abptt_category'               => $post_val( 'abptt_category' ),
                        'related_item'                 => $post_val( 'related_item' ),
                        'post_feature'                 => $post_val( 'post_feature' ),
                        'post_feature_excluded'        => $post_val( 'post_feature_excluded' ),
                        'display_feature_included'     => isset( $_POST['display_feature_included'] ) ? sanitize_text_field( wp_unslash( $_POST['display_feature_included'] ) ) : 'on',
                        'display_feature_excluded'     => isset( $_POST['display_feature_excluded'] ) ? sanitize_text_field( wp_unslash( $_POST['display_feature_excluded'] ) ) : 'on',
                        'abptt_slider'                 => $post_val( 'abptt_slider' ),
                        //================//
                        'seat_type'                    => $seat_type,
                        'display_ticket_type'          => $display_ticket_type,
                        'min_qty'                      => $post_int( 'min_qty' ),
                        'max_qty'                      => $post_int( 'max_qty' ),
                        'ticket_infos'                 => $ticket_infos,
                        'sp_infos'                     => $sp_infos,
                        'all_ticket_type'              => $all_ticket_types,
                        //================//
                        'abptt_dates'                  => $date_infos,
                        'time_infos'                   => $time_info,
                        'display_additional_services'  => $display_additional_services,
                        'active_global_additional'     => $active_global_additional,
                        'additional_services'          => $additional_services,
                        'display_client_form'          => $display_client_form,
                        'active_global_form'           => $active_global_form,
                        'display_single_form'          => $post_val( 'display_single_form', 'on' ),
                        'abptt_form'                   => $abptt_form,
                        'display_faq'                  => $display_faq,
                        'active_global_faq'            => $active_global_faq,
                        'abptt_faq'                    => $abptt_faq,
                        'display_tc'                   => $display_tc,
                        'active_global_tc'             => $active_global_tc,
                        'abptt_tc'                     => $abptt_tc,
                        //================//
                        'guide'                        => $post_val( 'guide' ),
                        'display_guide'                => isset( $_POST['display_guide'] ) ? sanitize_text_field( wp_unslash( $_POST['display_guide'] ) ) : 'on',
                    ];
                    $validation_errors           = $this->validate_event_configuration(
                        $post_id,
                        $seat_type,
                        $ticket_infos,
                        $sp_infos,
                        $date_infos,
                        $time_info
                    );
                    if ( get_post_status( $post_id ) === 'publish' && ! empty( $validation_errors ) && ! $validating_status ) {
                        $validating_status = true;
                        wp_update_post( [ 'ID' => $post_id, 'post_status' => 'draft' ] );
                        $validating_status = false;
                        set_transient(
                            'abptt_event_configuration_errors_' . get_current_user_id(),
                            $validation_errors,
                            MINUTE_IN_SECONDS
                        );
                    }
                    //=============tax================//
                    if ( get_option( 'woocommerce_calc_taxes' ) == 'yes' ) {
                        $meta_info['_tax_status'] = $post_val( '_tax_status', 'none' );
                        $meta_info['_tax_class']  = $post_val( '_tax_class' );
                    }
                    //=============================//
                    $meta_info = apply_filters( 'abptt_meta_info_update', $meta_info, $post_id );
                    if ( sizeof( $meta_info ) > 0 ) {
                        foreach ( $meta_info as $key => $value ) {
                            update_post_meta( $post_id, sanitize_key( $key ), $value );
                        }
                    }
                    foreach (
                        [
                            'abptt_category'  => 'abptt_category',
                            'abptt_organizer' => 'abptt_organizer',
                            'guide'           => 'abptt_guide',
                        ] as $meta_key => $taxonomy
                    ) {
                        if ( taxonomy_exists( $taxonomy ) && array_key_exists( $meta_key, $meta_info ) ) {
                            $value = $meta_info[ $meta_key ] ?? '';
                            $ids   = is_array( $value ) ? $value : explode( ',', (string) $value );
                            $ids   = array_values( array_filter( array_map( 'absint', $ids ) ) );
                            wp_set_object_terms( $post_id, $ids, $taxonomy, false );
                        }
                    }
                }
            }

            public function validate_event_configuration( $post_id, $seat_type, $ticket_infos, $sp_infos, $date_infos, $time_info ): array {
                $errors = [];
                if ( ! get_the_title( $post_id ) ) {
                    $errors[] = __( 'Add a tour title before publishing.', 'abp-tour-travel' );
                }
                $date_type  = $date_infos['date_type'] ?? 'particular';
                $dates      = [];
                $times      = [];
                $valid_time = fn( $time ) => $time && (bool) preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) $time );
                $slot_times = function ( $slots ) use ( $valid_time ) {
                    $out = [];
                    foreach ( (array) $slots as $slot ) {
                        $time = is_array( $slot ) ? ( $slot['time'] ?? '' ) : $slot;
                        if ( $valid_time( $time ) ) {
                            $out[] = (string) $time;
                        }
                    }

                    return $out;
                };
                if ( 'particular' === $date_type ) {
                    foreach ( ( $date_infos['particular_dates'] ?? [] ) as $particular_date ) {
                        if ( ! is_array( $particular_date ) || empty( $particular_date['checkin_date'] ) ) {
                            continue;
                        }
                        $dates[] = $particular_date['checkin_date'];
                        if ( ! empty( $particular_date['checkout_date'] ) ) {
                            $dates[] = $particular_date['checkout_date'];
                        }
                        foreach ( array( 'checkin_time', 'checkout_time' ) as $row_time ) {
                            if ( $valid_time( $particular_date[ $row_time ] ?? '' ) ) {
                                $times[] = $particular_date[ $row_time ];
                            }
                        }
                    }
                } elseif ( ! empty( $date_infos['repeat_start_date'] ) ) {
                    $dates[] = $date_infos['repeat_start_date'];
                    if ( ! empty( $date_infos['repeat_end_date'] ) ) {
                        $dates[] = $date_infos['repeat_end_date'];
                    }
                } else {
                    $dates[] = current_time( 'Y-m-d' );
                }
                $dates = array_filter( $dates, static function ( $date ) {
                    return $date && strtotime( $date ) !== false;
                } );
                if ( empty( $dates ) ) {
                    $errors[] = __( 'Configure at least one valid tour date.', 'abp-tour-travel' );
                }
                if ( 'particular' === $date_type ) {
                    $incomplete = false;
                    foreach ( ( $date_infos['particular_dates'] ?? [] ) as $particular_date ) {
                        if ( ! is_array( $particular_date ) || empty( $particular_date['checkin_date'] ) ) {
                            continue;
                        }
                        foreach ( array( 'checkin_time', 'checkout_date', 'checkout_time', 'reg_end_date', 'reg_end_time' ) as $required_field ) {
                            if ( empty( $particular_date[ $required_field ] ) ) {
                                $incomplete = true;
                            }
                        }
                    }
                    if ( $incomplete ) {
                        $errors[] = __( 'Complete every particular date entry with check-in, check-out and registration end date and time.', 'abp-tour-travel' );
                    }
                }
                if ( empty( $times ) ) {
                    $times = $slot_times( $time_info['time'] ?? array() );
                    foreach ( $time_info['day_time'] ?? array() as $day_times ) {
                        $times = array_merge( $times, $slot_times( $day_times ) );
                    }
                    foreach ( $time_info['date_times'] ?? array() as $date_time ) {
                        $times = array_merge( $times, $slot_times( $date_time['time'] ?? array() ) );
                    }
                }
                $times = array_values( array_unique( $times ) );
                if ( empty( $times ) ) {
                    $errors[] = __( 'Configure at least one valid tour session time.', 'abp-tour-travel' );
                }
                if ( $seat_type === 'ticket' ) {
                    $valid_tickets = array_filter( $ticket_infos, static function ( $ticket ) {
                        return isset( $ticket['price'], $ticket['qty'] ) && (float) $ticket['price'] >= 0 && (int) $ticket['qty'] > 0;
                    } );
                    if ( empty( $valid_tickets ) ) {
                        $errors[] = __( 'Add at least one ticket type with a valid price and quantity.', 'abp-tour-travel' );
                    }
                } elseif ( empty( $sp_infos ) ) {
                    $errors[] = __( 'Assign at least one seat plan to this tour.', 'abp-tour-travel' );
                }
                if ( empty( ABPTT_Function::get_post_info( $post_id, 'link_wc_id' ) ) ) {
                    $errors[] = __( 'The WooCommerce product could not be linked to this tour.', 'abp-tour-travel' );
                }

                return array_unique( $errors );
            }

            public function configuration_notice(): void {
                $errors = get_transient( 'abptt_event_configuration_errors_' . get_current_user_id() );
                if ( empty( $errors ) || ! is_array( $errors ) ) {
                    return;
                }
                delete_transient( 'abptt_event_configuration_errors_' . get_current_user_id() );
                echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'Tour was saved as draft because its configuration is incomplete:', 'abp-tour-travel' ) . '</strong></p><ul>';
                foreach ( $errors as $error ) {
                    echo '<li>' . esc_html( $error ) . '</li>';
                }
                echo '</ul></div>';
            }

            public function post_permanent_remove(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
                if ( $post_id <= 0 ) {
                    wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid ID ..... !! ', 'abp-tour-travel' ), 'type' => 'warn' ], 400 );
                }
                $title      = get_the_title( $post_id );
                $link_wc_id = absint( ABPTT_Function::get_post_info( $post_id, 'link_wc_id' ) );
                if ( $link_wc_id > 0 ) {
                    wp_delete_post( $link_wc_id, true );
                }
                wp_delete_post( $post_id, true );
                wp_send_json_success( [ 'html' => '', 'msg' => $title . ' : ' . __( 'Permanently removed. ..... !! ', 'abp-tour-travel' ), 'type' => 'error' ] );
            }

            public function post_move_trash(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
                if ( $post_id > 0 ) {
                    $title      = get_the_title( $post_id );
                    $link_wc_id = absint( ABPTT_Function::get_post_info( $post_id, 'link_wc_id' ) );
                    if ( $link_wc_id > 0 ) {
                        wp_trash_post( $link_wc_id );
                    }
                    wp_trash_post( $post_id );
                    wp_send_json_success( [ 'html' => '', 'msg' => $title . ' : ' . __( 'Moved to trash successfully...... !! ', 'abp-tour-travel' ), 'type' => 'warn' ] );
                }
                wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid  ID ..... !! ', 'abp-tour-travel' ), 'type' => 'warn' ], 400 );
            }

            public function post_restore(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
                if ( $post_id > 0 ) {
                    $link_wc_id = absint( ABPTT_Function::get_post_info( $post_id, 'link_wc_id' ) );
                    if ( $link_wc_id > 0 ) {
                        wp_untrash_post( $link_wc_id );
                    }
                    wp_untrash_post( $post_id );
                    $updated_post = [
                        'ID'          => $post_id,
                        'post_status' => 'publish',
                    ];
                    wp_update_post( $updated_post );
                    $title = get_the_title( $post_id );
                    wp_send_json_success( [ 'html' => '', 'msg' => $title . ' : ' . __( 'Restored successfully...... !! ', 'abp-tour-travel' ), 'type' => 'success' ] );
                }
                wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid  ID ..... !! ', 'abp-tour-travel' ), 'type' => 'warn' ], 400 );
            }

            public function reload_post_list(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $post_array  = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) ) : [];
                $filter_args = $post_array( 'filter_args' );
                ob_start();
                $this->post_table( $filter_args );
                $table_html = ob_get_clean();
                wp_send_json_success( [
                    'html' => $table_html,
                    'type' => 'success',
                    'msg'  => __( 'Post List Loaded successfully...... !! ', 'abp-tour-travel' )
                ] );
            }
        }
        new ABPTT_Post();
    }