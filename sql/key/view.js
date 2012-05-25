$this.find('button.zzz').click(function(){
    $this.remote('Hello','Somebody',function(err,response){
        //o.flash(response,'debug');
    });
})