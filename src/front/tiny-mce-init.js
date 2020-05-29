$(function(){
	$('textarea.textarea-with-tiny').each(function(){

		// пробуем прочитать локальные настройки .. .
		var key=$(this).data('confkey');
		if (typeof window['tinyWidget_'+key]!='undefined')
			$(this).tinymce(window['tinyWidget_'+key]);
		else{
			var url=$(this).data('confurl');
			$.get(url,(ret)=>{
				if (typeof ret.conf=='undefined')
					console.error('Настройки не заданы');
				else 
					$(this).tinymce(ret.conf);
			});	
		}
	});
});