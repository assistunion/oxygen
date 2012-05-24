$this.flash = function(d) {
    if(d.type != 'debug') {
        var color = ({info:'#CEF',error:'#F44',warning:'#FF0'})[d.type] || '#FFF';
        $('<li>').hide().text(d.message).appendTo($this)
            .fadeIn(200)
            .delay(1000)
            .animate({backgroundColor:color})
            .animate({backgroundColor:'#FED'})
            .animate({backgroundColor:color})
            .animate({backgroundColor:'#FED'})
            .animate({backgroundColor:color})
            .animate({backgroundColor:'#FED'})
            .delay(2000)
            .slideUp(200);
    } else {
        o.log(d.message);
    }
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

$this.updateFlash();

