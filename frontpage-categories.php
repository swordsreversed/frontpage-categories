<?php
/**
 * Plugin Name: Frontpage Categories
 * Plugin URI:
 * Description: Add and change frontpage categories
 * Version: 0.1
 * Author: D.Black
 * Author URI:
 * License: GPL2
 */

class Frontpage_Categories {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
    	//add_action( 'wp_head', array( $this, 'render_frontpage') );
        add_action( 'admin_menu', array($this, 'add_fpc_admin_page'));
        add_action( 'admin_init', array($this, 'page_init'));
        add_shortcode('render_frontpage', array($this, 'render_frontcategories'));
    }

    /**
     * Add options page
     */
    public function add_fpc_admin_page() {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Frontpage Categories',
            'manage_options',
            'my-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option( 'fpc_settings' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Front Page Settings</h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'fpc_option_group' );
                do_settings_sections( 'my-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {
        register_setting(
            'fpc_option_group', // Option group
            'fpc_settings', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'settings', // ID
            'Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );

        add_settings_field(
            'categoryies',
            'Add categories',
            array( $this, 'categories_callback' ),
            'my-setting-admin',
            'settings',
            array (
            	'setting_name' => 'categories',
            )
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * disabled - add back
     */
    public function sanitize( $input ) {
        $new_input = array();

        //if( isset( $input['categories'] ) )
        //    $new_input['categories'] = sanitize_text_field( $input['categories'] );

        return $input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info() {
        print 'Choose the categories you wish to appear on the front page.';
        echo '<p>Your current categories are:</p><ul>';
	    foreach ($this->options as $key => $value) {
	    	echo '<li>'.$value.'</li>';
	    }
	    echo '</ul>';
    }

    /**
     * Get the settings option array and print values
     */
    public function categories_callback() {

    	$map_cats = function($n) {
    		$v = array($n->slug, $n->name);
	    	return $v;
	    };

    	$categories_array = get_categories();
		$categories_list = array_map($map_cats, $categories_array);

    	echo '<select multiple="true" id="'.$args['setting_name'].'" name="fpc_settings['.$args['setting_name'].']" >';
    	foreach ($categories_list as $arr) {
			printf(
	            '<option value="%1$s" %2$s>%3$s</option>',
	            $arr[0],
	            selected( $arr[0], $args['value'], FALSE ), // this doesn't work???
	            $arr[1]
	        );
	}
    	echo '</select>';

        // TODO: selected option on admin page
    }


    /**
     * This will output the front page
     * TODO: extract into template
     */
    public function render_frontcategories() {
		if ( is_home() ) {
		    $carr = get_option('fpc_settings');
		    //echo $carr['categories'];
		    ?>
		    <div class="row">
			  <div id="newsbox" class="col-sm-12">
			    <?php if ( function_exists( 'meteor_slideshow' ) ) { meteor_slideshow( "main-page", "" ); } ?>
			  </div>
			</div>

		<?php foreach ($carr as $key => $value) {
			?>
			<div class="row">
			  <div class="col-sm-6"> <!-- col1 -->
			    <div id="newsbox" class="col-sm-12">
			          <?php
			            $args = array(
			              'category_name' => $value
			            );
			            $query = new WP_Query($args);

			            if ($query->have_posts()) {
			            	$category = get_the_category();
			            ?>

			            <h2><?php echo $category[0]->name; ?></h2>
			        		<div class="panel panel-default">
			         			<div class="panel-body">
			         	<?php
			              while ($query->have_posts()) {
			                $query->the_post();
			                ?>

			                <span class="pull-left img-pad-left">
			                  <?php  if ( has_post_thumbnail() ) {
			                    the_post_thumbnail('thmbCroppedSmll');
			                  } ?>
			                </span>
			                <div class="entry-summary gutter-bottom clearfix">
			                  <h3><a href="<?php the_permalink(); ?>" id="post-<?php the_ID(); ?>"><?php the_title(); ?></a></h3>
			                  <?php the_excerpt(); ?>
			                </div>

			              <?php
			            }
			          } else {
			          ?>
			            <h3>No posts found under "<?php echo $page_slug; ?>"</h3>
			          <?php
			          }
			          ?>
			         </div>
			       </div>
			    </div>
			  </div>
			<?php
			}
		
		} else {
		    echo 'damn';
		}
	}


}


 $frontpage_categories = new Frontpage_Categories();