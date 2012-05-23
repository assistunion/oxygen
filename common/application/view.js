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
    $this.remote('getFlash','',function(err,data){
        var clear = false;
        _.each(data,function(d){
            clear = true;
            $this.flash(d);
        });
        if(clear) $this.remote('clearFlash','',function(){},true);
    },true);
}

if(typeof($.fn.remote)==='undefined'){
    $.fn.remote = function(method,data,cb,flash) {
        if(typeof(data)==='function') {
            cb = data;
            data = {};
        }
        var url = JSON.parse($(this).data('remote'));
        return $.ajax({
            url:url,
            dataType: 'jsonp',
            headers: { 'X-Oxygen-RPC' : method },
            data: JSON.stringify(data),
            type: 'POST',
            success: function(resp,status,xhr){
                cb(resp.error,resp.data,status,xhr);
                if(!flash) {
                    $this.updateFlash();
                }
            }
        });
    }
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

$this.updateFlash();




