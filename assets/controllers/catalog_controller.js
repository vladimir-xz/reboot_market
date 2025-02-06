import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['node']

    async initialize() {
        this.component = await getComponent(this.element);
    }

    onClick(event) {
        const nextElement = event.target.nextElementSibling
        if (nextElement && nextElement.tagName === 'DIV') {
            nextElement.classList.toggle("hidden")
        }
    }

    renew(event) {
        console.log(event.detail.product);
    }
}
