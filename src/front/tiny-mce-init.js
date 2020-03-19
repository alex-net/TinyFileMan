$(function(){
	$('textarea.textarea-with-tiny').each(function(){
		$.get($(this).data('confurl'),(ret)=>{
			if (typeof ret.conf=='undefined')
				console.error('Настройки не заданы');
			else {
				$(this).tinymce(ret.conf);
			}
			
		});
		//console.log($(this).attr('id'));
		//var editorConf=$(this).data('editor-config');
		
	});
});