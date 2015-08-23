jQuery_proxy(function($){
	$('body *').on('mouseenter', function() {
		var pos = $(this).offset();
		var dim = {width: $(this).width(), height: $(this).height()};
		pos.top -= 5;
		pos.left -= 5;
		dim.width += 10;
		dim.height += 10;
		if (pos.top < 0) pos.top = 0;
		if (pos.left < 0) pos.left = 0;
		$('.selection').remove();
		$('body').append(
			'<div class="selection top" style="position: absolute; border-top: 1px dotted red;'
				+ ' top: ' + pos.top + 'px;'
				+ ' left: ' + pos.left + 'px;'
				+ ' width: ' + dim.width + 'px;'
				+ ' height: 1px;'
			+ '"></div>'
			+ '<div class="selection right" style="position: absolute; border-left: 1px dotted red;'
				+ ' top: ' + pos.top + 'px;'
				+ ' left: ' + (pos.left + dim.width) + 'px;'
				+ ' width: 1px;'
				+ ' height: ' + dim.height + 'px;'
			+ '"></div>'
			+ '<div class="selection bottom" style="position: absolute; border-top: 1px dotted red;'
				+ ' top: ' + (pos.top + dim.height) + 'px;'
				+ ' left: ' + pos.left + 'px;'
				+ ' width: ' + dim.width + 'px;'
				+ ' height: 1px;'
			+ '"></div>'
			+ '<div class="selection left" style="position: absolute; border-left: 1px dotted red;'
				+ ' top: ' + pos.top + 'px;'
				+ ' left: ' + pos.left + 'px;'
				+ ' width: 1px;'
				+ ' height: ' + dim.height + 'px;'
			+ '"></div>');
	});
});