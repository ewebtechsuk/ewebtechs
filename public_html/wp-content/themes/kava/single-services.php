<?php
/**
 * The template for displaying single Service posts
 *
 * @package Kava
 */

get_header();

do_action( 'kava-theme/site/site-content-before', 'single-services' ); ?>

<div <?php kava_content_class(); ?>>
    <div class="row">

        <?php do_action( 'kava-theme/site/primary-before', 'single-services' ); ?>

        <div id="primary" <?php kava_primary_content_class(); ?> >

            <?php do_action( 'kava-theme/site/main-before', 'single-services' ); ?>

            <main id="main" class="site-main">
                <?php
                while ( have_posts() ) : the_post();

                    kava_theme()->do_location( 'single', 'template-parts/content-services' );

                endwhile; // End of the loop.
                ?>
            </main><!-- #main -->

            <?php do_action( 'kava-theme/site/main-after', 'single-services' ); ?>

        </div><!-- #primary -->

        <?php do_action( 'kava-theme/site/primary-after', 'single-services' ); ?>

        <?php get_sidebar(); // Loads the sidebar.php template. ?>
    </div>
</div>

<?php do_action( 'kava-theme/site/site-content-after', 'single-services' );

get_footer();
