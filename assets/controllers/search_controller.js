import { Controller } from '@hotwired/stimulus';
import * as Turbo from '@hotwired/turbo';
import { getComponent } from '@symfony/ux-live-component';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
export default class extends Controller {
    async initialize() {
        window.addEventListener('search:checkPath', (event) => {
            if (window.location.pathname != '/search') {
                Turbo.visit('/_new_search?q=' + event.detail.query)
            }
        });
    }



}
