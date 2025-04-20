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
    }

    check(event) {
        const lastSegment = window.location.pathname.substring(window.location.pathname.lastIndexOf('/') + 1)
        if (event.params.exclude) {
            if (lastSegment != 'search') {
                console.log('Not a /search path')
                Turbo.visit('/_new_search?cat=ex_' + event.params.id)
            } else {
                this.dispatch("exclude", { detail: {id: event.params.id }})
            }
        } else {
            if (lastSegment != 'search') {
                Turbo.visit('/_new_search?cat=in_' + event.params.id)
            } else {
                this.dispatch("include", { detail: {id: event.params.id }})
            }
        }
    }

    onClick(event) {
        const lastSegment = window.location.pathname.substring(window.location.pathname.lastIndexOf('/') + 1)
            if (event.target.tagName === 'P') {
                if (lastSegment != 'search') {
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

    open(element) {
        const nextElement = element.nextElementSibling
        if (nextElement && nextElement.tagName === 'DIV') {
            nextElement.classList.remove("hidden")
        }
    }

    close(element) {
        const nextElement = element.nextElementSibling
        if (nextElement && nextElement.tagName === 'DIV') {
            nextElement.classList.add("hidden")
        }
    }

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

    renew(event) {
        const treeMap = event.detail.treeMap
        const isMapEmpty = treeMap.length === 0;
        this.nodeTargets.forEach((element) => {
            const elementId = Number(element.id)
            element.className = ''
            if (isMapEmpty) {
                element.classList.add("category_neutral")
                this.open(element.parentElement)
                this.updateCheck(element)
            } else if (elementId in treeMap) {
                const newStatus = "category_" + treeMap[elementId].status
                element.classList.add(newStatus)
                this.open(element.parentElement)

                if (treeMap[elementId].isLastNode) {
                    this.updateCheck(element, false, treeMap[elementId].status)
                } else {
                    this.updateCheck(element)
                }

            } else {
                this.updateCheck(element)
                this.close(element.parentElement)
            }
        })
    }
}
