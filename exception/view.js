$('.oxygen-exception').each(function(){
	var $exception = $(this);
	$exception.find('table.trace tr').each(function(){
		var $tr = $(this);
		$tr.find('li.complex-argument').each(function(){
			var $argument = $(this);
			  , $type     = $argument.find('.type')
			  , $value    = $argument.find('.value')
			  , hidden    = true
			  ;
			$type.click(function(){
				if(hidden){
					$value.show();
				} else {
					$value.hide();
				}
				hidden = !hidden;
			})
		})
	});
});