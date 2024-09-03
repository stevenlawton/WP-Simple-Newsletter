<?php
// Function to display the hero section sign-up form
// Define the background image constant
define('SNL_HERO_BACKGROUND', '/wp-content/uploads/2019/10/dnh30-222.jpg');

// Function to display the hero section sign-up form
function snl_hero_signup_form() {
    // Add custom CSS for the hero section
    $content = '<style>
        #snl-hero {
            background: url("' . SNL_HERO_BACKGROUND . '") no-repeat center center;
            background-size: cover;
            padding: 100px 20px;
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
            height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #snl-hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6); /* Increase the overlay opacity for better contrast */
            z-index: 1;
        }
        #snl-hero-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        #snl-hero h1 {
            font-size: 2.5em;
            margin-bottom: 0.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Add a subtle text shadow */
        }
        #snl-hero p {
            font-size: 1.2em;
            margin-bottom: 1.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Add a subtle text shadow */
        }
        #snl-hero-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 100%;
            margin: 0 auto;
        }
        #snl-hero-form input[type="text"],
        #snl-hero-form input[type="email"] {
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            margin-bottom: 10px;
            width: 80%;
            max-width: 400px;
            box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); /* Add a subtle shadow for depth */
        }
        #snl-hero-form input[type="checkbox"] {
            margin-right: 10px;
            vertical-align: middle;
        }
        #snl-hero-form label {
            font-size: 0.9em;
            color: #ccc;
        }
        #snl-hero-form button {
            padding: 15px 25px;
            background-color: #ff6600;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            width: 80%;
            max-width: 400px;
            box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); /* Add a subtle shadow for depth */
        }
        #snl-hero-form button:hover {
            background-color: #e65c00;
        }
        #snl-hero-incentive {
            margin-top: 15px;
            font-size: 1em;
            color: #fff;
            font-weight: bold;
        }
    </style>';

    // Create the hero section with the form
    $content .= '<div id="snl-hero">
                    <div id="snl-hero-overlay"></div>
                    <div id="snl-hero-content">
                        <h1>Join the Journey</h1>
                        <h2>Get Updates Direct to Your Inbox</h2>
                        <p>Subscribe to my newsletter and be the first to know about new projects, behind-the-scenes insights, and exclusive content.</p>
                        <form id="snl-hero-form" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">
                            <input type="email" name="snl-email" id="snl-email" placeholder="Your Email Address" required />
                            <button class="g-recaptcha" 
                                    data-sitekey="' . RECAPTCHA_SITE_KEY . '" 
                                    data-callback="onSubmit" 
                                    data-action="submit">Subscribe Now</button>';
    // Add the nonce field
    $content .= wp_nonce_field('snl_signup_form', 'snl_nonce', true, false);
    $content .= '</form>
                        <div id="snl-hero-incentive">
                            Get a free chapter of my book when you subscribe!
                        </div>
                    </div>
                </div>';

    return $content;
}

// Register the hero section shortcode
add_shortcode('snl-hero-signup', 'snl_hero_signup_form');

// Enqueue block editor assets
function snl_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'snl-hero-block',
        plugins_url('snl-hero-block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'snl-hero-block.js')
    );

    wp_enqueue_style(
        'snl-hero-block-editor-style',
        plugins_url('snl-hero-block.css', __FILE__),
        array('wp-edit-blocks'),
        filemtime(plugin_dir_path(__FILE__) . 'snl-hero-block.css')
    );
}
add_action('enqueue_block_editor_assets', 'snl_enqueue_block_editor_assets');

function snl_add_custom_block_category($categories, $post) {
    return array_merge(
        array(
            array(
                'slug' => 'snl-blocks',
                'title' => __('SNL Blocks', 'snl'),
            ),
        ),
        $categories
    );
}
add_filter('block_categories', 'snl_add_custom_block_category', 10, 2);
