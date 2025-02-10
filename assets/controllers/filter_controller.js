import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['filter']

    static values = {
        brands: String,
      }

    async initialize() {
        // Fetch the LiveComponent instance associated with this DOM element
        const stringBrands = JSON.parse(this.brandsValue)
        console.log(stringBrands)
    }

    new() {
        this.dispatch("new", { detail: { content: this.filterTarget.value } })
    }
    // ...
}
