<?php
/*
Plugin Name: Simple Newsletter Sign-Up with reCAPTCHA v3
Description: A custom plugin to collect emails, store them in the database, verify them with reCAPTCHA v3, and send a link to the first chapter of your book.
Version: 1.0
Author: Steven Lawton
*/

// Ensure the file is being accessed through WordPress
if (!defined('ABSPATH')) {
    exit;
}

// Include the reCAPTCHA configuration
require_once(plugin_dir_path(__FILE__) . 'config.php');
require_once(plugin_dir_path(__FILE__) . 'hero-section-signup.php');

// Function to create the database table on plugin activation
function snl_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'snl_emails';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        token varchar(255) NOT NULL,
        is_verified tinyint(1) DEFAULT 0 NOT NULL,
        date_subscribed datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'snl_create_table');

// Function to display the sign-up form
function snl_signup_form() {
    // Add custom CSS for the button and popover
    $content = '<style>
        #snl-signup-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        #snl-signup-button:hover {
            background-color: #555;
        }
        #snl-popover {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            padding: 20px;
            border-radius: 8px;
            overflow-y: auto;
        }
        #snl-popover-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
        }
        #snl-popover-close:hover {
            color: #888;
        }
        #snl-form {
            font-family: Arial, sans-serif;
        }
        #snl-popover h1 {
            margin-top: 0 !important;
            margin-right: 20px !important; /* Account for close button */
            padding: 0 !important;
            line-height: 1.2 !important;
        }
        #snl-popover h2 {
            margin-top: 0 !important;
            padding: 0 !important;
        }
        #snl-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        #snl-form input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        #snl-form input[type="checkbox"] {
            margin-right: 10px;
            vertical-align: middle;
        }
        #snl-form button {
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        #snl-form button:hover {
            background-color: #555;
        }
        #snl-consent {
            display: flex;
            align-items: center;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        #snl-consent label {
            margin: 0;
        }
        #snl-popover ul {
            padding-left: 20px; /* Indent the bullet points */
        }
        #snl-popover li {
            margin-bottom: 10px; /* Add space between list items */
        }
    </style>';

    // Add JavaScript for popover handling
    $content .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var button = document.getElementById("snl-signup-button");
            var popover = document.getElementById("snl-popover");
            var close = document.getElementById("snl-popover-close");

            button.addEventListener("click", function() {
                popover.style.display = "block";
            });

            close.addEventListener("click", function() {
                popover.style.display = "none";
            });

            window.addEventListener("click", function(event) {
                if (event.target == popover) {
                    popover.style.display = "none";
                }
            });
        });
    </script>';

    // Create the initial button
    $content .= '<div id="snl-signup-button">Sign Up to Newsletter</div>';
    $recaptcha_site_key = get_option('snl_recaptcha_site_key');

    // Create the popover with the form
    $content .= '<div id="snl-popover">
                    <span id="snl-popover-close">&times;</span>
                    <h1>Join Our Community of Readers</h1>
                    <p>Readers like you are already enjoying exclusive content and early access to our latest releases!</p>
                    <p>Sign up today to receive updates on my latest books, projects, courses, and more. As a bonus, you’ll get the first chapter of my latest book for free!</p>
                    <h2>Why Subscribe?</h2>
                    <ul>
                        <li>Get the <strong>first chapter free</strong></li>
                        <li>Receive <strong>exclusive updates</strong> on books, projects, and courses</li>
                        <li>Be the first to know about new releases and special offers</li>
                    </ul>
                    <form id="snl-form" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">
                        <p>
                            <label for="snl-email">Your Email (required)</label>
                            <input type="email" name="snl-email" id="snl-email" placeholder="Enter your email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,}$" required />
                        </p>
                        <p id="snl-consent">
                            <input type="checkbox" name="snl-consent" id="snl-consent" required>
                            <label for="snl-consent">I agree to receive updates about books, projects, courses, and other news. I understand I can unsubscribe at any time.</label>
                        </p>
                        <button class="g-recaptcha" 
                                data-sitekey="' . esc_attr($recaptcha_site_key) . '" 
                                data-callback="onSubmit" 
                                data-action="submit">Sign Up to Newsletter</button>';
    // Add the nonce field
    $content .= wp_nonce_field('snl_signup_form', 'snl_nonce', true, false);
    $content .= '</form>
                </div>';

    return $content;
}




// Function to handle form submission and send verification email
function snl_handle_form() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['g-recaptcha-response']) && isset($_POST['snl_nonce'])) {

        // Verify the nonce
        if (!wp_verify_nonce($_POST['snl_nonce'], 'snl_signup_form')) {
            echo '<div>Form check or nonce failed</div>';
            return;
        }

        // In your form processing function, use the stored keys instead of hard-coded values
        $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);
        $recaptcha_secret_key = get_option('snl_recaptcha_secret_key'); // Retrieve from settings

        $response = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            array(
                'body' => array(
                    'secret' => $recaptcha_secret_key,
                    'response' => $recaptcha_response,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                )
            )
        );


        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body);

        if (!$result->success || $result->score < 0.5) { // Adjust the score threshold as needed
            echo '<div>reCAPTCHA verification failed. Please try again.</div>';
            return;
        }

        $email = sanitize_email($_POST['snl-email']);

        // Check if the email already exists in the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'snl_emails';

        $existing_email = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE email = %s", $email));

        if ($existing_email > 0) {
            echo '<div>You have already signed up with this email address.</div>';
            return; // Stop further processing
        }

        // Generate a unique verification token
        $token = md5(uniqid($email, true));

        // Insert the email and token into the database
        $wpdb->insert(
            $table_name,
            array(
                'email' => $email,
                'token' => $token,
            )
        );

        // Send a verification email with the token link
        $subject = "Please Verify Your Email";
        $verification_link = add_query_arg(array(
            'snl_verify' => 'true',
            'token' => $token,
        ), home_url());

        $message = "Thank you for signing up! Please verify your email by clicking this link: $verification_link";
        $headers = 'From: Your Name <your-email@example.com>';

        if (wp_mail($email, $subject, $message, $headers)) {
            // Redirect to avoid form resubmission on refresh
            wp_redirect(add_query_arg('snl_submitted', 'true', esc_url($_SERVER['REQUEST_URI'])));
            exit;
        } else {
            echo '<div>Failed to send verification email. Please try again later.</div>';
        }
    }
}




function snl_form_shortcode() {
    ob_start();

    // Check if the form was successfully submitted
    if (isset($_GET['snl_submitted']) && $_GET['snl_submitted'] === 'true') {
        echo '<div>Thank you for signing up! Please check your email to verify your subscription.</div>';
    } else {
        // Only handle the form if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['g-recaptcha-response'])) {
            snl_handle_form();
        }

        // Always display the form
        echo snl_signup_form();
    }

    return ob_get_clean();
}
add_shortcode('snl-signup', 'snl_form_shortcode');


// Function to verify the email when the link is clicked
function snl_verify_email()
{
    if (isset($_GET['snl_verify']) && $_GET['snl_verify'] == 'true' && isset($_GET['token'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'snl_emails';

        // Sanitize the token
        $token = sanitize_text_field($_GET['token']);

        // Check if the token exists in the database
        $email_entry = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE token = %s",
            $token
        ));

        if ($email_entry && !$email_entry->is_verified) {
            // Update the entry to mark it as verified
            $wpdb->update(
                $table_name,
                array('is_verified' => 1),
                array('id' => $email_entry->id)
            );

            // Send the email with the book chapter link now that the email is verified
            $subject = "Your Free Chapter";
            $message = "Thank you for verifying your email! Here is the link to the first chapter of my book: [Your Link Here]";
            $headers = 'From: Your Name <your-email@example.com>';

            wp_mail($email_entry->email, $subject, $message, $headers);

            echo '<div>Your email has been verified! Please check your inbox for the free chapter.</div>';
        } else {
            echo '<div>This verification link is invalid or the email has already been verified.</div>';
        }
    }
}

add_action('init', 'snl_verify_email');

// Function to register a menu page in the admin dashboard
function snl_register_menu_page()
{
    add_menu_page(
        'Newsletter Sign-Ups',
        'Newsletter',
        'manage_options',
        'snl-signups',
        'snl_display_signups_page',
        'dashicons-email',
        6
    );
}

add_action('admin_menu', 'snl_register_menu_page');

// Function to display collected emails in the admin dashboard
// Function to display collected emails in the admin dashboard with a Remove button
function snl_display_signups_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'snl_emails';

    // Check if a "remove" action has been triggered
    if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
        $email_id = intval($_GET['remove']);

        // Delete the entry from the database
        $deleted = $wpdb->delete($table_name, array('id' => $email_id), array('%d'));

        // Provide feedback
        if ($deleted) {
            echo '<div class="updated notice"><p>Email removed successfully.</p></div>';
        } else {
            echo '<div class="error notice"><p>Failed to remove email. Please try again.</p></div>';
        }
    }

    // Fetch all email sign-ups
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<div class="wrap">';
    echo '<h1>Newsletter Sign-Ups</h1>';
    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr><th>ID</th><th>Email</th><th>Date Subscribed</th><th>Verified</th><th>Action</th></tr></thead>';
    echo '<tbody>';

    if ($results) {
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->id) . '</td>';
            echo '<td>' . esc_html($row->email) . '</td>';
            echo '<td>' . esc_html($row->date_subscribed) . '</td>';
            echo '<td>' . ($row->is_verified ? 'Yes' : 'No') . '</td>';
            echo '<td>';
            echo '<a href="' . esc_url(add_query_arg(array('remove' => $row->id))) . '" class="button button-danger" onclick="return confirm(\'Are you sure you want to remove this email?\');">Remove</a>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">No sign-ups found.</td></tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
