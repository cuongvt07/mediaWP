<?php
/**
 * Top User Menu Template
 * 
 * This template handles the display of the top user menu in the WPRentals theme.
 * It shows different options based on whether a user is logged in or not.
 * For logged-in users, it displays their profile picture and access to dashboard features.
 * For guests, it shows login, signup, and property submission options.
 *
 * @package WPRentals
 * @subpackage Templates
 * @since 4.0.0
 * 
 * Dependencies:
 * - wpestate_get_all_dashboard_template_links() for cached dashboard URLs
 * - wprentals_get_option() for theme settings
 * - wpestate_check_user_level() for user permission checks
 * - WooCommerce class (optional) for cart functionality
 */

// Get current user object
$current_user = wp_get_current_user();

// Batch fetch all user meta to reduce database queries
$all_user_meta = get_user_meta($current_user->ID);

// Get user's profile picture ID from the batched meta data
$user_small_picture_id = isset($all_user_meta['small_custom_picture'][0]) ? $all_user_meta['small_custom_picture'][0] : '';

// Set user's profile picture URL - default or custom
if ($user_small_picture_id == '') {
    // Use default picture if no custom picture is set
    $user_small_picture[0] = get_stylesheet_directory_uri().'/img/default_user_small.png';
} else {
    // Get custom picture in the correct size
    $user_small_picture = wp_get_attachment_image_src($user_small_picture_id, 'wpestate_user_thumb');
}

// Access global payment object for WooCommerce integration
global $wpestate_global_payments;

// SECTION: Logged-in User Display
if (is_user_logged_in()) { ?>
    <div class="user_menu user_loged" id="user_menu_u">
        <?php
        // Show WooCommerce cart icon if WooCommerce is active
        if (class_exists('WooCommerce')) {
            $wpestate_global_payments->show_cart_icon();
        }
        ?>
        
        <!-- User Profile Picture -->
        <div class="menu_user_picture" style="background-image: url('<?php print esc_url($user_small_picture[0]); ?>');"></div>
        
        <!-- Username Dropdown Trigger -->
        <a class="menu_user_tools dropdown" id="user_menu_trigger" data-toggle="dropdown">    
            <?php echo '<span class="menu_username">'.ucwords($current_user->user_login).'</span>'; ?>   
            <i class="fas fa-caret-down"></i> 
        </a>
       
<?php } else { 
    // SECTION: Guest User Display ?>
    <div class="user_menu" id="user_menu_u">   
        <?php
        // Show WooCommerce cart icon if WooCommerce is active
        if (class_exists('WooCommerce')) {
            $wpestate_global_payments->show_cart_icon();
        }
        ?>
        
        <!-- Login/Signup Links -->
        <div class="signuplink" id="topbarlogin"><?php esc_html_e('Login','wprentals');?></div>
        <div class="signuplink" id="topbarregister"><?php esc_html_e('Sign Up','wprentals')?></div>    
        
        <?php 
        // Show property submission link if enabled in theme options
        if (esc_html(wprentals_get_option('wp_estate_show_submit','')) === 'yes') {
            // Get cached dashboard links
            $dashboard_links = wpestate_get_all_dashboard_template_links();
            $add_link = isset($dashboard_links['user_dashboard_add_step1.php']) ? $dashboard_links['user_dashboard_add_step1.php'] : '';
            if ($add_link) { ?>
                <a href="<?php echo esc_url($add_link); ?>" id="submit_action"><?php esc_html_e('Submit Property','wprentals');?></a>
            <?php }
        } ?>                   
<?php } ?>                  
    </div> 
     
<?php 
// SECTION: Logged-in User Menu Options
if (0 != $current_user->ID && is_user_logged_in()) {
    // Get all dashboard links from cache
    $dashboard_links = wpestate_get_all_dashboard_template_links();
    $home_url = esc_html(home_url('/'));
    
    // Get unread messages count from batched meta
    $unread_mess = isset($all_user_meta['unread_mess'][0]) ? intval($all_user_meta['unread_mess'][0]) : 0;
    ?> 
    <div id="user_menu_open"> 
        <?php 
        // Profile Link - Show if not on profile page
        if (isset($dashboard_links['user_dashboard_profile.php']) && $home_url != $dashboard_links['user_dashboard_profile.php']) { ?>
            <a href="<?php echo esc_url($dashboard_links['user_dashboard_profile.php']); ?>">
                <i class="fas fa-cog"></i><?php esc_html_e('My Profile','wprentals');?>
            </a>   
        <?php }
        
        // Listings Link - Show if user has appropriate permissions
        if (isset($dashboard_links['user_dashboard.php']) && $home_url != $dashboard_links['user_dashboard.php'] && wpestate_check_user_level()) { ?>
            <a href="<?php echo esc_url($dashboard_links['user_dashboard.php']); ?>">
                <i class="fas fa-map-marker"></i><?php esc_html_e('My Listings','wprentals');?>
            </a>
        <?php }
        
        // Add Listing Link - Show if user has appropriate permissions
        if (isset($dashboard_links['user_dashboard_add_step1.php']) && $home_url != $dashboard_links['user_dashboard_add_step1.php'] && wpestate_check_user_level()) { ?>
            <a href="<?php echo esc_url($dashboard_links['user_dashboard_add_step1.php']); ?>">
                <i class="fas fa-plus"></i><?php esc_html_e('Add New Listing','wprentals');?>
            </a>        
        <?php }
        
        // Favorites Link
        if (isset($dashboard_links['user_dashboard_favorite.php']) && $home_url != $dashboard_links['user_dashboard_favorite.php']) { ?>
            <a href="<?php echo esc_url($dashboard_links['user_dashboard_favorite.php']); ?>" class="active_fav">
                <i class="fas fa-heart"></i><?php esc_html_e('Favorites','wprentals');?>
            </a>
        <?php }
        
        // Reservations Link
        if (isset($dashboard_links['user_dashboard_my_reservations.php']) && $home_url != $dashboard_links['user_dashboard_my_reservations.php']) { ?>
            <a href="<?php echo esc_url($dashboard_links['user_dashboard_my_reservations.php']); ?>" class="active_fav">
                <i class="fas fa-folder-open"></i><?php esc_html_e('Reservations','wprentals');?>
            </a>
        <?php }
        
        // Bookings Link - Show if user has appropriate permissions
        if (isset($dashboard_links['user_dashboard_my_bookings.php']) && $home_url != $dashboard_links['user_dashboard_my_bookings.php'] && wpestate_check_user_level()) { ?>
            <a href="<?php echo esc_url($dashboard_links['user_dashboard_my_bookings.php']); ?>" class="active_fav">
                <i class="far fa-folder-open"></i><?php esc_html_e('Bookings','wprentals');?>
            </a>
        <?php }

        // Reviews Link - Show only on mobile devices
        if (wp_is_mobile() && isset($dashboard_links['user_dashboard_my_reviews.php']) && $home_url != $dashboard_links['user_dashboard_my_reviews.php']) { ?>
            <a href="<?php echo esc_url($dashboard_links['user_dashboard_my_reviews.php']); ?>" class="active_fav">
                <i class="fa-solid fa-chart-simple"></i><?php esc_html_e('Reviews','wprentals');?>
            </a>
        <?php }
        
        // Inbox Link with unread message counter
        if (isset($dashboard_links['user_dashboard_inbox.php']) && $home_url != $dashboard_links['user_dashboard_inbox.php']) { ?>
            <a href="<?php echo esc_url($dashboard_links['user_dashboard_inbox.php']); ?>" class="active_fav">
                <div class="unread_mess_wrap_menu"><?php echo trim($unread_mess); ?></div>
                <i class="fas fa-inbox"></i><?php esc_html_e('Inbox','wprentals');?>
            </a>
        <?php }
        
        // Invoices Link - Show if user has appropriate permissions
        if (isset($dashboard_links['user_dashboard_invoices.php']) && $home_url != $dashboard_links['user_dashboard_invoices.php'] && wpestate_check_user_level()) { ?>
            <a href="<?php echo esc_url($dashboard_links['user_dashboard_invoices.php']); ?>" class="active_fav">
                <i class="far fa-file"></i><?php esc_html_e('Invoices','wprentals');?>
            </a>
        <?php } ?>
           
        <!-- Logout Link -->
        <a href="<?php echo wp_logout_url(wpestate_wpml_logout_url()); ?>" title="Logout" class="menulogout">
            <i class="fas fa-power-off"></i><?php esc_html_e('Log Out','wprentals');?>
        </a>
    </div>
    
<?php } 

// Show WooCommerce cart if WooCommerce is active
if (class_exists('WooCommerce')) {
    $wpestate_global_payments->show_cart();
}
?>