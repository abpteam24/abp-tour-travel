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
	//do_action( 'woocommerce_before_single_product' );
    $abptt_cart_success = function_exists( 'WC' ) && WC()->session ? WC()->session->get( 'abptt_cart_success' ) : '';
    if ( ! empty( $abptt_cart_success ) ) {
        ?>
        <div class="abptt_area">
            <div class="toast_notice" data-type="success">
                <?php echo esc_html( sanitize_text_field( $abptt_cart_success ) ); ?>
            </div>
        </div>
        <?php
        WC()->session->set( 'abptt_cart_success', null );
    }
    do_action( 'abptt_load_details_template', get_the_id() );
	if ( wp_is_block_theme() ) {
		?>
        <footer class="wp-block-template-part">
			<?php block_footer_area(); ?>
        </footer>
		<?php wp_footer(); ?>
        </body>
		<?php
	} else {
		get_footer();
	}