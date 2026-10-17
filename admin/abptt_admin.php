<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABPTT_ADMIN' ) ) {
        class ABPTT_ADMIN {
            public function __construct() {
                add_action( 'admin_menu', array( $this, 'admin_menu' ) );
                add_action( 'abptt_load_global', array( $this, 'load_global' ) );
            }

            public function admin_menu(): void {
                $label = ABPTT_Function::label();
                $slug  = ABPTT_Function::slug();
                $icon  = ABPTT_Function::icon_wp();
                add_menu_page( $label, $label, 'manage_options', $slug, array( $this, 'load_main_page' ), $icon, 50 );
            }

            public function load_main_page(): void {
                remove_all_actions( 'user_admin_notices' );
                remove_all_actions( 'admin_notices' );
                remove_all_actions( 'all_admin_notices' );
                remove_all_actions( 'network_admin_notices' );
                add_filter( 'wp_dependency_installer_errors', '__return_false' );
                $abptt_info   = ABPTT_Query::get_info();
                $label        = ABPTT_Function::label();
                $icon         = ABPTT_Function::icon();
                $total_post   = $abptt_info['total_post'] ?? 0;
                $total_order  = $abptt_info['total_order'] ?? 0;
                $allowed_tabs = [ 'dashboard', 'posts', 'orders', 'sp', 'global', 'configuration', 'documentation', 'admin_order' ];
                $active_tab   = 'dashboard';
                if ( isset( $_GET['_abptt_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_abptt_nonce'] ) ), 'abptt_url_action' ) ) {
                    $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'posts';
                }
                if ( ! in_array( $active_tab, $allowed_tabs, true ) ) {
                    $active_tab = 'dashboard';
                }
                ?>
                <div class="abptt_area  abptt_admin">
                    <div class="admin_head ">
                        <div class="head_brand">
                            <div class="brand_icon _all_center"><?php ABPTT_Layout::image_icon( $icon ); ?></div>
                            <div class="_fd_column">
                                <h4 class="abp"><?php echo esc_html( $label ); ?></h4>
                                <span class="brand_version"><?php echo esc_html( ABPTT_VERSION ); ?></span>
                            </div>
                        </div>
                        <div class="_group_content">
                            <a href="<?php echo esc_url( add_query_arg( 'tab', 'dashboard' ) ); ?>" class="_btn_xs <?php echo esc_attr( $active_tab == 'dashboard' ? 'abp_active' : '' ); ?>"><span>📊</span><?php esc_html_e( 'Dashboard', 'abp-tour-travel' ); ?></a>
                            <a href="<?php echo esc_url( ABPTT_Function::build_url( 'posts' ) ); ?>" class="_btn_xs post_tab <?php echo esc_attr( $active_tab == 'posts' ? 'abp_active' : '' ); ?>">
                                <?php ABPTT_Layout::image_icon( $icon );
                                    echo esc_html( $label ) . ' ' . esc_html__( 'Lists', 'abp-tour-travel' ); ?>
                                <sup class="_color_theme">( <?php echo esc_html( $total_post ); ?> )</sup>
                            </a>
                            <a href="<?php echo esc_url( ABPTT_Function::build_url( 'orders' ) ); ?>" class="_btn_xs <?php echo esc_attr( $active_tab == 'orders' ? 'abp_active' : '' ); ?>">
                                <?php ABPTT_Static::icon_svg( 'order' );
                                    esc_html_e( 'Orders', 'abp-tour-travel' ); ?>
                                <sup class="_color_theme">( <?php echo esc_html( $total_order ); ?> )</sup>
                            </a>
                            <a href="<?php echo esc_url( ABPTT_Function::build_url( 'sp' ) ); ?>" class="_btn_xs  <?php echo esc_attr( $active_tab == 'sp' ? 'abp_active' : '' ); ?>">
                                <?php ABPTT_Static::icon_svg( 'seat' );
                                    esc_html_e( 'Ticket/Seat Plan', 'abp-tour-travel' ); ?>
                            </a>
                            <?php do_action( 'abptt_add_admin_menu_tab_middle', $active_tab ); ?>
                            <a href="<?php echo esc_url( ABPTT_Function::build_url( 'global' ) ); ?>" class="_btn_xs <?php echo esc_attr( $active_tab == 'global' ? 'abp_active' : '' ); ?>">
                                <?php ABPTT_Static::icon_svg( 'globe' );
                                    esc_html_e( 'Global Data', 'abp-tour-travel' ); ?>
                            </a>
                            <a href="<?php echo esc_url( ABPTT_Function::build_url( 'configuration' ) ); ?>" class="_btn_xs <?php echo esc_attr( $active_tab == 'configuration' ? 'abp_active' : '' ); ?>">
                                <?php ABPTT_Static::icon_svg( 'setting' );
                                    esc_html_e( 'Configuration', 'abp-tour-travel' ); ?>
                            </a>
                            <a href="<?php echo esc_url( ABPTT_Function::build_url( 'documentation' ) ); ?>" class="_btn_xs <?php echo esc_attr( $active_tab == 'documentation' ? 'abp_active' : '' ); ?>">
                                <span>📘</span><?php esc_html_e( 'Documentation', 'abp-tour-travel' ); ?>
                            </a>
                            <?php do_action( 'abptt_add_admin_menu_tab', $active_tab ); ?>
                        </div>
                        <?php if ( ABPTT_WC == 2 ) { ?>
                            <div class="_group_content">
                                <button type="button" class="_btn_xs" data-href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . ABPTT_Function::get_cpt() ) ); ?>" data-blank="_blank">
                                    <?php ABPTT_Static::icon_svg( 'plus' );
                                        echo esc_html( $label ); ?>
                                </button>
                                <?php ABPTT_Layout::button_global_popup( 'tax_location', ABPTT_Function::location_label(), '_btn_xs' );
                                    if ( ABPTT_Function::on_off( 'category' ) ) {
                                        ABPTT_Layout::button_global_popup( 'tax_category', ABPTT_Function::category_label(), '_btn_xs' );
                                    } ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="dashboard_content">
                        <?php do_action( 'abptt_notice' );
                            do_action( 'abptt_load_' . $active_tab, $abptt_info ); ?>
                    </div>
                    <?php ABPTT_Layout::load_admin_globally(); ?>
                </div>
                <?php
            }

            public function load_global( $abptt_info ): void {
                $allowed_tabs = [ 'dates', 'additional', 'client_form', 'faq', 'why_book', 'tc', 'category', 'organizer', 'location', 'feature', 'discount', 'guide' ];
                $active_tab   = 'dates';
                if ( isset( $_GET['_abptt_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_abptt_nonce'] ) ), 'abptt_url_action' ) ) {
                    $active_tab = isset( $_GET['global'] ) ? sanitize_text_field( wp_unslash( $_GET['global'] ) ) : 'dates';
                }
                if ( ! in_array( $active_tab, $allowed_tabs, true ) ) {
                    $active_tab = 'dates';
                }
                ?>
                <div class="abp_tabs tab_left">
                    <ul class="abp tab_lists">
                        <li data-href="<?php echo esc_url( ABPTT_Function::build_url( 'global', [ 'global' => 'dates' ] ) ); ?>" class="<?php echo esc_attr( $active_tab == 'dates' ? 'abp_active' : '' ); ?>">
                            <?php ABPTT_Static::icon_svg( 'date_1' ); ?><?php esc_html_e( 'Dates', 'abp-tour-travel' ); ?>
                        </li>
                        <?php if ( ABPTT_Function::on_off( 'additional_info' ) ) { ?>
                            <li data-href="<?php echo esc_url( ABPTT_Function::build_url( 'global', [ 'global' => 'additional' ] ) ); ?>" class=" <?php echo esc_attr( $active_tab == 'additional' ? 'abp_active' : '' ); ?>">💰<?php esc_html_e( 'Additional services', 'abp-tour-travel' ); ?></li>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'client_info' ) ) { ?>
                            <li  data-href="<?php echo esc_url( ABPTT_Function::build_url( 'global', [ 'global' => 'client_form' ] ) ); ?>" class="<?php echo esc_attr( $active_tab == 'client_form' ? 'abp_active' : '' ); ?>">📋<?php esc_html_e( 'Traveler Forms', 'abp-tour-travel' ); ?></li>
                        <?php } ?>
                        <?php do_action( 'abptt_add_admin_global_tab', $active_tab ); ?>
                        <li  data-href="<?php echo esc_url( ABPTT_Function::build_url( 'global', [ 'global' => 'location' ] ) ); ?>" class="<?php echo esc_attr( $active_tab == 'location' ? 'abp_active' : '' ); ?>">
                            <span class="fas fa-map-marker-alt"></span><?php echo esc_html( ABPTT_Function::location_label() ); ?>
                        </li>
                        <?php if ( ABPTT_Function::on_off( 'category' ) ) { ?>
                            <li  data-href="<?php echo esc_url( ABPTT_Function::build_url( 'global', [ 'global' => 'category' ] ) ); ?>" class="<?php echo esc_attr( $active_tab == 'category' ? 'abp_active' : '' ); ?>">
                                <?php ABPTT_Static::icon_svg( 'category_1' ); ?><?php echo esc_html( ABPTT_Function::category_label() ); ?>
                            </li>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'organizer' ) ) { ?>
                            <li  data-href="<?php echo esc_url( ABPTT_Function::build_url( 'global', [ 'global' => 'organizer' ] ) ); ?>" class="<?php echo esc_attr( $active_tab == 'organizer' ? 'abp_active' : '' ); ?>">🏢<?php echo esc_html( ABPTT_Function::organizer_label() ); ?></li>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'feature' ) ) { ?>
                            <li  data-href="<?php echo esc_url( ABPTT_Function::build_url( 'global', [ 'global' => 'feature' ] ) ); ?>" class="<?php echo esc_attr( $active_tab == 'feature' ? 'abp_active' : '' ); ?>">🔗<?php echo esc_html( ABPTT_Function::feature_label() ); ?></li>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'guide' ) ) { ?>
                            <li  data-href="<?php echo esc_url( ABPTT_Function::build_url( 'global', [ 'global' => 'guide' ] ) ); ?>" class="<?php echo esc_attr( $active_tab == 'guide' ? 'abp_active' : '' ); ?>">🎤<?php echo esc_html( ABPTT_Function::guide_label() ); ?></li>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'faq' ) ) { ?>
                            <li  data-href="<?php echo esc_url( ABPTT_Function::build_url( 'global', [ 'global' => 'faq' ] ) ); ?>" class="<?php echo esc_attr( $active_tab == 'faq' ? 'abp_active' : '' ); ?>">❓<?php esc_html_e( 'Global FAQ', 'abp-tour-travel' ); ?></li>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'why_book_us' ) ) { ?>
                            <li  data-href="<?php echo esc_url( ABPTT_Function::build_url( 'global', [ 'global' => 'why_book' ] ) ); ?>" class="<?php echo esc_attr( $active_tab == 'why_book' ? 'abp_active' : '' ); ?>">👍<?php esc_html_e( 'Why Book With Us', 'abp-tour-travel' ); ?></li>
                        <?php } ?>
                        <?php if ( ABPTT_Function::on_off( 'tc' ) ) { ?>
                            <li  data-href="<?php echo esc_url( ABPTT_Function::build_url( 'global', [ 'global' => 'tc' ] ) ); ?>" class="<?php echo esc_attr( $active_tab == 'tc' ? 'abp_active' : '' ); ?>">🤝<?php esc_html_e( 'Term & Conditions', 'abp-tour-travel' ); ?></li>
                        <?php } ?>
                    </ul>
                    <div class="tab_content">
                        <?php do_action( 'abptt_global_' . $active_tab, $abptt_info ); ?>
                    </div>
                </div>
                <?php
            }
        }
        new ABPTT_ADMIN();
    }