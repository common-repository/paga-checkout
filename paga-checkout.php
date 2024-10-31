<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.mypaga.com/
 * @since             1.0.0
 * @package           Paga_Checkout
 *
 * @wordpress-plugin
 * Plugin Name:       Paga Checkout
 * Plugin URI:        https://github.com/pagadevcomm/wc-pagaCheckout
 * Description:       Paga Checkout for accepting online payments.
 * Version:           2.0.3
 * Author:            Paga
 * Author URI:        https://www.mypaga.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       paga-checkout
 * Domain Path:       /languages
 */

 if (! defined( 'ABSPATH') ) {
     exit;
 }

 define('PAGA_CHECKOUT_MAIN_FILE', __FILE__);
 define('PAGA_CHECKOUT_VERSION', '2.0.0');

/**
 * Initialize PagaCheckout WooCommerce payment gateway
 * 
 * @version 1.0.0
 * @since 1.0.0
 * @access public
 */

  function wc_paga_checkout_init()
  {
      load_plugin_textdomain('paga-checkout', false, plugin_basename( dirname(__FILE__)) . '/languages');

      if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		add_action( 'admin_notices', 'paga_checkout_missing_notice' );
		return;
    }
    
    add_action('admin_notices', 'paga_checkout_testmode_notice');

    //Functions
    require_once dirname(__FILE__) . '/includes/class-paga-checkout.php';

    //Settings
    add_filter('woocommerce_payment_gateways', 'add_paga_checkout_gateway',99);

    //Action links
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'paga_checkout_plugin_action_links');



    
  }

  add_action('plugins_loaded', 'wc_paga_checkout_init',99);


 


  /**
   * Add Settings link to the plugin entry in the plugins menu
   * 
   * @version 1.0.0
   * @since 1.0.0
   * @param array $links Plugin action links.
   * 
   * @return array
   */

   function paga_checkout_plugin_action_links($links)
   {
       $settings_link = array(
        //    'settings' => '<a href="' .admin_url( 'admin.php?page=wc-settings&tab=checkout&section=pagacheckout') .'" title="' . __('Paga Checkout Woo Commerce Settings', 'paga-checkout').'">' . __( 'Settings', 'paga-checkout') . '</a>',
        'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=WC_Paga_Checkout' ) . '" title="' . __( 'View Paga WooCommerce Settings', 'paga-checkout' ) . '">' . __( 'Settings', 'paga-checkout' ) . '</a>',
       );

       return array_merge($settings_link, $links);
   }

   

 
     /**
     * Add Paga Checkout to WooCommerce
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param array $methods WooCommerce payment gateways methods
     * @return array
     */

    function add_paga_checkout_gateway($methods)
    {

       if (class_exists('WC_Payment_Gateway') && 'NGN' === get_woocommerce_currency()) {
           $methods[] = 'WC_Paga_Checkout';
           $settings = get_option('paga_checkout_settings', '');
       } else {
          return;
       }
       return $methods;
      
    }

  

     function paga_checkout_missing_notice()
     {
         echo '<div class="error"><p><strong>' . sprintf(__('Paga Checkout requires WooCommerce to be installed and active. Click %s to install WooCommerce.','paga-checkout' ),'<a href="' .admin_url('plugin-install-php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539') .'" class="thickbox open-plugin-details-modal">here</a>') .'</strong></p></div>';
     }

    /**
     * Display the test mode notice
     * 
     * @since 1.0.0
     * @version 1.0.0
     */
      function paga_checkout_testmode_notice()
      {
          $paga_checkout_setting = get_option('paga_checkout_settings');
          $test_mode = isset($paga_checkout_setting['testmode']) ? $paga_checkout_setting['testmode'] : '';

          if ('yes' == $test_mode) {
              //Paga Checkout settings page URL link
              echo '<div class="update-nag">' .sprintf(__('Paga Checkout test mode is still enabled, Click <strong><a href="%s">here</a></strong> to disable it when you want to start accepting live payment on your site. ', 'paga-checkout'),esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=pagacheckout'))).'</div>';
          }
      }