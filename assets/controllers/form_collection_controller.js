import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['collectionContainer']
    static values = {
        index: Number,
        prototype: String,
    }

    addCollectionElement(event)
    {
        const item = document.createElement('li');
        item.innerHTML = this.prototypeValue.replace(/__name__/g, this.indexValue);
        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;
    }

    remove(event)
    {
        event.preventDefault();
        event.target.parentElement.parentElement.remove()
        // remove the li for the tag form
        // item.remove();
    }
}
