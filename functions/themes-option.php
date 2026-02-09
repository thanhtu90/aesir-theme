<?php

// Hook to admin_menu to create the menu item in the dashboard
add_action('admin_menu', 'custom_theme_options_menu');

// Hook to admin_init to register settings
add_action('admin_init', 'custom_theme_settings_init');

// Enqueue the media uploader script
function custom_enqueue_media_uploader()
{
    if (isset($_GET['page']) && $_GET['page'] == 'theme-options') {
        wp_enqueue_media(); // This enqueues the wp.media script
        wp_enqueue_script('media-upload');
    }
}
add_action('admin_enqueue_scripts', 'custom_enqueue_media_uploader');

// Function to add a custom options page
function custom_theme_options_menu()
{
    add_theme_page(
        'Theme Options', // Page title
        'Theme Options', // Menu title
        'manage_options', // Capability
        'theme-options',  // Menu slug
        'custom_theme_options_page' // Function to display the page
    );
}

add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
});

// Initialize and register settings
function custom_theme_settings_init()
{
    // Header settings
    register_setting('theme_options_group', 'header_logo');
    register_setting('theme_options_group', 'header_logo_text');

    // Footer settings
    register_setting('theme_options_group', 'footer_copyright');
    register_setting('theme_options_group', 'footer_address');
    register_setting('theme_options_group', 'footer_facebook');
    register_setting('theme_options_group', 'footer_instagram');
    register_setting('theme_options_group', 'footer_pinterest');
    register_setting('theme_options_group', 'footer_youtube');

    // Add section for Header
    add_settings_section(
        'header_settings_section',
        'Header Settings',
        '',
        'theme-options'
    );

    // Logo upload field
    add_settings_field(
        'header_logo',
        'Upload Logo',
        'header_logo_field_render',
        'theme-options',
        'header_settings_section'
    );

    // Text to replace logo field
    add_settings_field(
        'header_logo_text',
        'Text to Replace Logo',
        'header_logo_text_field_render',
        'theme-options',
        'header_settings_section'
    );

    // Add section for Footer
    add_settings_section(
        'footer_settings_section',
        'Footer Settings',
        '',
        'theme-options'
    );

    // Footer fields: copyright, address, social media
    add_settings_field(
        'footer_copyright',
        'Footer Copyright Text',
        'footer_copyright_field_render',
        'theme-options',
        'footer_settings_section'
    );

    add_settings_field(
        'footer_address',
        'Footer Address',
        'footer_address_field_render',
        'theme-options',
        'footer_settings_section'
    );

    add_settings_field(
        'footer_facebook',
        'Facebook URL',
        'footer_facebook_field_render',
        'theme-options',
        'footer_settings_section'
    );

    add_settings_field(
        'footer_instagram',
        'Instagram URL',
        'footer_instagram_field_render',
        'theme-options',
        'footer_settings_section'
    );

    add_settings_field(
        'footer_pinterest',
        'Pinterest URL',
        'footer_pinterest_field_render',
        'theme-options',
        'footer_settings_section'
    );

    add_settings_field(
        'footer_youtube',
        'YouTube URL',
        'footer_youtube_field_render',
        'theme-options',
        'footer_settings_section'
    );
}

// Render the logo upload field (Header Section)
function header_logo_field_render()
{
    $header_logo = get_option('header_logo');
    ?>
    <input type="text" id="header_logo" name="header_logo" value="<?php echo esc_attr($header_logo); ?>" />
    <button id="upload_logo_button" class="button">Upload Logo</button>
    <script>
        jQuery(document).ready(function ($) {
            var frame;
            $('#upload_logo_button').on('click', function (e) {
                e.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: 'Select or Upload Logo',
                    button: { text: 'Use this logo' },
                    multiple: false
                });
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#header_logo').val(attachment.url);
                });
                frame.open();
            });
        });
    </script>
    <?php
}

// Render the text to replace logo field (Header Section)
function header_logo_text_field_render()
{
    $header_logo_text = get_option('header_logo_text');
    ?>
    <input type="text" name="header_logo_text" value="<?php echo esc_attr($header_logo_text); ?>" />
    <?php
}

// Render the copyright field (Footer Section)
function footer_copyright_field_render()
{
    $footer_copyright = get_option('footer_copyright');
    ?>
    <input type="text" name="footer_copyright" value="<?php echo esc_attr($footer_copyright); ?>" />
    <?php
}

// Render the address field (Footer Section)
function footer_address_field_render()
{
    $footer_address = get_option('footer_address');
    ?>
    <input type="text" name="footer_address" value="<?php echo esc_attr($footer_address); ?>" />
    <?php
}

// Render the Facebook URL field (Footer Section)
function footer_facebook_field_render()
{
    $footer_facebook = get_option('footer_facebook');
    ?>
    <input type="text" name="footer_facebook" value="<?php echo esc_attr($footer_facebook); ?>" />
    <?php
}

// Render the Instagram URL field (Footer Section)
function footer_instagram_field_render()
{
    $footer_instagram = get_option('footer_instagram');
    ?>
    <input type="text" name="footer_instagram" value="<?php echo esc_attr($footer_instagram); ?>" />
    <?php
}

// Render the Pinterest URL field (Footer Section)
function footer_pinterest_field_render()
{
    $footer_pinterest = get_option('footer_pinterest');
    ?>
    <input type="text" name="footer_pinterest" value="<?php echo esc_attr($footer_pinterest); ?>" />
    <?php
}

// Render the YouTube URL field (Footer Section)
function footer_youtube_field_render()
{
    $footer_youtube = get_option('footer_youtube');
    ?>
    <input type="text" name="footer_youtube" value="<?php echo esc_attr($footer_youtube); ?>" />
    <?php
}

// Create the options page structure
function custom_theme_options_page()
{
    ?>
    <div class="wrap">
        <h1>Theme Options</h1>

        <form method="post" action="options.php">
            <?php
            settings_fields('theme_options_group');
            do_settings_sections('theme-options');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

?>