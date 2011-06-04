function saveEditableOnBlur(event, eventProperties) {
	//post to connector for save.
	$.post(alohaXSettings.assetsPath + 'connector.php', {
		id: alohaXSettings.resourceIdentifier
		,field: eventProperties.editable.getId()
		,content: eventProperties.editable.getContents()
		,HTTP_MODAUTH: alohaXSettings.HTTP_MODAUTH
		,action: 'web/savefield'
	}, function(response){
		response = Ext.util.JSON.decode(response);
		$(document).alohaStatus({
			message: response.message
			,success: response.success
			,type: 'warning'
			,time: 2000
		});
		
	});
}

function saveResource(){
	$.post(alohaXSettings.assetsPath + 'connector.php', {
		id: alohaXSettings.resourceIdentifier
		,resource: alohaXSettings.resource
		,HTTP_MODAUTH: alohaXSettings.HTTP_MODAUTH
		,action: 'web/saveresource'
	}, function(response){
		response = Ext.util.JSON.decode(response);
		$(document).alohaStatus({
			message: response.message
			,success: response.success
			,type: 'warning'
			,time: 2000
		});
		
		if(response.success){
			alohaXSettings.dirtyFieldCount = 0;
			for(var fieldname in alohaXSettings.dirtyFields){
				alohaXSettings.dirtyFields[fieldname] = 0;
			}
		}
		
	});
}

alohaXSettings.dirtyFieldCount = 0;

function saveEditableOnBlurForLater(event, eventProperties){
	var field = eventProperties.editable.getId();
	if(alohaXSettings.dirtyFields[field] == 0){
		//count how many dirty fields we have.
		alohaXSettings.dirtyFieldCount++;
		alohaXSettings.dirtyFields[field]++;
	}
	
	//push the content into the resource object.
	alohaXSettings.resource[field] = eventProperties.editable.getContents();
	$(document).alohaStatus({
		message: "You have " + alohaXSettings.dirtyFieldCount + " unsaved changes. <a href='javascript:saveResource();'>Click here</a> to save."
		,waitUntilClose: true
		,type: 'warning'
		,success: false
	});
}

$(document).ready(function(){
	$(document).alohaStatus({
		message			 : 'AlohaX is ready - start editing this page!',
		time   : 1500
	});
	
	for(var x = 0; x < alohaXSettings.fields.length; x++){
		fieldname = alohaXSettings.fields[x];
		if(jQuery.trim($("#" + fieldname).html()) == ''){
			//inject dummy otherwise totally empty fields wont be editable
			$("#" + fieldname).html('Click me to edit.');
		}
		if($("#" + fieldname).length){
			$("#" + fieldname).aloha();
			
		}
		
	}
	
	
});

window.onbeforeunload = function(){ 
	if(alohaXSettings.dirtyFieldCount > 0){
		return 'You have ' + alohaXSettings.dirtyFieldCount + " unsaved changes.";
	}
}

/* ALOHA CONFIGS */
GENTICS.Aloha.settings = {
	logLevels: {'error': false, 'warn': false, 'info': false, 'debug': false},
	errorhandling : false,
	ribbon: false,	
	"i18n": {
		"current": "en" 
	},
	"plugins": {
	 	"com.gentics.aloha.plugins.Format": {
		 	// all elements with no specific configuration get this configuration
			config : [ 'b', 'i','u','a'],
		  	editables : {
				// no formatting allowed for headings
				'h1'	: [ ], 
				'h2'	: [ ], 
				'h3'	: [ ], 
				'h4'	: [ ], 
				'h5'	: [ ], 
				'h6'	: [ ],
				// formatting for all editable DIVs
				'div'	: [ 'b', 'i', 'img', 'a', 'u', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'removeFormat']
			}
		}
		
		,
	 	"com.gentics.aloha.plugins.Link": {
		 	// all elements with no specific configuration may insert links
			config : [ 'a' ],
		  	// use all resources of type website for autosuggest
		  	objectTypeFilter: ['modx-link'],
		  	// handle change of href
		  	onHrefChange: function( obj, href, item ) {
			  	if ( item ) {
					jQuery(obj).attr('data-name', item.name);
					jQuery(obj).attr('style', '');//aloha adds stuff by default
			  	} else {
					jQuery(obj).removeAttr('data-name');
					jQuery(obj).attr('style', '');//aloha adds stuff by default
			  	}
		  	}
		}
		
	 	,"com.gentics.aloha.plugins.Table": { 
		 	// all elements with no specific configuration are not allowed to insert tables
			config : [ ],
		  	editables : {
				// Allow insert tables only into .article
				'div'	: [ 'table' ] 
		  	}
		}
  	}
};

if(alohaXSettings.saveOnBlur == 1){
	//subscribe to onblur event if it is chosen.
	GENTICS.Aloha.EventRegistry.subscribe(GENTICS.Aloha, "editableDeactivated", saveEditableOnBlur);	
} else {
	//subscribe to onblur event only for storage.
	GENTICS.Aloha.EventRegistry.subscribe(GENTICS.Aloha, "editableDeactivated", saveEditableOnBlurForLater);
}

$.fn.serializeObject = function()
{
   var o = {};
   var a = this.serializeArray();
   $.each(a, function() {
       if (o[this.name]) {
           if (!o[this.name].push) {
               o[this.name] = [o[this.name]];
           }
           o[this.name].push(this.value || '');
       } else {
           o[this.name] = this.value || '';
       }
   });
   return o;
};