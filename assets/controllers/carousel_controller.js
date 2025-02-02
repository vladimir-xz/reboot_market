import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = [ "slide", "button" ]
  static values = {
    refreshInterval: {type: Number, default: 2500}
  }

  initialize() {
    this.count = this.slideTargets.length
    this.showCurrentSlide()
    this.startRefreshing()
  }

  next() {
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