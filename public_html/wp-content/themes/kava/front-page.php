<?php
/**
 * Custom front page template for eWeb Techs.
 *
 * @package Kava
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php bloginfo( 'name' ); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
    <style>
        :root {
            --primary: #0f62fe;
            --secondary: #ffb703;
            --dark: #0b1b2b;
            --muted: #5f7383;
            --gradient: linear-gradient(135deg, rgba(15,98,254,.95) 0%, rgba(79,70,229,.9) 100%);
        }
        body {
            font-family: 'Roboto', Arial, sans-serif;
            color: var(--dark);
            background-color: #f4f7fb;
        }
        .navbar {
            transition: box-shadow .3s ease;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: .5px;
        }
        .navbar.scrolled {
            box-shadow: 0 .5rem 1.5rem rgba(11,27,43,.08);
            background-color: #fff !important;
        }
        .btn-primary {
            background-image: var(--gradient);
            border: none;
            box-shadow: 0 .75rem 1.5rem rgba(15,98,254,.25);
        }
        .btn-outline-light:hover {
            color: #fff;
            background-color: rgba(255,255,255,.1);
        }
        .hero {
            position: relative;
            background: radial-gradient(circle at top left, rgba(255,255,255,.12), rgba(11,27,43,.3)),
                        url('https://images.unsplash.com/photo-1523475472560-d2df97ec485c?auto=format&fit=crop&w=1600&q=80') center/cover;
            min-height: 90vh;
            color: #fff;
            display: flex;
            align-items: center;
        }
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(5,12,30,.75);
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .hero-content .badge {
            background: rgba(255,255,255,.1);
            padding: .75rem 1.5rem;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: .75rem;
        }
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .hero-stat {
            background-color: rgba(255,255,255,.08);
            border-radius: .75rem;
            padding: 1.5rem;
        }
        .hero-stat h4 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }
        section {
            scroll-margin-top: 5rem;
        }
        .section-title {
            max-width: 700px;
            margin: 0 auto 3rem;
        }
        .service-card {
            background-color: #fff;
            border: none;
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            box-shadow: 0 1.5rem 3rem rgba(15,98,254,.08);
            transition: transform .3s ease, box-shadow .3s ease;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 1.75rem 3.5rem rgba(11,27,43,.12);
        }
        .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: rgba(15,98,254,.12);
            color: var(--primary);
            font-size: 1.5rem;
            margin: 0 auto 1.25rem;
        }
        .bg-soft-primary {
            background: rgba(15,98,254,.08);
        }
        .bg-soft-secondary {
            background: rgba(255,183,3,.12);
        }
        .process-step {
            background-color: #fff;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 1rem 2.5rem rgba(11,27,43,.06);
            height: 100%;
        }
        .process-step span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--gradient);
            color: #fff;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .testimonial {
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 1.25rem;
            box-shadow: 0 1.5rem 3rem rgba(11,27,43,.08);
            height: 100%;
        }
        .testimonial .bi {
            font-size: 2rem;
            color: var(--secondary);
        }
        .logos img {
            filter: grayscale(1);
            opacity: .5;
            transition: opacity .3s ease, filter .3s ease;
        }
        .logos img:hover {
            filter: grayscale(0);
            opacity: 1;
        }
        .cta-banner {
            background: var(--gradient);
            color: #fff;
            border-radius: 1.5rem;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }
        .cta-banner::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            top: -60px;
            right: -60px;
        }
        footer {
            background-color: #0a1624;
        }
    </style>
</head>
<body <?php body_class( 'ewebtechs-front-page' ); ?>>
<?php
if ( function_exists( 'kava_body_open' ) ) {
    kava_body_open();
} elseif ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
}
?>
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top py-3">
        <div class="container">
            <a class="navbar-brand" href="#">eWeb Techs</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#process">Process</a></li>
                    <li class="nav-item"><a class="nav-link" href="#portfolio">Work</a></li>
                    <li class="nav-item"><a class="nav-link" href="#testimonials">Reviews</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
                <a class="btn btn-primary ms-lg-3 mt-3 mt-lg-0" href="#contact">Start a Project</a>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="container hero-content text-center">
            <span class="badge text-white mb-4">Award-winning London Web Studio</span>
            <h1 class="display-4 fw-bold mb-3">Growth-driven websites built to convert visitors into customers.</h1>
            <p class="lead mx-auto" style="max-width: 650px;">We design, develop and optimise high-performing digital experiences for ambitious SMEs, SaaS brands and professional services. From strategy to launch in as little as 30 days.</p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-4">
                <a class="btn btn-primary btn-lg px-4" href="#contact">Book a Discovery Call</a>
                <a class="btn btn-outline-light btn-lg px-4" href="#portfolio">See Our Work</a>
            </div>
            <div class="hero-stats mt-5">
                <div class="hero-stat">
                    <h4>320%</h4>
                    <p class="mb-0">Average increase in qualified leads after redesign.</p>
                </div>
                <div class="hero-stat">
                    <h4>4.9/5</h4>
                    <p class="mb-0">Client rating across Clutch, TrustPilot and Google.</p>
                </div>
                <div class="hero-stat">
                    <h4>12+</h4>
                    <p class="mb-0">Years crafting conversion-focused digital products.</p>
                </div>
            </div>
        </div>
    </header>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <p class="text-uppercase text-primary fw-semibold mb-2">Why eWeb Techs</p>
                    <h2 class="fw-bold mb-3">Your strategic partner for digital acceleration</h2>
                    <p class="text-muted mb-4">We combine data-driven insights with award-winning creative to deliver lightning-fast, SEO optimised websites that capture attention, communicate value and close business around the clock.</p>
                    <div class="d-flex gap-4 flex-wrap">
                        <div>
                            <h4 class="fw-bold mb-1">98% Retention</h4>
                            <p class="text-muted mb-0">Clients stay with us because we deliver measurable ROI.</p>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">Conversion First</h4>
                            <p class="text-muted mb-0">We align design decisions to your funnel and KPIs.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="service-card text-center h-100">
                                <div class="icon-circle"><i class="bi bi-window"></i></div>
                                <h5 class="fw-bold">Custom Web Design</h5>
                                <p class="text-muted">Distinctive brand experiences with pixel-perfect responsive layouts.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="service-card text-center h-100">
                                <div class="icon-circle"><i class="bi bi-bag-check"></i></div>
                                <h5 class="fw-bold">Ecommerce Growth</h5>
                                <p class="text-muted">High-converting Shopify and WooCommerce stores engineered to sell.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="service-card text-center h-100">
                                <div class="icon-circle"><i class="bi bi-bar-chart-line"></i></div>
                                <h5 class="fw-bold">Performance SEO</h5>
                                <p class="text-muted">Technical optimisation, content strategy and analytics reporting.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="service-card text-center h-100">
                                <div class="icon-circle"><i class="bi bi-lightning-charge"></i></div>
                                <h5 class="fw-bold">Growth Sprints</h5>
                                <p class="text-muted">Rapid CRO experimentation to lift conversion and retention.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="py-5">
        <div class="container">
            <div class="section-title text-center">
                <p class="text-uppercase text-primary fw-semibold">Services</p>
                <h2 class="fw-bold">Full-stack expertise for every touchpoint</h2>
                <p class="text-muted">From the first wireframe to ongoing optimisation, our multidisciplinary team keeps your digital experience performing at its peak.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="service-card h-100 text-center">
                        <div class="icon-circle"><i class="bi bi-pencil-square"></i></div>
                        <h6 class="fw-bold">Brand Strategy</h6>
                        <p class="text-muted mb-0">Workshops, messaging frameworks and visual identity guidelines.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="service-card h-100 text-center">
                        <div class="icon-circle"><i class="bi bi-phone"></i></div>
                        <h6 class="fw-bold">UX &amp; Prototyping</h6>
                        <p class="text-muted mb-0">User research, journey mapping and prototype testing to validate fast.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="service-card h-100 text-center">
                        <div class="icon-circle"><i class="bi bi-code-slash"></i></div>
                        <h6 class="fw-bold">Web Development</h6>
                        <p class="text-muted mb-0">Scalable WordPress, Webflow and headless builds with enterprise security.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="service-card h-100 text-center">
                        <div class="icon-circle"><i class="bi bi-rocket"></i></div>
                        <h6 class="fw-bold">Launch &amp; Optimisation</h6>
                        <p class="text-muted mb-0">Analytics setup, CRO roadmaps and retained support.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="portfolio" class="py-5 bg-light">
        <div class="container">
            <div class="section-title text-center">
                <p class="text-uppercase text-primary fw-semibold">Case Studies</p>
                <h2 class="fw-bold">Recent launches delivering powerful business outcomes</h2>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=800&q=80" class="card-img-top" alt="SaaS dashboard redesign">
                        <div class="card-body">
                            <h5 class="fw-bold">SaaS Platform Revamp</h5>
                            <p class="text-muted">Boosted free trial sign-ups by 210% with a frictionless onboarding journey.</p>
                            <span class="badge bg-soft-primary text-primary">Product UX</span>
                            <span class="badge bg-soft-secondary text-dark">Automation</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80" class="card-img-top" alt="Ecommerce store design">
                        <div class="card-body">
                            <h5 class="fw-bold">Luxury Ecommerce Sprint</h5>
                            <p class="text-muted">Delivered a bespoke Shopify store with 3x higher conversion in month one.</p>
                            <span class="badge bg-soft-primary text-primary">Shopify</span>
                            <span class="badge bg-soft-secondary text-dark">CRO</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80" class="card-img-top" alt="Professional services website">
                        <div class="card-body">
                            <h5 class="fw-bold">Professional Services Site</h5>
                            <p class="text-muted">Launched new positioning and site in 5 weeks, unlocking enterprise leads.</p>
                            <span class="badge bg-soft-primary text-primary">WordPress</span>
                            <span class="badge bg-soft-secondary text-dark">Branding</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="process" class="py-5">
        <div class="container">
            <div class="section-title text-center">
                <p class="text-uppercase text-primary fw-semibold">Our Process</p>
                <h2 class="fw-bold">A proven delivery framework engineered for speed and quality</h2>
                <p class="text-muted">We break complex projects into agile sprints to ensure clarity, collaboration and results your competitors can’t match.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="process-step">
                        <span>01</span>
                        <h5 class="fw-bold">Discovery &amp; Strategy</h5>
                        <p class="text-muted mb-0">Stakeholder workshops, competitor benchmarking and KPI alignment.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <span>02</span>
                        <h5 class="fw-bold">UX &amp; Creative</h5>
                        <p class="text-muted mb-0">Wireframes, moodboards and high-fidelity concepts tailored to personas.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <span>03</span>
                        <h5 class="fw-bold">Build &amp; Integrate</h5>
                        <p class="text-muted mb-0">Component-based development, CMS setup, integrations and QA.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <span>04</span>
                        <h5 class="fw-bold">Launch &amp; Scale</h5>
                        <p class="text-muted mb-0">Performance tuning, analytics dashboards and optimisation roadmap.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="py-5 bg-white">
        <div class="container">
            <div class="section-title text-center">
                <p class="text-uppercase text-primary fw-semibold">Testimonials</p>
                <h2 class="fw-bold">Trusted by marketing leaders and founders across the UK</h2>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="testimonial">
                        <i class="bi bi-quote"></i>
                        <p class="mt-3">“eWeb Techs rebuilt our SaaS website and enquiries jumped 4x within six weeks. Their team feels like an extension of ours.”</p>
                        <strong class="d-block">Alex Patel</strong>
                        <span class="text-muted">Chief Marketing Officer, Flux Analytics</span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial">
                        <i class="bi bi-quote"></i>
                        <p class="mt-3">“The level of strategic thinking and execution is world-class. We finally have a website that tells our story and converts.”</p>
                        <strong class="d-block">Jamie Lewis</strong>
                        <span class="text-muted">Founder, Sterling Legal</span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial">
                        <i class="bi bi-quote"></i>
                        <p class="mt-3">“From branding to launch, everything was seamless. Their CRO roadmap continues to deliver monthly gains.”</p>
                        <strong class="d-block">Morgan Shaw</strong>
                        <span class="text-muted">Head of Digital, Horizon Retail</span>
                    </div>
                </div>
            </div>
            <div class="logos d-flex justify-content-center align-items-center gap-4 flex-wrap mt-5">
                <img src="https://dummyimage.com/120x40/ced4da/212529&text=Flux" alt="Flux Analytics logo" height="40">
                <img src="https://dummyimage.com/120x40/ced4da/212529&text=Sterling" alt="Sterling Legal logo" height="40">
                <img src="https://dummyimage.com/120x40/ced4da/212529&text=Horizon" alt="Horizon Retail logo" height="40">
                <img src="https://dummyimage.com/120x40/ced4da/212529&text=Pulse" alt="Pulse Health logo" height="40">
            </div>
        </div>
    </section>

    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="cta-banner h-100 d-flex flex-column justify-content-center">
                        <h2 class="fw-bold mb-3">Ready to outrank and outperform your competitors?</h2>
                        <p class="lead mb-4">Tell us about your goals and we’ll craft a personalised action plan with timelines, investment and quick-win recommendations.</p>
                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <a class="btn btn-light btn-lg text-primary" href="mailto:info@ewebtechs.com">Email the team</a>
                            <a class="btn btn-outline-light btn-lg" href="tel:02037402750">Call 0203 740 2750</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-white p-4 p-lg-5 rounded-4 shadow-sm">
                        <h4 class="fw-bold mb-4">Tell us about your project</h4>
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" placeholder="Full name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" placeholder="you@company.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company</label>
                                    <input type="text" class="form-control" placeholder="Business name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Budget Range</label>
                                    <select class="form-select">
                                        <option>£5k - £10k</option>
                                        <option>£10k - £25k</option>
                                        <option>£25k - £50k</option>
                                        <option>£50k+</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Project Goals</label>
                                    <textarea class="form-control" rows="4" placeholder="Tell us what success looks like"></textarea>
                                </div>
                                <div class="col-12 d-grid d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary btn-lg px-4">Request Proposal</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-4 bg-dark text-white">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> eWeb Techs. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    </script>
    <?php wp_footer(); ?>
</body>
</html>
