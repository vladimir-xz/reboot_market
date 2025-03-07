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
        console.log('catalog tree has been initalized')
        this.component = await getComponent(this.element);
        console.log(this.component.valueStore.props)
        if (this.component.valueStore.props.treeMap) {
            const detail = this.component.valueStore.props
            this.renew({ detail })
            this.component.valueStore.props.treeMap = []
        }
    }

    // historyPush() {
    //     console.log(this.element.getAttribute('src'))
    //     const url = new URL('https://127.0.0.1:8000/search');
    //     // history.pushState(history.state, "", url);
    //     Turbo.navigator.history.push(url);
    //     console.log("my_push_state", url, history.state);
    // }

    check(event) {
        if (event.params.exclude) {
            if (window.location.pathname != '/search') {
                console.log('Not a /search path')
                Turbo.visit('/_new_search?cat=ex_' + event.params.id)
            } else {
                this.dispatch("exclude", { detail: {id: event.params.id }})
            }
        } else {
            if (window.location.pathname != '/search') {
                Turbo.visit('/_new_search?cat=in_' + event.params.id)
            } else {
                this.dispatch("include", { detail: {id: event.params.id }})
            }
        }
        // this.dispatch("update", { detail: event.params })
        // if (event.params.exclude) {
        //     this.component.action('excludeCategories', { newId: event.params.id});
        // } else {
        //     this.component.action('includeCategories', { newId: event.params.id});
        // }
    }

    onClick(event) {
        console.log(window.location.pathname)
        console.log(event.target.parentElement.parentElement.id)
            if (event.target.tagName === 'P') {
                // this.dispatch("load")
                if (window.location.pathname != '/search') {
                    Turbo.visit('/_new_search?cat=rev_' + event.target.parentElement.parentElement.id)
                } else {
                    this.dispatch("revert", { detail: {id: event.target.parentElement.parentElement.id }})
                }
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

    // loadProducts() {
    //     console.log('is it loading?')
    //     console.log(window.location.search)
    //     Turbo.visit("/_new_product_scroll" + window.location.search)
    // }

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

    // setMax() {
    //     console.log('attempt to set max')
    //     this.dispatch('setMax')
    // }

    updateCheck(element, ifRemoveBoth = true, boxType) {
        if (ifRemoveBoth) {
            element.querySelector(`.included_box > input`).checked = false
            element.querySelector(`.excluded_box > input`).checked = false
        } else {
            const opositeBoxType = boxType == 'excluded' ? 'included' : 'excluded'
            element.querySelector(`.${boxType}_box > input`).checked = true
            element.querySelector(`.${opositeBoxType}_box > input`).checked = false
        }
    }


    // sendNewResult() {
    //     console.log('reseting')
    //     console.log(Date.now())
    //     Turbo.visit("/_new_product_scroll" + window.location.search)
    //     this.dispatch("reset")
    // }

    renew(event) {
        // this.sendNewResult()
        // this.setMax()
        console.log(event)
        const treeMap = event.detail.treeMap
        this.nodeTargets.forEach((element) => {
            const elementId = Number(element.id)
            element.classList.remove("category_neutral", "category_included", "category_excluded", "category_active")
            if (elementId in treeMap) {
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
