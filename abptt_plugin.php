<?php
	/**
	 * Plugin Name: ABP Tour & Travel
	 * Description: WooCommerce tour and travel booking system. Create tours with fixed, recurring or specific schedules, sell admission tickets and reserved seats, add extra services, itinerary and timelines, and manage bookings, seats and revenue.
	 * Version: 1.0.0
	 * Author: abpteam
	 * Author URI: https://abp-team.com
	 * Text Domain: abp-tour-travel
	 * Domain Path: /languages
	 * WC requires at least: 8.0.0
	 * WC tested up to: 11.2
	 * Requires at least: 6.2
	 * Requires PHP: 7.4
	 * Requires MySQL: 5.7+
	 * License: GPLv2 or later
	 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTT_Plugin' ) ) {
		class ABPTT_Plugin {
			public function __construct() {
				add_action( 'admin_init', function () {
					if ( ! function_exists( 'is_plugin_active' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
				} );
				add_action(
					'before_woocommerce_init', // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
					function () {
						if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
							\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
								'custom_order_tables',
								__FILE__,
								true
							);
						}
					}
				);
				$this->load_plugin();
			}

			private function load_plugin(): void {
				if ( ! defined( 'ABPTT_Plugin_FILE' ) ) {
					define( 'ABPTT_Plugin_FILE', __FILE__ );
				}
				if ( ! defined( 'ABPTT_VERSION' ) ) {
					define( 'ABPTT_VERSION', '1.0.0' );
				}
				if ( ! defined( 'ABPTT_DIR' ) ) {
					define( 'ABPTT_DIR', plugin_dir_path( __FILE__ ) );
				}
				if ( ! defined( 'ABPTT_URL' ) ) {
					define( 'ABPTT_URL', plugin_dir_url( __FILE__ ) );
				}
				if ( ! defined( 'ABPTT_BASE' ) ) {
					define( 'ABPTT_BASE', basename( __FILE__ ) );
				}
				if ( ! defined( 'ABPTT_BLANK_IMG_URL' ) ) {
					define( 'ABPTT_BLANK_IMG_URL', ABPTT_URL . 'assets/images/blank_image.png' );
				}
				require_once ABPTT_DIR . 'includes/abptt_dependencies.php';
				if ( ! defined( 'ABPTT_WC' ) ) {
					define( 'ABPTT_WC', ABPTT_Function::check_wc() );
				}
				if ( ! defined( 'ABPTT_Configuration' ) ) {
					define( 'ABPTT_Configuration', ABPTT_Function::get_option( 'abptt_configuration' ) );
				}
				if ( ! defined( 'ABPTT_Date_Config' ) ) {
					define( 'ABPTT_Date_Config', ABPTT_Function::get_option( 'abptt_date_config' ) );
				}
				if ( ! defined( 'ABPTT_Dates' ) ) {
					define( 'ABPTT_Dates', ABPTT_Function::get_option( 'abptt_dates' ) );
				}
				if ( ! defined( 'ABPTT_Category' ) ) {
					define( 'ABPTT_Category', ABPTT_Function::get_option( 'abptt_category' ) );
				}
				if ( ! defined( 'ABPTT_Organizer' ) ) {
					define( 'ABPTT_Organizer', ABPTT_Function::get_option( 'abptt_organizer' ) );
				}
				if ( ! defined( 'ABPTT_Feature' ) ) {
					define( 'ABPTT_Feature', ABPTT_Function::get_option( 'abptt_feature' ) );
				}
				if ( ! defined( 'ABPTT_Location' ) ) {
					define( 'ABPTT_Location', ABPTT_Function::get_option( 'abptt_location' ) );
				}
				if ( ! defined( 'ABPTT_Guide' ) ) {
					define( 'ABPTT_Guide', ABPTT_Function::get_option( 'abptt_guide' ) );
				}
				if ( ! defined( 'ABPTT_ids' ) ) {
					define( 'ABPTT_ids', ABPTT_Query::get_post_id());
				}
				if ( ! defined( 'ABPTT_Ticket' ) ) {
					define( 'ABPTT_Ticket', ABPTT_Function::get_option( 'abptt_ticket' ) );
				}
				if ( ! defined( 'ABPTT_Decor' ) ) {
					define( 'ABPTT_Decor', ABPTT_Function::get_option( 'abptt_decor' ) );
				}
				if ( ! defined( 'ABPTT_Ticket_SP' ) ) {
					define( 'ABPTT_Ticket_SP', ABPTT_Function::get_option( 'abptt_ticket_sp' ) );
				}
				if ( ! defined( 'ABPTT_On_Off' ) ) {
					define( 'ABPTT_On_Off', ABPTT_Function::get_option( 'abptt_on_off' ) );
				}
				if ( ! defined( 'ABPTT_JS_Date_Format' ) ) {
					define( 'ABPTT_JS_Date_Format', ABPTT_Function::date_format_js() );
				}
				if ( ! defined( 'ABPTT_Time_Format' ) ) {
					define( 'ABPTT_Time_Format', ABPTT_Date_Config['time_format'] ?? get_option( 'time_format' ) );
				}
			}
		}
		new ABPTT_Plugin();
		register_activation_hook( __FILE__, function () {
			if ( class_exists( 'ABPTT_Dependencies' ) ) {
				ABPTT_Dependencies::activation();
			}
		} );
		register_deactivation_hook( __FILE__, function () {
			if ( class_exists( 'ABPTT_Dependencies' ) ) {
				ABPTT_Dependencies::deactivate();
			}
		} );
		register_uninstall_hook( __FILE__, 'abptt_uninstall' );
	}

	/**
	 * Clean up plugin data on uninstall.
	 *
	 * Invoked by WordPress when the plugin is deleted, via the
	 * register_uninstall_hook call in abptt_plugin.php.
	 *
	 * Honours the remove_uninstall switch: with it off nothing is deleted.
	 */
	function abptt_uninstall(): void {
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			return;
		}

		$abptt_on_off = get_option( 'abptt_on_off', array() );
		$remove_data  = is_array( $abptt_on_off ) && isset( $abptt_on_off['remove_uninstall'] ) ? sanitize_text_field( $abptt_on_off['remove_uninstall'] ) : 'off';

		if ( 'on' !== $remove_data ) {
			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . 'abptt_orders' ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . 'abptt_sp' ) );

		$abptt_options = array(
			'abptt_configuration',
			'abptt_on_off',
			'abptt_date_config',
			'abptt_dates',
			'abptt_category',
			'abptt_location',
			'abptt_organizer',
			'abptt_guide',
			'abptt_feature',
			'abptt_ticket',
			'abptt_ticket_sp',
			'abptt_decor',
			'abptt_additional',
			'abptt_form',
			'abptt_faq',
			'abptt_tc',
			'abptt_place',
			'abptt_color',
			'abptt_css_var',
			'abptt_slider',
			'abptt_contact',
			'abptt_per_page_item',
			'abptt_page',
			'abptt_why_book',
			'abptt_my_account_flushed',
			'abptt_dummy_registry',
			'abptt_orders_schema_version',
			'abptt_activation_redirect',
		);

		foreach ( $abptt_options as $option ) {
			delete_option( $option );
		}

		$posts = get_posts( array(
			'post_type'      => 'abptt_post',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );
		foreach ( $posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}

		$taxonomies = array( 'abptt_category', 'abptt_location', 'abptt_organizer', 'abptt_guide', 'abptt_place' );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_terms( array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'fields'     => 'ids',
			) );
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term_id ) {
					wp_delete_term( $term_id, $taxonomy );
				}
			}
		}
	}