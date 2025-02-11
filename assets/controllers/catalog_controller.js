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
        // const lastNodes = this.component.valueStore.props.activeLastNodes;
        // this.component.emit('redraw', { newCatalogs: lastNodes });
    }

    onClick(event) {
        if (event.target.tagName === 'P') {
            console.log('attempt');
            this.component.action('updateCategories', { newId: event.target.parentElement.id });
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
        console.log(event.detail.activeCategories)
        this.nodeTargets.forEach((element) => {
            if (Number(element.id) in event.detail.activeCategories.active) {
                element.classList.remove("category_active")
                element.classList.add("category_chosen")
                this.open(element.nextElementSibling)
            } else if (Number(element.id) in event.detail.activeCategories.chosen) {
                element.classList.remove("category_chosen")
                element.classList.add("category_active")
                this.open(element.nextElementSibling)
            } else if (Number(element.id) in event.detail.activeCategories.neutral) {
                element.classList.remove("category_chosen")
                element.classList.remove("category_active")
                this.close(element.nextElementSibling)
            } else {
                element.classList.remove("category_chosen")
                element.classList.remove("category_active")
                this.close(element.nextElementSibling)
            }
        })
    }
}
