import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import * as Turbo from '@hotwired/turbo';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['node']

    async initialize() {
        this.component = await getComponent(this.element);
        document.addEventListener("turbo:load", function() {
            console.log("This page was loaded via Turbo!");
        });
        // const lastNodes = this.component.valueStore.props.activeLastNodes;
        // this.component.emit('redraw', { newCatalogs: lastNodes });
    }

    historyPush() {
        console.log(this.element.getAttribute('src'))
        const url = new URL('http://127.0.0.1:8000/search');
        // history.pushState(history.state, "", url);
        Turbo.navigator.history.push(url);
        console.log("my_push_state", url, history.state);
    }

    check(event) {
        if (window.location.pathname != '/search') {
            this.historyPush()
            Turbo.visit("/_search")
        }

        if (event.params.exclude) {
            this.component.action('excludeCategories', { newId: event.params.id});
        } else {
            this.component.action('includeCategories', { newId: event.params.id});
        }
    }

    onClick(event) {
        console.log('this is detail')
        console.log(window.location.pathname)
        if (event.target.tagName === 'P') {
            console.log('attempt');
            if (window.location.pathname != '/search') {
                this.historyPush()
                Turbo.visit("/_search")
            }

            this.component.action('revertCategories', { newId: event.target.parentElement.id });
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

    // updateCheck(element, ifRemoveBoth = true, boxType = '') {
    //     if (ifRemoveBoth) {
    //         element.querySelector(`.chosen_box > input`).checked = false
    //         element.querySelector(`.excluded_box > input`).checked = false
    //     } else {
    //         element.querySelector(`.${boxType}_box > input`).checked = true
    //     }
    // }

    // renew(event) {
    //     this.activeCategories = event.detail.activeCategories
    //     this.activate()
    // }

    updateCheck(element, ifRemoveBoth = true, boxType) {
        if (ifRemoveBoth) {
            element.querySelector(`.chosen_box > input`).checked = false
            element.querySelector(`.excluded_box > input`).checked = false
        } else {
            const opositeBoxType = boxType == 'excluded' ? 'chosen' : 'excluded'
            element.querySelector(`.${boxType}_box > input`).checked = true
            element.querySelector(`.${opositeBoxType}_box > input`).checked = false
        }
    }

    renew(event) {
        const treeMap = event.detail.treeMap
        this.nodeTargets.forEach((element) => {
            const elementId = Number(element.id)
            element.classList.remove("category_neutral", "category_chosen", "category_excluded", "category_active")
            if (elementId in treeMap) {
                console.log(elementId)
                const newStatus = "category_" + treeMap[elementId].status
                element.classList.add(newStatus)
                this.open(element.nextElementSibling)

                if (treeMap[elementId].isLastNode && treeMap[elementId].status != 'neutral') {
                    this.updateCheck(element, false, treeMap[elementId].status)
                } else {
                    this.updateCheck(element)
                }

            } else {
                this.updateCheck(element)
                this.close(element.nextElementSibling)
            }
        })
    }
}
