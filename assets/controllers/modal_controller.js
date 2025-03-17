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
        this.background = document.getElementById(this.closeValue)
        window.addEventListener('click', (e) => {
            if (e.target === this.background) {
                this.close()
            }
        });
    }

    close() {
        window.location.hash = '';
        this.background.classList.add('hidden')
    }
}
