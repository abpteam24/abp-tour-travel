<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTT_Frontend' ) ) {
		class ABPTT_Frontend {
			public function __construct() {
				add_filter( 'single_template', [ $this, 'load_single_page' ] );
				add_filter( 'template_include', array( $this, 'load_taxonomy_page' ) );
			}
			public function load_single_page( $template ): string {
				if ( is_singular( ABPTT_Function::get_cpt() ) ) {
					nocache_headers();
					$custom_template = ABPTT_Function::template_path( 'page/details_page.php' );
					if ( ! empty( $custom_template ) ) {
						return $custom_template;
					}
				}
				return (string) $template;
			}
			public function load_taxonomy_page( $template ): string {
				if ( in_array( get_query_var( 'taxonomy' ), [ 'abptt_category', 'abptt_location', 'abptt_organizer', 'abptt_guide' ], true ) ) {
					nocache_headers();
				}
				if ( is_tax( 'abptt_category' ) ) {
					return ABPTT_Function::template_path( 'page/category.php' );
				}
				if ( is_tax( 'abptt_location' ) ) {
					return ABPTT_Function::template_path( 'page/location.php' );
				}
				if ( is_tax( 'abptt_organizer' ) ) {
					return ABPTT_Function::template_path( 'page/organizer.php' );
				}
				if ( is_tax( 'abptt_guide' ) ) {
					return ABPTT_Function::template_path( 'page/guide.php' );
				}
				return (string) $template;
			}
		}
		new ABPTT_Frontend();
	}