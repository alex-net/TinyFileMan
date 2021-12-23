jQuery(function (){
	$('.rfm-wrapper-widget').on('click','.open-fm-target',(e)=>{
		e.preventDefault();
		// обёртка .. 
		let root=$(e.target).parents('.rfm-wrapper-widget').eq(0);
		let input=root.find('.input-file-receiver');
		input.val('');
		let modal=root.find('.rfm-wrapp-modal-container');

		modal.one('hidden.bs.modal',(me)=>{
			let ar=[];// массив выбоора ... 
			try{
				ar=JSON.parse(input.val());
			}catch(e){
				// заполняем если что то попало в input
				if (input.val())
					ar=[input.val()];
			}
			root.trigger('rfm-wrapp-file-loaded',[ar,e.target])
		});

		if (!modal.find('.modal-body iframe').length){
			let iframe=$('<iframe>');
			iframe.attr({
				width:'100%',
				height:500,
				src:input.data('fm-url'),
			});
			modal.find('.modal-body').append(iframe);
		}
		modal.modal('show');
	});
	/// input-file-receiver
});

