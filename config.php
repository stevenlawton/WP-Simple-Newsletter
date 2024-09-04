<?php
// Google reCAPTCHA Configuration

define('SNL_HERO_BACKGROUND', '/wp-content/uploads/2019/10/dnh30-222.jpg');

// Function to add the settings page to the admin menu
function snl_add_snl_settings_page() {
    add_options_page(
        'Simple News Letter Settings', // Page title
        'Simple News Letter Settings', // Menu title
        'manage_options',     // Capability required to access the page
        'snl-settings', // Menu slug
        'snl_render_snl_settings_page' // Callback function to render the page
    );
}
add_action('admin_menu', 'snl_add_snl_settings_page');

// Register the settings for reCAPTCHA keys
function snl_register_snl_settings() {
    register_setting('snl-settings-group', 'snl_recaptcha_site_key');
    register_setting('snl-settings-group', 'snl_recaptcha_secret_key');
    register_setting('snl-settings-group', 'snl_hero_background_path');
}
add_action('admin_init', 'snl_register_snl_settings');

// Add a link to the settings page on the Plugins page
function snl_add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=snl-settings">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'snl_add_settings_link');


// Render the reCAPTCHA settings page
function snl_render_snl_settings_page() {
    ?>
    <div class="wrap">
        <h1>reCAPTCHA Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('snl-settings-group'); ?>
            <?php do_settings_sections('snl-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">reCAPTCHA Site Key</th>
                    <td>
                        <input type="text" name="snl_recaptcha_site_key" value="<?php echo esc_attr(get_option('snl_recaptcha_site_key')); ?>" style="width: 100%;" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">reCAPTCHA Secret Key</th>
                    <td>
                        <input type="text" name="snl_recaptcha_secret_key" value="<?php echo esc_attr(get_option('snl_recaptcha_secret_key')); ?>" style="width: 100%;" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Hero Background Path</th>
                    <td>
                        <input type="text" name="snl_hero_background_path" value="<?php echo esc_attr(get_option('snl_hero_background_path')); ?>" style="width: 100%;" />
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
