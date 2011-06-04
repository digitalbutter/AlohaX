function saveEditableOnBlur(event, eventProperties) {
	//post to connector for save.
	$.post(assetsPath + 'connector.php', {
		id: id
		,field: eventProperties.editable.getId()
		,content: eventProperties.editable.getContents()
		,HTTP_MODAUTH: HTTP_MODAUTH
		,action: 'save'
	}, function(response){

	});
}