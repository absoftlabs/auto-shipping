<?php
/**
 * Plugin Name: Auto Shipping
 * Description: Automatically selects the correct shipping method based on the selected billing district.
 * Version: 1.2.2
 * Author: absoftlab
 * Author URI: https://absoftlab.com
 * License: GPL-2.0+
 */


if (!defined('ABSPATH')) exit;

// Filter shipping rates based on billing state
add_filter('woocommerce_package_rates', 'fitmax_shipping_method_control', 10, 2);
function fitmax_shipping_method_control($rates, $package) {
    $billing_state = WC()->customer->get_billing_state();

    // Method IDs
    $inside_dhaka_id      = 'inside_dhaka';
    $outside_dhaka_id     = 'outside_dhaka';
    $sundarban_courier_id = 'sundarban_courier';

    $filtered_rates = [];

    // If billing district is Dhaka
    if (strtoupper($billing_state) === 'BD-13') {
        if (isset($rates[$inside_dhaka_id])) {
            $filtered_rates[$inside_dhaka_id] = $rates[$inside_dhaka_id];
            WC()->session->set('chosen_shipping_methods', [$inside_dhaka_id]);
        }
        if (isset($rates[$sundarban_courier_id])) {
            $filtered_rates[$sundarban_courier_id] = $rates[$sundarban_courier_id];
        }
    } else {
        if (isset($rates[$outside_dhaka_id])) {
            $filtered_rates[$outside_dhaka_id] = $rates[$outside_dhaka_id];
            WC()->session->set('chosen_shipping_methods', [$outside_dhaka_id]);
        }
        if (isset($rates[$sundarban_courier_id])) {
            $filtered_rates[$sundarban_courier_id] = $rates[$sundarban_courier_id];
        }
    }

    return !empty($filtered_rates) ? $filtered_rates : $rates;
}

// JS to update shipping methods dynamically on district change
add_action('wp_footer', 'fitmax_reload_shipping_on_state_change');
function fitmax_reload_shipping_on_state_change() {
    if (!is_checkout()) return;
    ?>
    <script type="text/javascript">
        jQuery(function($) {
            $('#billing_state').on('change', function() {
                $('body').trigger('update_checkout');
            });
        });
    </script>
    <?php
}
