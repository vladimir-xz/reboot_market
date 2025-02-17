import { Controller } from '@hotwired/stimulus';
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
    async initialize() {
        // alert('Hello, world!');
        // Fetch the LiveComponent instance associated with this DOM element
        // this.target = await getComponent(document.getElementById('live-1884135188-0'));
    }

    greet() {
        alert('Hello, world!');
    }

    toggleMode() {
        this.target.render();
        this.component.render();
    }
}
