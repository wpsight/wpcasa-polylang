<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * WPSight_Polylang_Admin class
 */
class WPSight_Polylang_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		
		// Sync some meta values
		add_action( 'updated_post_meta', array( $this, 'updated_post_meta' ), 10, 4 );
		
		// Add new field to maintain default description
		add_filter( 'wpsight_meta_box_user_fields', array( $this, 'user_fields' ) );
		
		// Set agent description for each language
		add_action( 'wpsight_profile_agent_update_save_options', array( $this, 'updated_agent_description' ), 10, 2 );
		
		// Set agent description defaul in listing editor
		add_filter( 'wpsight_meta_box_listing_agent_fields', array( $this, 'listing_agent_description' ) );

	}
	
	/**
	 *	updated_post_meta()
	 *	
	 *	Sync some meta values between
	 *	listing translations. Important
	 *	for changes through action buttons.
	 *	
	 *	@access	public
	 *	@param	integer	$meta_id
	 *	@param	integer	$object_id
	 *	@param	string	$meta_key
	 *	@param	string	$_meta_value
	 *	@uses	wpsight_post_type()
	 *	@uses	$polylang->model->get_translations()
	 *	@uses	update_post_meta()
	 *	
	 *	@since 1.0.0
	 */
	public function updated_post_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {
		global $polylang;
		
		// Set meta keys to be updated
		
		$update_meta = array(
			'_listing_sticky',
			'_listing_featured',
			'_listing_expires',
			'_listing_not_available'
		);
		
		// Check if one of them is updated
		
		if( in_array( $meta_key, $update_meta ) ) {
			
			// Get all translations of current listing
			$post_ids = $polylang->model->get_translations( wpsight_post_type(), $object_id );
			
			// Update all translations			
			foreach( $post_ids as $post_id )
				update_post_meta( $post_id, $meta_key, $_meta_value );
			
		}
		
	}
	
	/**
	 *	user_fields()
	 *	
	 *	We need the $_POST['description']
	 *	value that seems to be removed
	 *	by Polylang.
	 *	
	 *	@access	public
	 *	@param	array	$fields
	 *	
	 *	@since 1.0.0
	 */
	public function user_fields( $fields ) {
	
		$fields['agent_description'] = array(
			'name'	=> false,
			'desc'  => false,
			'id'    => 'description',
			'type'  => 'hidden'
		);
		
		return $fields;
	
	}
	
	/**
	 *	updated_agent_description()
	 *	
	 *	Correctly update agent description
	 *	for all registered languages to
	 *	be available in our listing editor.
	 *	
	 *	@access	public
	 *	@param	array	$agent_options
	 *	@param	integer	$user_id
	 *	@uses	pll_languages_list()
	 *	
	 *	@since 1.0.0
	 */
	public function updated_agent_description( $agent_options, $user_id ) {
	
		// Set descriptions in all languages
		
		foreach( pll_languages_list() as $lang )
			$agent_options[ '_agent_description_' . $lang ] = trim( $_POST[ 'description_' . $lang ] );
		
		return $agent_options;
			
	}
	
	/**
	 *	listing_agent_description()
	 *	
	 *	Set the default agent description
	 *	depending on the post language if
	 *	already set.
	 *	
	 *	@access	public
	 *	@param	array	$fields
	 *	@uses	pll_get_post_language()
	 *	@uses	wp_get_current_user()
	 *	@uses	get_user_meta()
	 *	@return	array	$fields
	 *	
	 *	@since 1.0.0
	 */
	public function listing_agent_description( $fields ) {
		
		// Get post ID early
		$post_id = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : false;
		
		if( $post_id ) {
		
			// Get post language
			$post_lang = pll_get_post_language( $post_id );
			
			// Get default description		
			$description = get_user_meta( wp_get_current_user()->ID, 'description', true );
			
			// Get description in post language
			$description_lang = get_user_meta( wp_get_current_user()->ID, 'description_' . $post_lang, true );
			
			// Set default value of desription
			$fields['description']['default'] = $description_lang ? $description_lang : $description;
		
		}
		
		return $fields;
		
	}

}
