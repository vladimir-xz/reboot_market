import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = [ "slide", "button" ]
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
    this.restartRefreshing()
    this.indexValue = event.params.index
    this.showCurrentSlide()
  }

  showCurrentSlide() {
    console.log(this.indexValue)
    this.slideTargets.forEach((element, index) => {
      element.hidden = index !== this.indexValue
    })
    this.buttonTargets.forEach((element, index) => {
      index === this.indexValue ? element.classList.add("carousel__button") : element.classList.remove("carousel__button")
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

  prolong() {
    this.refreshIntervalValue *= 2
    this.restartRefreshing()
  }

  shorten() {
    this.refreshIntervalValue /= 2
    this.restartRefreshing()
  }
}