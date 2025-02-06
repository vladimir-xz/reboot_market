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
        this.activeCategories = event.detail.activeCategories
        this.activate()
    }

    activate() {
        console.log(this.activeCategories.includes('17'))
        console.log(this.activeCategories)
        this.nodeTargets.forEach((element) => {
            console.log(element.id)
            this.activeCategories.includes(Number(element.id)) ? element.classList.add("category__active") : element.classList.remove("category__active")
        })
    }
}
