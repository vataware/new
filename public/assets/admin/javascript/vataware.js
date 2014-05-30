$(document).ready(function() {
	$('body').on('click', 'a[href]:not(.btn-confirm)', function(e) {
		if($(this).data('method') !== undefined && $(this).data('method') !== 'GET') {
			e.preventDefault();
			postdata = '';
			if($(this).data('method') !== 'POST') postdata = '_method=' + $(this).data('method');
			$.post($(this).attr('href'), postdata, function(data) {
				window.location.replace(data);
			});
		}
	});

	$('body').on('click', '.btn-confirm', function(e) {
		e.preventDefault();
		$modal = $('#modal-confirm');
		$modal.find('.confirm-message').html($(this).data('message'));
		
		if(typeof $(this).data('title') == 'undefined') title = 'Action confirmation';
		else title = $(this).data('title');
		$modal.find('.confirm-title').text(title);

		if(typeof $(this).data('type') == 'undefined') type = 'primary';
		else type = $(this).data('type');
		$modal.find('.confirm-button').attr('class','confirm-button btn btn-' + type);
		
		if(typeof $(this).data('confirm') == 'undefined') confirm = 'Confirm';
		else confirm = $(this).data('confirm');
		$modal.find('.confirm-button').text(confirm);

		if(typeof $(this).attr('href') == 'undefined') link = '#';
		else link = $(this).attr('href');
		$modal.find('.confirm-button').attr('href', link);

		if(typeof $(this).data('method') !== 'undefined') $modal.find('.confirm-button').attr('data-method', $(this).data('method'));

		$modal.modal('show');
	});
});