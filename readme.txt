=== PagaCheckout for WooCommerce===
Contributors: Paga
Donate link: https://www.mypaga.com/
Tags: wooCommerce, Paga, express checkout, payment, cards, bank, e-commerce, commerce, woothemes, WordPress e-commerce, store, sales, sell, shop, shopping, cart, checkout,
Requires at least: PHP: >=5.0.0
Tested up to: 5.7.1
Stable tag: 2.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

This is a PagaCheckout payment gateway for WooCommerce.
Paga solves two key problems that exist around the world - eliminating the use of cash for transactions and access to financial services. 

PagaCheckout payment gateway provides an easy-to-integrate payment collection tool for any online merchant. It supports multiple customer funding sources including cards, bank accounts and ​[Paga](https://www.mypaga.com/) wallet​.

With  PagaCheckout WooCommerce Payment Gateway plugin, you can  accept the following payment methods on your woocommerce site:

* __Paga__
* __Mastercard__
* __Visa__
* __Verve__
* __Bank Account__

= Note =

This plugin is recommended for merchants who do business in Naira.

= Plugin Features =

*   __Accepts payment__ seamlessly directly on your site.
*   __Seamless integration__ into the WooCommerce checkout page.


= Suggestions / Feature Request =

If you have suggestions or a new feature request, feel free to get in touch with us via the contact form on our website [here](https://www.paga.dev/).

You can also follow us on Twitter! **[@PagaDeveloper](https://twitter.com/pagadeveloper)**



== Installation ==

= Manual Installation =
1. 	Download the plugin zip file from [Github](https://github.com/pagadevcomm/paga-checkout-for-woocommerce) or [composer](https://packagist.org/packages/pagadevcomm/paga-checkout-for-woocommerce) by running composer require pagadevcomm/paga-checkout-for-woocommerce. Unzip the file.
2. 	Login to your WordPress Admin. Click on "Plugins > Add New" from the left-hand menu.
3.  Click on the "Upload" option, then click "Choose File" to select the unzipped file from your computer. Once selected, press "OK" and press the "Install Now" button.
4.  Activate the plugin.
5. 	Open the settings page for WooCommerce and click the "Checkout" tab.
6. 	Click on the __PagaCheckout__ link from the available Checkout Options
7.	Configure your __PagaCheckout payment plugin__ settings. See below for details.


= Configure the plugin =
To configure the plugin, go to __WooCommerce > Settings__ from the left hand menu, then click __Payment__ from the top tab. You will see __PagaCheckout__ as part of the available Payment methods. Click on it to configure the payment gateway.

* __Enable/Disable__ - check the box to enable PagaCheckout.
* __Description__ - controls the message that appears under the payment fields on the checkout page. Here you can list the types of cards you accept.
* __Test Mode__ - Check to enable test mode. Test mode enables you to test payments before going live. If you ready to start receving real payment on your site, kindly uncheck this.
* __Test Public Key__ - Enter your Test Public Key here. Get your API keys from your Paga Business account. For more details check the [Documentation](https://developer-docs.paga.com/docs/overview)
* __Live Public Key__ - Enter your Live Public Key here. Get your API keys from your Paga Business account. For more details check the [Documentation](https://developer-docs.paga.com/docs/overview)
* __Test Secret Key__ - Enter your Test Secret Key here. Get your API keys from your Paga Business account. For more details check the [Documentation](https://developer-docs.paga.com/docs/overview)
* __Live Secret Key__ - Enter your Live Secret Key here. Get your API keys from your Paga Business account. For more details check the [Documentation](https://developer-docs.paga.com/docs/overview)
* __Customer care contact__ - Enter contact details that customer can contact in case of payment issues. This could be email, phone number or contact address.
* Click on __Save Changes__ for the changes you made to be effected.

== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

1.	You need to have WooCommerce plugin installed and activated on your WordPress site.
2.	You need to ensure the Naira currency is enabled in your WordPress site setting.
3.	You need to open a Paga Business account on [Paga](https://www.mypaga.com/paga-business/register.paga), for test business account click [here]((https://beta.mypaga.com/paga-business/register.paga))


= Order status Tag After Payment =

1.	Completed - This means that the transaction was successful and has been verified at paga's end to be successful.
2.	Pending Payment - This means that the transaction verification was inconclusive either because the verify endpoint service was down or there was no response on verifying the transaction . In this case, the plugin would continue to try to verify that transaction until a definitive status of the transaction is gotten.
3.	Failed - This means the transaction was not successful after verification or did not exist.
4.  Cancelled - It means the transaction is inconclusive.

== Changelog ==

= 1.0.0 =
* First release

= 1.0.1 =
* Added assets for PagaCheckout Plugin logo and banner on marketplace

= 1.0.2 =
* Removed both test_secret_key and live_secret_key in paga checkout setting page
* Added the data charge url field, to specify url to redirect to after successful transaction

= 1.0.3 =
* Updated the Paga checkout plugin Documentation

= 1.0.4 =
* Updated the taglines a customers sees when they want to make payment

= 1.0.5 =
* Fixed the bug to display a tagline when selecting a payment method if the tagline is not specified in the plugin setting.

= 2.0.0 =
* Implmented transaction verification after payment.
* Implemented updating order status after the verifying that the transaction was successful.
* Added field to provide secret key in the plugin admin setting and removed the charge url field .

= 2.0.1 =
* Created a buffer to handle scenarios whereby the verify transaction service is down which aids merchant in automatically verifying the transaction when the service comes up.

= 2.0.2 =
* Created implementation where pending and processing transaction status are only verified if the payment method is paga-checkout.

= 2.0.3 =
* Created a patch to ensure that error from wrong credentials doesn't convert transactions to failed transaction and gives more information to users.

== Screenshots ==
1. PagaCheckout WooCommerce Payment Gateway Setting Page
2. PagaCheckout WooCommerce Payment Gateway on Payment Page
3. PagaCheckout WooCommerce Payment Gateway on Checkout page




