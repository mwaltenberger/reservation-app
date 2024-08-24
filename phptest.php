<?php
// Dazzle Theme functions and definitions.
require_once ( get_template_directory() . '/framework/plugins/class-tgm-plugin-activation.php');

if ( file_exists( get_template_directory() . '/framework/admin/admin-init.php' ) ) {
    require_once( get_template_directory() . '/framework/admin/admin-init.php' );
}

if ( file_exists( get_template_directory() . '/framework/meta-box/meta-box-framework/meta-box.php' ) ) {
    require_once( get_template_directory() . '/framework/meta-box/meta-box-framework/meta-box.php' );
}

// Extensions
include( get_template_directory().'/framework/meta-box/meta-box-extensions/meta-box-show-hide/meta-box-show-hide.php');
include( get_template_directory().'/framework/meta-box/meta-box-extensions/meta-box-conditional-logic/meta-box-conditional-logic.php');
include( get_template_directory().'/framework/meta-box/meta-box-extensions/meta-box-tabs/meta-box-tabs.php');
include( get_template_directory().'/framework/meta-box/meta-box-extensions/meta-box-columns/meta-box-columns.php');
include( get_template_directory() . '/framework/meta-box/meta-box-config.php' );

require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/breadcrumbs.php';
require get_template_directory() . '/inc/navigation.php';
require get_template_directory() . '/inc/jetpack.php';

include (get_template_directory()."/inc/image-resizer.php");
include (get_template_directory()."/framework/widgets/widget-social.php");

include (get_template_directory()."/framework/extend_woocommerce.php");

require_once (get_template_directory().'/framework/plugins/envato_setup/envato_setup.php');

class Dazzle_Delicious {

    function __construct() {
        add_action( 'after_setup_theme', array($this, 'dazzle_wooc_init' ));
        add_action( 'after_setup_theme', array($this, 'dazzle_setup' ) );
        add_action( 'after_setup_theme', array($this, 'dazzle_content_width'), 0 );
        add_action( 'widgets_init', array($this, 'dazzle_widgets_init') );
        add_action( 'init', array($this, 'dazzle_image_sizes') );
        add_action( 'wp_enqueue_scripts', array($this, 'dazzle_scripts') );
        add_action( 'admin_print_footer_scripts', array($this, 'dazzle_add_quicktags') );
        add_action( 'tgmpa_register',  array($this, 'dazzle_register_required_plugins') );
        add_action( 'init', array($this, 'dazzle_remove_redux_notices') );
        add_action( 'wp_head', array($this, 'dazzle_header_custom_js') ) ;
        add_action( 'wp_footer', array($this, 'dazzle_footer_custom_js') );
        add_action( 'wp_print_scripts', array($this, 'dazzle_dequeue_script'), 100 );

        add_filter( 'the_content_more_link', array($this, 'dazzle_wrap_readmore'), 10, 1 );
        add_filter( 'excerpt_length', array($this, 'dazzle_custom_excerpt_length'), 999 );
        add_filter( 'excerpt_more', array($this, 'dazzle_excerpt_more') );
        add_filter( 'the_content_more_link', array($this, 'dazzle_remove_more_link_scroll') );
        add_filter( 'upload_mimes', array($this, 'dazzle_mime_types') );

        add_filter('dazzle_theme_setup_wizard_username', array($this,'dazzle_set_theme_setup_wizard_username'), 10);
        add_filter('dazzle_theme_setup_wizard_oauth_script', array($this,'dazzle_set_theme_setup_wizard_oauth_script'), 10);

        add_action( 'pt-ocdi/after_import', array($this,'dazzle_ocdi_after_import_setup') );
        add_filter( 'pt-ocdi/import_files', array($this,'dazzle_ocdi_import_files') );
        add_filter( 'pt-ocdi/disable_pt_branding', '__return_true' );
        add_filter( 'pt-ocdi/regenerate_thumbnails_in_content_import', '__return_false' );

        add_filter('wp_nav_menu_items',array($this, 'dazzle_add_more_menu_item'), 10, 2);

        add_filter('loop_shop_columns', array($this, 'dazzle_loop_columns') );

        add_filter( 'the_password_form', array($this, 'dazzle_password_form' ));
    }

    // woocommerce theme support
    public function dazzle_wooc_init () {
        add_theme_support( 'woocommerce' );
        // add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );
    }

    // Theme setups
    public function dazzle_setup() {

        load_theme_textdomain( 'dazzle', get_template_directory() . '/languages' );
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );

        register_nav_menus( array(
            'primary' => esc_html__( 'Primary Menu', 'dazzle' ),
        ) );

        add_theme_support( 'html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ) );

        add_theme_support( 'post-formats', array(
            'video',
            'gallery',
            'quote'
        ) );
    }

    // Set the content width in pixels, based on the theme's design and stylesheet.
    public function dazzle_content_width() {
        $GLOBALS['content_width'] = apply_filters( 'dazzle_content_width', 1280 );
    }

    // Register blog sidebar, footer and custom sidebar
    public function dazzle_widgets_init() {
        register_sidebar(array(
            'name' => esc_html__( 'Blog Sidebar', 'dazzle' ),
            'id' => 'sidebar',
            'description' => esc_html__( 'Widgets in this area will be shown in the sidebar.', 'dazzle' ),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ));

        register_sidebar(array(
            'name' => esc_html__( 'Footer', 'dazzle' ),
            'id' => 'footer',
            'description' => esc_html__( 'Widgets in this area will be shown in the footer.', 'dazzle' ),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ));

        register_sidebar(array(
            'name' => esc_html__( 'Page Sidebar', 'dazzle' ),
            'id' => 'page-sidebar',
            'description' => esc_html__( 'Widgets in this area will be shown in the sidebar of any page.', 'dazzle' ),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ));
        register_sidebar(array(
            'name' => esc_html__( 'Sliding Menu Sidebar', 'dazzle' ),
            'id' => 'sliding-menu-sidebar',
            'description' => esc_html__( 'Widgets in this area will be shown in the sidebar of the sliding menu. You can enable it from Appearance->Delicious Options->Header: Vertical Sliding Menu.', 'dazzle' ),
            'before_widget' => '<aside id="%1$s" class="darker-overlay widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ));
    }


    // Set different thumbnail dimensions
    public function dazzle_image_sizes() {
        add_image_size( 'dazzle-blog-thumbnail', 1120, 9999, false ); 	// Blog thumbnails
        add_image_size( 'dazzle-blog-grid-thumbnail', 640, 9999, false ); // Blog thumbnails
        add_image_size( 'dazzle-full-size',  9999, 9999, false ); 		// Full Size
    }


    // Enqueue scripts and styles.

    public function dazzle_dequeue_script() {
        wp_dequeue_script( 'isotope' );
    }

    public function dazzle_scripts() {
        $dazzle_data = dazzle_dt_data();
        global $post;

        $dazzle_postid = '';
        if( !is_404() || !is_search() ) {
            if($post != NULL) {
                $dazzle_postid = $post->ID;
            }
        }

        wp_enqueue_style( 'dazzle-style', get_stylesheet_uri() );

        wp_enqueue_style( 'fontawesome', get_template_directory_uri() . '/assets/fonts/font-awesome/css/font-awesome.css' );
        wp_enqueue_style( 'et-line', get_template_directory_uri() . '/assets/fonts/et-line-font/et-line.css' );


        // preloader
        if(isset($dazzle_data['dazzle_enable_preloader'])) {
            if($dazzle_data['dazzle_enable_preloader'] != 0) {
                wp_enqueue_script('dazzle-qloader', get_template_directory_uri() . '/assets/js/plugins/jquery.queryloader2.js', array('jquery'), '1.0', false );
                wp_enqueue_script('dazzle-custom-loader', get_template_directory_uri() . '/assets/js/custom-loader.js', array('jquery', 'dazzle-qloader'), '1.0', false );

                $dazzle_preloader_logo_output = '<div id="spinner"></div>';
                if(isset($dazzle_data['dazzle_preloader_logo']['url']) && ($dazzle_data['dazzle_preloader_logo']['url'] !='')) {
                    $dazzle_preloader_logo_output = '<div id="preloader-with-img"><div id="spinner"></div><img class="is-in-preloader" src="'.$dazzle_data['dazzle_preloader_logo']['url'].'" width="80" height= "80"/></div></div>';
                }
                wp_localize_script( 'dazzle-qloader', 'dazzle_load', array( 'logo' => $dazzle_preloader_logo_output) );

            }
        }
        wp_enqueue_script( 'dazzle-plugins', get_template_directory_uri() . '/assets/js/plugins/jquery-plugins.js', array('jquery'), false, true );

        if(isset($dazzle_data['dazzle_smoothscroll_enabled']) && ($dazzle_data['dazzle_smoothscroll_enabled'] =='1')) {
            wp_enqueue_script( 'smoothscroll', get_template_directory_uri() . '/assets/js/plugins/smoothScroll.js', array('jquery'), '1.4.0', true );
        }
        wp_enqueue_script( 'dazzle-nav', get_template_directory_uri() . '/assets/js/custom-nav.js', array('jquery'), '1.0', true );
        wp_enqueue_script( 'dt-isotope', get_template_directory_uri() . '/assets/js/plugins/jquery.isotope.js', array('jquery'), '3.0.1', true );

        wp_enqueue_script( 'dazzle-navscroll', get_template_directory_uri() . '/assets/js/custom-navscroll.js', array('jquery'), '1.0', true );
        wp_enqueue_script( 'dazzle-custom-js', get_template_directory_uri() . '/assets/js/custom.js', array('jquery'), '1.0', true );

        if(isset($dazzle_data['dazzle_header_type']) && ($dazzle_data['dazzle_header_type'] == 'header-left')) {
            wp_enqueue_script( 'dazzle-custom-vc', get_template_directory_uri() . '/assets/js/custom-vc.js', array('jquery'), '1.0', true );
        }

        if(isset($dazzle_data['dazzle_slide_sidebar']) && ($dazzle_data['dazzle_slide_sidebar'] == '1')) {
            wp_enqueue_script( 'dazzle-classie', get_template_directory_uri() . '/assets/js/plugins/classie.js', '1.0', true );
            wp_enqueue_script( 'dazzle-slidemenu', get_template_directory_uri() . '/assets/js/custom-slidemenu.js', array('dazzle-classie'), '1.0', true );
        }
        wp_register_script('dazzle-social', get_template_directory_uri() . '/assets/js/custom-social.js', array('jquery'), FALSE, true );

        if (is_page_template('template-blog.php')) {
            wp_enqueue_script( 'dazzle-masonry-blog', get_template_directory_uri() . '/assets/js/custom-masonry-blog.js', array('dt-isotope'), '1.0', true );
        }

        $dazzle_onepage_nav_offset = 0;
        $dazzle_onepage_nav_hashtags = false;

        if(isset($dazzle_data['dazzle_nav_hashtags'])&& ($dazzle_data['dazzle_nav_hashtags'] == 1)) {
            $dazzle_onepage_nav_hashtags = $dazzle_data['dazzle_nav_hashtags'];
        }

        if(isset($dazzle_data['dazzle_scrolloffset'])&& ($dazzle_data['dazzle_scrolloffset'] != '')) {
            $dazzle_onepage_nav_offset = $dazzle_data['dazzle_scrolloffset'];
        }

        wp_enqueue_script('dazzle-onepage-custom-nav', get_template_directory_uri() . '/assets/js/custom-onepage-nav.js', array('jquery'), '1.0', true );

        wp_localize_script( 'dazzle-onepage-custom-nav', 'dazzle_onepage', array( 'dazzle_offset' => $dazzle_onepage_nav_offset, 'dazzle_hashtags' => $dazzle_onepage_nav_hashtags) );


        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }

        if(isset($dazzle_data['dazzle_social_box'])) {
            if($dazzle_data['dazzle_social_box'] =='1') {
                wp_enqueue_script('dazzle-social');
            }
        }

        //counting footer widgets number and assigning them a width
        $dazzle_number = self::dazzle_count_sidebar_widgets( 'footer', false );
        $dazzle_footer_columns = '';
        if($dazzle_number == 2) {
            $dazzle_footer_columns = '#topfooter aside { width: 48%; }'; }
        else if($dazzle_number == 3) {
            $dazzle_footer_columns = '#topfooter aside { width:30.66%; }'; }

        else if ($dazzle_number == 4) {
            $dazzle_footer_columns = '#topfooter aside { width:22%; }'; }

        else if ($dazzle_number == 5) {
            $dazzle_footer_columns = '#topfooter aside { width:16.8%; }'; }

        wp_add_inline_style( 'dazzle-style', $dazzle_footer_columns );

        //custom css
        $dazzle_custom_css = '';
        if(!empty($dazzle_data['dazzle_more_css'])) {
            $dazzle_custom_css .= $dazzle_data['dazzle_more_css'];
        }
        wp_add_inline_style( 'dazzle-style', $dazzle_custom_css );


        $dazzle_grayscale_css = '';
        $dazzle_grayscale_svg = "filter: url("."data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg'><filter id='grayscale'><feColorMatrix type='matrix' values='0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0'/></filter></svg>#grayscale".");";
        if(isset($dazzle_data['dazzle_grayscale_effect'])&& ($dazzle_data['dazzle_grayscale_effect'] == 1)) {
            $dazzle_grayscale_css .= '.member-wrapper .thumbnail-wrapper img, .portfolio>li a.img-anchor img, .post-thumbnail img, .client-thumbnail img {-webkit-filter: grayscale(100%); -moz-filter: grayscale(100%); -ms-filter: grayscale(100%); -o-filter: grayscale(100%); filter: grayscale(100%); filter: gray; '.$dazzle_grayscale_svg.' }';
        }

        wp_add_inline_style( 'dazzle-style', $dazzle_grayscale_css );


        // secondary font options
        $dazzle_secondary_font = 'Lato';
        $dazzle_secondary_font_output = '';

        $dazzle_heading_font = 'Lato';
        $dazzle_heading_font_output = '';

        if(isset($dazzle_data['dazzle_body_font_typo']) && $dazzle_data['dazzle_body_font_typo'] != '') {
            $dazzle_heading_font = $dazzle_data['dazzle_body_font_typo']['font-family'];

            $dazzle_heading_font_output = '.dt-title-wrapper * { font-family: '.$dazzle_heading_font.', sans-serif; }';

        }
        wp_add_inline_style( 'dazzle-style', $dazzle_heading_font_output );

        if(isset($dazzle_data['dazzle_secondary_typo']) && $dazzle_data['dazzle_secondary_typo'] != '') {
            $dazzle_secondary_font = $dazzle_data['dazzle_secondary_typo']['font-family'];

            $dazzle_secondary_font_output = '.dt-button, button, input[type="submit"].solid, input[type="reset"].solid, input[type="button"].solid, .dt-dropcap-1, .format-quote span, .dt-play-video, .skillbar-title, .counter-number, .package-title, .package-price, .ias-wrapper .ias-trigger, .ias-wrapper .ias-trigger .to-trigger, .ias-noneleft p, .ias-infinite-loader, .ias-wrapper.to-hide, .timeline-wrapper .timeline-list li .timeline-item .timeline-number, span.info-title, .to-trigger, .projnav li span, .is-alt-text, .pagenav, .small-btn-link { font-family: '.$dazzle_secondary_font.', Helvetica, Arial, sans-serif;} ';
        }

        wp_add_inline_style( 'dazzle-style', $dazzle_secondary_font_output );

        // menu items style
        $dazzle_menu_item_style = 'strikethrough';
        $dazzle_menu_item_output = '';

        if((isset($dazzle_data['dazzle_menu_items_style'])) && ($dazzle_data['dazzle_menu_items_style'] != '')) {
            $dazzle_menu_item_style = $dazzle_data['dazzle_menu_items_style'];
        }

        if($dazzle_menu_item_style == 'underline') {
            $dazzle_menu_item_output = 'html .main-navigation a:before,html .main-navigation a:after,html .main-navigation .current>a:before,html .main-navigation .current>a:after{bottom:0;top:inherit;margin-right:0;margin-left:0}html .main-navigation .current>a:after{width:100%}html .main-navigation a:before{margin-left:0}html .main-navigation a:after{margin-left:0}html .main-navigation a:hover:before{width:100%;margin-left:0}html .main-navigation a:hover:after{margin-right:0;width:100%}html .main-navigation li > a{padding-bottom:3px} html .main-navigation :not(.menu-item-type-custom).current_page_item>a:after {top: auto;}';
        } else
            if($dazzle_menu_item_style == 'noline') {
                $dazzle_menu_item_output = '.main-navigation a:before, .main-navigation a:after { content: none !important; }';
            }

        wp_add_inline_style( 'dazzle-style', $dazzle_menu_item_output );

        // custom color scheme
        $dazzle_color_scheme = '#f39c12';
        $dazzle_output_scheme = '';
        if((isset($dazzle_data['dazzle_custom_color_scheme'])) && ($dazzle_data['dazzle_custom_color_scheme'] != '')) {
            $dazzle_color_scheme = $dazzle_data['dazzle_custom_color_scheme'];


            $dazzle_output_scheme = '.dt-button.button-primary:hover, button.button-primary:hover, input[type="submit"].button-primary:hover, input[type="reset"].button-primary:hover, input[type="button"].button-primary:hover, .dt-button.button-primary:focus, button.button-primary:focus, input[type="submit"].button-primary:focus, input[type="reset"].button-primary:focus, input[type="button"].button-primary:focus, .widget-area .tagcloud a:hover, #headersocial li a:hover { background-color: '.$dazzle_color_scheme.'; border-color:  '.$dazzle_color_scheme.'; }

					.main-navigation ul ul li.current-menu-item > a, .main-navigation a:hover, .main-navigation .current > a, .main-navigation .current > a:hover, .main-navigation.dark-header a:hover, .pagenav a:hover, .pagenav span.current, .author-bio .author-description h3 a:hover, .blog-grid a.excerpt-read-more span:hover, h2.entry-title a:hover, h1.entry-title a:hover, .post-read-more a:hover, aside[id^="woocommerce_"] li a:hover, html .woocommerce ul.products li.product a h3:hover, html .woocommerce .woocommerce-message:before, .widget-area a:hover,.main-navigation :not(.menu-item-type-custom).current_page_item > a, 

					.process-item-title .pi-title, .no-fill .dt-service-icon * , .thin-fill .dt-service-icon span, .thin-fill .dt-service-icon i, .dt-services-grid .delicious-service .delicious-service-icon, .dt-blog-carousel h3.entry-title a:hover, .dt-blog-carousel a.excerpt-read-more span:hover, .dt_pagination a, .dt_pagination a:hover, .dt_pagination span.current, .portfolio.portfolio-layout-mosaic li .dt-awesome-project h3 a:hover, .portfolio.portfolio-layout-parallax li .dt-awesome-project h3 a:hover, ul.dt-tabs li:hover, ul.dt-tabs li.current span.dt-tab-count, ul.dt-tabs li.current span.dt-tab-title, .dt-service-box:hover .dt-service-box-icon span, .timeline-wrapper .timeline-list li .timeline-item:hover .timeline-number, .darker-overlay ul li a:hover, .rev_slider a, .intro-wrapper #text-typed, .intro-wrapper .typed-cursor, #dazzle-left-side .menu li a:hover, #dazzle-left-side .menu li.current > a, #dazzle-left-side #menu-dazzle-regular-menu .current-menu-item a
					 { color: '.$dazzle_color_scheme.'; }

					.main-navigation a:before, .main-navigation a:after, .main-navigation a:hover:before, .main-navigation .current > a:after, .dt-play-video:hover, .main-navigation :not(.menu-item-type-custom).current_page_item > a:after, ::-webkit-scrollbar-thumb:hover,

					.work-cta:hover, .bold-fill .dt-service-icon i, .bold-fill .dt-service-icon span, .dt-blog-carousel .post-thumbnail .post-icon, .portfolio.portfolio-layout-mosaic li .dt-awesome-project h3 a:after, .portfolio.portfolio-layout-parallax li .dt-awesome-project h3 a:after, .timeline-wrapper .timeline-list li:hover:after, #dazzle-left-side .menu li > a:before, html .main-navigation .current_page_item:not(.menu-item-type-custom) > a:after, html .main-navigation .current_page_item:not(.menu-item-type-custom) > a:before
					 { background: '.$dazzle_color_scheme.' ; }

					 html .main-navigation .current_page_item:not(.menu-item-type-custom) > a:after, html .main-navigation .current_page_item:not(.menu-item-type-custom) > a:before, .main-navigation a:hover::before, .main-navigation .current > a::after, .main-navigation a:before, .main-navigation a:after, .timeline-wrapper .timeline-list li:hover:after {
					 	background-color: '.$dazzle_color_scheme.' ;
					 }
					
					p a, .pagenav a:hover, .pagenav span.current, html #comments .bodycomment a, .blog-grid a.excerpt-read-more span:hover, html .post-read-more a, html .woocommerce .woocommerce-message,  html .single-product.woocommerce #content .product .woocommerce-tabs .tabs li.active,

					.thin-fill .dt-service-icon span, .thin-fill .dt-service-icon i, .dt-blog-carousel a.excerpt-read-more span:hover, .dt_pagination a:hover, .dt_pagination span.current
					   { border-color: '.$dazzle_color_scheme.'}
				';
        }

        wp_add_inline_style( 'dazzle-style', $dazzle_output_scheme );

        wp_localize_script( 'dazzle-custom-loader', 'dazzle_loader', array( 'dazzle_bcolor' => $dazzle_color_scheme) );


        // custom background colors
        $dazzle_style_css ='';

        // solid header switch
        $dazzle_solid_header_switch = rwmb_meta("dazzle_solid_header_switch");
        $dazzle_push_header_top = rwmb_meta("dazzle_push_header_top");
        if (isset($dazzle_solid_header_switch) && ($dazzle_solid_header_switch == 1)) {
            $dazzle_style_css .= '.menu-fixer { display: none;}';
            if (isset($dazzle_solid_header_switch) && ($dazzle_solid_header_switch == 1)) {
                wp_localize_script( 'dazzle-custom-js', 'dazzle_custom', array( 'dazzle_id' => $dazzle_postid, 'dazzle_header_top' => $dazzle_push_header_top) );
            }
        }


        $dazzle_page_title_bg = rwmb_meta("dazzle_page_title_bg");

        if (isset($dazzle_page_title_bg) && ($dazzle_page_title_bg != "")) {
            $dazzle_page_title_bg_img = wp_get_attachment_image_src($dazzle_page_title_bg, 'dazzle-full-size');
            $dazzle_style_css .= 'html .page-id-'.$dazzle_postid.' .page-title-wrapper, html .postid-'.$dazzle_postid.' .page-title-wrapper { background: url('.$dazzle_page_title_bg_img[0].') fixed center center; background-size: cover; -webkit-background-size: cover; }';
        }


        // header background
        $dazzle_default_header_color = "#fff";

        if((isset($dazzle_data['dazzle_header_background'])) || ($dazzle_data['dazzle_header_background'] != '')) {

            if($dazzle_data['dazzle_header_background']['alpha'] != '1' ) {
                $dazzle_default_header_color = $dazzle_data['dazzle_header_background']['rgba'];
            }
            else
                $dazzle_default_header_color = $dazzle_data['dazzle_header_background']['color'];
        }

        // header background on scroll
        $dazzle_header_color_on_scroll = "#fff";
        if(isset($dazzle_data['dazzle_header_background_on_scroll'])) {
            if(($dazzle_data['dazzle_header_background_on_scroll']['alpha'] != '1' ) && array_key_exists('rgba', $dazzle_data['dazzle_header_background_on_scroll'])) {
                $dazzle_header_color_on_scroll = $dazzle_data['dazzle_header_background_on_scroll']['rgba'];
            }
            else
                $dazzle_header_color_on_scroll = $dazzle_data['dazzle_header_background_on_scroll']['color'];
        }
        else {
            $dazzle_header_color_on_scroll = "#fff";
        }

        if(!empty($dazzle_data['dazzle_body_background'])) {
            $dazzle_style_css .= 'html body {background: '.$dazzle_data['dazzle_body_background'].';}';
        }
        if(!empty($dazzle_data['dazzle_wrapper_background'])) {
            $dazzle_style_css .= '#wrapper {background: '.$dazzle_data['dazzle_wrapper_background'].';}';
        }

        // margin-top for logo
        if(!empty($dazzle_data['dazzle_margin_logo'])) {
            $dazzle_style_css .= '#header .logo img { margin-top: '.$dazzle_data['dazzle_margin_logo'].'px;}';
        }

        //background patterns
        if((!empty($dazzle_data['dazzle_pattern'])) && ($dazzle_data['dazzle_pattern'] != 'bg12')) {
            $dazzle_style_css .= 'html body #page { background: url('.get_template_directory_uri().'/assets/images/bg/'.$dazzle_data['dazzle_pattern'].'.png) repeat scroll 0 0;}';
        }

        wp_add_inline_style( 'dazzle-style', $dazzle_style_css );

        // disable floating header
        $dazzle_no_float = '';
        if(isset($dazzle_data['dazzle_floating_header'])) {
            if($dazzle_data['dazzle_floating_header'] == 0) {
                $dazzle_no_float .= '#header { position: relative; } .menu-fixer { display: none !important }';
            }
        }

        wp_add_inline_style('dazzle-style', $dazzle_no_float);

        $dazzle_logo_onscroll_height = '25';
        if(isset($dazzle_data['dazzle_onscroll_logo_height'])) {
            $dazzle_logo_onscroll_height = $dazzle_data['dazzle_onscroll_logo_height'];
        }

        $dazzle_mainlogo_src = '';
        $dazzle_logo_details = array('0', '110', '25');
        if(isset($dazzle_data['dazzle_custom_logo']['id']) && ($dazzle_data['dazzle_custom_logo']['id'] != '')) {
            $dazzle_logo_details = wp_get_attachment_image_src($dazzle_data['dazzle_custom_logo']['id'], 'dazzle-full-size');
            $dazzle_mainlogo_src = $dazzle_data['dazzle_custom_logo']['url'];
        }

        $dazzle_alternative_logo_src = '';
        $dazzle_alternative_svg_logo_enabled = '0';
        $dazzle_alternative_svg_logo_src = '';
        $dazzle_alternative_svg_logo_width = '110';
        $dazzle_alternative_svg_logo_height = '25';

        if(isset($dazzle_data['dazzle_alternativelogo_enabled']) && ($dazzle_data['dazzle_alternativelogo_enabled'] == '1')) {
            if(isset($dazzle_data['dazzle_alternative_logo']['id']) && ($dazzle_data['dazzle_alternative_logo']['url'] != '')) {
                $dazzle_alternative_logo_src = $dazzle_data['dazzle_alternative_logo']['url'];
            }
            if(isset($dazzle_data['dazzle_alternativelogo_enabled']) && ($dazzle_data['dazzle_alternativelogo_enabled'] == '1')) {
                // $dazzle_alternative_svg_logo_enabled = $dazzle_data['dazzle_alternativelogo_enabled'];
                $dazzle_alternative_svg_logo_enabled = $dazzle_data['dazzle_alternative_svg_enabled'];
                if(isset($dazzle_data['dazzle_alternative_svg_logo']['id']) && ($dazzle_data['dazzle_alternative_svg_logo']['url'] != '')) {
                    $dazzle_alternative_svg_logo_src = $dazzle_data['dazzle_alternative_svg_logo']['url'];
                }
                $dazzle_alternative_svg_logo_width = $dazzle_data['dazzle_alternative_svg_logo_width'];
                $dazzle_alternative_svg_logo_height = $dazzle_data['dazzle_alternative_svg_logo_height'];
            }

        }


        $dazzle_logo_width = $dazzle_logo_details[1];
        $dazzle_logo_height = $dazzle_logo_details[2];

        $dazzle_logo_svg_url = '';
        $dazzle_logo_svg_enabled = '0';
        $dazzle_svg_logo_css = '';

        if(isset($dazzle_data['dazzle_svg_enabled']) && ($dazzle_data['dazzle_svg_enabled'] == '1')) {
            $dazzle_logo_svg_enabled = $dazzle_data['dazzle_svg_enabled'];
            $dazzle_logo_svg_url = $dazzle_data['dazzle_svg_logo']['url'];
            $dazzle_logo_width = $dazzle_data['dazzle_svg_logo_width'];
            $dazzle_logo_height = $dazzle_data['dazzle_svg_logo_height'];
            $dazzle_svg_logo_css = '.logo img { width: '.$dazzle_logo_width.'px; height: '.$dazzle_logo_height.'px; }';
        }

        wp_add_inline_style('dazzle-style', $dazzle_svg_logo_css);

        $dazzle_init_pt = 48;
        $dazzle_init_pb = 48;
        $dazzle_scroll_pt = 15;
        $dazzle_scroll_pb = 15;

        if(isset($dazzle_data['dazzle_initial_header_padding'])) {
            $dazzle_init_pt = $dazzle_data['dazzle_initial_header_padding']['padding-top'];
            $dazzle_init_pb = $dazzle_data['dazzle_initial_header_padding']['padding-bottom'];
        }

        if(isset($dazzle_data['dazzle_onscroll_header_padding'])) {
            $dazzle_scroll_pt = $dazzle_data['dazzle_onscroll_header_padding']['padding-top'];
            $dazzle_scroll_pb = $dazzle_data['dazzle_onscroll_header_padding']['padding-bottom'];
        }

        $dazzle_scrolling_effect = 1;
        if(isset($dazzle_data['dazzle_scrolling_effect'])) {
            if ($dazzle_data['dazzle_scrolling_effect'] == 0) {
                $dazzle_scrolling_effect = 0;
            }
        }

        // theme options header scheme
        $dazzle_headerscheme = 'light-header';
        if(isset($dazzle_data['dazzle_header_scheme'])) { $dazzle_headerscheme = $dazzle_data['dazzle_header_scheme']; }

        // theme options header scheme on scroll
        $dazzle_headerscheme_on_scroll = 'light-header';
        if(isset($dazzle_data['dazzle_header_scheme_on_scroll'])) { $dazzle_headerscheme_on_scroll = $dazzle_data['dazzle_header_scheme_on_scroll']; }


        // page custom header options
        $dazzle_pagenav_behavior_switch = rwmb_meta('dazzle_pagenav_behavior_switch');

        // page custom navigation style
        $dazzle_initial_navigation_style = rwmb_meta('dazzle_initial_navigation_style');
        $dazzle_onscroll_navigation_style = rwmb_meta('dazzle_onscroll_navigation_style');

        // page custom header background color
        $dazzle_initial_header_color = self::dazzle_hex2rgb(rwmb_meta('dazzle_initial_header_color'));

        $dazzle_onscroll_header_color = self::dazzle_hex2rgb(rwmb_meta('dazzle_onscroll_header_color'));

        // page custom header background color opacity
        $dazzle_initial_header_color_opacity = rwmb_meta('dazzle_initial_header_color_opacity');
        $dazzle_onscroll_header_color_opacity = rwmb_meta('dazzle_onscroll_header_color_opacity');

        // page custom header logo
        $dazzle_init_logo_img = rwmb_meta('dazzle_initial_logo_image', 'type=image_advanced&size=full', $dazzle_postid );
        $dazzle_onscroll_logo_img = rwmb_meta('dazzle_onscroll_logo_image', 'type=image_advanced&size=full', $dazzle_postid );


        $dazzle_initial_logo_image_url = '';
        $dazzle_initial_logo_image_width = '110';
        $dazzle_initial_logo_image_height = '25';

        $dazzle_onscroll_logo_image_url = '';
        $dazzle_onscroll_logo_image_width = '110';
        $dazzle_onscroll_logo_image_height = '25';

        if( !is_404() ) {
            if( !is_search() ) {
                foreach($dazzle_init_logo_img as $dazzle_init_logo_img_fields) {
                    $dazzle_initial_logo_image_url = $dazzle_init_logo_img_fields['url'];
                    $dazzle_initial_logo_image_width = $dazzle_init_logo_img_fields['width'];
                    $dazzle_initial_logo_image_height = $dazzle_init_logo_img_fields['height'];
                }

                foreach($dazzle_onscroll_logo_img as $dazzle_onscroll_logo_img_fields) {
                    $dazzle_onscroll_logo_image_url = $dazzle_onscroll_logo_img_fields['url'];
                    $dazzle_onscroll_logo_image_width = $dazzle_onscroll_logo_img_fields['width'];
                    $dazzle_onscroll_logo_image_height = $dazzle_onscroll_logo_img_fields['height'];
                }
            }
        }

        // page custom header svg logo

        $dazzle_initial_logo_svg_retina = rwmb_meta('dazzle_initial_logo_svg_retina');
        $dazzle_onscroll_logo_svg_retina = rwmb_meta('dazzle_onscroll_logo_svg_retina');

        $dazzle_initial_svg_retina_logo_width = rwmb_meta('dazzle_initial_svg_retina_logo_width');
        $dazzle_initial_svg_retina_logo_height = rwmb_meta('dazzle_initial_svg_retina_logo_height');

        $dazzle_onscroll_svg_retina_logo_width = rwmb_meta('dazzle_onscroll_svg_retina_logo_width');
        $dazzle_onscroll_svg_retina_logo_height = rwmb_meta('dazzle_onscroll_svg_retina_logo_height');

        wp_localize_script( 'dazzle-navscroll', "dazzle_styles",
            array(
                'dazzle_logo_svg_url' => $dazzle_logo_svg_url,
                'dazzle_logo_svg_enabled' => $dazzle_logo_svg_enabled,
                'dazzle_header_bg' => $dazzle_default_header_color,
                'dazzle_header_scroll_bg' => $dazzle_header_color_on_scroll,
                'dazzle_default_color' => $dazzle_default_header_color,
                'dazzle_logo_width' => $dazzle_logo_width,
                'dazzle_logo_height' => $dazzle_logo_height,
                'dazzle_logo_onscroll_height' => $dazzle_logo_onscroll_height,
                'dazzle_init_pt' => $dazzle_init_pt,
                'dazzle_init_pb' => $dazzle_init_pb,
                'dazzle_scroll_pt' => $dazzle_scroll_pt,
                'dazzle_scroll_pb' => $dazzle_scroll_pb,
                'dazzle_scrolling_effect' => $dazzle_scrolling_effect,
                'dazzle_mainlogosrc' => $dazzle_mainlogo_src ,
                'dazzle_alternativelogosrc' => $dazzle_alternative_logo_src ,
                'dazzle_alternativelogo' => $dazzle_data['dazzle_alternativelogo_enabled'],
                'dazzle_alternative_svg_logo_src' => $dazzle_alternative_svg_logo_src,
                'dazzle_alternative_svg_logo_width' => $dazzle_alternative_svg_logo_width,
                'dazzle_alternative_svg_logo_width' => $dazzle_alternative_svg_logo_width,
                'dazzle_alternative_svg_logo_height' => $dazzle_alternative_svg_logo_height,
                'dazzle_alternative_svg_logo_enabled' => $dazzle_alternative_svg_logo_enabled,
                'dazzle_scheme' => $dazzle_headerscheme,
                'dazzle_scheme_on_scroll' => $dazzle_headerscheme_on_scroll,

                'dazzle_pagenav_behavior_switch' => $dazzle_pagenav_behavior_switch,
                'dazzle_initial_navigation_style' => $dazzle_initial_navigation_style,
                'dazzle_onscroll_navigation_style' => $dazzle_onscroll_navigation_style,
                'dazzle_initial_header_color' => $dazzle_initial_header_color,
                'dazzle_onscroll_header_color' => $dazzle_onscroll_header_color,
                'dazzle_initial_header_color_opacity' => $dazzle_initial_header_color_opacity,
                'dazzle_onscroll_header_color_opacity' => $dazzle_onscroll_header_color_opacity,
                'dazzle_initial_logo_image_url' => $dazzle_initial_logo_image_url,
                'dazzle_initial_logo_image_width' => $dazzle_initial_logo_image_width,
                'dazzle_initial_logo_image_height' => $dazzle_initial_logo_image_height,
                'dazzle_onscroll_logo_image_url' => $dazzle_onscroll_logo_image_url,
                'dazzle_onscroll_logo_image_width' => $dazzle_onscroll_logo_image_width,
                'dazzle_onscroll_logo_image_height' => $dazzle_onscroll_logo_image_height,

                'dazzle_initial_logo_svg_retina' => $dazzle_initial_logo_svg_retina,
                'dazzle_onscroll_logo_svg_retina' => $dazzle_onscroll_logo_svg_retina,
                'dazzle_initial_svg_retina_logo_width' => $dazzle_initial_svg_retina_logo_width,
                'dazzle_initial_svg_retina_logo_height' => $dazzle_initial_svg_retina_logo_height,
                'dazzle_onscroll_svg_retina_logo_width' => $dazzle_onscroll_svg_retina_logo_width,
                'dazzle_onscroll_svg_retina_logo_height' => $dazzle_onscroll_svg_retina_logo_height,


                'page_id' => $dazzle_postid

            ) );

        $dazzle_init_h_padding = '';
        $dazzle_init_h_padding = '#header { padding-top: '.$dazzle_init_pt.'px; padding-bottom: '.$dazzle_init_pb.'px;  }';
        wp_add_inline_style( 'dazzle-style', $dazzle_init_h_padding );


    }


    // Read More wrapping
    public function dazzle_wrap_readmore($dazzle_more_link)
    {
        return '<div class="post-read-more">'.$dazzle_more_link.'</div>';
    }


    //set excerpt length
    public function dazzle_custom_excerpt_length( $length ) {
        return 18;
    }

    // customize excerpt read more
    public function dazzle_excerpt_more( $more ) {
        return '... <a class="excerpt-read-more" href="' . esc_url(get_permalink( get_the_ID() )) . '"><span>' . esc_html__( 'Continue Reading', 'dazzle' ) . '</span></a>';
    }

    // prevent page scroll when clicking the more link
    public function dazzle_remove_more_link_scroll( $dazzle_link ) {
        $dazzle_link = preg_replace( '|#more-[0-9]+|', '', $dazzle_link );
        return $dazzle_link;
    }


    // header custom js
    public function dazzle_header_custom_js() {
        $dazzle_data = dazzle_dt_data();
        if(isset($dazzle_data['dazzle__header_custom_js']) && ($dazzle_data['dazzle__header_custom_js'] !='')) { echo '<script>'. $dazzle_data['dazzle__header_custom_js'] .'</script>'; }
    }


    // footer custom js
    public function dazzle_footer_custom_js() {
        $dazzle_data = dazzle_dt_data();
        if(isset($dazzle_data['dazzle__footer_custom_js']) && ($dazzle_data['dazzle__footer_custom_js'] !='')) { echo '<script>'. $dazzle_data['dazzle__footer_custom_js'] .'</script>'; }
    }


    // allow svg files to be used with the theme
    public function dazzle_mime_types($dazzle_mimes) {
        $dazzle_mimes['svg'] = 'image/svg+xml';
        return $dazzle_mimes;
    }


    public function dazzle_add_quicktags() {
        if (wp_script_is('quicktags')){
            ?>
            <script type="text/javascript">
                QTags.addButton( 'section-intro', 'dazzle Intro', '<p class="section-intro">', '</p>', '8', 'dazzle Section Intro', 201 );
            </script>
            <?php
        }
    }

    public function dazzle_add_more_menu_item ( $dazzle_items, $dazzle_args ) {

        $dazzle_data = dazzle_dt_data();

        if(isset($dazzle_data['dazzle_slide_sidebar']) && ($dazzle_data['dazzle_slide_sidebar'] === '1')) {

            if( $dazzle_args->theme_location === 'primary')  {

                $dazzle_items .=  '<li class="svg-more"><svg class="menu-button" id="open-button" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve"><path d="M80.6,50c0,3.8-3.1,6.9-6.9,6.9c-3.8,0-6.9-3.1-6.9-6.9c0-3.8,3.1-6.9,6.9-6.9C77.5,43.1,80.6,46.2,80.6,50z M26.3,56.9  c3.8,0,6.9-3.1,6.9-6.9c0-3.8-3.1-6.9-6.9-6.9c-3.8,0-6.9,3.1-6.9,6.9C19.4,53.8,22.5,56.9,26.3,56.9z M43.1,50  c0,3.8,3.1,6.9,6.9,6.9c3.8,0,6.9-3.1,6.9-6.9c0-3.8-3.1-6.9-6.9-6.9C46.2,43.1,43.1,46.2,43.1,50z"/></svg></li>';

            }
        }
        return $dazzle_items;
    }



    public function dazzle_password_form() {
        global $post;
        $dazzle_label = 'pwbox-'.( empty( $post->ID ) ? rand() : $post->ID );
        $dazzle_output = '<form class="post-password-form" action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">
	    ' . esc_html__( "This content is password protected. To view it please enter your password below:", 'dazzle' ) . '
	    <div class="half-space"></div><input name="post_password" id="' . esc_attr($dazzle_label) . '" type="password" size="20" maxlength="20" /><input type="submit" class="solid" name="Submit" value="' . esc_attr__( "Submit", 'dazzle' ) . '" />
	    </form>
	    ';
        return $dazzle_output;
    }


    // UTILITY FUNCTIONS

    // Hex 2 RGB values
    function dazzle_hex2rgb($dazzle_hex) {
        $dazzle_hex = str_replace("#", "", $dazzle_hex);

        if(strlen($dazzle_hex) == 3) {
            $dazzle_r = hexdec(substr($dazzle_hex,0,1).substr($dazzle_hex,0,1));
            $dazzle_g = hexdec(substr($dazzle_hex,1,1).substr($dazzle_hex,1,1));
            $dazzle_b = hexdec(substr($dazzle_hex,2,1).substr($dazzle_hex,2,1));
        } else {
            $dazzle_r = hexdec(substr($dazzle_hex,0,2));
            $dazzle_g = hexdec(substr($dazzle_hex,2,2));
            $dazzle_b = hexdec(substr($dazzle_hex,4,2));
        }
        $dazzle_rgb = array($dazzle_r, $dazzle_g, $dazzle_b);
        return implode(",", $dazzle_rgb); // returns the rgb values separated by commas
    }

    // count sidebar widgets
    function dazzle_count_sidebar_widgets( $dazzle_sidebar_id, $dazzle_echo = true ) {
        $dazzle_the_sidebars = wp_get_sidebars_widgets();
        if( !isset( $dazzle_the_sidebars[$dazzle_sidebar_id] ) )
            return esc_html__( 'Invalid sidebar ID', 'dazzle' );
        if( $dazzle_echo )
            echo count( $dazzle_the_sidebars[$dazzle_sidebar_id] );
        else
            return count( $dazzle_the_sidebars[$dazzle_sidebar_id] );
    }

    // get all sidebars in an array
    function dazzle_my_sidebars() {
        global $wp_registered_sidebars;
        $dazzle_all_sidebars = array();
        if ( $wp_registered_sidebars && ! is_wp_error( $wp_registered_sidebars ) ) {

            foreach ( $wp_registered_sidebars as $dazzle_sidebar ) {
                $dazzle_all_sidebars[ $dazzle_sidebar['id'] ] = $dazzle_sidebar['name'];
            }

        }
        return $dazzle_all_sidebars;
    }

    //get sidebar position
    static function dazzle_sidebar_position($dazzle_postid) {
        global $dazzle_sidebar_pos;
        $dazzle_sidebar_pos = get_post_meta($dazzle_postid, 'dazzle_sidebar_position', true);

        $dazzle_sidebar_class = '';

        if($dazzle_sidebar_pos == 'sidebar-right')
            $dazzle_sidebar_class = 'sidebar-right';
        else if($dazzle_sidebar_pos == 'sidebar-left')
            $dazzle_sidebar_class = 'sidebar-left';
        else if($dazzle_sidebar_pos == 'no-sidebar')
            $dazzle_sidebar_class = 'no-sidebar';
        echo esc_attr($dazzle_sidebar_class);
    }


    // dazzle Video Function
    function dazzle_video($dazzle_postid) {

        $dazzle_external_item = get_post_meta($dazzle_postid, 'dazzle_external_video_block', true);

        if(($dazzle_external_item != '')) {
            if( strpos($dazzle_external_item, 'youtube') ) {
                preg_match(
                    '/[\\?\\&]v=([^\\?\\&]+)/',
                    $dazzle_external_item,
                    $dazzle_matches
                );
                $dazzle_id = $dazzle_matches[1];

                $dazzle_width = '780';
                $dazzle_height = '440';
                echo '<div class="post-video"><iframe class="dt-youtube" width="' .esc_attr($dazzle_width). '" height="'.esc_attr($dazzle_height).'" src="//www.youtube.com/embed/'.esc_attr($dazzle_id).'" frameborder="0" allowfullscreen></iframe></div>';
            }

            if( strpos($dazzle_external_item, 'vimeo') ) {
                preg_match(
                    '/\/\/(www\.)?vimeo.com\/(\d+)($|\/)/',
                    $dazzle_external_item,
                    $dazzle_matches
                );
                $dazzle_id = $dazzle_matches[2];

                $dazzle_width = '780';
                $dazzle_height = '440';

                echo '<div class="post-video"><iframe src="https://player.vimeo.com/video/'.esc_attr($dazzle_id).'?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;color=ffffff" width="'.esc_attr($dazzle_width).'" height="'.esc_attr($dazzle_height).'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>';
            }
        }

    }

    // dazzle Gallery Function
    function dazzle_gallery($dazzle_postid) {

        $dazzle_token = wp_generate_password(5, false, false);
        wp_enqueue_script('dazzle-custom-gallery', get_template_directory_uri() . '/assets/js/custom-gallery.js', array('jquery'), '1.0', false );
        wp_localize_script( 'dazzle-custom-gallery', 'dazzle_gallery_' . $dazzle_token, array( 'dazzle_post_id' => $dazzle_postid) );


        $dazzle_gallery_images = array();
        if(class_exists('RW_Meta_Box')) {
            $dazzle_gallery_images = rwmb_meta( 'dazzle_blog_gallery', 'type=image_advanced&size=full', $dazzle_postid );
        }

        if(!empty($dazzle_gallery_images)) {

            echo '<div class="owl-carousel gallery-slider dt-gallery" id="gs-'.esc_attr($dazzle_postid).'" data-token="' . $dazzle_token . '">';

            foreach ($dazzle_gallery_images as $dazzle_gallery_item) {

                $dazzle_resizer_url = $dazzle_gallery_item['url'];
                $dazzle_resized_image = aq_resize( $dazzle_resizer_url, 780, 408, true );

                echo  '<div class="slider-item">';
                echo  '<a class="not-link dt-lightbox-gallery" href="'.esc_url($dazzle_resizer_url).'" >';
                echo  '<img src="'.esc_url($dazzle_resized_image).'" alt="'.esc_attr($dazzle_gallery_item['caption']).'" />';
                echo  '</a>';
                echo  '</div>';
            }

            echo  '</div><!--end slides-->';
        }
    }


    public function dazzle_loop_columns() {
        $dazzle_data = dazzle_dt_data();
        return $dazzle_data["dazzle_woo_products_per_row"];
    }


    public function dazzle_register_required_plugins() {

        /**
         * Array of plugin arrays. Required keys are name and slug.
         * If the source is NOT from the .org repo, then source is also required.
         */
        $dazzle_plugins = array(

            array(
                'name' 	   => esc_html__('Redux Framework', 'dazzle'),
                'slug' 	   => 'redux-framework',
                'required' => true
            ),

            array(
                'name'                  => esc_html__('Delicious Addons - Dazzle Edition', 'dazzle'),
                'version'				=> '1.3.4',
                'slug'                  => 'delicious-addons-dazzle',
                'source'                => get_template_directory_uri() . '/framework/plugins/delicious-addons-dazzle/delicious-addons-dazzle.zip',
                'required'              => true,
            ),

            array(
                'name'                  => esc_html__('WPBakery Visual Composer', 'dazzle'),
                'version'				=> '6.1',
                'slug'                  => 'js_composer',
                'source'                => get_template_directory_uri() . '/framework/plugins/visual-composer/js_composer.zip',
                'required'              => true,
            ),

            array(
                'name' 		=> esc_html__('One Click Demo Import', 'dazzle'),
                'slug' 		=> 'one-click-demo-import',
                'version'				=> '',
                'required' 	=> false,
            ),

            array(
                'name'                  => esc_html__('Templatera Addon for Visual Composer', 'dazzle'),
                'version'				=> '2.0.3',
                'slug'                  => 'templatera',
                'source'                => get_template_directory_uri() . '/framework/plugins/visual-composer/templatera.zip',
                'required'              => false,
            ),

            array(
                'name'                  => esc_html__('Revolution Slider', 'dazzle'),
                'version'				=> '6.2.2',
                'slug'                  => 'revslider',
                'source'                => get_template_directory_uri() . '/framework/plugins/revolution-slider/revslider.zip',
                'required'              => false,
            ),

            array(
                'name'                  => esc_html__('Envato Market', 'dazzle'),
                'version'				=> '2.0.3',
                'slug'                  => 'envato-market',
                'source'                => get_template_directory_uri() . '/framework/plugins/envato-market/envato-market.zip',
                'required'              => false,
            ),

            array(
                'name' 		=> esc_html__('Contact Form 7', 'dazzle'),
                'slug' 		=> 'contact-form-7',
                'version'				=> '',
                'required' 	=> false,
            ),

        );

        $theme_text_domain = 'dazzle';

        $dazzle_tgmpa_config = array(
            'id'           => 'dazzle',
            'default_path' => '',
            'menu'         => 'tgmpa-install-plugins',
            'parent_slug'  => 'themes.php',
            'capability'   => 'edit_theme_options',
            'has_notices'  => true,
            'dismissable'  => true,
            'dismiss_msg'  => '',
            'is_automatic' => false,
            'message'      => '',
        );

        tgmpa( $dazzle_plugins, $dazzle_tgmpa_config );

    }

    public function dazzle_ocdi_import_files() {
        return array(
            array(
                'import_file_name'           => 'DAZZLE DEMO CONTENT',
                'import_file_url'            => 'https://dev.deliciousthemes.com/dazzle/demofiles/dazzle-demo-data.xml',
                'import_preview_image_url'   => 'https://dev.deliciousthemes.com/dazzle/demofiles/dazzle-oneclick-demo-import.jpg',
            ),
        );
    }


    public function dazzle_ocdi_after_import_setup() {

        register_nav_menu( 'primary', esc_html__( 'Primary Menu', 'dazzle' ) );
        $menu_header = get_term_by('name', 'Dazzle One Page Menu', 'nav_menu');
        set_theme_mod( 'nav_menu_locations', array(
            'primary' => $menu_header->term_id) );

        // Assign front page
        $front_page_id = get_page_by_title( 'Homepage - Image Slider' );

        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $front_page_id->ID );

    }

    public function dazzle_set_theme_setup_wizard_username($username){
        return 'deliciousthemes';
    }


    public function dazzle_set_theme_setup_wizard_oauth_script($oauth_url){
        return 'https://deliciousthemes.com/envato/api/server-script.php';
    }

    // remove the theme options panel notices
    public function dazzle_remove_redux_notices() {
        if ( class_exists('ReduxFrameworkPlugin') ) {
            remove_filter( 'plugin_row_meta', array( ReduxFrameworkPlugin::get_instance(), 'plugin_metalinks'), null, 2 );
        }
        if ( class_exists('ReduxFrameworkPlugin') ) {
            remove_action('admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );
        }
    }

} // END CLASS

$dazzle_delicious = new dazzle_Delicious();



// Language Switcher for WPML
if (!function_exists('dazzle_language_selector')) {
    function dazzle_language_selector() {
        if (function_exists('icl_get_languages')) {
            $dazzle_languages = icl_get_languages();

            if(!empty($dazzle_languages)){
                echo '<div id="header_language_list"><ul>';
                foreach($dazzle_languages as $dazzle_l){
                    if($dazzle_l['active']) { echo '<li class="active-lang switch-lang" original-title="'.esc_attr($dazzle_l['native_name']).'">'; }
                    else { echo '<li class="switch-lang" original-title="'.esc_attr($dazzle_l['native_name']).'">'; }
                    if(!$dazzle_l['active']) echo '<a href="'.esc_url($dazzle_l['url']).'">';
                    if($dazzle_l['code'] != 'zh-hant') {
                        if($dazzle_l['code'] === 'ru') {
                            echo "PY";
                        }
                        else
                            echo esc_html(substr($dazzle_l['native_name'], 0, 2));
                    }
                    else { echo esc_html($dazzle_l['native_name']); }
                    if(!$dazzle_l['active']) echo '</a>';
                    echo '</li>';
                }
                echo '</ul></div>';
            }
        }
    }
}


// Sets how comments are displayed
if(!function_exists('dazzle_comment')) {
    function dazzle_comment($dazzle_comment, $dazzle_args, $dazzle_depth) {
        $GLOBALS['comment'] = $dazzle_comment; ?>
    <li class="comment" <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
        <div class="commentwrap">
            <div class="avatar">
                <?php echo get_avatar($dazzle_comment,$size='60'); ?>
            </div><!--end avatar-->

            <div class="metacomment">
                <span class="comment-author-name"><?php echo get_comment_author_link() ?></span>
                <span class="comment-time"><?php echo get_comment_date(); ?></a><?php edit_comment_link(esc_html__('Edit', 'dazzle'),'  ','') ?></span>
            </div><!--end metacomment-->

            <div class="bodycomment">
                <?php if ($dazzle_comment->comment_approved == '0') : ?>
                    <em><?php esc_html_e('Your comment is awaiting moderation.', 'dazzle') ?></em>
                    <br />
                <?php endif; ?>
                <?php comment_text() ?>
            </div><!--end bodycomment-->

            <div class="replycomment">
                <?php comment_reply_link(array_merge( $dazzle_args, array('depth' => $dazzle_depth, 'max_depth' => $dazzle_args['max_depth']))) ?>
            </div>
        </div><!--end commentwrap-->

    <?php }

    function dazzle_dt_data() {
        global $dazzle_redux_data;
        return $dazzle_redux_data;
    }

    function dazzle_more_tag() {
        global $more;
        if(!is_single()) { $more = 0; }
    }

}
