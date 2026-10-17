<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( wp_is_block_theme() ) { ?>
		<!DOCTYPE html>
		<html  lang="" <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<title></title>
			<?php
				do_blocks( '<div class="wp-block-group"></div>' );
				wp_head();
			?>
		</head>
		<body <?php body_class(); ?>>
		<?php wp_body_open(); ?>
		<div class="wp-site-blocks">
			<header class="wp-block-template-part site-header">
				<?php block_header_area(); ?>
			</header>
		</div>
		<?php
	} else {
		get_header();
		if ( have_posts() ) {
			the_post();
		}
	}
	$term = get_queried_object();
	if ( $term instanceof WP_Term ) {
		printf( '<main class="abptt_area"><div class="abp_container"><h1 class="abptt_details_title">%s</h1>%s</div></main>', esc_html( $term->name ), do_shortcode( '[abptt-post cat_id="' . absint( $term->term_id ) . '"]' ) );
	}
	if ( wp_is_block_theme() ) {
		?>
		<footer class="wp-block-template-part">
			<?php block_footer_area(); ?>
		</footer>
		<?php wp_footer(); ?>
		</body>
		</html>
		<?php
	} else {
		get_footer();
	}