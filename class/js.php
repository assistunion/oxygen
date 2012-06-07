(function(o,undefined){
    var $ = o.$, _ = o._;
    $(o.components['<?=$css?>'] = function() {
        $('.<?=$css?>').each(function(){
            var $this = $(this);
            <? include $source ?>
        });
    });
})(oxygen);