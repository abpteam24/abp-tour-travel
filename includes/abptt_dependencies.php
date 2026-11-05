<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTT_Dependencies' ) ) {
		class ABPTT_Dependencies {
			private const ORDER_SCHEMA_VERSION = 4;

			public function __construct() {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ), 90 );
				add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ), 90 );
				$this->load_file();
				add_action( 'init', [ $this, 'register_cpt' ] );
				add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
				add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
				add_action( 'upgrader_process_complete', [ $this, 'flush_rewrite' ] );
				add_action( 'admin_init', array( $this, 'activation_redirect' ) );
				add_action( 'init', array( $this, 'maybe_upgrade_order_schema' ), 5 );
				add_action( 'save_post_' . ABPTT_Function::get_cpt(), function (): void {
					ABPTT_Query::flush_cache();
				}, 20 );
			}

			public function admin_enqueue( $hook ): void {
				$screen    = get_current_screen();
				$post_type = $screen ? $screen->post_type : '';
				if ( false === strpos( $hook, ABPTT_Function::slug() ) && $post_type !== ABPTT_Function::get_cpt() ) {
					return;
				}
				$label   = ABPTT_Function::label();
				$post_id = get_the_ID();
				$this->global_enqueue();
				wp_enqueue_editor();
				wp_enqueue_media();
				//admin script
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_style( 'wp-codemirror' );
				wp_enqueue_script( 'wp-codemirror' );
				//=============================//
				wp_enqueue_script( 'abptt_admin', ABPTT_URL . 'assets/js/abptt_admin.js', array( 'jquery' ), ABPTT_VERSION, true );
				wp_localize_script( 'abptt_admin', 'abptt_admin_data', [
					'ajax_url'        => admin_url( 'admin-ajax.php' ),
					'nonce'           => wp_create_nonce( 'abptt_admin_ajax_nonce' ),
					'icon_url'        => ABPTT_URL . 'assets/js/abptt_icons.json',
					'related_info'    => wp_json_encode( ABPTT_Function::related_info_js( $post_id ) ),
					'abptt_feature'   => wp_json_encode( ABPTT_Function::option_js( $post_id, 'abptt_feature' ) ),
					'abptt_category'  => wp_json_encode( ABPTT_Function::option_js( $post_id, 'abptt_category' ) ),
					'abptt_organizer' => wp_json_encode( ABPTT_Function::option_js( $post_id, 'abptt_organizer' ) ),
					'abptt_guide'     => wp_json_encode( ABPTT_Function::option_js( $post_id, 'abptt_guide' ) ),
					'sp_data'         => wp_json_encode( ABPTT_Seat_Plan::get_sp_js( $post_id ) ),
					'abptt_location'  => wp_json_encode( ABPTT_Function::option_js( $post_id, 'abptt_location' ) ),
					'msg'             => [
						'confirm_delete'      => __( 'Are you sure you want to delete this item?', 'abp-tour-travel' ),
						'confirm_ok'          => __( '1. Ok : To Remove Item .', 'abp-tour-travel' ),
						'confirm_cancel'      => __( '2. Cancel : To Cancel .', 'abp-tour-travel' ),
						'saving'              => __( 'Saving.............!', 'abp-tour-travel' ),
						'saved'               => __( 'Saved...............!', 'abp-tour-travel' ),
						'date_content'        => __( 'Importing Global Date Configuration........', 'abp-tour-travel' ),
						'additional_content'  => __( 'Importing Global Additional Service Configuration........', 'abp-tour-travel' ),
						'client_form_content' => __( 'Importing Global Guest Form Configuration........', 'abp-tour-travel' ),
						'faq_content'         => __( 'Importing Global FAQ Configuration........', 'abp-tour-travel' ),
						'tc_content'          => __( 'Importing Global term And Condition Configuration........', 'abp-tour-travel' ),
						'importing'           => __( 'Importing........', 'abp-tour-travel' ),
						'imported'            => __( 'Imported Successfully............. !', 'abp-tour-travel' ),
						'loading'             => __( 'Loading........', 'abp-tour-travel' ),
						'price_loading'       => __( 'Price Configuration Loading........', 'abp-tour-travel' ),
						'type_switch'         => __( 'Ticket Type Switching... Please Wait.......', 'abp-tour-travel' ),
						'loaded'              => __( 'Loaded Successfully............. !', 'abp-tour-travel' ),
						'order_loading'       => __( 'Order Loading........ !', 'abp-tour-travel' ),
						'error'               => __( 'An error occurred. Please try again.', 'abp-tour-travel' ),
						'deleting'            => __( 'Deleting.............', 'abp-tour-travel' ),
						'delete_success'      => __( 'Item Deleted Successfully............. !', 'abp-tour-travel' ),
						'select_stops'        => __( 'Select Location..', 'abp-tour-travel' ),
						'select_ticket'       => __( 'Select Ticket Type..', 'abp-tour-travel' ),
						'post_loading'        => $label . ' ' . __( 'List Loading.............', 'abp-tour-travel' ),
						'permanent_remove'    => $label . ' ' . __( 'Permanent Deleting.........!', 'abp-tour-travel' ),
						'move_trash'          => $label . ' ' . __( 'move to Trashing.........!', 'abp-tour-travel' ),
						'restore'             => $label . ' ' . __( 'Restoring.........!', 'abp-tour-travel' ),
						'wc_install_active'   => __( 'WooCommerce Downloading And Installing.........Please Wait...............!!', 'abp-tour-travel' ),
						'wc_active'           => __( 'WooCommerce  Installing.........Please Wait...............!!', 'abp-tour-travel' ),
						'create_post_page'    => __( 'Page Creating ........!', 'abp-tour-travel' ),
						'no_item'             => __( 'No More Item Found !', 'abp-tour-travel' ),
						'no_item_selected'    => __( 'No Item selected !', 'abp-tour-travel' ),
					],
				] );
				wp_enqueue_style( 'abptt_admin', ABPTT_URL . 'assets/css/abptt_admin.css', array(), ABPTT_VERSION );
				$google_map_key = ABPTT_Function::get_options( 'abptt_configuration', 'google_map_key', '' );
				if ( ABPTT_Function::on_off( 'google_map' ) && ! empty( $google_map_key ) ) {
					// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External Google Maps API script; version is managed by Google.
					wp_enqueue_script( 'abptt_gmaps', 'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode( $google_map_key ) . '&libraries=places&callback=abptt_gmap_init', array(), null, true );
				}
				wp_enqueue_script( 'abptt_sp', ABPTT_URL . 'assets/js/abptt_sp.js', array( 'jquery' ), ABPTT_VERSION, true );
				wp_localize_script( 'abptt_sp', 'abptt_sp_config', [
					'seat_type'  => wp_json_encode( ABPTT_Seat_Plan::get_ticket_type_js() ),
					'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( 'abptt_admin_ajax_nonce' ),
					'decor_item' => wp_json_encode( ABPTT_Seat_Plan::get_decor_js() ),
					'msg'        => [
						'sp_delete_confirm' => __( 'Are you sure you want to delete this Seat Plan?', 'abp-tour-travel' ),
						'sp_clear_confirm'  => __( 'Are you absolutely sure you want to clear the full layout?', 'abp-tour-travel' ),
						'sp_clear'          => __( 'Layout clear successfully.............!', 'abp-tour-travel' ),
						'sp_deleting'       => __( 'Seat Plan Deleting.............!', 'abp-tour-travel' ),
						'sp_saving'         => __( 'Seat Plan Saving.............!', 'abp-tour-travel' ),
						'sp_loading'        => __( 'Seat Plan Loading.............!', 'abp-tour-travel' ),
					],
				] );
				//=============================//
				do_action( 'abptt_admin_enqueue' );
			}

			public function frontend_enqueue(): void {
				// Always enqueued on the frontend so the plugin's shortcodes work on any page
				// and its styles apply wherever its UI is embedded (cart, checkout, account, etc.).
				if ( in_array( 'woocommerce/woocommerce.php', (array) get_option( 'active_plugins' ), true ) ) {
					wp_enqueue_script( 'wc-checkout' );
					wp_enqueue_style( 'select2' );
					wp_enqueue_script( 'select2' );
				}
				wp_enqueue_style( 'abptt_slick', ABPTT_URL . 'assets/css/slick.min.css', array(), ABPTT_VERSION );
				wp_enqueue_script( 'abptt_frontend', ABPTT_URL . 'assets/js/abptt_frontend.js', array( 'jquery' ), ABPTT_VERSION, true );
				$google_map_key = ABPTT_Function::get_options( 'abptt_configuration', 'google_map_key', '' );
				$use_google_map = ABPTT_Function::on_off( 'google_map' ) && '' !== trim( (string) $google_map_key );
				wp_localize_script( 'abptt_frontend', 'abptt_map_config', array(
					'use_gmap' => $use_google_map ? '1' : '',
				) );
				if ( $use_google_map ) {
					// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External Google Maps API script; version is managed by Google.
					wp_enqueue_script( 'abptt_gmaps', 'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode( $google_map_key ) . '&libraries=places&callback=abptt_route_map_init', array( 'abptt_frontend' ), null, true );
				}
				wp_enqueue_script( 'abptt_slick', ABPTT_URL . 'assets/js/slick.min.js', array( 'jquery' ), ABPTT_VERSION, true );
				$this->global_enqueue();
				do_action( 'abptt_frontend_enqueue' );
			}

			public function global_enqueue(): void {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_style( 'abptt_jquery_ui', ABPTT_URL . 'assets/css/jquery-ui.min.css', array(), '1.13.2' );
				wp_enqueue_style( 'abptt_font_awesome', ABPTT_URL . 'assets/css/font_awesome.min.css', array(), '5.15.4' );
				wp_enqueue_style( 'abptt_leaflet', ABPTT_URL . 'assets/css/leaflet.min.css', array(), '1.9.4' );
				wp_enqueue_script( 'abptt_leaflet', ABPTT_URL . 'assets/js/leaflet.min.js', array(), '1.9.4', true );
				wp_enqueue_style( 'abptt_lib', ABPTT_URL . 'assets/css/abptt_lib.css', array(), ABPTT_VERSION );
				wp_enqueue_script( 'abptt_lib', ABPTT_URL . 'assets/js/abptt_lib.js', array( 'jquery' ), ABPTT_VERSION, true );
				if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
					wp_localize_script( 'abptt_lib', 'abptt_var', [
						'currency_symbol'     => get_woocommerce_currency_symbol(),
						'currency_position'   => get_option( 'woocommerce_currency_pos' ),
						'currency_decimal'    => wc_get_price_decimal_separator(),
						'thousands_separator' => wc_get_price_thousand_separator(),
						'decimal_num'         => ABPTT_Function::get_option( 'woocommerce_price_num_decimals', 2 ),
						'currency_suffix'     => ABPTT_Function::get_option( 'woocommerce_price_display_suffix', '' ),
						'blank_image'         => ABPTT_BLANK_IMG_URL,
						'date_format'         => ABPTT_JS_Date_Format,
					] );
				} else {
					wp_localize_script( 'abptt_lib', 'abptt_var', [
						'currency_symbol'     => '',
						'currency_position'   => '',
						'currency_decimal'    => '',
						'thousands_separator' => '',
						'decimal_num'         => '',
						'wc_suffix'           => '',
						'blank_image'         => ABPTT_BLANK_IMG_URL,
						'date_format'         => ABPTT_JS_Date_Format,
					] );
				}
				$colors          = ABPTT_Function::get_option( 'abptt_color' );
				$available       = ! empty( $colors['available'] ) ? $colors['available'] : "#D4EDDA";
				$sold            = ! empty( $colors['sold'] ) ? $colors['sold'] : "#F8D7DA";
				$booked          = ! empty( $colors['booked'] ) ? $colors['booked'] : "#6C757D";
				$selected        = ! empty( $colors['selected'] ) ? $colors['selected'] : "#007BFF";
				$abptt_css_var   = ABPTT_Function::get_option( 'abptt_css_var' );
				$default_color   = ( $abptt_css_var['color_default'] ?? null ) ?: '#303030';
				$color_theme     = ( $abptt_css_var['color_theme'] ?? null ) ?: '#95951c';
				$alternate_color = ( $abptt_css_var['color_theme_alternate'] ?? null ) ?: '#fff';
				$color_warning   = ( $abptt_css_var['color_warning'] ?? null ) ?: '#E67C30';
				$bg_section      = ( $abptt_css_var['bg_section'] ?? null ) ?: '#FAFCFE';
				$bg_button       = ( $abptt_css_var['bg_button'] ?? null ) ?: '#222';
				$color_button    = ( $abptt_css_var['color_button'] ?? null ) ?: $alternate_color;
				$color_theme_ee  = $color_theme . 'ee';
				$color_theme_cc  = $color_theme . 'cc';
				$color_theme_aa  = $color_theme . 'aa';
				$color_theme_88  = $color_theme . '88';
				$color_theme_77  = $color_theme . '77';
				$default_br      = ! empty( $abptt_css_var['br_default'] ) ? $abptt_css_var['br_default'] . 'px' : '5px';
				$br_xl           = ! empty( $abptt_css_var['br_default'] ) ? $abptt_css_var['br_default'] * 2 . 'px' : '10px';
				$fs_h1           = ! empty( $abptt_css_var['fs_h1'] ) ? $abptt_css_var['fs_h1'] . 'px' : '30px';
				$fs_h2           = ! empty( $abptt_css_var['fs_h2'] ) ? $abptt_css_var['fs_h2'] . 'px' : '26px';
				$fs_h3           = ! empty( $abptt_css_var['fs_h3'] ) ? $abptt_css_var['fs_h3'] . 'px' : '24px';
				$fs_h4           = ! empty( $abptt_css_var['fs_h4'] ) ? $abptt_css_var['fs_h4'] . 'px' : '20px';
				$fs_h5           = ! empty( $abptt_css_var['fs_h5'] ) ? $abptt_css_var['fs_h5'] . 'px' : '17px';
				$fs_h6           = ! empty( $abptt_css_var['fs_h6'] ) ? $abptt_css_var['fs_h6'] . 'px' : '15px';
				$fs_label        = ! empty( $abptt_css_var['fs_label'] ) ? $abptt_css_var['fs_label'] . 'px' : '14px';
				$default_fs      = ! empty( $abptt_css_var['fs_default'] ) ? $abptt_css_var['fs_default'] . 'px' : '12px';
				$button_fs       = ! empty( $abptt_css_var['fs_button'] ) ? $abptt_css_var['fs_button'] . 'px' : '14px';
				$off             = esc_html__( 'OFF', 'abp-tour-travel' );
				$on              = esc_html__( 'ON', 'abp-tour-travel' );
				$abptt_var       =
					":root {
						--tt_br: {$default_br};						
						--tt_br_xl: {$br_xl};						
						--tt_text_off:'{$off}';
						--tt_text_on: '{$on}';
						--tt_fs: {$default_fs};				
						--tt_fs_label: {$fs_label};
						--tt_fs_h6: {$fs_h6};
						--tt_fs_h5: {$fs_h5};
						--tt_fs_h4: {$fs_h4};
						--tt_fs_h3: {$fs_h3};
						--tt_fs_h2: {$fs_h2};
						--tt_fs_h1: {$fs_h1};						
						--tt_button_bg: {$bg_button};
						--tt_button_color: {$color_button};
						--tt_button_fs: {$button_fs};						
						--tt_color_default: {$default_color};						
						--tt_color_section: {$bg_section};
						--tt_color_theme: {$color_theme};
						--tt_color_theme_ee: {$color_theme_ee};
						--tt_color_theme_cc: {$color_theme_cc};
						--tt_color_theme_aa: {$color_theme_aa};
						--tt_color_theme_88: {$color_theme_88};
						--tt_color_theme_77: {$color_theme_77};
						--tt_color_theme_alter: {$alternate_color};
						--tt_color_warning:{$color_warning};						
						--tt_color_available:{$available};						
						--tt_color_sold:{$sold};						
						--tt_color_booked:{$booked};						
						--tt_color_seclected:{$selected};						
					}";
				wp_add_inline_style( 'abptt_lib', wp_kses_post( $abptt_var ) );
				wp_enqueue_style( 'abptt', ABPTT_URL . 'assets/css/abptt.css', array(), ABPTT_VERSION );
				wp_enqueue_script( 'abptt_infos', ABPTT_URL . 'assets/js/abptt.js', array( 'jquery' ), ABPTT_VERSION, true );
				$rental_data = array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'abptt_ajax_nonce' ),
					'now'      => current_time( 'Y-m-d H:i' ),
					'msg'      => [
						'date_loading'        => __( 'Date  Loading.............', 'abp-tour-travel' ),
						'end_date_loading'    => __( 'Return Date  Loading.............', 'abp-tour-travel' ),
						'select_post'         => __( 'Please Select', 'abp-tour-travel' ) . ' ' . ABPTT_Function::label(),
						'select_start_date'   => __( 'Please Select Journey Date', 'abp-tour-travel' ),
						'select_session_time' => __( 'Please Select Session Time', 'abp-tour-travel' ),
						'free'                => __( 'FREE', 'abp-tour-travel' ),
						'loading'             => __( 'Loading..............!', 'abp-tour-travel' ),
					],
				);
				wp_localize_script( 'abptt_infos', 'abptt_infos', $rental_data );
				do_action( 'abptt_global_script' );
			}

			private function load_file(): void {
				require_once ABPTT_DIR . 'includes/abptt_static.php';
				require_once ABPTT_DIR . 'includes/abptt_function.php';
				require_once ABPTT_DIR . 'includes/abptt_query.php';
				require_once ABPTT_DIR . 'includes/abptt_layout.php';
				if ( is_admin() ) {
					require_once ABPTT_DIR . 'admin/abptt_admin.php';
					require_once ABPTT_DIR . 'admin/abptt_dashboard.php';
					require_once ABPTT_DIR . 'admin/abptt_post.php';
					require_once ABPTT_DIR . 'admin/abptt_ticket.php';
					require_once ABPTT_DIR . 'admin/abptt_orders.php';
					require_once ABPTT_DIR . 'admin/abptt_dates.php';
					require_once ABPTT_DIR . 'admin/abptt_additional.php';
					require_once ABPTT_DIR . 'admin/abptt_form.php';
					require_once ABPTT_DIR . 'admin/abptt_seat_plan.php';
					require_once ABPTT_DIR . 'admin/abptt_faq.php';
					require_once ABPTT_DIR . 'admin/abptt_why_book.php';
					require_once ABPTT_DIR . 'admin/abptt_tc.php';
					require_once ABPTT_DIR . 'admin/abptt_timeline.php';
					require_once ABPTT_DIR . 'admin/abptt_configuration.php';
					require_once ABPTT_DIR . 'admin/abptt_documentation.php';
					require_once ABPTT_DIR . 'admin/abptt_category.php';
					require_once ABPTT_DIR . 'admin/abptt_organizer.php';
					require_once ABPTT_DIR . 'admin/abptt_location.php';
					require_once ABPTT_DIR . 'admin/abptt_guide.php';
					require_once ABPTT_DIR . 'admin/abptt_feature.php';
					require_once ABPTT_DIR . 'admin/abptt_dummy_import.php';
					new ABPTT_Dummy_Import();
				}
				if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
					require_once ABPTT_DIR . 'includes/abptt_hooks.php';
					require_once ABPTT_DIR . 'includes/abptt_ajax.php';
					require_once ABPTT_DIR . 'includes/abptt_frontend.php';
					require_once ABPTT_DIR . 'includes/abptt_shortcodes.php';
					require_once ABPTT_DIR . 'includes/abptt_woocommerce.php';
					require_once ABPTT_DIR . 'includes/abptt_my_account.php';
					require_once ABPTT_DIR . 'admin/abptt_hidden_post.php';
				}
			}

			public function register_cpt(): void {
				$cpt   = ABPTT_Function::get_cpt();
				$label = ABPTT_Function::label();
				register_post_type( $cpt, [
					'public'              => true,
					'labels'              => [
						'name'                  => esc_html( $label ),
						'singular_name'         => esc_html( $label ),
						'menu_name'             => esc_html( $label ),
						'name_admin_bar'        => esc_html( $label ),
						'archives'              => __( 'Post List', 'abp-tour-travel' ),
						'attributes'            => __( 'Post List', 'abp-tour-travel' ),
						'parent_item_colon'     => __( 'Post Item:', 'abp-tour-travel' ),
						'all_items'             => __( 'Post', 'abp-tour-travel' ),
						'add_new_item'          => __( 'Add Post', 'abp-tour-travel' ),
						'add_new'               => __( 'Add Post', 'abp-tour-travel' ),
						'new_item'              => __( 'Add Post', 'abp-tour-travel' ),
						'edit_item'             => __( 'Edit Post', 'abp-tour-travel' ),
						'update_item'           => __( 'Update Post', 'abp-tour-travel' ),
						'view_item'             => __( 'View Post', 'abp-tour-travel' ),
						'view_items'            => __( 'View Post', 'abp-tour-travel' ),
						'search_items'          => __( 'Search Post', 'abp-tour-travel' ),
						'not_found'             => __( 'Post Not Found', 'abp-tour-travel' ),
						'not_found_in_trash'    => __( 'Post Not found in Trash', 'abp-tour-travel' ),
						'featured_image'        => __( 'Post Image', 'abp-tour-travel' ),
						'set_featured_image'    => __( 'Post Image', 'abp-tour-travel' ),
						'remove_featured_image' => __( 'Remove Post Image', 'abp-tour-travel' ),
						'use_featured_image'    => __( 'Use image Post as featured image', 'abp-tour-travel' ),
						'insert_into_item'      => __( 'Insert  Post', 'abp-tour-travel' ),
						'uploaded_to_this_item' => __( 'Uploaded  Post', 'abp-tour-travel' ),
						'items_list'            => __( 'Post List', 'abp-tour-travel' ),
						'items_list_navigation' => __( 'Category list navigation', 'abp-tour-travel' ),
						'filter_items_list'     => __( 'Filter Post List', 'abp-tour-travel' )
					],
					'menu_icon'           => ABPTT_Function::icon_wp(),
					'supports'            => [ 'title', 'editor', 'thumbnail' ],
					'rewrite'             => [ 'slug' => ABPTT_Function::slug(), 'with_front' => true, 'pages' => true, 'feeds' => true, ],
					'show_in_rest'        => true,
					'rest_base'           => 'abptt_post',
					'capability_type'     => 'post',
					'publicly_queryable'  => true,  // you should be able to query it
					'show_ui'             => true,  // you should be able to edit it in wp-admin
					'show_in_menu'        => false,
					'exclude_from_search' => true,  // you should exclude it from search results
					'show_in_nav_menus'   => true,  // you should be able to add it to menus
					'has_archive'         => true,  // it should have archive page
				] );
				register_taxonomy( 'abptt_location', $cpt, [
					'hierarchical'      => true,
					"public"            => true,
					'labels'            => [
						'name'          => $label . ' ' . ABPTT_Function::location_label(),
						'singular_name' => $label . ' ' . ABPTT_Function::location_label(),
					],
					'show_ui'           => true,
					'show_admin_column' => false,
					'show_in_menu'      => false,
					'query_var'         => true,
					'rewrite'           => [ 'slug' => ABPTT_Function::location_slug() ],
					'show_in_rest'      => true,
					'rest_base'         => 'abptt_location',
					'meta_box_cb'       => false,
				] );
				if ( ABPTT_Function::on_off( 'category' ) ) {
					register_taxonomy( 'abptt_category', $cpt, [
						'hierarchical'      => true,
						"public"            => true,
						'labels'            => [
							'name'          => $label . ' ' . ABPTT_Function::category_label(),
							'singular_name' => $label . ' ' . ABPTT_Function::category_label(),
						],
						'show_ui'           => true,
						'show_admin_column' => false,
						'show_in_menu'      => false,
						'query_var'         => true,
						'rewrite'           => [ 'slug' => ABPTT_Function::category_slug() ],
						'show_in_rest'      => true,
						'rest_base'         => 'abptt_category',
						'meta_box_cb'       => false,
					] );
				}
				if ( ABPTT_Function::on_off( 'organizer' ) ) {
					register_taxonomy( 'abptt_organizer', $cpt, [
						'hierarchical'      => true,
						"public"            => true,
						'labels'            => [
							'name'          => $label . ' ' . ABPTT_Function::organizer_label(),
							'singular_name' => $label . ' ' . ABPTT_Function::organizer_label(),
						],
						'show_ui'           => true,
						'show_admin_column' => false,
						'show_in_menu'      => false,
						'query_var'         => true,
						'rewrite'           => [ 'slug' => ABPTT_Function::organizer_slug() ],
						'show_in_rest'      => true,
						'rest_base'         => 'abptt_organizer',
						'meta_box_cb'       => false,
					] );
				}
				if ( ABPTT_Function::on_off( 'guide' ) ) {
					register_taxonomy( 'abptt_guide', $cpt, [
						'hierarchical'      => false,
						'public'            => true,
						'labels'            => [
							'name'          => ABPTT_Function::guide_label(),
							'singular_name' => ABPTT_Function::guide_label(),
						],
						'show_ui'           => false,
						'show_admin_column' => false,
						'show_in_menu'      => false,
						'query_var'         => true,
						'rewrite'           => [ 'slug' => ABPTT_Function::guide_slug() ],
						'show_in_rest'      => true,
						'rest_base'         => 'abptt_guide',
						'meta_box_cb'       => false,
					] );
				}
				// Flush once when a rewrite-relevant setting changes, not on every
				// request. The old unconditional flush wrote rewrite rules on each
				// page load and was a measurable performance cost.
				$rewrite_signature = md5( wp_json_encode( [
					'cpt'       => ABPTT_Function::slug(),
					'category'  => ABPTT_Function::on_off( 'category' ) ? ABPTT_Function::category_slug() : '',
					'location'  => ABPTT_Function::location_slug(),
					'organizer' => ABPTT_Function::on_off( 'organizer' ) ? ABPTT_Function::organizer_slug() : '',
					'guide'     => ABPTT_Function::on_off( 'guide' ) ? ABPTT_Function::guide_slug() : '',
				] ) );
				if ( get_option( 'abptt_rewrite_signature' ) !== $rewrite_signature ) {
					flush_rewrite_rules( false );
					update_option( 'abptt_rewrite_signature', $rewrite_signature, false );
				}
			}

			public static function activation(): void {
				self::create_table();
				flush_rewrite_rules();
			}

			public static function deactivate(): void {
				flush_rewrite_rules();
			}

			public static function create_table(): void {
				global $wpdb;
				$order_table = $wpdb->prefix . 'abptt_orders';
				$sp_table    = $wpdb->prefix . 'abptt_sp';
				$collate     = $wpdb->get_charset_collate();
				// Non-destructive schema upgrade: dbDelta only ever ADDS missing columns/indexes, so
				// existing booking rows are preserved. Never drop the table on a version bump.
				$abptt_orders = "CREATE TABLE $order_table (
					        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					        order_id bigint(20) unsigned NOT NULL,
					        item_id bigint(20) unsigned NOT NULL,
					        post_id bigint(20) unsigned NOT NULL,
					        user_id bigint(20) unsigned NOT NULL,
					        tour_date date DEFAULT NULL,
					        end_date date DEFAULT NULL,
					        session_time time DEFAULT NULL,
					        nights smallint(5) unsigned NOT NULL DEFAULT 0,
					        seat_type varchar(30) NOT NULL DEFAULT 'ticket',
					        sp_id bigint(20) unsigned NOT NULL DEFAULT 0,
					        ticket_info text DEFAULT NULL,
					        ticket_id text DEFAULT NULL,
					        qty int(5) NOT NULL DEFAULT 1,
					        price decimal(10,2) DEFAULT NULL,
					        ex_info text DEFAULT NULL,
					        ex_id text DEFAULT NULL,
					        ex_price decimal(10,2) DEFAULT NULL,
					        total decimal(10,2) DEFAULT NULL,
					        pp_choice varchar(20) DEFAULT NULL,
					        pp_full varchar(100) DEFAULT NULL,
					        pp_deposit varchar(100) DEFAULT NULL,
					        pp_balance varchar(100) DEFAULT NULL,
					        pp_date datetime DEFAULT NULL,
					        pass_info text DEFAULT NULL,
					        checkin tinyint(1) NOT NULL DEFAULT 0,
					        order_status varchar(30) NOT NULL DEFAULT 'wc-pending',
					        payment_method varchar(100) DEFAULT NULL,
					        billing_name varchar(100) DEFAULT NULL,
					        billing_email varchar(100) DEFAULT NULL,
					        billing_phone varchar(50) DEFAULT NULL,
					        billing_address varchar(255) DEFAULT NULL,
					        payment_status varchar(30) DEFAULT NULL,
					        others text DEFAULT NULL,
					        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
					        updated_at datetime DEFAULT NULL,
					        PRIMARY KEY  (id),
					        KEY order_id  (order_id),
					        KEY user_id  (user_id),
					        KEY item_id  (item_id),
					        KEY post_id  (post_id),
					        KEY sp_id  (sp_id),
					        KEY order_status  (order_status),
					        KEY checkin  (checkin),
					        KEY tour_date  (tour_date),
					        KEY session_time  (session_time),
					        KEY avail  (post_id, tour_date, session_time)
					    ) $collate;";
				// Seat Plan Table
				$sp = "CREATE TABLE $sp_table (
					        id mediumint unsigned NOT NULL AUTO_INCREMENT,
					        name varchar(100) DEFAULT NULL,
					        total_seats mediumint NOT NULL DEFAULT 0,
					        layout_data longtext DEFAULT NULL,
					        seat_info longtext DEFAULT NULL,
					        others longtext DEFAULT NULL,
					        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
					        updated_at datetime DEFAULT NULL,
					        PRIMARY KEY  (id)
					    ) $collate;";
				if ( ! function_exists( 'dbDelta' ) ) {
					require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				}
				dbDelta( $abptt_orders );
				dbDelta( $sp );
				update_option( 'abptt_orders_schema_version', self::ORDER_SCHEMA_VERSION );
			}

			public function maybe_upgrade_order_schema(): void {
				global $wpdb;
				$order_table = $wpdb->prefix . 'abptt_orders';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table existence probe on init/upgrade.
				$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $order_table ) ) === $order_table;
				if ( ! $table_exists || (int) get_option( 'abptt_orders_schema_version', 0 ) < self::ORDER_SCHEMA_VERSION ) {
					self::create_table();
				}
			}

			public function plugin_settings_link( $links_array, $plugin_file_name ) {
				if ( strpos( $plugin_file_name, ABPTT_BASE ) ) {
					array_unshift( $links_array, '<a class="abp" href="' . esc_url( ABPTT_Function::build_url( 'configuration' ) ) . '">' . __( 'Configuration', 'abp-tour-travel' ) . '</a>' );
				}

				return $links_array;
			}

			public function flush_rewrite(): void {
				flush_rewrite_rules();
			}

			public function disable_gutenberg( $current_status, $post_type ) {
				if ( $post_type === ABPTT_Function::get_cpt() ) {
					return false;
				}

				return $current_status;
			}

			public function activation_redirect(): void {
				$active_tab = '';
				$page       = '';
				if ( isset( $_GET['_abptt_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_abptt_nonce'] ) ), 'abptt_url_action' ) ) {
					$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'dashboard';
					$page       = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
				}
				if ( $page === ABPTT_Function::slug() && ABPTT_WC < 2 && $active_tab != 'dashboard' ) {
					wp_safe_redirect( ABPTT_Function::build_url( 'dashboard' ) );
					exit;
				}
			}
		}
		new ABPTT_Dependencies();
	}
