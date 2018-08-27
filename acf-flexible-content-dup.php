<?php

/*
Plugin Name: 	Advanced Custom Fields - Flexible Content Duplication
Plugin URI: 	https://github.com/magicstickuk/Flexible-Content-Duplication
Description:    Duplicate/Copy/Clone any section of ACF's flexible content field from one page to another
Version: 		1.0
Author: 		Mario Jaconelli
Author URI:  	http://www.wpmaz.uk
Text Domain: 	acf-flexible-content-dup
*/





/**
 * Register required scripts and styles
 *
 * @return void
 */
function fcd_dup_admin_styles_and_scripts(){

    wp_register_style(
        'fcd-dup-admin-styles',
        plugins_url( '/css/fcd.css', __FILE__ ),
        false,
        false
    );
    wp_enqueue_style('fcd-dup-admin-styles');



    wp_register_style(
        'fcd-dup-remodal-styles',
        plugins_url( '/css/remodal.css', __FILE__ ),
        false,
        false
    );
    wp_enqueue_style('fcd-dup-remodal-styles');



    wp_register_style(
        'fcd-dup-remodal-theme',
        plugins_url( '/css/remodal-theme.css', __FILE__ ),
        false,
        false
    );
    wp_enqueue_style('fcd-dup-remodal-theme');



    wp_enqueue_script("fcd-remodal-js", plugins_url( '/js/remodal.js', __FILE__ ));



    wp_enqueue_script("fcd-acf-dup-js", plugins_url( '/js/fcd.js', __FILE__ ), array(), false, true);


 
}

add_action( 'admin_enqueue_scripts', 'fcd_dup_admin_styles_and_scripts' );




/**
 * Ajax wrapper for copy_acf_level()
 *
 * @return void
 */
function fcd_ajax_copy_acf_level(){

    $args = array(
        'field'                 => $_POST['source_field'],
        'index'                 => $_POST['source_index'],
        'post_id'               => $_POST['source'],
        'destination_post_id'   => $_POST['dest_id'],
        'destination_index'     => $_POST['dest_index']
    );

    $result = fcd_copy_acf_level($args);

    echo $result;

    wp_die();

}
add_action('wp_ajax_copy_acf_fc_level', 'fcd_ajax_copy_acf_level');





/**
 * 
 * 
 * Copy a 'level of a Flexible content field to a desired position in another flexible content field
 *
 * @param array $args
 * @return string The edit post url for the destination post
 * 
 */
function fcd_copy_acf_level($args){

    if(function_exists('get_field')){

        // Collect args
        $field                  = $args['field'];
        $index                  = $args['index'];
        $post_id                = $args['post_id'];
        $destination_post_id    = $args['destination_post_id'];
        $destination_index      = $args['destination_index'];



        // Get the source (unformatted) flexible content field
        $source                 = get_field($field, $post_id, false);


        // Get the destination (unformatted) flexible content field
        $destination            = get_field($field, $destination_post_id, false);



        // Get the 'level' of the source to copy
        $source_level           = $source[$index];



        // If there is already a flexible field in the database at the destination
        if($destination){

        
            // Adding to the end of the destination flexible field 
            if($destination_index == -1){

                $destination[]  = $source_level;

            }else{

                // Adding to a specified index (only giving option of beginning in the UI atm)
                array_splice($destination, $destination_index, 0, array($source_level));

            }

        }else{

            // If no destination flexible field yet then value is just the source
            $destination        = array($source_level);

        }


        // Update the destination with the new structure
        $destination            = update_field($field, $destination, $destination_post_id);


        
        // If update successful return a edit URL for the modal 
        $destination_url        = $destination ? get_edit_post_link($destination_post_id, '') : 0;



        
        return $destination_url;


    }
    

}




/**
 * Create the user interface for the duplication process
 *
 * @return void
 */
function fcd_remodal_markup(){

    global $post;

    
    
    // Get all the pages on the site to be added to the select box
    $the_pages = get_posts(array(

        'post_type'         => 'page',
        'post_status'       => array('publish', 'pending', 'draft', 'future', 'private', 'inherit'),
        'posts_per_page'    => -1,
        'post__not_in'      => array( $post->ID )

    ));


    
    if(!empty($the_pages)):?>

        <!-- Do the modal markup -->
        <div data-remodal-id="modal-fcd">

            <button data-remodal-action="close" class="remodal-close"></button>

            <div id="dup-non-saved-notice" class="notice-error" style="display:none;">
                <p>The page has unsaved changes. Please save your post before duplicating.</p>
            </div>
            
            <h1>Select a page to copy this section to:</h1>

            <select name="dup-destination" id="dup-destination" class="dup-sel">

                <option>Select Page ...</option>
                <option value="<?php echo $post->ID ?>" class="dup-current-option">Current Page</option>
                <?php foreach ($the_pages as $the_page): ?>

                    <option value="<?php echo $the_page->ID;?>"><?php echo $the_page->post_title;?></option>

                <?php endforeach;?>

            </select>
            
            <h2>Select where in the layout you want it inserted:</h2>

            <select name="dup-destination-index" id="dup-destination-index" class="dup-sel">

                <option value="0">Start</option>
                <option value="-1">End</option>

            </select>

            <p><button id="dup-submit" class="button button-primary button-large">Copy</button> <img id="dup-loading" style="display:none;" src="<?php echo plugins_url( '/img/spinner.gif', __FILE__ ) ?>" alt=""/></p>

            <p id="dup-success-message" style="display:none;">The section was duplicated. <a id="dup-success-link" href="">Go to the page now</a></p>

            <p id="dup-failed-message" style="display:none;">Something went wrong. Please try again.</p>

        </div>


        <script>

            // What to do on submission of dup form
            jQuery("#dup-submit").on('click', function(){
                


                // Show 'loading' gif on submit of
                jQuery('#dup-loading').show();



                // Get the selectbox values
                var dest_id     = jQuery("#dup-destination").val();
                var dest_index  = jQuery("#dup-destination-index").val();
                



                // Setup all the data we have for ajax post rquest
                var data        = {
                    'action' 		: 'copy_acf_fc_level',
                    'dest_id' 		: dest_id,
                    'dest_index' 	: dest_index,
                    'source'		: acf.get('post_id'),	
                    'source_index'	: acf.get('current-dup-index'),
                    'source_field'  : acf.get('current-dup-name')
                };



                // Post ajax request
                jQuery.post(ajaxurl, data, function(response){

                    if(response){

                        if(response != 0){

                            // On success give user a link to go to destination post and show success message
                            jQuery('#dup-success-link').attr('href', response);
                            jQuery('#dup-loading').hide();
                            jQuery('#dup-success-message').show();

                        }else{

                            // On failed show error message
                            jQuery('#dup-loading').hide();
                            jQuery('#dup-failed-message').show();

                        }
                          
                    }

                });

            });
            
        </script>

    <?php endif;
    
}


add_action('acf/input/admin_footer', 'fcd_remodal_markup');