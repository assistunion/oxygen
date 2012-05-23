var $a = $this.find('a.test');

$a.click(function(){
    $this.remote('RpcDemo',{x:123},function(err,data){
        if(err) {
            console.log('Got Error:' + err);
        } else {
            console.log(data);
        }
    });
});

