$ = window.jQuery
if !window.ISLY
  window.ISLY =
    'IslyPinboard': null
window.ISLY.IslyPinboard = class IslyPinboard
  constructor: (options) ->
    @pinboard = options.pinboard.pinboard
    @element = $('#' + options.id)
    @transitionTimer = options.transitionTimer
    @width = @element.width()
    @placeholder = @element.find('.isly-pinboard-placeholder')
    @spinning = true;
    @looping = true;
    @timer = 0

    @build()
  build: () ->
#    Randomize the pinboard.  It's just too predictable otherwise
    @pinboard.sort (a, b) ->
      (Math.random() > 0.5) ? 1 : -1
    @element.height(@width)
    @fetchImages()

#    Spin the placeholder and start the loop
    @spin @placeholder, 2000, =>
      @loopImages()

#    Set up events to stop and start the loop if necessary
    @element.on 'islyPinboardStop', =>
      @looping = false
    @element.on 'islyPinboardStart', =>
      @looping = true
      clearTimeout(@timer)
      @loopImages()

  spin: (image, millis, callback) ->
    image.css
      marginTop: -.5 * image.height()
      marginLeft: -.5 * image.width()
      visibility: 'visible'
    image.transition 'rotateY': '360deg', millis, ->
      if typeof callback == 'function'
        callback()

  fetchImages: (callback) ->
    pinboard = @pinboard
    i = pinboard.length
    while i--
      pinboard[i].image = $(new Image())
      pinboard[i].image.attr 'src', pinboard[i].src

#   Delay callback until the first image is loaded.  That should ensure that no half-loaded images get displayed.
    pinboard[0].image.load ->
      if typeof callback == 'function'
        callback()

  loopImages: =>
    if !@looping
      return
    previousSlide = @element.find('.previous-slide')
    currentSlide = @element.find('.current-slide')

#    Shift the first pin off of the front and push it back onto the end.  Maintains order without tracking current array key
    nextPinboard = @pinboard.shift()
    @pinboard.push nextPinboard

#    Create next slide with link and image
    nextSlide = $('<div>').addClass('slide next-slide')
    nextLink = $('<a>').attr 'href', 'http://pinterest.com/pin/' + nextPinboard.pinID + '/'
    nextPinboard.image.addClass('isly-pinboard-image').css
      maxWidth: @width
      maxHeight: @width

    nextLink.append nextPinboard.image
    nextSlide.append nextLink
    @element.prepend nextSlide

#    Center image using the absolute position with negative margin trick. See accompanying CSS.  Swap classes.
    nextPinboard.image.css
      marginLeft: -.5 *nextPinboard.image.width()
      marginTop: -.5 *nextPinboard.image.height()
    if previousSlide
      previousSlide.remove()
    currentSlide.addClass('previous-slide').removeClass('current-slide')
    nextSlide.removeClass('next-slide').addClass('current-slide')

#    Wait just long enough and start the loop again
    @timer = setTimeout @loopImages, @transitionTimer

