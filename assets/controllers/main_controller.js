import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
// export default class extends Controller {

//     connect() {
//         console.log('MainController connected');
//         window.addEventListener('product:search', (event) => {
//             console.log('Global listener caught product:search event', event);
//         });
//     }
// }
