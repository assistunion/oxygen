$this.dialog({
    title: 'Confirm',
    modal: true,
    close: function() {
        o.result(false,'CANCEL');
    },
    buttons: {
        yes : function() {
            o.result(false, 'YES');
            $this.dialog('close');
        },
        no : function(){
            o.result(false, 'NO');
            $this.dialog('close');
        }
    }
})