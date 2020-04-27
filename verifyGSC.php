<?php
/*
Plugin Name: WP Verify Google Search Console
Description: Simple, lightweight, SEO-friendly plugin for your domain's Google Search Console verification!
Version: 1.0
Author: Saad Malik
Author URI: https://saad.codes/
*/

class verifyGSC {

    public $msg;

    /*
    |--------------------------------------------------------------------------
    | Initialize plugin
    |--------------------------------------------------------------------------
    */
    public function init() {
        # load css file on init
        wp_enqueue_style('verifyGSC_css', plugins_url('css/style.css', __FILE__));
        # setup meta box
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        # save when post is saved
        add_action( 'save_post', array( $this, 'save' ) );
    }

    /*
    |--------------------------------------------------------------------------
    | Create meta box below post
    |--------------------------------------------------------------------------
    */
    public function add_meta_box( $post_type ) {

        # add meta box to posts and pages
        $post_types = array( 'post', 'page' );
        # meta box details
        if ( in_array( $post_type, $post_types )) {
            add_meta_box(
                'verifyGSC_box_id',            // Unique ID
                'Enter Google Site Verification content code below:',      // Box title
                array( $this, 'render_form'), // Content callback
                $post_type
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Save meta data
    |--------------------------------------------------------------------------
    */
    public function save( $post_id ) {
     global $wp_session;

        # ensure data exists and is valid request from user
        if ( array_key_exists('verifyGSC_field', $_POST ) && wp_verify_nonce( $_REQUEST['verifyGSC_nonce'], __FILE__ ) ) {

         # check if array is empty
         $fields = array_filter( $_POST['verifyGSC_field'] );

         # update meta with new data
         if (empty($fields)) {
          update_post_meta( $post_id, '_verifyGSC_field', array( $o_source ) );
         }

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show html
    |--------------------------------------------------------------------------
    */
    public function render_form( $post ) {

     # get meta data
     $fields = get_post_meta( $post->ID, '_verifyGSC_field', true );

     # html input
     ?>
     <div class="verifyGSC">
     <?php
      for($i = 0; $i < 4; $i++){
      ?>
      <div class="field">
      <label for="verifyGSC_field_<?=$i+1?>"> verifyGSC - Original Source <?=$i+1?></label>
      <input type="text" name="verifyGSC_field[]" id="verifyGSC_field_<?=$i+1?>" value="<?php if ( is_array($fields) ) echo $fields[$i]; ?>">
     </div>
      <?php
     }
     ?>
     <input type="hidden" name="verifyGSC_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
    </div>
     <?php
    }

}

# init class
$verifyGSC = new verifyGSC();

# run when in admin panel
add_action('admin_init', array($verifyGSC, 'init'));

# add meta data to page header
add_action('wp_head', 'header_osource_meta' );

/*
|--------------------------------------------------------------------------
| Add original source(s) to header
|--------------------------------------------------------------------------
*/
function header_osource_meta() {
 # allow access to post
 global $post;

 # get meta data
 $fields = get_post_meta( $post->ID, '_verifyGSC_field', true );

 # loop through fields and display data
 if(is_array($fields)){
  foreach ($fields as $field) {
   echo '<meta name="google-site-verification" content="' . $field . '">' . PHP_EOL;
  }
 }
}
