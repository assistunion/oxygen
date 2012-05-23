var $document = $(document)
  , $content = $('div.oxy-content')
  , $menu = $('div.oxy-menu')
  , $header = $('div.oxy-header')
  , $footer = $('div.oxy-footer')
 ;

$.oxygenRpc = function(component,method,data,cb) {
    
}

function updateLayout() {
    var clientHeight = $document.height()-$header.height()-$footer.height();
    $content.height(clientHeight);
    $menu.height(clientHeight);
}

var lazyLayout = _.debounce(updateLayout, 300);
$(window).resize(updateLayout);
$(window).resize(updateLayout);

updateLayout();


