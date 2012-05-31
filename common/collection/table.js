var $more = $this.find('button.more');
var $body = $this.find('tbody');
var $loader = $this.find('img.loader');

var offset = $this.data('offset');
var limit = $this.data('limit');
var order = $this.data('order');
var filter = $this.data('filter');

var noMore = false;

var waitMore = false;
function getMore(cb) {
    cb = cb || function() {}
    if(waitMore === true || noMore === true) return;
    waitMore = true;
    $loader.show();
    $this.remote('getMore',limit,function(err,data){
        var count = 0;
        if(err) {
            o.flash(err,'error');
        } else {
            limit += data.count;
            count = data.count;
            $body.embed(data.embed);
        }
        waitMore = false;
        $loader.hide();
        if(count === 0) noMore = true;
        cb(count);
    })
}

function expandData() {
    getMore(function(count){
        if(count>0 && $body.height() < $(window) .height()) {
            expandData();
        }
    })
}

expandData();

$(window).scroll(function(){
    if ($(window).scrollTop()+$(window).height() >= ($(document).height() - ($(window).height()))){
        getMore();
    }
});

$this.on('click','tbody > tr > td[data-source]',true,function(e){
    var $cell = $(this);
    if(!$cell.hasClass('edit-cell')) beginEdit($cell);
});

$this.on('focusout','tbody > tr > td[data-source]  div.edit-placeholder',true,function(e){
    var $cell = this.editCell;
    var original = $cell.data('original');
    var cell = $cell.get(0);
    var $e = $cell.find('span.empty');
    $e.remove();
    var current = cell.editDiv.text();
    if(current === original) {
        cancelEdit($cell);
    } else {
        $e.appendTo(this);
    }
});



$this.on('keydown','tbody > tr > td[data-source]  div.edit-placeholder',true,function(e){
    if(e.keyCode === 27) {
        cancelEdit(this.editCell);
        this.editCell = null;
        return true;
    } else if(e.keyCode === 13) {
        commitEdit(this.editCell);
        this.editCell = null;
    } else if(e.keyCode === 9) {
        commitEdit(this.editCell);
        var $n = this.editCell.next();
        this.editCell = null;
        if($n.length === 1) {
            setTimeout(function(){
                beginEdit($n);

            },0);
        }
    }
});

var lastCell = null;

function beginEdit($cell){
    var original = $cell.text();
    var cell = $cell.get(0);
//    if(lastCell === null) {
//        lastCell = cell;
//        cell.prevEdit = cell;
//        cell.nextEdit = cell;
//    } else {
//        cell.nextEdit = lastCell.nextEdit;
//        cell.nextEdit.prevEdit = cell;
//        lastCell.nextEdit = cell;
//        cell.prevEdit = lastCell;
//    }
    $cell.data('original',original);
    $cell.addClass('edit-cell');
    cell.editRemote = $cell.parent('tr[data-remote]').data('remote');
    cell.editSource = $cell.data('source');
    var $wrapper=$('<div>').addClass('edit-wrapper');
    var $div=$('<div>').addClass('edit-placeholder').attr('contentEditable',true);
    if(original === '') {
        var $e = $('<span>').attr('contentEditable',false).text('null').addClass('empty');
        $div.append($e);
    } else {
        $div.text(original);
    }

    $wrapper.append($div);
    var div = $div.get(0);
    div.editCell = $cell;
    var $accept=$('<div>').addClass('icon').addClass('accept').attr('title','Save changes [Enter]').click(function(){
        commitEdit($cell);
    });
    var $cancel=$('<div>').addClass('icon').addClass('cancel').attr('title','Undo changes [Esc]').click(function(){
        cancelEdit($cell);
    });
    cell.editDiv = $div;
    $wrapper.append($cancel).append($accept);
    $cell.html($wrapper);
    $div.focus();
    document.execCommand('selectAll',false,null);
}

function cancelEdit($cell) {
    var original = $cell.data('original');
    $cell.get(0).editDiv.contentEditable = false;
    try {
        $cell.text(original).removeClass('edit-cell');
    } catch (__e) {
        //Chorme/jQuery bug: Ticket #11828
    }
}

function commitEdit($cell) {
    var original = $cell.data('original');
    var cell = $cell.get(0);
    $cell.find('span.empty').remove();
    var current = cell.editDiv.text();
    try {
        $cell.text(current);
    } catch(e) {
        // Chrome/jQuery error;
    }
    $cell.removeClass('edit-cell');
    cell.editDiv = null;
    if (original != current) {
        $this.remote('updateCell',{
            original: original,
            current: current,
            remote: cell.editRemote,
            source: cell.editSource
        }, function(err,data){
            if(err) {
                o.flash(err,'error');
                $cell.text(original);
            } else {
                o.flash(data);
            }
        });
    }
}

