<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptt_guide_light_template', function ( $guides = [], $post_id = 0 ) {
		if ( empty( $guides ) || ! is_array( $guides ) ) {
			return;
		}
		?>
        <section class="abptt_guide abptt_guide_light">
            <div class="abptt_sp_light_head">
                <h4 class="abptt_sp_light_title"><?php echo esc_html( ABPTT_Function::guide_label() ); ?></h4>
                <span class="abptt_sp_light_sub"><?php
					// translators: %s: number of tour guides.
					echo esc_html( sprintf( _n( '%s guide', '%s guides', count( $guides ), 'abp-tour-travel' ), number_format_i18n( count( $guides ) ) ) );
				?></span>
            </div>
            <div class="abptt_sp_cards">
				<?php foreach ( $guides as $guide ) {
					$name        = $guide['name'] ?? '';
					$designation = $guide['designation'] ?? '';
					$company     = $guide['company'] ?? '';
					$bio         = $guide['description'] ?? '';
					$website     = $guide['website'] ?? '';
					$photo_url   = $guide['photo_url'] ?? '';
					$link        = $guide['link'] ?? '';
					$social      = $guide['social'] ?? [];
					$initials    = '';
					foreach ( preg_split( '/\s+/', trim( $name ) ) as $part ) {
						if ( '' !== $part ) {
							$initials .= mb_substr( $part, 0, 1 );
							if ( mb_strlen( $initials ) >= 2 ) {
								break;
							}
						}
					}
					$initials = mb_strtoupper( $initials );
					?>
                    <article class="abptt_sp_card">
                        <div class="abptt_sp_card_media">
							<?php $sp_photo_img = ABPTT_Function::guide_photo_img( $guide['photo_id'] ?? 0, 'medium_large', $name, 'attachment-medium_large size-medium_large' );
							if ( '' !== $sp_photo_img ) { ?>
								<?php echo wp_kses_post( $sp_photo_img ); ?>
							<?php } else { ?>
                                <span class="abptt_sp_initials" aria-hidden="true"><?php echo esc_html( $initials ); ?></span>
							<?php } ?>
                        </div>
                        <div class="abptt_sp_card_body">
                            <h5 class="abptt_sp_card_name">
								<?php if ( ! empty( $link ) ) { ?>
									<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $name ); ?></a>
								<?php } else { ?>
									<?php echo esc_html( $name ); ?>
								<?php } ?>
							</h5>
							<?php if ( '' !== $designation ) { ?>
								<div class="abptt_sp_card_designation"><?php echo esc_html( $designation ); ?></div>
							<?php } ?>
							<?php if ( '' !== $company ) { ?>
								<div class="abptt_sp_card_company"><?php echo esc_html( $company ); ?></div>
							<?php } ?>
							<?php if ( '' !== trim( wp_strip_all_tags( $bio ) ) ) { ?>
								<div class="abptt_sp_card_bio">
									<?php
										// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
										echo wp_kses_post( apply_filters( 'the_content', $bio ) );
									?>
								</div>
							<?php } ?>
                        </div>
						<?php if ( ! empty( $social ) || '' !== $website ) { ?>
							<div class="abptt_sp_card_foot">
								<?php if ( '' !== $website ) { ?>
									<a class="abptt_sp_link" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $website ); ?>"><i class="fas fa-globe" aria-hidden="true"></i><span class="screen-reader-text"><?php esc_html_e( 'Website', 'abp-tour-travel' ); ?></span></a>
								<?php } ?>
								<?php foreach ( $social as $item ) { ?>
									<a class="abptt_sp_link" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $item['label'] ); ?>"><i class="<?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i><span class="screen-reader-text"><?php echo esc_html( $item['label'] ); ?></span></a>
								<?php } ?>
							</div>
						<?php } ?>
                    </article>
				<?php } ?>
            </div>
        </section>
		<?php
	}, 10, 2 );
