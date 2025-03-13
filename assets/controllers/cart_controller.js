import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = [ "figure" ]

  add({ detail  }) {
    let cart = getCart();
    const existingProduct = cart.find(item => item.id === detail.id);
    
    if (existingProduct) {
      // Update quantity if product exists
      existingProduct.quantity += detail.amount || 1;
    } else {
      // Add new product
      cart.push({
        id: detail.id,
        name: detail.name,
        price: detail.price,
        amount: detail.amount || 1
      });
    }
    
    saveCart(cart);
  }

  getCart() {
    const cartCookie = document.cookie
    .split('; ')
    .find(row => row.startsWith('cart='));
  
    if (cartCookie) {
        try {
        return JSON.parse(decodeURIComponent(cartCookie.split('=')[1]));
        } catch (e) {
        return [];
        }
    }
    return [];
  }
  
  // Function to save cart to cookies
  saveCart(cart) {
    const expires = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toUTCString();
    document.cookie = `cart=${encodeURIComponent(JSON.stringify(cart))}; expires=${expires}; path=/`;
  }
}