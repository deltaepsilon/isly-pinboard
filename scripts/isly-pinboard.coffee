$ = window.jQuery
if !window.ISLY
  window.ISLY =
    'IslyPinboard': null
window.ISLY.IslyPinterest = class IslyPinterest
  constructor: (options) ->
    @permalinkClass = options.permalinkClass || '.isly-pinterest-permalink'
    @minHeight = options.minHeight || 100
    @verticalOffset = options.verticalOffset || 0
    @horizontalOffset = options.horizontalOffset || 0
    @contentContainers = []
    @pin = $(document.createElement('a')).attr
      'id': 'isly-pinterest-pin'
      'title': 'Pin It!'
      'target': '_blank'
    this.build()
  build: () ->
    $(document.body).append @pin
    @window = $(window)
    @permalinks = $(@permalinkClass)
    @findContainer permalink for permalink in this.permalinks
    @setListeners()
    @pin.hover ->
      $(this).show()
    , ->
      $(this).hide()