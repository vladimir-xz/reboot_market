import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/

export default class extends Controller {
    static targets = ['container', 'card']

    static values = {
        brands: String,
      }

    async initialize() {
        this.itemWidth = this.cardTarget.offsetWidth
    }

    next() {
        this.containerTarget.scrollBy({
            left: this.itemWidth,
            behavior: 'smooth'
          });
    }

    previous() {
        this.containerTarget.scrollBy({
            left: -this.itemWidth,
            behavior: 'smooth'
          });
    }

    // ...
}
