(function($){
	$(function(){
		$('.ep-checkbox:checked').closest('.ep-user').addClass('selected');

		$('.ep-checkbox').click(function(){	
			if ( $(this).attr('checked') ) 
				$(this).closest('.ep-user').addClass('selected');
			else
				$(this).closest('.ep-user').removeClass('selected');
		});
	});
}(jQuery))
