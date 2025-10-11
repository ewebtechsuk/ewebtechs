<?php
/**
 * Front Page Template for eWeb Techs
 *
 * This template provides a bespoke static homepage for the eWeb Techs site.  It
 * replaces the default Kava index/page template by offering a dedicated hero
 * section, service highlights, portfolio showcase, testimonials and a clear
 * call-to-action.  Place this file in your child theme (e.g. `wp-content/themes/kava-child/front-page.php`).
 * WordPress will automatically use it for the site front page when
 * your Reading settings are configured to display a static page.
 *
 * The design uses semantic HTML with descriptive class names and hooks into
 * WordPress functions to keep content dynamic.  Feel free to extend the
 * sections with JetEngine widgets or Elementor templates.
 *
 * @package Kava_Child
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

get_header();

?>

<!-- Hero Section -->
<section class="et-hero-section" style="background:#0a192f;color:#ffffff;padding:60px 0;text-align:center;">
    <div class="container">
        <h1 style="font-size:2.5rem;margin-bottom:15px;">
            <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
        </h1>
        <p style="font-size:1.25rem;max-width:700px;margin:0 auto 30px;">
            We create responsive and modern websites to grow your business.
        </p>
        <a href="<?php echo esc_url( site_url( '/our-services' ) ); ?>" class="btn btn-primary" style="background:#64ffda;color:#0a192f;padding:15px 30px;border-radius:4px;font-weight:bold;text-decoration:none;">
            Our Services
        </a>
    </div>
</section>

<!-- Services Section -->
<section class="et-services-section" style="padding:60px 0;background:#f9f9f9;">
    <div class="container">
        <h2 style="text-align:center;margin-bottom:40px;">Our Expertise</h2>
        <div class="services-grid" style="display:flex;flex-wrap:wrap;justify-content:center;gap:30px;">
            <?php
            // Define an array of services with icon classes and descriptions.  These
            // could be converted to custom post types or JetEngine listings for
            // more flexibility.
            $services = [
                [
                    'icon' => 'dashicons-admin-site',
                    'title' => 'Website Design',
                    'description' => 'Crafting bespoke, responsive WordPress sites tailored to your brand.',
                    'link' => '/our-services/website-design',
                ],
                [
                    'icon' => 'dashicons-chart-line',
                    'title' => 'SEO & Marketing',
                    'description' => 'Boost your online presence with strategic SEO and digital marketing.',
                    'link' => '/our-services/seo-marketing',
                ],
                [
                    'icon' => 'dashicons-cloud',
                    'title' => 'CRM & Integrations',
                    'description' => 'Streamline workflows with custom CRM systems and API integrations.',
                    'link' => '/our-services/crm-integrations',
                ],
            ];
            foreach ( $services as $service ) :
                ?>
                <div class="service-item" style="flex:1 1 250px;background:#ffffff;padding:30px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.05);text-align:center;">
                    <span class="dashicons <?php echo esc_attr( $service['icon'] ); ?>" style="font-size:48px;color:#64ffda;margin-bottom:20px;"></span>
                    <h3 style="margin-bottom:15px;">
                        <?php echo esc_html( $service['title'] ); ?>
                    </h3>
                    <p style="margin-bottom:20px;">
                        <?php echo esc_html( $service['description'] ); ?>
                    </p>
                    <a href="<?php echo esc_url( site_url( $service['link'] ) ); ?>" class="btn" style="color:#64ffda;font-weight:bold;text-decoration:none;">Learn more →</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Portfolio Section -->
<section class="et-portfolio-section" style="padding:60px 0;">
    <div class="container">
        <h2 style="text-align:center;margin-bottom:40px;">Recent Projects</h2>
        <?php
        // Query recent portfolio items (replace 'portfolio' with your custom post type if needed).
        $portfolio_query = new WP_Query( [
            'post_type'      => 'portfolio',
            'posts_per_page' => 3,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );
        if ( $portfolio_query->have_posts() ) :
            echo '<div class="portfolio-grid" style="display:flex;flex-wrap:wrap;gap:30px;justify-content:center;">';
            while ( $portfolio_query->have_posts() ) :
                $portfolio_query->the_post();
                ?>
                <article class="portfolio-item" style="flex:1 1 300px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                    <a href="<?php the_permalink(); ?>" style="text-decoration:none;color:inherit;">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="portfolio-thumbnail" style="height:200px;overflow:hidden;">
                                <?php the_post_thumbnail( 'medium_large', [ 'style' => 'width:100%;height:100%;object-fit:cover;' ] ); ?>
                            </div>
                        <?php endif; ?>
                        <div class="portfolio-content" style="padding:20px;">
                            <h3 style="margin-bottom:10px;">
                                <?php the_title(); ?>
                            </h3>
                            <p style="margin-bottom:0;">
                                <?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?>
                            </p>
                        </div>
                    </a>
                </article>
                <?php
            endwhile;
            echo '</div>';
            wp_reset_postdata();
        else :
            echo '<p style="text-align:center;">No portfolio items found.</p>';
        endif;
        ?>
    </div>
</section>

<!-- Testimonials Section -->
<section class="et-testimonials-section" style="padding:60px 0;background:#f9f9f9;">
    <div class="container">
        <h2 style="text-align:center;margin-bottom:40px;">Client Testimonials</h2>
        <?php
        // If using a testimonials CPT or JetEngine listing, query here.  For this
        // example, we'll hardcode a few testimonials.
        $testimonials = [
            [
                'quote' => 'eWeb Techs transformed our online presence. Our new site not only looks fantastic but also converts better than ever.',
                'author' => 'Sarah K.',
                'role' => 'Marketing Director',
            ],
            [
                'quote' => 'The team delivered a custom CRM integration that streamlined our workflow and saved us hours each week.',
                'author' => 'James L.',
                'role' => 'Operations Manager',
            ],
            [
                'quote' => 'Their SEO services propelled us to the top of Google. We saw a tangible increase in traffic and leads.',
                'author' => 'Priya D.',
                'role' => 'Entrepreneur',
            ],
        ];
        echo '<div class="testimonials-grid" style="display:flex;flex-wrap:wrap;gap:30px;justify-content:center;">';
        foreach ( $testimonials as $testimonial ) :
            ?>
            <blockquote class="testimonial-item" style="flex:1 1 280px;background:#ffffff;padding:30px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                <p style="font-style:italic;margin-bottom:20px;">&ldquo;<?php echo esc_html( $testimonial['quote'] ); ?>&rdquo;</p>
                <cite style="display:block;font-weight:bold;">&ndash; <?php echo esc_html( $testimonial['author'] ); ?>, <span style="font-weight:normal;"><?php echo esc_html( $testimonial['role'] ); ?></span></cite>
            </blockquote>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="et-cta-section" style="padding:60px 0;background:#0a192f;color:#ffffff;">
    <div class="container" style="text-align:center;">
        <h2 style="margin-bottom:20px;">Ready to Elevate Your Digital Presence?</h2>
        <p style="margin-bottom:30px;max-width:700px;margin-left:auto;margin-right:auto;">
            Get in touch today to discuss how we can build a high-performing website, implement a custom CRM, or optimise your SEO strategy.
        </p>
        <a href="<?php echo esc_url( site_url( '/contact' ) ); ?>" class="btn btn-primary" style="background:#64ffda;color:#0a192f;padding:15px 30px;border-radius:4px;font-weight:bold;text-decoration:none;">
            Contact Us
        </a>
    </div>
</section>

<?php
get_footer();

