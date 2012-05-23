var $a = $this.find('a.test');

$a.click(function(){
    remoteCall('class',function(err,data){
        console.log(data);
    });
});

