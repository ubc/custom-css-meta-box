<?php

/*
Plugin Name: Custom CSS Meta Box
Plugin URI:
Description: Abiliy to place custom CSS on individual pages and posts. Once enabled a custom text box will apear on page and post write panels and the custom CSS will be written in the Head of the HTML document.
Author: CTLT Dev
Version: 1.0
Author URI:

*/

Class Custom_CSS_Meta_Box {
	static $instance;

	function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * init function.
	 *
	 * @access public
	 * @return void
	 */
	function init(){
		// filters
		add_action( 'wp_head',array( $this, 'display_css' ) );


		// admin side
		/* Use the admin_menu action to define the custom boxes */
		add_action( 'admin_menu', array( $this, 'init_meta_box' ) );

		/* Use the save_post action to do something with the data entered */
		add_action('save_post',  array( $this, 'save_meta_data' ) );

		add_action( 'admin_print_styles-post-new.php', array( $this,'script_and_style') );
		add_action( 'admin_print_styles-post.php',array( $this,'script_and_style') );

	}


	/**
	 * display_css function.
	 *
	 * @access public
	 * @return void
	 */
	function display_css() {
 		global $post;

		if( is_single() || is_page() ):

	 		$custom_field = trim( get_post_meta( $post->ID, '_custom_css' , true ) );

	 		if( !empty( $custom_field ) )
	 			echo '<!-- CSS FROM META BOX -->
	 			<style type="text/css">'.$custom_field.'</style>';

		endif;
	}



	// ADMIN SIDE
	/* Adds a custom section to the "advanced" Post and Page edit screens */
	function script_and_style(){
		global $post;

		if( !in_array($post->post_type, array('post','page') ) )
			return;
		// add javascript
		wp_enqueue_script( 'codemirror',  plugins_url( 'custom-css-meta-box/js/codemirror.js' ), array( 'jquery' ) );
		wp_enqueue_script( 'codemirror-script-css', plugins_url( 'custom-css-meta-box/js/css.js' ), array( 'codemirror' ) );
		// add just the styles
		wp_enqueue_style( 'codemirror-style', plugins_url( 'custom-css-meta-box/css/codemirror.css' ) );

	}


	/**
	 * init_meta_box function.
	 *
	 * @access public
	 * @return void
	 */
	function init_meta_box() {

		// on posts
		add_meta_box( 'custom_css_meta_box', __( 'Custom CSS', 'custom-css-meta-box' ), array( $this, 'display_meta_box' ), 'post', 'advanced','low' );

		// on pages
		add_meta_box( 'custom_css_meta_box', __( 'Custom CSS', 'custom-css-meta-box' ), array( $this, 'display_meta_box' ), 'page', 'advanced','low' );

	}

	/**
	 * meta_box_display function.
	 *
	 * @access public
	 * @return void
	 */
	function display_meta_box( $post ) {

		$post_id = $post->ID;

        if ( 'attachment' === $post->post_type ) {
                // Other plugins, such as the portoflio slideshow plugin override the global $post, which causes problems
                $post_id = absint( $_GET['post'] );
				$post = get_post( $post_id );
        }

		$custom_css = get_post_meta( $post_id, '_custom_css', true );

   		// Use nonce for verification
   		echo '<input type="hidden" name="custom_css_mate_box_noncename" id="custom_css_mate_box_noncename" value="' .wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

		echo __( 'The CSS will appear only on this ','custom-css-meta-box' ) . $post->post_type . __( ' and will be included at the HEAD of the HTML','custom-css-meta-box' );
		// The actual fields for data entry
		?>
		<pre><code> &lt;style type="text/css" &gt;</code></pre>
		<textarea name="custom_css_meta_box"  id="custom-css-meta-box"><?php echo esc_textarea( $custom_css ); ?></textarea>
		<pre><code> &lt;/style&gt;</code></pre>
		<?php

	}


	/**
	 * save_meta_data function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @return void
	 */
	function save_meta_data( $post_id ) {
		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
		// to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			return $post_id;

		if (isset($_POST['custom_css_mate_box_noncename']) && !wp_verify_nonce( $_POST['custom_css_mate_box_noncename'], plugin_basename(__FILE__) ))
				return $post_id;

		// only update the data if it is a string
		if(isset($_POST['custom_css_meta_box']) && is_string( $_POST['custom_css_meta_box'] ) )
			add_post_meta( $post_id, '_custom_css', $_POST['custom_css_meta_box'], true) or update_post_meta( $post_id, '_custom_css', $_POST['custom_css_meta_box'] );

		return $post_id;

	}

}
new Custom_CSS_Meta_Box;
