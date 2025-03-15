import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = [ "figure" ]

  add({ detail  }) {
    let cart = this.getCart();
    const existingProduct = cart.ids?.[Number(detail.id)];
    
    if (existingProduct) {
      // Update quantity if product exists
      if (detail.max < existingProduct.amount + detail.amount) {
        alert(`The selected quantity could not be added to the shopping cart because it exceeds the available stock. ${detail.amount} are currently in stock.`)
        return
      }

      existingProduct.amount += detail.amount || 1;
      cart.total += detail.price * detail.amount
    } else {
      // Add new product
      cart.ids = cart.ids || {};
      cart.ids[Number(detail.id)] = ({
        name: detail.name,
        price: detail.price,
        amount: detail.amount || 1
      })
      cart.total = Number(cart.total) || 0;
      cart.total += Number(detail.price) * Number(detail.amount)
    }
    
    this.saveCart(cart);
    this.figureTarget.innerHTML = cart.total
    const cartPopup = document.getElementById('cart-popup')
    cartPopup.classList.remove('hidden')
  }

  getCart() {
    const cartCookie = document.cookie
    .split('; ')
    .find(row => row.startsWith('cart='));
  
    if (cartCookie) {
        try {
        return JSON.parse(decodeURIComponent(cartCookie.split('=')[1]));
        } catch (e) {
        return {};
        }
    }
    return {};
  }
  
  // Function to save cart to cookies
  saveCart(cart) {
    const expires = new Date(Date.now() + 60 * 60 * 1000).toUTCString();
    console.log(cart)
    console.log(JSON.stringify(cart))
    document.cookie = `cart=${encodeURIComponent(JSON.stringify(cart))}; expires=${expires}; path=/`;
    console.log(document.cookie)
  }
}