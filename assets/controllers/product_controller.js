import { Controller } from '@hotwired/stimulus';
import * as Turbo from '@hotwired/turbo';
import { getComponent } from '@symfony/ux-live-component';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
export default class extends Controller {

    async initialize() {
        this.component = await getComponent(this.element);
    }

    filter ({ detail: { content } }) {
        console.log('Filter on product side is working')
        this.component.action('setFilter', { newFilters: content });
        console.log(content)
    }

    revert({ detail }) {
        this.component.action('revertCategories', { newId: detail.id });
    }
    
    exclude({ detail }) {
        this.component.action('excludeCategories', { newId: detail.id});
    }

    include({ detail }) {
        this.component.action('includeCategories', { newId: detail.id});
    }

}
