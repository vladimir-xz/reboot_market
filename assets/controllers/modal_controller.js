import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        close: String
      }

    async initialize() {
        window.addEventListener('click', (e) => {
            if (e.target !== this.element) {
                window.location.hash = '';
            }
        });
    }

    close() {
        document.getElementById(this.closeValue).classList.add('hidden')
    }
}
