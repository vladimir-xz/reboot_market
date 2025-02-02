import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = [ "slide" ]
  static values = {
    index: { type: Number, default: 0},
    refreshInterval: Number,
  }

  initialize() {
    console.log(this.indexTarget)
    this.count = this.slideTargets.length
    this.showCurrentSlide()
    this.startRefreshing()
  }

  next() {
    // console.log(this.indexTarget.value)
    this.indexValue++
    if (this.indexValue >= this.count) {
      this.indexValue = 0
    }
    this.showCurrentSlide()
  }

  switch(event) {
    const selectedSlide = event.params.index;
    this.restartRefreshing()
    this.indexValue = selectedSlide
    this.showCurrentSlide()
  }

  showCurrentSlide() {
    console.log(this.indexValue)
    this.slideTargets.forEach((element, index) => {
      element.hidden = index !== this.indexValue
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