if(acf){

	acf.addAction('ready', function(){


		// add form data to storage so we can check for changes later
		jQuery('#post').data('serialize', jQuery('#post').serialize() );



		// Enable the DUP modal
		jQuery('[data-remodal-id=modal-fcd]').remodal();

		

		// Set select boxes to a nice select2 box
		jQuery('.dup-sel').select2({width: '80%'});


		
	});



	acf.addAction('ready_field/type=flexible_content', function(field){
		

		// Field name from action params
		var field_name 	= field.data.name;


		// Do some actions to each layout in the flexible field
		field.$el.find('.layout').each(function(i,e){

			// Get the index
			var index = jQuery(e).attr('data-id');

			// Add our duplicate button
			jQuery(e).find('.acf-fc-layout-controls').prepend('<a data-remodal-target="modal-fcd" data-name="' + field_name + '" data-index="' + index + '" class="acf-icon acf-dup-icon acf-dup-icon-' + field_name + '_'+ index + ' -dup small light acf-js-tooltip" href="#" data-name="dup-layout" title="Duplicate layout"></a>');
			
			//Bind actions to dup button
			fcd_do_button(field, index);


		});
				 

	});

	acf.addAction('append_field/type=flexible_content', function(field){	

		// If new level added show warning
		fcd_do_button(field, 'acfcloneindex');

	}); 
	

}



/**
 * 
 * Bind actions to the duplication button
 * 
 * @param {acf field object} field 
 * @param {mixed} index 
 */
function fcd_do_button(field, index){


	// Field name from action params
	var field_name 	= field.data.name;

	// If new level added show warning
	if(index == 'acfcloneindex'){
		
		jQuery('#dup-non-saved-notice').show();
		
	}


	// Set the click event on the duplicate icon
	field.$el.find('.acf-dup-icon-' + field_name + '_' + index + '.-dup').on('click', function(e){



		// Reset any error notices
		jQuery('.notice-error').hide();
		
		


		// Check to see if the form data has changed since page load. If it has show the warning message on the modal
		if(jQuery('#post').serialize() != jQuery('#post').data('serialize')){

			// Form has changed without saving. 
			jQuery('#dup-non-saved-notice').show();

			
		}



		// Add field name and index of clicked duplicate icon into acf storage to be passed into duplicate function
		acf.set('current-dup-index', e.target.dataset.index);
		acf.set('current-dup-name', e.target.dataset.name);


		
	});
	

}