jQuery(function(){

	$('.rfm-input-widget').on('click','button',(e)=>{
		// Исключаем  кнопку close ... 
		if ($(e.target).hasClass('close'))
			return ;	
		let root=$(e.target).parents('.rfm-input-widget');
		let modal=root.find('div.modal');
		if (!modal.find('.modal-body iframe').length){
			let iframe=$('<iframe>');
			iframe.attr({
				width:'100%',
				height:500,
				src:root.data('fm-url'),
			});
			modal.find('.modal-body').append(iframe);
		}
		modal.modal('show');
	});
});