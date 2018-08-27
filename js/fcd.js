if(acf){

	acf.addAction('ready', function(){

		// add form data to storage so we can check for changes later
		jQuery('#post').data('serialize', jQuery('#post').serialize() );



		// Enable the DUP modal
		var inst = jQuery('[data-remodal-id=modal-fcd]').remodal();
	


		// Add the duplicate icon to each section in the flexible content fields and give attributes to identify field name and index
		jQuery('.acf-field-flexible-content .acf-fc-layout-controls').each(function(i,v){
			
			
			
			// Collect data
			var field_name 	= jQuery(this).closest('.acf-field-flexible-content').attr('data-name') ;
			var index 		= jQuery(this).closest('.layout').attr('data-id');
			
			// Add button markup
			jQuery(this).prepend('<a data-remodal-target="modal" data-name="' + field_name + '" data-index="' + index + '" class="acf-icon acf-dup-icon -dup small light acf-js-tooltip" href="#" data-name="dup-layout" title="Duplicate layout"></a>');
	

			
		});



		// Set the click event on the duplicate icon
		jQuery('.acf-dup-icon.-dup').on('click', function(e){



			// Check to see if the form data has changed since page load. If it has show the warning message on the modal
			if(jQuery('#post').serialize() != jQuery('#post').data('serialize')){
				// Form has changed without saving. 
				jQuery('#dup-non-saved-notice').show();
				
			}


	
			// Add field name and index of clicked duplicate icon into acf storage to be passed into duplicate function
			acf.set('current-dup-index', e.target.dataset.index);
			acf.set('current-dup-name', e.target.dataset.name);
			


			// Open modal
			inst.open();



			// Set select boxes to a nice select2 box
			jQuery('.dup-sel').select2({width: '80%'});



		
		});
	
	});

}