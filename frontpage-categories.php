<?php
/**
 * Plugin Name: Frontpage Categories
 * Plugin URI:
 * Description: Add and change frontpage categories
 * Version: 0.7
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
        print 'Choose the categories you wish to appear on the front page, up to a maximum of 4.';
    }

    /**
     * Get the settings option array and print values
     */
    public function categories_callback() {

      $cat_limit = 4;
      $map_cats = function($n) {
        $v = array($n->slug, $n->name);
        return $v;
      };

      $categories_array = get_categories();
      $categories_list = array_map($map_cats, $categories_array);

      echo '<ul>';
      for ($i=0; $i < $cat_limit ; $i++) {
        echo '<li><select id="fpc_settings" name="fpc_settings[]" >';
        echo '<option value="" ></option>';
        foreach ($categories_list as $arr) {
            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                $arr[0],
                selected( $arr[0], $this->options[$i], FALSE ), // this doesn't work???
                $arr[1]
            );
        }
        echo '</select></li>';

      }
      echo '</ul>';

        // TODO: selected option on admin page
    }


    /**
     * This will output the front page
     * TODO: extract into template
     */
    public function render_frontcategories() {
    if ( is_home() ) {
        $fpc_categories_array = get_option('fpc_settings');
        ?>
            <div class="row">
              <div id="newsbox" class="col-sm-12">
                  <?php if ( function_exists( 'meteor_slideshow' ) ) { meteor_slideshow( "main-page", "" ); } ?>
              </div>
            </div>
            <div class="row">
            <?php

                $modcount = 0;
                $col1_array = [
                            'start' => '<div id="col" class="col-sm-6">',
                            'inner_col' => '',
                            'end' => '</div>'
                            ];
                $col2_array = [
                            'start' => '<div id="col" class="col-sm-6">',
                            'inner_col' => '',
                            'end' => '</div>'
                            ];

                foreach ($fpc_categories_array as $category_slug) {
                  if ($category_slug != '') { // check if category is not empty
                      $modcount++;
                      if ($modcount % 2 != 0) {
                          // column one
                          $col1 = '<div id="newsbox" class="col-sm-12">';

                          $args = [ 'category_name' => $category_slug,
                                    'posts_per_page' => 3
                                  ];
                          $query = new WP_Query($args);

                          if ($query->have_posts()) {
                              $category = get_term_by( 'slug', $category_slug, 'category');

                              $col1 .= '<h2>'.$category->name.'</h2>';
                              $col1 .= '<div class="panel panel-default"><div class="panel-body">';
                              while ($query->have_posts()) {
                                  $query->the_post();
                                  $pid = get_the_ID();

                                  $col1 .= '<span class="pull-left img-pad-left">';
                                  if ( has_post_thumbnail() )
                                      $col1 .= get_the_post_thumbnail($pid, 'thmbCroppedSmll');
                                  $col1 .= '</span><div class="entry-summary gutter-bottom clearfix">';
                                  $col1 .= '<h3><a href="'.get_permalink().'" id="post-'.$pid.'">'.get_the_title().'</a></h3>';
                                  $col1 .= '<p>'.get_the_excerpt().'</p>';
                                  $col1 .= '</div>';
                              }

                          } else {
                              $col1 .=  '<h3>No posts found.</h3>';
                          }
                          $col1 .= '</div><p class="fpc__more-posts text-center"><a href="'.$category_slug.'">See more posts</a></p></div></div>';

                          // insert into col1 array
                          $col1_array['inner_col'] .= $col1;
                      } else {
                          // column two
                          $col2 = '<div id="newsbox" class="col-sm-12">';

                          $args = [ 'category_name' => $category_slug,
                                    'posts_per_page' => 3
                                  ];

                          $query = new WP_Query($args);
                          if ($query->have_posts()) {
                              $category = get_term_by( 'slug', $category_slug, 'category');

                              $col2 .= '<h2>'.$category->name.'</h2>';
                              $col2 .= '<div class="panel panel-default"><div class="panel-body">';
                              while ($query->have_posts()) {
                                  $query->the_post();
                                  $pid = get_the_ID();

                                  $col2 .= '<span class="pull-left img-pad-left">';
                                  if ( has_post_thumbnail() )
                                      $col2 .= get_the_post_thumbnail($pid, 'thmbCroppedSmll');
                                  $col2 .= '</span><div class="entry-summary gutter-bottom clearfix">';
                                  $col2 .= '<h3><a href="'.get_permalink().'" id="post-'.$pid.'">'.get_the_title().'</a></h3>';
                                  $col2 .= '<p>'.get_the_excerpt().'</p>';
                                  $col2 .= '</div>';
                              }

                          } else {
                              $col2 .=  '<h3>No posts found.</h3>';
                          }
                          $col2 .= '</div><p class="fpc__more-posts text-center"><a href="'.$category_slug.'">See more posts</a></p></div></div>';

                          // insert into col2 array
                          $col2_array['inner_col'] .= $col2;
                      }
                  }  // end category check
                }  // end foreach

              //print out array of column elements
              foreach ($col1_array as $section) {
                  echo $section;
              }

              foreach ($col2_array as $section) {
                  echo $section;
              }

            ?>
            </div> <!-- end row of categories -->
        <?php
    } else {
        echo 'No categories defined!';
    }
  }


} // end class


 $frontpage_categories = new Frontpage_Categories();


