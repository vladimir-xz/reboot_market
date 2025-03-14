import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = [ "input" ]
  static values = {
    min: { type: Number, default: 1}
  }

  initialize() {
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
    this.dispatch("add", { detail: { 
      id: Number(event.target.dataset.prodId),
      name: event.target.dataset.name,
      price: Number(event.target.dataset.price),
      amount: Number(this.inputTarget.value),
      max: Number(this.inputTarget.getAttribute('max'))
    } })
  }
  
}