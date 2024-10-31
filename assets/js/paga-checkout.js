function checkoutForm() {
    const pagaForm = document.createElement('form');
    const embedCheckout = document.getElementById('embed-checkout');
    pagaForm.setAttribute('action',wc_paga_checkout_params['charge_url']);
    pagaForm.setAttribute('method','POST');
    const scriptTag  = document.createElement('script');
    scriptTag.type = 'text/javascript';
    const attributeKeys=['data-public_key','data-display_name','data-charge_url','data-redirect_url_method','data-display_image','data-display_tagline','data-email','data-amount','data-payment_reference','data-currency','data-phone_number','src']
    const attributeValues = Object.values(wc_paga_checkout_params);
    for (let index = 0; index < attributeValues.length; index++) {
        scriptTag.setAttribute(attributeKeys[index],attributeValues[index]);
    }
    pagaForm.appendChild(scriptTag);
    embedCheckout.appendChild(pagaForm);

}
checkoutForm();