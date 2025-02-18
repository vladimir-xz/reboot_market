import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/

export default class extends Controller {
    static targets = ['filter', 'body']

    static values = {
        brands: String,
      }

    async initialize() {
        // Fetch the LiveComponent instance associated with this DOM element
        // const stringBrands = JSON.parse(this.brandsValue)
        // const params = new Proxy(new URLSearchParams(window.location.search), {
        //     get: (searchParams, prop) => searchParams.get(prop),
        //   });
        // this.value = params.f;
        // console.log(this.value)
        // console.log()
    }

    new(event) {
        this.dispatch("new", { detail: { content: event.params.payload } })
    }

    toggle() {
        this.bodyTarget.classList.toggle('hidden')
    }

    removeAll() {
        this.filterTargets.forEach((element) => {
            element.checked = false;
        })
    }

    updateValidFilters(event) {
        console.log('Updating filters', event.detail.filters)
        const filters = event.detail.filters
        this.filterTargets.forEach((element) => {
            console.log(element)
            const elementId = element.id
            const parentModel = element.parentElement.parentElement.dataset.model
            element.nextElementSibling.classList.remove("filter_active", "filter_passive")
            if (elementId in filters[parentModel]) {
                element.nextElementSibling.classList.add('filter_active')

            } else {
                element.nextElementSibling.classList.add('filter_passive')
            }
        })
    }
    // ...
}
