<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( wp_is_block_theme() ) { ?>
        <!DOCTYPE html>
        <html lang="" <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo( 'charset' ); ?>">
            <title><?php echo esc_html( wp_get_document_title() ); ?></title>
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
	/**
	 * Every value this template prints comes from a single filtered view model, so
	 * a site can surface its own guide data through the abptt_guide_* filters
	 * instead of overriding this template. See ABPTT_Function::guide_page_data().
	 */
	$abptt_guide = ABPTT_Function::guide_page_data( get_queried_object() );
	if ( ! empty( $abptt_guide ) ) {
		$abptt_name = $abptt_guide['name'];
		?>
        <main class="abptt_area abptt_guide_profile_page">
            <div class="abp_container">
                <article class="abptt_guide_card">
                    <header class="abptt_guide_card_head">
                        <div class="abptt_guide_hero_ring">
                            <div class="abptt_guide_hero_photo">
								<?php if ( '' !== $abptt_guide['photo_img'] ) {
									echo wp_kses_post( $abptt_guide['photo_img'] );
								} else { ?>
                                        <span class="abptt_sp_initials" aria-hidden="true"><?php echo esc_html( $abptt_guide['initials'] ); ?></span>
									<?php } ?>
                            </div>
                        </div>
                        <h1 class="abptt_guide_hero_name"><?php echo esc_html( $abptt_name ); ?></h1>
					<?php if ( ! empty( $abptt_guide['role_parts'] ) ) { ?>
                        <div class="abptt_guide_hero_role"><?php echo wp_kses_post( implode( ' <span aria-hidden="true">&bull;</span> ', $abptt_guide['role_parts'] ) ); ?></div>
						<?php } ?>
						<?php if ( ! empty( $abptt_guide['facts'] ) ) { ?>
                            <dl class="abptt_guide_facts">
								<?php foreach ( $abptt_guide['facts'] as $abptt_fact ) { ?>
                                    <div class="abptt_guide_fact">
                                        <dt class="abptt_guide_fact_label">
                                            <span class="abptt_guide_fact_icon" aria-hidden="true"><i class="<?php echo esc_attr( $abptt_fact['icon'] ); ?>"></i></span>
											<?php echo esc_html( $abptt_fact['label'] ); ?>
                                        </dt>
										<?php if ( '' !== $abptt_fact['url'] ) { ?>
                                            <dd class="abptt_guide_fact_value"><a class="abptt_guide_fact_link" href="<?php echo esc_url( $abptt_fact['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $abptt_fact['value'] ); ?></a></dd>
										<?php } else { ?>
                                            <dd class="abptt_guide_fact_value"><?php echo esc_html( $abptt_fact['value'] ); ?></dd>
										<?php } ?>
                                    </div>
								<?php } ?>
                            </dl>
						<?php } ?>
						<?php if ( ! empty( $abptt_guide['socials'] ) ) { ?>
                            <div class="abptt_guide_hero_socials">
								<?php foreach ( $abptt_guide['socials'] as $abptt_social ) { ?>
                                    <a class="abptt_guide_social" href="<?php echo esc_url( $abptt_social['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $abptt_social['label'] ); ?>">
                                        <i class="<?php echo esc_attr( $abptt_social['icon'] ); ?>" aria-hidden="true"></i>
                                        <span><?php echo esc_html( $abptt_social['label'] ); ?></span>
                                    </a>
								<?php } ?>
                            </div>
						<?php } ?>
                    </header>
					<?php if ( $abptt_guide['has_bio'] ) { ?>
                        <div class="abptt_guide_bio_wrap">
                            <h2 class="abptt_guide_block_title">
                                <span class="abptt_guide_block_icon" aria-hidden="true"><i class="fas fa-quote-left"></i></span>
								<?php esc_html_e( 'Biography', 'abp-tour-travel' ); ?>
                            </h2>
                            <div class="abptt_guide_bio">
								<?php
									echo wp_kses_post( ABPTT_Function::format_content( $abptt_guide['bio'] ) );
								?>
                            </div>
                        </div>
					<?php } ?>
                </article>
            </div>
        </main>
		<?php
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
