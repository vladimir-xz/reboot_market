import { Controller } from '@hotwired/stimulus';
import * as Turbo from '@hotwired/turbo';
import { getComponent } from '@symfony/ux-live-component';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
export default class extends Controller {
    static targets = ['scroll']


    async initialize() {
        this.component = await getComponent(this.element);
        this.currentPage = 1
        console.log(this.component)
        this.maxPages = this.component.valueStore.props.maxNbPages
        console.log('this is max pages: ', this.maxPages)
    }

    connect() {
        window.addEventListener('scroll', () => {
            if (window.scrollY + window.innerHeight >= document.documentElement.scrollHeight) {
                if (this.currentPage < this.maxPages) {
                    this.currentPage = this.currentPage + 1
                    const searchParams = new URLSearchParams(window.location.search)
                    searchParams.set("p", this.currentPage)
                    const url = "/_product_scroll?" + searchParams.toString()
                    Turbo.visit(url)
                }
            }   
            // Check if the user has scrolled to the bottom
        });
    }

    setNewMaxAndClearScroll(event) {
        this.maxPages = event.detail.max
        console.log('Setting new max and clear scroll')
        this.currentPage = 1
        this.scrollTarget.innerHTML = ''
    }

    filter ({ detail: { content } }) {
        this.component.action('setFilter', { newFilters: content });
        console.log(content)
    }

}
