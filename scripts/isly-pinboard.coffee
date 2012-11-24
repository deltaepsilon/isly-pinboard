$ = window.jQuery
if !window.ISLY
  window.ISLY =
    'IslyPinboard': null
window.ISLY.IslyPinboard = class IslyPinboard
  constructor: (options) ->
    @pinboard = options.pinboard
    @element = $('#' + options.id)
    @width = @element.width()
    @cube = @element.find('.cube')
    @cubeWidth = @cube.width()
    @faces = @cube.find('.face')
#    @sideOne = @cube.find('.one').first()
#    @sideTwo = @cube.find('.two').first()
#    @sideThree = @cube.find('.three').first()
#    @sideFour = @cube.find('.four').first()
#    @sideFive = @cube.find('.five').first()
#    @sideSix = @cube.find('.six').first()

    @placeholder = @element.find('.isly-pinboard-placeholder')
    @spinning = true;
    if $.browser.webkit
      @vendorPrefix = '-webkit-'
    else if $.browser.mozilla
      @vendorPrefix = '-moz-'
    else if $.browser.msie
      @vendorPrefix = '-m-'
    else if $.browser.opera
      @vendorPrefix = '-o-'
    else
      @vendorPrefix = ''

    this.build()
  rotations: [
    'rotateX(90deg)',
    '',
    'rotateY(90deg)',
    'rotateY(180deg)',
    'rotateY(-90deg)',
    'rotateX(-90deg)'
  ]
  build: () ->
    that = this
    transform = @vendorPrefix + 'transform'

    @element.height(@width)
    this.setTransforms()
    this.fetchImages()
    this.spin @placeholder, 5000, ->
      that.setImages()
      that.rotateCube()

  spin: (image, millis, callback) ->
    image.css
      marginTop: -.5 * image.height()
      marginLeft: -.5 * image.width()
      visibility: 'visible'
    image.transition 'rotateY': '360deg', millis, ->
      if typeof callback == 'function'
        callback()

  setTransforms: ->
    i = @faces.length
    while i--
      this.setTransform @faces[i], @rotations[i]

  setTransform: (element, rotation) ->
    prefixed = @vendorPrefix + 'transform'
    $(element).css prefixed, rotation + ' translateZ(' + @cubeWidth/2 + 'px)'

  fetchImages: ->
    #    TODO Loop through @pinboard to create images and begin the cacheing process
    console.log 'fetching images'
  setImages: ->
#    TODO Loop through cached images, assign to cube sides and fade them in.
    console.log 'setting images'
  rotateCube: ->
#    TODO randomly rotate cube
    console.log 'rotating cube'
