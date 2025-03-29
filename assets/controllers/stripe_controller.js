import { Controller } from '@hotwired/stimulus';
import { loadStripe } from '@stripe/stripe-js';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        purchase: Object,
        stripeKey: { type: String, default: 'pk_test_51R7yBfB7BZrVxkqm7TMo0lTQoAojNiol6CvLKuJCLeGqKhrcaD44DafMNtBwVbO9cAWVx7E2Hz67v2gRIAqqOsHx00A9O1Bh8N'}
      }

    async initialize() {
        const stripe = await loadStripe(this.stripeKeyValue);
        console.log(this.purchaseValue)
        const fetchClientSecret = async () => {
          const response = await fetch("/create-checkout-session", {
            method: "POST",
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
              },
            body: JSON.stringify(this.purchaseValue),
          });
          const { clientSecret } = await response.json();
          return clientSecret;
        };
      
        // Initialize Checkout
        const checkout = await stripe.initEmbeddedCheckout({
          fetchClientSecret,
        });
      
        // Mount Checkout
        checkout.mount('#checkout');
    }
}
