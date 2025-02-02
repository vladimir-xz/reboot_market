import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = [ "slide" ]
  static values = {
    refreshInterval: Number,
  }

  initialize() {
    this.index = 0
    this.count = this.slideTargets.length
    this.showCurrentSlide()
    this.startRefreshing()
  }

  next() {
    this.index++
    if (this.index >= this.count) {
      this.index = 0
    }
    this.showCurrentSlide()
  }

  nextForce() {
    this.restartRefreshing()
    this.next()
  }

  showCurrentSlide() {
    this.slideTargets.forEach((element, index) => {
      element.hidden = index !== this.index
    })
  }

  startRefreshing() {
    this.refreshTimer = setInterval(() => {
      this.next()
    }, this.refreshIntervalValue)
  }

  restartRefreshing() {
    clearInterval(this.refreshTimer)
    this.startRefreshing()
  }
}