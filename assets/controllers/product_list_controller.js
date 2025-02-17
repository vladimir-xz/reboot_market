import { Controller } from '@hotwired/stimulus';
import * as Turbo from '@hotwired/turbo';
import { getComponent } from '@symfony/ux-live-component';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    static targets = ['result']

    static values = {
        page: Number,
      }
    
    async initialize() {
    }

    connect() {
        this.maxPages = this.resultTarget.dataset.maxPageNumber


        window.addEventListener('scroll', () => {
            if (window.scrollY + window.innerHeight >= document.documentElement.scrollHeight) {
                var currentPage = Number(this.resultTarget.dataset.currentPage)

                if (currentPage < this.maxPages) {
                    currentPage = currentPage + 1
                    this.resultTarget.dataset.currentPage = currentPage
                    const searchParams = new URLSearchParams(window.location.search)
                    searchParams.set("p", currentPage)
                    const url = "/_product_scroll?" + searchParams.toString()
                    Turbo.visit(url)
                }
            }
            // Check if the user has scrolled to the bottom
        });
    }

    resetPage() {
        this.resultTarget.dataset.currentPage = 1
    }

    loadingState () {
        console.log('loading!!!!')
        const node = document.createElement("span")
        node.classList.add('loader')
        this.resultTarget.firstElementChild.classList.add('blur')
        this.resultTarget.appendChild(node)
    }

    disconnect() {
        this.maxPages = 0
    }
}
