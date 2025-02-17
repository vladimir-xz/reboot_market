import { Controller } from '@hotwired/stimulus';
import * as Turbo from '@hotwired/turbo';
import { getComponent } from '@symfony/ux-live-component';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
export default class extends Controller {
    static targets = ['result']


    async initialize() {
        // this.component = await getComponent(this.element);
        // this.page = 1
        // console.log(this.page)
        // window.addEventListener('scroll', () => {
        //     if (window.scrollY + window.innerHeight >= document.documentElement.scrollHeight) {
        //         this.maxNbPages = this.resultTarget.dataset.maxPageNumber
        //         console.log(this.maxNbPages)
        //         console.log(this.page)
        //         if (this.page < this.maxNbPages) {
        //             this.page = this.page + 1
        //             const url = "/_product_scroll" + window.location.search + 'p=' + String(this.page)
        //             Turbo.visit(url)
        //         }
        //         console.log("You've reached the bottom of the page!");
        //     }
        //     // Check if the user has scrolled to the bottom
        // });
    }

    filter ({ detail: { content } }) {
        this.component.action('setFilter', { newFilters: content });
        console.log(content)
    }

}
