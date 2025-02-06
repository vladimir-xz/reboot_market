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
        if (event.target.tagName === 'P') {
            console.log('attempt');
            this.component.action('save', { id: event.target.parentElement.id });
        } else {
            const nextElement = event.target.nextElementSibling
            if (nextElement && nextElement.tagName === 'DIV') {
                nextElement.classList.toggle("hidden")
            }
        }
    }

    open(nextElement) {
        if (nextElement && nextElement.tagName === 'DIV') {
            nextElement.classList.remove("hidden")
        }
    }

    close(nextElement) {
        if (nextElement && nextElement.tagName === 'DIV') {
            nextElement.classList.add("hidden")
        }
    }

    // renew(event) {
    //     this.activeCategories = event.detail.activeCategories
    //     this.activate()
    // }

    renew(event) {
        this.nodeTargets.forEach((element) => {
            if (Number(element.id) in event.detail.activeCategories) {
                element.classList.add("category__active")
                this.open(element.nextElementSibling)
            } else {
                element.classList.remove("category__active")
                this.close(element.nextElementSibling)
            }
        })
    }
}
