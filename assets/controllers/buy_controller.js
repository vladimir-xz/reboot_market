import { Controller } from "@hotwired/stimulus"
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
  static targets = [ "input" ]
  static values = {
    min: { type: Number, default: 1}
  }

  async initialize() {
    this.component = await getComponent(this.element);
    this.inputTarget.addEventListener('focusout', () => {
      this.checkInput()
    })
    this.inputTarget.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        this.checkInput()
      }
    })
  }

  incr() {
    const input = this.inputTarget
    if (Number(input.getAttribute('max')) > Number(input.value)) {
      this.inputTarget.value++
    } else {
      this.inputTarget.value = Number(input.dataset.stockMax)
    }
  }

  decr() {
    const input = this.inputTarget
    if (this.minValue < Number(input.value)) {
      this.inputTarget.value--
    } else {
      this.inputTarget.value = this.minValue
    }
  }

  checkInput() {
    const input = this.inputTarget
    if (Number(input.getAttribute('max')) < Number(input.value)) {
      this.inputTarget.value = input.getAttribute('max')
    } else if (this.minValue > Number(input.value)) {
      this.inputTarget.value = this.minValue
    }
  }

  send(event) {
    this.component.action('save', { amount: Number(this.inputTarget.value) });
  }
  
}