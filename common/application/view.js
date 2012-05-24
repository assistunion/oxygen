var $document = $(document)
  , $content = $('div.oxy-content')
  , $menu = $('div.oxy-menu')
  , $header = $('div.oxy-header')
  , $footer = $('div.oxy-footer')
 ;

$this.flash = function(d) {
    console.log({flash:d});
}

$this.updateFlash = function () {
    $this.remote('getFlash', function(err,data){
        var clear = false;
        _.each(data,function(d){
            clear = true;
            $this.flash(d);
        });
        if(clear) $this.remote('clearFlash',function(){}, false);
    }, false);
}

o.on('remote-call-complete',function(){
    $this.updateFlash();
});

function updateLayout() {
    var clientHeight = $document.height()-$header.height()-$footer.height();
    $content.height(clientHeight);
    $menu.height(clientHeight);
}

var lazyLayout = _.debounce(updateLayout, 300);
$(window).resize(updateLayout);
$(window).resize(updateLayout);

updateLayout();

$this.updateFlash();




