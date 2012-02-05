<?php
/*
Plugin Name: Rental Post Type
Plugin URI: https://github.com/orangeroomsoftware/WP-Rental-Post-Type
Version: 1.0
Author: <a href="http://www.orangeroomsoftware.com/">Orange Room Software</a>
Description: A post type for Rentals
*/

define('RENTAL_PLUGIN_URL', '/wp-content/plugins/' . basename(dirname(__FILE__)) );
define('RENTAL_PLUGIN_DIR', dirname(__FILE__));

/**
 * Theme Admin Options
 */
require_once ( RENTAL_PLUGIN_DIR . '/plugin-options.php' );

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
  wp_enqueue_style('rental-template-style', RENTAL_PLUGIN_URL . "/style.css", 'ors-rental', null, 'all');
}

# Admin Stylesheet
add_action('admin_print_styles', 'ors_admin_stylesheets', 6);
function ors_admin_stylesheets() {
  wp_enqueue_style('rental-admin-style', RENTAL_PLUGIN_URL . "/admin-style.css", 'ors-admin', null, 'all');
}

# Admin Javascript
add_action('admin_print_scripts', 'ors_rental_plugin_admin_script', 5);
function ors_rental_plugin_admin_script() {
  wp_register_script( 'ors_rental_plugin_admin_script', RENTAL_PLUGIN_URL . "/admin-script.js", 'jquery', time() );
  wp_enqueue_script('ors_rental_plugin_admin_script');
}

/*
 * First time activation
*/
register_activation_hook( __FILE__, 'activate_vehicle_post_type' );
function activate_rental_post_type() {
  create_rental_post_type();
  flush_rewrite_rules();
  add_option( 'ors-rental-global-features', '2 Car Garage|4 Car Garage|Air Conditioning|Alarm|Assigned Parking|Ceiling Fan|Central Heating|Covered Parking|Den/Office|Dining Area|Dining Room|Dishwasher|Disposal|Enclosed Patios|Evaporative Cooler|Family Room|Fenced Back Yard|Fireplace|Full Kitchen|Game Room|Garage|Generous Closet Areas|Interior Storage|Living Room|Loft|Microwave|Patio|RV Parking|Refrigerator|Separate Dining Room|Spa|Sprinklers|Storage Shed|Stove/Oven|Swimming Pool|Utility Room|Washer/Dryer|Washer/Dryer Hookup|Central Vac', '', true );
  add_option( 'ors-rental-global-options', 'Pool Service|Pest Control Service|Yard Service|Sewer and Trash|Playground|Small Pets Considered|Tennis Court|Garbage Pickup|Satellite TV|Water', '', true );
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
    'menu_icon' => RENTAL_PLUGIN_URL . '/icon.png',
    'rewrite' => array(
      'slug' => 'rentals',
      'with_front' => false
    )
  );

  register_post_type( 'rental', $args );
}

/**
 * Meta Box for Editor
 */
add_action( 'add_meta_boxes', 'add_custom_rental_meta_boxes' );
function add_custom_rental_meta_boxes() {
  add_meta_box("rental_meta", 'Rental Information', "custom_rental_meta_boxes", "rental", "normal", "high");
}

function custom_rental_meta_boxes() {
  global $post;
  $custom_data = get_post_custom($post->ID);

  $features = array_filter(explode('|', $custom_data['features'][0]), 'strlen');
  sort($features);
  $options = array_filter(explode('|', $custom_data['options'][0]), 'strlen');
  sort($options);

  $global_features = explode('|', get_option('ors-rental-global-features'));
  $global_options = explode('|', get_option('ors-rental-global-options'));

  ?>
  <div class="group">
    <p>
      Availability Status:<br>
      <input type="radio" name="rental_meta[available]" value="Available Now" <?php echo ($custom_data['available'][0] == 'Available Now') ? 'checked' : ''; ?>>
      <label>Available Now</label>

      <input type="radio" name="rental_meta[available]" value="Coming Soon" <?php echo ($custom_data['available'][0] == 'Coming Soon') ? 'checked' : ''; ?>>
      <label>Coming Soon</label>
    </p>
    <p>
      Property Type:<br>
      <input type="radio" name="rental_meta[property_type]" value="House" <?php echo ($custom_data['property_type'][0] == 'House') ? 'checked' : ''; ?>>
      <label>House</label>
      <input type="radio" name="rental_meta[property_type]" value="Apartment" <?php echo ($custom_data['property_type'][0] == 'Apartment') ? 'checked' : ''; ?>>
      <label>Apartment</label>
      <input type="radio" name="rental_meta[property_type]" value="Condominium" <?php echo ($custom_data['property_type'][0] == 'Condominium') ? 'checked' : ''; ?>>
      <label>Condominium</label>
    </p>
  </div>

  <p>
    Rental Price:<br>
    $<input type="text" name="rental_meta[price]" value="<?php echo $custom_data['price'][0]; ?>" size="4">
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
      <input type="text" name="rental_meta[home_size]" value="<?php echo $custom_data['home_size'][0]; ?>" size="4" class="numeric">sq ft
    </p>
    <p>
      Lot Size:<br>
      <input type="text" name="rental_meta[lot_size]" value="<?php echo $custom_data['lot_size'][0]; ?>" size="4" class="numeric">sq ft
    </p>
    <p>
      Bedrooms:<br>
      <input type="text" name="rental_meta[bedrooms]" value="<?php echo $custom_data['bedrooms'][0]; ?>" size="2" class="numeric">
    </p>
    <p>
      Bathrooms:<br>
      <input type="text" name="rental_meta[bathrooms]" value="<?php echo $custom_data['bathrooms'][0]; ?>" size="2" class="numeric">
    </p>
  </div>

  <p>
    Features:<br>
    <input type="hidden" id="features-data" name="rental_meta[features]" value="<?php echo implode('|', $features); ?>">
    <ul id="features" class="bundle">
      <?php foreach ( $global_features as $value ) { if (empty($value)) continue; ?>
      <li><input type="checkbox" value="<?php echo $value; ?>" <?php echo in_array($value, $features) ? 'checked="checked"' : ''; ?>> <?php echo $value; ?></li>
      <?php } ?>
    </ul>
    <input type="text" id="add-feature-text" name="add-feature" value="" size="20">
    <input type="button" id="add-feature-button" value="Add">
  </p>

  <p>
    Optional:<br>
    <input type="hidden" id="options-data" name="rental_meta[options]" value="<?php echo $custom_data['options'][0]; ?>">
    <ul id="options" class="bundle">
      <?php foreach ( $global_options as $value ) { if (empty($value)) continue; ?>
      <li><input type="checkbox" value="<?php echo $value; ?>" <?php echo in_array($value, $options) ? 'checked="checked"' : ''; ?>> <?php echo $value; ?></li>
      <?php } ?>
    </ul>
    <input type="text" id="add-option-text" name="add-option" value="" size="20">
    <input type="button" id="add-option-button" value="Add">
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

  // Page Meta
  $custom_data = $_POST['rental_meta'];
  foreach ($custom_data as $key=>$value) {
    update_post_meta($post_id, $key, $value);
  }

  // Global Features and Options
  $features = explode('|', $custom_data['features']); sort($features);
  $options = explode('|', $custom_data['options']); sort($options);
  $global_features = explode('|', get_option('ors-rental-global-features'));
  $global_options = explode('|', get_option('ors-rental-global-options'));
  $global_features = array_filter(array_unique(array_merge($global_features, $features)), 'strlen');
  $global_options = array_filter(array_unique(array_merge($global_options, $options)), 'strlen');
  sort($global_features);
  sort($global_options);
  update_option('ors-rental-global-features', implode('|', $global_features));
  update_option('ors-rental-global-options', implode('|', $global_options));
}

add_filter("manage_edit-rental_columns", "rental_edit_columns");
function rental_edit_columns($columns){
  $columns = array(
    "cb" => "<input type=\"checkbox\" />",
    "thumbnail" => "Photo",
    "title" => "Property Title",
    "available" => "Availability",
    "price" => "Price",
    "property_type" => "Type",
    "bed-bath" => "Bed/Bath",
    "street" => "Street",
    "city" => "City",
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
    case "thumbnail":
      if ( has_post_thumbnail( $post->ID ) ) {
        the_post_thumbnail(array(50,50));
      }
      break;
    case "price":
      echo '$' . $custom["price"][0];
      break;
    case "available":
      echo $custom["available"][0];
      break;
    case "property_type":
      echo $custom["home_size"][0] . 'sqft ' . $custom["property_type"][0];
      break;
    case "bed-bath":
      echo $custom["bedrooms"][0] . '/' . $custom["bathrooms"][0];
      break;
    case "street":
      echo $custom["street"][0];
      break;
    case "city":
      echo $custom["city"][0];
      break;
  }
}

/*
 * Custom Query for this post type to sort by price
 * Don't use this sort in Admin
*/
if ( !is_admin() ) add_filter( 'posts_clauses', 'ors_rental_query' );
function ors_rental_query($clauses) {
  if ( !strstr($clauses['where'], 'rental') ) return $clauses;

  global $wpdb, $ors_rental_cookies;
  $clauses['fields'] .= ", CAST((select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'price') as decimal) as price";
  $clauses['fields'] .= ", CAST((select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'home_size') as decimal) as home_size";
  $clauses['fields'] .= ", CAST((select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'bedrooms') as decimal) as bedrooms";
  $clauses['fields'] .= ", CAST((select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'bathrooms') as decimal) as bathrooms";
  $clauses['having'] = array();
  $clauses['orderby'] = '';

  if ( isset($ors_rental_cookies['text_search']) and $ors_rental_cookies['text_search'] != '' ) {
    $clauses['where'] .= " and ({$wpdb->posts}.post_title like '%{$ors_rental_cookies['text_search']}%'";
    $clauses['where'] .= " or {$wpdb->posts}.post_content like '%{$ors_rental_cookies['text_search']}%')";
  }

  $search_params = array('bedrooms', 'bathrooms');
  foreach ($search_params as $param) {
    if ( isset($ors_rental_cookies[$param]) and $ors_rental_cookies[$param] != '' ) {
      $clauses['having'][] = "$param = '$ors_rental_cookies[$param]'";
    }
  }
  if ( !empty($clauses['having']) ) {
    $clauses['where'] .= ' HAVING ' . implode(' and ', $clauses['having']);
  }

  $order_params = array('price' => 'price_near', 'home_size' => 'size_near');
  foreach ($order_params as $field => $param) {
    if ( isset($ors_rental_cookies[$param]) and $ors_rental_cookies[$param] != '' ) {
      $clauses['orderby'] .= ", ABS({$ors_rental_cookies[$param]} - $field)";
    }
  }
  if ( $clauses['orderby'] == '' ) $clauses['orderby'] = 'price ASC';
  else $clauses['orderby'] = substr($clauses['orderby'], 2);

  // print "<pre>" . print_r($clauses, 1) . "</pre>";
  return $clauses;
}

/*
 * Search Box
*/
add_filter( 'loop_start', 'ors_rental_search_box' );
function ors_rental_search_box() {
  if ( get_post_type() != 'rental' ) return;

  if ( is_single() ) {
    print '<a class="back-button" href="' . $_SERVER['HTTP_REFERER'] . '">⬅ Back to Listings</a>';
    return;
  }

  global $ors_rental_cookies;
  ?>
  <div id='ors-rental-search-box'>
    <form action="/rentals/" method="POST">
      Price Near <input type="text" name="price_near" size=4 value="<?php echo $ors_rental_cookies['price_near'] ?>">
      Size Near <input type="text" name="size_near" size=4 value="<?php echo $ors_rental_cookies['size_near'] ?>">
      Bedrooms <input type="text" name="bedrooms" size=2 value="<?php echo $ors_rental_cookies['bedrooms'] ?>">
      Bathrooms <input type="text" name="bathrooms" size=2 value="<?php echo $ors_rental_cookies['bathrooms'] ?>">
      Text <input type="text" name="text_search" size=30 value="<?php echo $ors_rental_cookies['text_search'] ?>">
      <input type="hidden" name="post_type" value="rental">
      <input type="submit" name="submit" value="Search">
      <input type="submit" name="clear" value="Clear">
    </form>
  </div>
  <?php
}

function ors_rental_set_cookies() {
  global $ors_rental_cookies;
  $search_params = array('price_near', 'size_near', 'bedrooms', 'bathrooms', 'text_search');

  foreach ($search_params as $param) {
    if ( isset($_POST[$param]) ) {
      if ( $_POST['clear'] == 'Clear' ) $_POST[$param] = '';
      $ors_rental_cookies[$param] = $_POST[$param];
      setcookie($param, $_POST[$param], time() + 3600, COOKIEPATH, COOKIE_DOMAIN, false);
    }

    elseif ( isset($_COOKIE[$param]) ) {
      $ors_rental_cookies[$param] = $_COOKIE[$param];
    }
  }
}
add_action( 'init', 'ors_rental_set_cookies');


/*
 * Fix the content
*/
add_filter( 'the_title', 'rental_title_filter' );
function rental_title_filter($content) {
  if ( !in_the_loop() or get_post_type() != 'rental' ) return $content;

  foreach ( get_post_custom() as $key => $value ) {
    $custom[$key] = $value[0];
  }

  if ( $custom['available'] == 'Coming Soon' ) $visible = false; else $visible = true;

  $output = '';

  $output .= '<span class="price">$' . $custom['price'] . '/mo</span>';
  if ( $visible ) $output .= '<span class="title">' . $content . '</span>';
  else $output .= '<span class="title">Coming Soon</span>';
  $output .= '<span class="property-type">' . $custom['property_type'] . '</span>';

  return $output;
}

add_filter('the_excerpt', 'rental_excerpt_filter');
function rental_excerpt_filter($content) {
  if ( get_post_type() != 'rental' ) return $content;

  foreach ( get_post_custom() as $key => $value ) {
    $custom[$key] = $value[0];
  }

  if ( $custom['available'] == 'Coming Soon' ) $visible = false; else $visible = true;

  $address = $custom['street'] . ", " . $custom['city'] . ", " . $custom['state'] . "  " . $custom['zip'];

  $output  = '';

  if ( !has_post_thumbnail( $post->ID ) ) {
    $output .= '<a href="' . get_permalink() . '"><img width="150" height="150" src="' . RENTAL_PLUGIN_URL . '/nophoto.png" class="attachment-thumbnail wp-post-image" alt="No Photo" title="' . $address . '"></a>';
  }

  if ( $custom['available'] ) {
    $output .= "<div class='availability burst-8 " . preg_replace('/\-{2}+/','',preg_replace('/[^A-Za-z0-9]/','-',strtolower(strip_tags($custom['available'])))) . "'>" . ucwords($custom['available']) . "</div>";
  }

  $output .= "<ul class='meta'>";
  if ( $visible ) $output .= "  <li>Address: " . $address . '</li>';
  $output .= "  <li>{$custom['home_size']} Square Foot, {$custom['bedrooms']} Bedrooms, {$custom['bathrooms']} Bathrooms</li>";
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

  if ( $custom['available'] == 'Coming Soon' ) $visible = false; else $visible = true;

  $address = $custom['street'] . ", " . $custom['city'] . ", " . $custom['state'] . "  " . $custom['zip'];

  $features = array_filter(explode('|', $custom['features']), 'strlen');
  $options = array_filter(explode('|', $custom['options']), 'strlen');

  $output  = "[slideshow]<br/>" . $content;
  $output .= "<ul class='meta'>";
  if ( $visible ) $output .= "  <li>Address: " . $address . '</li>';
  $output .= "  <li>" . $custom['bedrooms'] . ' Bedrooms ';
  $output .= "  " . $custom['bathrooms'] . ' Bath</li>';
  if ( $custom['home_size'] )
    $output .= "  <li>{$custom['home_size']} Square Foot {$custom['property_type']}</li>";
  if ( $custom['lot_size'] )
    $output .= "  <li>{$custom['lot_size']} Square Foot Lot</li>";
  $output .= "</ul>";

  if ( is_array($features) and !empty($features[0]) ) {
    $output .= "<div class='features'>";
    $output .= "Features:<br>";
    $output .= '<ul>';
    foreach ( $features as $value ) {
      $output .= '  <li>' . $value . '</li>';
    }
    $output .= '</ul></div>';
  }

  if ( is_array($options) and !empty($options[0]) ) {
    $output .= "<div class='options'>";
    $output .= "Optional:<br>";
    $output .= '<ul>';
    foreach ( $options as $value ) {
      $output .= '  <li>' . $value . '</li>';
    }
    $output .= '</ul></div>';
  }

  if ( $inquiry = get_option('ors-inquiry-form') ) {
    $output .= '<div class="inquiry-form">';
    $output .= '<h2>Send Email Inquiry</h2>';
    $output .= $inquiry;
    $output .= '</div>';
  }

  if ( $tell_a_friend = get_option('ors-tell-a-friend-form') ) {
    $output .= '<div class="inquiry-form">';
    $output .= '<h2>Tell-A-Friend</h2>';
    $output .= $tell_a_friend;
    $output .= '</div>';
  }

  return $output;
}
