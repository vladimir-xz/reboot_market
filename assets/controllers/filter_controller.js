import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['filter', 'body']

    static values = {
        brands: String,
      }

    async initialize() {
        // Fetch the LiveComponent instance associated with this DOM element
        const stringBrands = JSON.parse(this.brandsValue)
        const params = new Proxy(new URLSearchParams(window.location.search), {
            get: (searchParams, prop) => searchParams.get(prop),
          });
        this.value = params.f;
        console.log(this.value)
        console.log()
    }

    new(event) {
        this.dispatch("new", { detail: { content: event.params.payload } })
    }

    toggle() {
        this.bodyTarget.classList.toggle('hidden')
    }

    removeAll() {
        console.log('removing all')

        this.filterTargets.forEach((element) => {
            element.checked = false;
        })
    }
    // ...
}
