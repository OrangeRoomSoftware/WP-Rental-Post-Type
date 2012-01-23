<?php
/*
Plugin Name: Rental Post Type
Plugin URI: http://www.orangeroomsoftware.com/website-plugin/
Version: 1.0
Author: <a href="http://www.orangeroomsoftware.com/">Orange Room Software</a>
Description: A post type for Rentals
*/

# Post Thumbnails
add_theme_support( ‘post-thumbnails’ );

/*
 * Add shortcodes to the widgets and excerpt
*/
add_filter( 'get_the_excerpt', 'do_shortcode' );
add_filter( 'the_excerpt', 'do_shortcode' );
add_filter( 'widget_text', 'do_shortcode' );

# Site Stylesheet
add_action('wp_print_styles', 'ors_rental_template_stylesheets', 6);
function ors_rental_template_stylesheets() {
  wp_enqueue_style('rental-template-style', '/wp-content/plugins/'.basename(dirname(__FILE__)).'/style.css', 'ors-rental', null, 'all');
}

# Admin Stylesheet
add_action('admin_print_styles', 'ors_admin_stylesheets', 6);
function ors_admin_stylesheets() {
  wp_enqueue_style('rental-admin-style', '/wp-content/plugins/'.basename(dirname(__FILE__)).'/admin-style.css', 'ors-admin', null, 'all');
}

/*
 * First time activation
*/
register_activation_hook( __FILE__, 'activate_vehicle_post_type' );
function activate_rental_post_type() {
  create_rental_post_type();
  flush_rewrite_rules();
}

# Custom post type
add_action( 'init', 'create_rental_post_type' );
function create_rental_post_type() {
  $labels = array(
    'name' => _x('Rentals', 'post type general name'),
    'singular_name' => _x('Rental', 'post type singular name'),
    'add_new' => _x('Add New', 'rental'),
    'add_new_item' => __('Add New Rental'),
    'edit_item' => __('Edit Rental'),
    'new_item' => __('New Rental'),
    'view_item' => __('View Rental'),
    'search_items' => __('Search Rentals'),
    'not_found' =>  __('No rentals found'),
    'not_found_in_trash' => __('No rentals found in Trash'),
    'parent_item_colon' => '',
    'menu_name' => 'Rentals'

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => 6,
    'supports' => array('title', 'location', 'gallery', 'thumbnail', 'editor', 'tags'),
    'menu_icon' => '/wp-content/plugins/'.basename(dirname(__FILE__)).'/icon.png',
    'rewrite' => array(
      'slug' => 'rentals',
      'with_front' => false
    )
  );

  register_post_type( 'rental', $args );
}

$custom_rental_fields = array('Available', 'City', 'State', 'ZIP');

add_action( 'add_meta_boxes', 'add_custom_rental_meta_boxes' );
function add_custom_rental_meta_boxes() {
  add_meta_box("rental_meta", 'Rental Information', "custom_rental_meta_boxes", "rental", "normal", "high");
}

function custom_rental_meta_boxes() {
  global $post;
  $custom_data = get_post_custom($post->ID);
  ?>
  <p>
    Availability Status:<br>
    <input type="radio" name="rental_meta[available]" value="Available Now" <?php echo ($custom_data['available'][0] == 'Available Now') ? 'checked' : ''; ?>>
    <label>Available Now</label>

    <input type="radio" name="rental_meta[available]" value="Coming Soon" <?php echo ($custom_data['available'][0] == 'Coming Soon') ? 'checked' : ''; ?>>
    <label>Coming Soon</label>
  </p>
  <p>
    Property Type:<br>
    <input type="radio" name="rental_meta[property_type]" value="Home" <?php echo ($custom_data['property_type'][0] == 'Home') ? 'checked' : ''; ?>>
    <label>Home</label>
    <input type="radio" name="rental_meta[property_type]" value="Apartment" <?php echo ($custom_data['property_type'][0] == 'Apartment') ? 'checked' : ''; ?>>
    <label>Apartment</label>
  </p>
  <p>
    Rental Price:<br>
    <input type="text" name="rental_meta[price]" value="<?php echo $custom_data['price'][0]; ?>" size="10">
  </p>
  <p>
    <label>Street:</label><br>
    <input name="rental_meta[street]" value="<?php echo $custom_data['street'][0]; ?>" size="60">
  </p>
  <div class="group">
    <p>
      <label>City:</label><br>
      <input name="rental_meta[city]" value="<?php echo $custom_data['city'][0]; ?>" size="40">
    </p>
    <p>
      <label>State:</label><br>
      <input name="rental_meta[state]" value="<?php echo $custom_data['state'][0]; ?>" size="2">
    </p>
    <p>
      <label>ZIP:</label><br>
      <input name="rental_meta[zip]" value="<?php echo $custom_data['zip'][0]; ?>" size="5">
    </p>
  </div>

  <div class="group">
    <p>
      Home Size:<br>
      <input type="text" name="rental_meta[home_size]" value="<?php echo $custom_data['home_size'][0]; ?>" size="10">
    </p>
    <p>
      Lot Size:<br>
      <input type="text" name="rental_meta[lot_size]" value="<?php echo $custom_data['lot_size'][0]; ?>" size="10">
    </p>
  </div>

  <div class="group">
    <p>
      Bedrooms:<br>
      <input type="text" name="rental_meta[bedrooms]" value="<?php echo $custom_data['bedrooms'][0]; ?>" size="4">
    </p>
    <p>
      Bathrooms:<br>
      <input type="text" name="rental_meta[bathrooms]" value="<?php echo $custom_data['bathrooms'][0]; ?>" size="4">
    </p>
  </div>

  <p>
    Features:<br>
    <input type="text" name="rental_meta[features]" value="<?php echo $custom_data['features'][0]; ?>" size="20"> Add New
  </p>

  <p>
    Options:<br>
    <input type="text" name="rental_meta[options]" value="<?php echo $custom_data['options'][0]; ?>" size="20"> Add New
  </p>

  <?php
}

add_action( 'save_post', 'save_rental_postdata' );
function save_rental_postdata( $post_id ) {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    return;

  // Check permissions
  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
      return;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
      return;
  }

  $data = $_POST['rental_meta'];

  foreach ($data as $key=>$value) {
    update_post_meta($post_id, $key, $value);
  }
}

add_filter("manage_edit-rental_columns", "rental_edit_columns");
function rental_edit_columns($columns){
  $columns = array(
    "cb" => "<input type=\"checkbox\" />",
    "title" => "Property Title",
    "available" => "Availability",
    "price" => "Price",
    "street" => "Street",
    "author" => "Author",
    "date" => "Date Added"
  );

  return $columns;
}

add_action("manage_posts_custom_column",  "rental_custom_columns");
function rental_custom_columns($column){
  global $post;
  $custom = get_post_custom();

  switch ($column) {
    case "price":
      echo '$' . $custom["price"][0];
      break;
    case "available":
      echo $custom["available"][0];
      break;
    case "street":
      echo $custom["street"][0];
      break;
  }
}

/*
 * Fix the content
*/
add_filter('the_title', 'rental_title_filter');
function rental_title_filter($content) {
  if ( !in_the_loop() or get_post_type() != 'rental' ) return $content;

  foreach ( get_post_custom() as $key => $value ) {
    $custom[$key] = $value[0];
  }

  $output = '';

  $output .= '<span class="price">$' . $custom['price'] . '/mo</span>';
  $output .= '<span class="title">' . $content . '</span>';
  $output .= '<span class="property-type">' . $custom['property_type'] . '</span>';

  if ( $custom['available'] ) {
    $output .= "<span class='availability " . preg_replace('/\-{2}+/','',preg_replace('/[^A-Za-z0-9]/','-',strtolower(strip_tags($custom['available'])))) . "'>" . ucwords($custom['available']) . "</span>";
  }

  return $output;
}

add_filter('the_excerpt', 'rental_excerpt_filter');
function rental_excerpt_filter($content) {
  if ( get_post_type() != 'rental' ) return $content;

  foreach ( get_post_custom() as $key => $value ) {
    $custom[$key] = $value[0];
  }

  $address = $custom['street'] . ", " . $custom['city'] . ", " . $custom['state'] . "  " . $custom['zip'];

  $output  = '';
  $output .= "<ul class='meta'>";
  $output .= "  <li>Address: " . $address . '</li>';
  $output .= "</ul>";

  $output .= "<p class='excerpt'>";
  $output .= "  " . $content;
  $output .= "</p>";

  return $output;
}

add_filter('the_content', 'rental_content_filter');
function rental_content_filter($content) {
  if ( !is_single() or get_post_type() != 'rental' ) return $content;

  foreach ( get_post_custom() as $key => $value ) {
    $custom[$key] = $value[0];
  }

  $address = $custom['street'] . ", " . $custom['city'] . ", " . $custom['state'] . "  " . $custom['zip'];

  $output  = $content;

  $output .= "<ul class='meta'>";
  $output .= "  <li>Address: " . $address . '</li>';
  $output .= "  <li>Bedrooms: " . $custom['bedrooms'] . '</li>';
  $output .= "  <li>Bathrooms: " . $custom['bathrooms'] . '</li>';
  $output .= "</ul>";

  $output .= '<ul class="features">';
  $output .= '</ul>';

  $output .= '<ul class="options">';
  $output .= '</ul>';

  return $output;
}
