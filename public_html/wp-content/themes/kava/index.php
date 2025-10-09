<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Kava
 */
get_header();

do_action( 'kava-theme/site/site-content-before', 'index' ); ?>

	<div <?php kava_content_class(); ?>>
		<div class="row">

			<?php do_action( 'kava-theme/site/primary-before', 'index' ); ?>

			<div id="primary" <?php kava_primary_content_class(); ?>>

				<?php do_action( 'kava-theme/site/main-before', 'index' ); ?>

				<main id="main" class="site-main">
					<?php if ( is_home() && ! is_paged() ) :
						$hero_title        = get_theme_mod( 'kava_home_hero_title', __( 'Create. Share. Inspire.', 'kava' ) );
						$hero_description  = get_theme_mod( 'kava_home_hero_description', __( 'Explore the latest stories, tutorials, and resources crafted by our digital studio.', 'kava' ) );
						$hero_button_label = get_theme_mod( 'kava_home_hero_button_label', __( 'Browse Latest Posts', 'kava' ) );
						$hero_button_url   = get_theme_mod( 'kava_home_hero_button_url' );
						$posts_page_id     = (int) get_option( 'page_for_posts' );

						if ( ! $hero_button_url && $posts_page_id ) {
							$hero_button_url = get_permalink( $posts_page_id );
						}

						if ( ! $hero_button_url ) {
							$hero_button_url = home_url( '/' );
						}

						$hero_image_id = (int) get_theme_mod( 'kava_home_hero_image_id' );
						$hero_image    = $hero_image_id ? wp_get_attachment_image_src( $hero_image_id, 'full' ) : false;
						$hero_style    = $hero_image ? sprintf( ' style="background-image: url(%s);"', esc_url( $hero_image[0] ) ) : '';
						?>
						<section class="home-hero" aria-label="<?php esc_attr_e( 'Site introduction', 'kava' ); ?>"<?php echo $hero_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<div class="home-hero__inner container">
								<?php if ( $hero_title ) : ?>
									<h1 class="home-hero__title"><?php echo esc_html( $hero_title ); ?></h1>
								<?php endif; ?>

								<?php if ( $hero_description ) : ?>
									<p class="home-hero__description"><?php echo esc_html( $hero_description ); ?></p>
								<?php endif; ?>

								<?php if ( $hero_button_label && $hero_button_url ) : ?>
									<a class="home-hero__cta btn btn-primary" href="<?php echo esc_url( $hero_button_url ); ?>">
										<?php echo esc_html( $hero_button_label ); ?>
									</a>
								<?php endif; ?>
							</div>
						</section>
					<?php endif; ?>

					<?php
					if ( have_posts() ) :
						if ( is_home() && ! is_front_page() ) :
							?>
							<header>
								<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
							</header>
							<?php
						endif;

						kava_theme()->do_location( 'archive', 'template-parts/posts-loop' );
					else :
						get_template_part( 'template-parts/content', 'none' );
					endif;
					?>
				</main><!-- #main -->

				<?php do_action( 'kava-theme/site/main-after', 'index' ); ?>

			</div><!-- #primary -->

			<?php do_action( 'kava-theme/site/primary-after', 'index' ); ?>

			<?php
			get_sidebar(); // Loads the sidebar.php template.
			?>
		</div>
	</div>

<?php do_action( 'kava-theme/site/site-content-after', 'index' );

get_footer();
