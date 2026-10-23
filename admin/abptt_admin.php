<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABPTT_ADMIN' ) ) {
        class ABPTT_ADMIN {
            public function __construct() {
                add_action( 'admin_menu', array( $this, 'admin_menu' ) );
                add_action( 'in_admin_header', array( $this, 'clean_admin_screen' ), 1 );
            }

            public function clean_admin_screen(): void {
                $screen = get_current_screen();
                if ( ! $screen || 'toplevel_page_' . ABPTT_Function::slug() !== $screen->id ) {
                    return;
                }
                foreach ( array( 'user_admin_notices', 'admin_notices', 'all_admin_notices', 'network_admin_notices' ) as $hook ) {
                    $this->remove_foreign_notices( $hook );
                }
            }

            private function remove_foreign_notices( string $hook ): void {
                global $wp_filter;
                if ( empty( $wp_filter[ $hook ] ) || empty( $wp_filter[ $hook ]->callbacks ) ) {
                    return;
                }
                $plugin_dir  = str_replace( '\\', '/', ABPTT_DIR );
                $to_remove   = [];
                foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
                    foreach ( $callbacks as $callback ) {
                        $function = $callback['function'];
                        try {
                            if ( is_array( $function ) ) {
                                $reflection = new ReflectionMethod( $function[0], $function[1] );
                            } elseif ( is_string( $function ) && strpos( $function, '::' ) !== false ) {
                                [ $class, $method ] = explode( '::', $function, 2 );
                                $reflection = new ReflectionMethod( $class, $method );
                            } elseif ( $function instanceof Closure ) {
                                $reflection = new ReflectionFunction( $function );
                            } else {
                                $reflection = new ReflectionFunction( $function );
                            }
                            $file = $reflection->getFileName();
                        } catch ( Throwable $e ) {
                            $file = false;
                        }
                        if ( ! $file || strpos( str_replace( '\\', '/', $file ), $plugin_dir ) !== 0 ) {
                            $to_remove[] = array( $function, $priority );
                        }
                    }
                }
                foreach ( $to_remove as [ $function, $priority ] ) {
                    remove_action( $hook, $function, $priority );
                }
            }

            public function admin_menu(): void {
                $label = ABPTT_Function::label();
                $slug  = ABPTT_Function::slug();
                $icon  = ABPTT_Function::icon_wp();
                add_menu_page( $label, $label, 'manage_options', $slug, array( $this, 'load_main_page' ), $icon, 50 );
            }

            public function load_main_page(): void {
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
                                <?php ABPTT_Static::svg( 'order' );
                                    esc_html_e( 'Orders', 'abp-tour-travel' ); ?>
                                <sup class="_color_theme">( <?php echo esc_html( $total_order ); ?> )</sup>
                            </a>
                            <a href="<?php echo esc_url( ABPTT_Function::build_url( 'sp' ) ); ?>" class="_btn_xs  <?php echo esc_attr( $active_tab == 'sp' ? 'abp_active' : '' ); ?>">
                                <?php ABPTT_Static::svg( 'seat' );
                                    esc_html_e( 'Ticket/Seat Plan', 'abp-tour-travel' ); ?>
                            </a>
                            <?php do_action( 'abptt_add_admin_menu_tab_middle', $active_tab ); ?>
                            <a href="<?php echo esc_url( ABPTT_Function::build_url( 'configuration' ) ); ?>" class="_btn_xs <?php echo esc_attr( $active_tab == 'configuration' ? 'abp_active' : '' ); ?>">
                                <?php ABPTT_Static::svg( 'setting' );
                                    esc_html_e( 'Global Configuration', 'abp-tour-travel' ); ?>
                            </a>
                            <a href="<?php echo esc_url( ABPTT_Function::build_url( 'documentation' ) ); ?>" class="_btn_xs <?php echo esc_attr( $active_tab == 'documentation' ? 'abp_active' : '' ); ?>">
                                <span>📘</span><?php esc_html_e( 'Documentation', 'abp-tour-travel' ); ?>
                            </a>
                            <?php do_action( 'abptt_add_admin_menu_tab', $active_tab ); ?>
                        </div>
                        <?php if ( ABPTT_WC == 2 ) { ?>
                            <div class="_group_content">
                                <button type="button" class="_btn_xs" data-href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . ABPTT_Function::get_cpt() ) ); ?>" data-blank="_blank">
                                    <?php ABPTT_Static::svg( 'plus' );
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
        }
        new ABPTT_ADMIN();
    }