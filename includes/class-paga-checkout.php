<?php
/**
 * PagaCheckout Woocommerce plugin
 * PHP version 7.2.0
 * 
 * @category PHP
 * @package  PagaCheckout
 * @author   Nwabuokei Nnamdi <nwabuokeinnamdi19@gmail.com>
 * @license  GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link     https://wordpress.org/plugins/paga-checkout/
 */

if (! defined('ABSPATH')) {
    exit;
}
/**
 * PagaCheckout Woocommerce class
 * 
 * @category PHP
 * @package  PagaCheckout
 * @author   Nwabuokei Nnamdi <nwabuokeinnamdi19@gmail.com>
 * @license  GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link     https://wordpress.org/plugins/paga-checkout/
 */
class WC_Paga_Checkout extends WC_Payment_Gateway
{
    /**
     * Check If Test mode is active;
     *
     * @var bool
     */
    public $testmode;

    /**
     * Paga Payment Page Type
     *
     * @var string
     */
    // public $payment_page;

    /**
     * Paga Checkout test public key.
     *
     * @var string
     */

    public $test_public_key;


     /**
      * Paga Checkout test secret key.
      *
      * @var string
      */

    public $test_secret_key;


    /**
     * Paga Checkout live public key
     *
     * @var string
     */
    public $live_public_key;

     /**
      * Paga Checkout live secret key
      *
      * @var string
      */
    public $live_secret_key;


    /**
     * Constructor
     * 
     * @version 1.0.0
     * @since   1.0.0
     */
    public function __construct()
    {
        $this->id  = 'paga-checkout';
        $this->icon = apply_filters(
            'woocommerce_paga_icon', 
            trailingslashit(plugins_url('assets/pay-with-paga.png', __FILE__))
        );
        $this->has_fields = false;
        $this->method_title = __('PagaCheckout ', 'paga-checkout');
        $this->method_description=sprintf(
            __(
                'Paga Checkout provides an easy-to-integrate payment collection tool 
                for any online merchant.
                It supports funding sources from Cards, 
            Bank accounts and Paga wallet.<a href="%1$s" target="_blank">Sign up</a> 
            for a Paga account,
             and <a href="%2$s" target="_blank">get your paga-checkout 
             credentials</a>.', 'paga-checkout'
            ),
            esc_url('https://www.mypaga.com/'),
            esc_url('https://www.mypaga.com/paga-business/register.paga')
        );
        $this->has_fields = true;

        //Loads the form fields
        $this->init_form_fields();

        //Load the settings
        $this->init_settings();


        //Get setting values
        $this->title =                      __('Checkout');
        
        $this->description = $this->get_option('description') == '' 
        ? 'Make Payments with any payment method - cards, 
        direct bank debit, mobile money, and more' : 
        get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->testmode=$this->get_option('testmode') == 'yes' ? true : false;
        $this->paga_checkout_display_name= $this->get_option(
            'paga_checkout_display_name'
        );
        $this->display_image_url=$this->get_option('display_image_url');
        $this->display_tagline=$this->get_option('display_tagline');

        $this->test_public_key = $this->get_option('test_public_key');

        $this->live_public_key = $this->get_option('live_public_key');

        $this->test_secret_key = $this->get_option('test_secret_key');

        $this->live_secret_key = $this->get_option('live_secret_key');
        $this->support_contact = $this->get_option('merchant_contact_details');

        $this->public_key = $this->testmode ? $this->test_public_key : 
        $this->live_public_key;

        $this->secret_key = $this->testmode ? $this->test_secret_key : 
        $this->live_secret_key;
     
        // $this->charge_url= $this->get_option('charge_url');
      

        // $this->charge_url = wc_get_page_permalink('shop');

        //Hooks
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
   
        add_action('admin_notices', array($this, 'admin_notices'));

        add_action('admin_enqueue_scripts', array($this,'load_admin_style'));
        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            array(
            $this, 'process_admin_options'
            )
        );
    
        
        add_action(
            'woocommerce_receipt_' . $this->id, array(
                $this, 'generatePagaCheckoutWidget',
                )
        );

        //Webhook listener/API hook
        add_action('woocommerce_api_' . $this->id, array( $this, 'webhook' ));

        add_action('init', 'woocommerce_clear_cart_url');


        //Check if the gateway can be used
        if (!$this->is_valid_for_use()) {
            $this->enabled= false;
        }
    }


    
    /**
     * Check if the gateway is enabled and available for user's country.
     * 
     * @version 1.0.0
     * @since   1.0.0
     * @return  boolean
     */
    public function is_valid_for_use()
    {
        if (! in_array(get_woocommerce_currency(), apply_filters('woocommerce_paga_checkout_supported_currencies', array('NGN')))) {
            $this->msg= sprintf(__('Paga Checkout does not support your store currency, Kindly set it to either NGN (&#8358) <a href="%s">here</a>', 'paga-checkout'), admin_url('admin.php?page=wc-settings&tab=general'));
            return false;
        }
        return true;
    }



    /**
     * Display pagacheckout payment icon
     * 
     * @version 1.0.0
     * @since   1.0.0
     * @return  string
     */
    public function get_icon()
    {
        $icon = '<img src="' .WC_HTTPS::force_https_url(plugins_url('assets/images/pay-with-paga.png', PAGA_CHECKOUT_MAIN_FILE)).'" alt="cards"/>';
        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }

    /**
     * Check if Paga Checkout merchant details is filled.
     * 
     * @version 1.0,0
     * @since   1.0.0
     * @return  null
     */
    public function admin_notices()
    {
        if ($this ->enabled == 'no') {
            return;
        }

        //Check required fields.
        if (!($this->public_key)) {
            echo '<div class="notice notice-error" </p>' .sprintf(__('Please enter your Paga merchant details <a href="%s">here</a> to be able to use Paga Checkout WooCommerce plugin.', 'paga-checkout'), admin_url('admin.php?page=wc-settings&tab=checkout&section=paga-checkout')) .'</p></div>';
            return;
        }

        if (!($this->secret_key)) {
            echo '<div class="notice notice-error" </p>' .sprintf(__('Please enter your Secret Key <a href="%s">here</a> to be able to use Paga Checkout WooCommerce plugin.', 'paga-checkout'), admin_url('admin.php?page=wc-settings&tab=checkout&section=paga-checkout')) .'</p></div>';
            return;
        }

    }
        
    /**
     * Check if Paga Checkout is enabled
     * 
     * @version 1.0.0
     * @since   1.0.0
     *
     * @return bool
     */
    public function is_available()
    {
        if ('yes' == $this->enabled) {
            if (!($this->public_key)) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Admin Panel Options
     * 
     * @version 1.0.0
     * @since   1.0.0
     * @return  null
     */
    public function admin_options()
    {
        ?>
              <h1 class='paga-check-out-title' ><?php _e('Paga Checkout', 'paga-checkout'); ?>
              <?php
                if (function_exists('wc_back_link')) {
                    wc_back_link(
                        __('Return to payments', 'paga-checkout'),
                        admin_url('admin.php?page=wc-settings&tab=checkout')
                    );
                } ?>
              </h1>
              <p><?php _e('Paga Payment Gateway allows you to accept payment on your Woocommerce Powered Store Using Paga Express Checkout', 'paga-checkout'); ?></p>
              <?php
                if ($this->is_valid_for_use()) {
                     echo '<table class="form-button">';
                     $this->generate_settings_html();
                     echo '</table>';
                } else {
                    ?>
                    <div class="inline error"><p><strong><?php _e('Paga Checkout is disabled ', 'paga-checkout'); ?></strong>: <?php echo $this->msg; ?></p></div>

                    <?php
                }
    }

    /**
     * Initialise Paga Checkout Settings Form Fields.
     * 
     * @version 1.0.0
     * @since   1.0.0
     * @return  null
     */
    public function init_form_fields()
    {
        $form_fields = array(
                  'enabled'                  =>  array(
                      'title'       => __('Enable/Disable', 'paga-checkout'),
                      'label'       => __('Enable Paga Checkout', 'paga-checkout'),
                      'type'        => 'checkbox',
                      'description' => __('Enable Paga Checkout as a payment option on the checkout page', 'paga-checkout'),
                      'default'     =>'no',
                      'desc_tip'    => true,
                    
                  ),
                  'description'                   => array(
                      'title'       => __('Description', 'paga-checkout'),
                      'type'        => 'textarea',
                      'description' => __('This gives the user information about the payment methods available during checkout', 'paga-checkout'),
                      'default'     => __('Make Payments with any payment method - cards, direct bank debit, mobile money, and more', 'paga-checkout'),
                      'desc_tip'    => true,
                  ),

                  'testmode'                     => array(
                      'title'       => __('Test mode', 'paga-checkout'),
                      'label'       => __('Enable Test Mode', 'paga-checkout'),
                      'type'        => 'checkbox',
                      'description' => __('Test mode enables you to test payments before going live.', 'paga-checkout'),
                      'default'     => 'yes',
                      'desc_tip'    => true,

                  ),

                  'paga_checkout_display_name'             => array(
                    'title'       => __('Display Name', 'paga-checkout'),
                    'type'        => 'text',
                    'description' => __(
                        'Enter the name that would be displayed on paga-checkout widget.', 'paga-checkout'
                    ),
                    'default'     => ''
                ),
                'display_image_url'                   => array(
                    'title'       => __('Store logo', 'paga-checkout'),
                    'type'        => 'text',
                    'description' => __(
                        'Enter the link to your store\'s logo', 
                        'paga-checkout'
                    ),
                    'desc_tip'    => true,
                ),
                'display_tagline'                   => array(
                    'title'       => __('Store tagline', 'paga-checkout'),
                    'type'        => 'text',
                    'description' => __(
                        'Enter  your store\'s tagline', 'paga-checkout'
                    ),
                    'desc_tip'    => true,
                ),

                  'test_public_key'             => array(
                    'title'       => __('Test Public key', 'paga-checkout'),
                    'type'        => 'text',
                    'description' => __(
                        'Enter your Test Public Key here.', 'paga-checkout'
                    ),
                      'default'     => '',
                      
                  ),

                'live_public_key'             => array(
                    'title'       => __('Live Public key', 'paga-checkout'),
                    'type'        => 'text',
                    'description' => __(
                        'Enter your Live Public Key here.', 'paga-checkout'
                    ),
                    'default'     => ''
                ),

              'test_secret_key'                     => array(
                'title'       => __('Test Secret Key', 'paga-checkout'),
                'type'        => 'text',
                'description' => __(
                    'Enter your Test Secret key here', 'paga-checkout'
                ),
                  'default'     => '',

            ),

            'live_secret_key'                     => array(
                'title'       => __('Live Secret Key', 'paga-checkout'),
                'type'        => 'text',
                'description' => __(
                    'Enter your Live Secret key here', 'paga-checkout'
                ),
                  'default'     => '',

            ),
            'merchant_contact_details'             => array(
                'title'       => __('Customer care contact', 'paga-checkout'),
                'type'        => 'text',
                'description' => __(
                    'Enter contact details that customer can contact in case of payment issues. This could be email, phone number or contact address', 'paga-checkout'
                ),
                'default'     => '',
                'desc_tip'    => true,
            ),
                  );
        
        $this->form_fields=$form_fields;
    }


    /**
     * Verify if transaction was successful
     * 
     * @param string $public_key Public key 
     * @param string $secret_key Secret key
     * 
     * @return  Array
     * @since   2.0.1
     * @version 2.0.1
     */
    public function verifyOutstandingTransactions($public_key, $secret_key, $current_order_id) 
    {
        $getPendingOrders = wc_get_orders(
            array(
            'status'      => array('wc-cancelled', 'wc-pending'),
            'payment_method' => 'paga-checkout',
            'limit'       =>  20,
            'return'      => 'ids',
            )
        );

        $this -> send_to_console($getPendingOrders);
        $pending_orders =array_diff($getPendingOrders, array($current_order_id));

        if (count($pending_orders) > 0) {
            foreach ($pending_orders as $orders => $order_value) {
                $order=wc_get_order($order_value);
                $transaction_amount = method_exists($order, 'get_total') ? 
                $order->get_total():$order->order_total;
                $this->send_to_console($order);
                $pending_details = $this->verifyTransaction(
                    $public_key, $secret_key, $transaction_amount, 
                    $pending_orders[$orders]
                );
                $status = isset($pending_details["status_code"]) ? 
                $pending_details["status_code"] : $pending_details["code"];
                if ($status === 0) {
                    
                    $order->update_status(
                        'completed', __('Payment completed', 'paga-checkout')
                    );
        
                } elseif ($status === 500) {
                    $order->update_status(
                        'pending', __('Payment Pending verification', 'paga-checkout')
                    );
                } elseif ($status === 38066) {
                    $order->update_status(
                        'failed', __('Payment failed', 'paga-checkout')
                    );
                } else {
                    $order->update_status(
                        'failed', __('Payment failed', 'paga-checkout')
                    );
                }
            }
        }
    }


    /**
     * Generate paga-checkout-form
     * 
     * @since   1.0.0
     * @version 1.0.0
     * @return  null
     */
    public function generatePagaCheckoutWidget()
    {
        
        $order_key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : '';
        $order_id = absint(get_query_var('order-pay'));
        $order = wc_get_order($order_id);
        $transaction_amount = method_exists($order, 'get_total') ? 
        $order->get_total():$order->order_total;
        global $wp;
        $path = $wp->request;
        $get_path_object = explode("/", $path);
        $payment_reference = !empty(strval($get_path_object[count($get_path_object)-1])) ? strval($get_path_object[count($get_path_object)-1]) : '';


        $redirect_url = home_url() . "/" . $order_id . "/wc-api/paga-checkout";
       
        
        $public_key = isset($this->public_key) ? $this->public_key : '';
        $secret_key = isset($this->secret_key) ? $this->secret_key : '';
        $contact_details = isset($this->support_contact) ? $this->support_contact : '';
        if (empty($public_key) || empty($secret_key) || empty($transaction_amount) || empty($payment_reference)) {
            wc_add_notice('Your secret key or public key was not provided', 'error');
            exit;
        }
        $this -> verifyOutstandingTransactions($public_key, $secret_key, $order_id);

        $response_details = $this->verifyTransaction(
            $public_key, $secret_key, $transaction_amount, $payment_reference
        );
        $status = isset($response_details["status_code"]) ? 
        $response_details["status_code"] : $response_details["code"];

        if ($status === 0) {
            
            $order->update_status(
                'completed', __('Payment completed', 'paga-checkout')
            );

            WC()->cart->empty_cart();

            wp_redirect(home_url(), '301');
            return;

        } elseif ($status === 401) {
            wc_print_notice(sprintf(__('Invalid checkout  credentials. Please contact Customer care %s'), $contact_details), 'error');  
            return;         
        } else {

            if (!is_checkout_pay_page()) {
                return;
            }

            if ($this->enabled=== 'no') {
                return;
            }
                
        
            $order->update_status(
                'pending', __('Payment pending', 'paga-checkout')
            );

            

            $paga_checkout_params = array(
                    'public_key' => $public_key,
                    'paga_checkout_display_name' => !empty($this->paga_checkout_display_name) ? $this->paga_checkout_display_name : 'Paga Checkout',
                    'charge_url'=> $redirect_url,
                    'data-redirect_url_method' => 'GET',
                    'display_image_url' => (isset($this->display_image_url) && !($this->display_image_url === '')) ? $this->display_image_url : esc_url_raw('https://cdn-assets-cloud.frontify.com/local/frontify/eyJwYXRoIjoiXC9wdWJsaWNcL3VwbG9hZFwvc2NyZWVuc1wvMTgzMDM4XC8wYWU2ODA0MmE5ZWU2OWUwMmE2YjlkOWRhZjdhNDhjMS0xNTQxNzYxMDM1LnBuZyJ9:frontify:bbSWcJvMlA_jz0c7aiHQ8wDCc-XjuUIQWdhxRXA-ROs?width=2400'),
                    'display_tagline' =>(isset($this->display_tagline) && !($this->display_tagline === '')) ? $this->display_tagline: __('')
                    
                );


            if (is_checkout_pay_page() && get_query_var('order-pay')) {
                $email      = method_exists($order, 'get_billing_email') ? 
                $order->get_billing_email() : sanitize_email($order->billing_email);
                $phone_number= method_exists($order, 'get_billing_phone') ?
                $order->get_billing_phone() : $order->get_billing_phone;
                $amount     = $transaction_amount;
                $txnref     = $order_id;
                $the_order_id  = method_exists($order, 'get_id') 
                ? $order->get_id() : $order->id;
                $the_order_key = method_exists($order, 'get_order_key')
                ? $order->get_order_key() : $order->order_key;
            }

            if ($the_order_id == $order_id && $the_order_key == $order_key) {
                $paga_checkout_params['email'] = sanitize_email($email);
                $paga_checkout_params['amount']= $amount;
                $paga_checkout_params['txn_ref']= $txnref;
                $paga_checkout_params['currency'] = get_woocommerce_currency();
                $paga_checkout_params['phone_number']= $phone_number;
            }
            

            if ($this->testmode) {
                $paga_checkout_params['checkout']  = esc_url_raw(
                    'https://qa1.mypaga.com/checkout/'
                );
            } else {
                $paga_checkout_params['checkout']  = esc_url_raw(
                    'https://www.mypaga.com/checkout/'
                );
            } ?>
                <div id='embed-checkout'>
                <p id="end-note"><?php _e('Thank you for your order, please click the button to pay with Paga.', 'paga-checkout')?></p>
                </div>
                
            
                <?php
                wp_enqueue_script('wc_paga_checkout', plugins_url('assets/js/paga-checkout' . '.js', PAGA_CHECKOUT_MAIN_FILE));
                wp_localize_script('wc_paga_checkout', 'wc_paga_checkout_params', $paga_checkout_params);
        }


    }

    /**
     * Console to the browser
     * @since 1.0.0
     * @version 1.0.0
     */
    public function send_to_console($debug_output) 
    {

        $cleaned_string = '';
        if (!is_string($debug_output))
            $debug_output = print_r($debug_output, true);
    
          $str_len = strlen($debug_output);
        for($i = 0; $i < $str_len; $i++) {
            $cleaned_string .= '\\x' . sprintf('%02x', ord(substr($debug_output, $i, 1)));
        }
        $javascript_ouput = "<script>console.log('Debug Info: " .$cleaned_string. "');</script>";
        echo $javascript_ouput;
    }

  
    /**
     * Process the payment and return the result
     * 
     * @param int $order_id Order Id
     * 
     * @since   1.0.0
     * @version 1.0.0
     *
     * @return array|void
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        return array(
                   'result' => 'success',
                   'redirect' => $order->get_checkout_payment_url(true)
               );
    }




    /**
     * Verify if transaction was successful
     * 
     * @param string $public_key        Public key 
     * @param string $secret_key        Secret key
     * @param number $amount            Transaction amount
     * @param string $payment_reference Reference number/Payment reference
     * 
     * @return  Array
     * @since   2.0.0
     * @version 2.0.0
     */
    function verifyTransaction($public_key, $secret_key, $amount, $payment_reference)
    {

        $body =    array( 
            "publicKey" => $public_key,
            "secretKey" => $secret_key,
            "amount" => $amount,
            "currency" => "NGN",
            "paymentReference" => $payment_reference
            ) ;

        $args = array(
            'timeout' => 60,
            'redirection' => 5,
            'blocking' => true,
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => 
            json_encode($body),
            'cookies' => array()                            
        );

        $response = "";

        if ($this->testmode) {
            $verify_url = 'https://qa1.mypaga.com/checkout/transaction/verify';
            $response = wp_remote_post(
                $verify_url, $args
            );
           

        } else {
            $verify_url = 'https://www.mypaga.com/checkout/transaction/verify';
            $response = wp_remote_post(
                $verify_url, $args
            );
        }
        $response_details = json_decode($response["body"], true);
     
        if ($response_details === null) {
             $response_details = $response["response"];
        }
        $this->send_to_console($response_details);
        return $response_details;

    }


    /**
     * Web hook to verify and update payment status
     * 
     * @since   2.0.0
     * @version 2.0.0
     * @return  null
     */
    function webhook()
    { 
        $current_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        // $get_id = explode("/", $current_url);
        $url_components = parse_url($current_url); 
        parse_str($url_components['query'], $params); 
        $order_id = $params['charge_reference'];
        $order=wc_get_order($order_id);
        $transaction_amount = method_exists($order, 'get_total') ? 
        $order->get_total():$order->order_total;
        $public_key = isset($this->public_key) ? $this->public_key : '';
        $secret_key = isset($this->secret_key) ? $this->secret_key : '';


        //mG8*bqYeA+BERnB
        $response_details = $this->verifyTransaction(
            $public_key, $secret_key, $transaction_amount, $order_id
        );
        $status = isset($response_details["status_code"]) ? 
        $response_details["status_code"] : $response_details["code"];
        if ($status === 0) {
            
          
            WC()->cart->empty_cart();
           

            $order->update_status(
                'completed', __('Payment completed', 'paga-checkout')
            );
           
                $page=home_url();
                
                header('location: ' . $page, true, 301); exit;

        } elseif ($status === 500) {
            WC()->cart->empty_cart();
           

            $order->update_status(
                'pending', __('Payment Pending verification', 'paga-checkout')
            );
           
                $page=home_url();
                
                header('location: ' . $page, true, 301); exit;
        } else {
                $order->update_status(
                    'failed', __('Payment failed', 'paga-checkout')
                );
        }

        return;
    }

    
    /**
     * Clear cart on initialization
     * 
     * @since   1.0.0
     * @version 1.0.0
     * @return  null
     */
    function woocommerce_clear_cart_url() 
    { 
        if (isset($_GET['key'])) {
            global $woocommerce;
            $woocommerce->cart->empty_cart();
            // WC()->cart->empty_cart();
        }
    }

    /**
     * Load admin scripts
     * 
     * @since   1.0.0
     * @version 1.0.0
     * @return  null
     */
    public function admin_scripts()
    {
        if ('woocommerce_page_wc-settings' !== get_current_screen()->id) {
            return;
        }
        
        wp_enqueue_script('wc_paga_checkout_admin', plugins_url('assets/js/paga-checkout-admin' . '.js', PAGA_CHECKOUT_MAIN_FILE));
        
    }



    /**
     * Add extra css styling
     * 
     * @since   1.0.0
     * @version 1.0.0
     * @return  null
     */
    public function load_admin_style()
    {
        wp_register_style('custom-styles', plugins_url('assets/css/paga-checkout' . '.css', PAGA_CHECKOUT_MAIN_FILE));
        // Load my custom stylesheet
        wp_enqueue_style('custom-styles');
    }

}
