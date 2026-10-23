<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptt_guide_default_template', function ( $guides = [], $post_id = 0 ) {
		if ( empty( $guides ) || ! is_array( $guides ) ) {
			return;
		}
		?>
        <section class="abptt_guide abptt_guide_default _section">
            <div class="_fj_between_gap_xs">
                <h4 class="abp_gap_xs"><i class="fas fa-microphone-alt" aria-hidden="true"></i><?php echo esc_html( ABPTT_Function::guide_label() ); ?></h4>
                <span class="abptt_sp_count"><?php echo esc_html( number_format_i18n( count( $guides ) ) ); ?></span>
            </div>
            <div class="_divider_xs"></div>
            <div class="abptt_sp_list">
                <?php foreach ( $guides as $guide ) {
                    $name        = $guide['name'] ?? '';
                    $designation = $guide['designation'] ?? '';
                    $company     = $guide['company'] ?? '';
                    $bio         = $guide['description'] ?? '';
                    $website     = $guide['website'] ?? '';
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
                    <article class="abptt_sp_item">
                        <div class="abptt_sp_photo">
                            <?php $sp_photo_img = ABPTT_Function::guide_photo_img( $guide['photo_id'] ?? 0, 'thumbnail', $name, 'attachment-thumbnail size-thumbnail' );
                                if ( '' !== $sp_photo_img ) { ?>
                                    <?php if ( ! empty( $link ) ) { ?>
                                        <a href="<?php echo esc_url( $link ); ?>"><?php echo wp_kses_post( $sp_photo_img ); ?></a>
                                    <?php } else { ?>
                                        <?php echo wp_kses_post( $sp_photo_img ); ?>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="abptt_sp_initials" aria-hidden="true"><?php echo esc_html( $initials ); ?></span>
                                <?php } ?>
                        </div>
                        <div class="abptt_sp_content">
                            <h5 class="abptt_sp_name">
                                <?php if ( ! empty( $link ) ) { ?>
                                    <a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $name ); ?></a>
                                <?php } else { ?>
                                    <?php echo esc_html( $name ); ?>
                                <?php } ?>
                            </h5>
                            <?php if ( '' !== $designation || '' !== $company ) { ?>
                                <div class="abptt_sp_role">
                                    <?php
                                        $role = array_map( 'esc_html', array_filter( [ $designation, $company ] ) );
                                        echo wp_kses_post( implode( ' <span aria-hidden="true">&bull;</span> ', $role ) );
                                    ?>
                                </div>
                            <?php } ?>
                            <?php if ( '' !== trim( wp_strip_all_tags( $bio ) ) ) { ?>
                                <div class="abptt_sp_bio">
                                    <?php
                                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
                                        echo wp_kses_post( apply_filters( 'the_content', $bio ) );
                                    ?>
                                </div>
                            <?php } ?>
                            <?php if ( ! empty( $social ) || '' !== $website ) { ?>
                                <div class="abptt_sp_links">
                                    <?php if ( '' !== $website ) { ?>
                                        <a class="abptt_sp_link" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $website ); ?>"><i class="fas fa-globe" aria-hidden="true"></i><span class="screen-reader-text"><?php esc_html_e( 'Website', 'abp-tour-travel' ); ?></span></a>
                                    <?php } ?>
                                    <?php foreach ( $social as $item ) { ?>
                                        <a class="abptt_sp_link" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $item['label'] ); ?>"><i class="<?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i><span class="screen-reader-text"><?php echo esc_html( $item['label'] ); ?></span></a>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </article>
                <?php } ?>
            </div>
        </section>
		<?php
	}, 10, 2 );
