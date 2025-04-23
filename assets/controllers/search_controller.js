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
            const lastSegment = window.location.pathname.substring(window.location.pathname.lastIndexOf('/') + 1)
            console.log(lastSegment)
            if (lastSegment != 'search') {
                Turbo.visit('/_new_search?q=' + event.detail.query)
            }
        });
    }



}
