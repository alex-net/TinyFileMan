$(function(){
	$('textarea.textarea-with-tiny').each(function(){
		console.log($(this).attr('id'));
		var editorConf=$(this).data('editor-config');
		$(this).tinymce(editorConf);
	});
});