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

    check(event) {
        this.component.action('updateCategories', { newId: event.params.id, ifExclude: event.params.exclude });
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

    updateCheck(element, ifRemoveBoth = true, boxType = '') {
        if (ifRemoveBoth) {
            element.querySelector(`.chosen_box > input`).checked = false
            element.querySelector(`.excluded_box > input`).checked = false
        } else {
            element.querySelector(`.${boxType}_box > input`).checked = true
        }
    }

    // renew(event) {
    //     this.activeCategories = event.detail.activeCategories
    //     this.activate()
    // }

    renew(event) {
        const treeMap = event.detail.treeMap
        console.log(treeMap)
        console.log(treeMap['1'].status)
        this.nodeTargets.forEach((element) => {
            const elementId = Number(element.id)
            element.classList.remove("category_neutral", "category_chosen", "category_excluded", "category_active")
            if (elementId in treeMap) {
                console.log(elementId)
                const newStatus = "category_" + treeMap[elementId].status
                element.classList.add(newStatus)
                this.open(element.nextElementSibling)

                if (treeMap[elementId].isLastNode && treeMap[elementId].status != 'neutral') {
                    element.querySelector(`.${treeMap[elementId].status}_box > input`).checked = true
                } else {
                    element.querySelector(`.chosen_box > input`).checked = false
                    element.querySelector(`.excluded_box > input`).checked = false
                }

            } else {
                this.close(element.nextElementSibling)
            }
        })
    }
}
