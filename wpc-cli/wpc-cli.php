<?php
/**
 * Plugin Name: WPC CLI
 * Plugin URL: https://youtube.com/c/wpcasts
 * Description: Custom WP-CLI Commands
 * Author: Alex Young (WPCasts)
 * Version: 1.0.0
 * Author URI: https://youtube.com/c/wpcasts
 */

if (defined('WP_CLI') && WP_CLI) {

  require_once plugin_dir_path(__FILE__) . '/vendor/fzaninotto/Faker/src/autoload.php';

  class WPC_CLI{

    public function generate_floorplans($args, $assoc_args){

      $amount = $assoc_args['amount'];

      $home_type_terms = get_terms([
        'taxonomy' => 'home_type',
        'hide_empty' => false
      ]);
      $home_type_ids = array_map(function ($home_type) {
        return $home_type->term_id;
      }, $home_type_terms);

      $home_feature_terms = get_terms([
        'taxonomy' => 'home_feature',
        'hide_empty' => false,
      ]);
      $home_feature_ids = array_map(function ($home_feature) {
        return $home_feature->term_id;
      }, $home_feature_terms);

      $faker = Faker\Factory::create();
      $progress = \WP_CLI\Utils\make_progress_bar('Generating Floorplans', $amount);

      for ($i=0; $i < $amount; $i++) { 
        
        $floorplan = wp_insert_post([
          'post_title' => 'The ' . $faker->firstName,
          'post_status' => 'publish',
          'post_type' => 'floorplan',
        ]);

        wp_set_object_terms($floorplan, array_rand(array_flip($home_type_ids), 1), 'home_type');
        wp_set_object_terms($floorplan, array_rand(array_flip($home_feature_ids), 3), 'home_feature');

        $possible_images = [37, 101, 102, 103, 104];

        $img_keys = [
          'field_5dfd548d72361', // featured image
          'field_5e140224a8906', // interior 1
          'field_5e140247a8907', // interior 2
          'field_5e140266a8908', // interior 3
          'field_5e14026ba8909', // interior 4
        ];

        foreach ($img_keys as $key) {
          update_field($key, array_rand(array_flip($possible_images), 1), $floorplan);
        }

        // beds
        update_field('field_5dfd51250dd87', $faker->numberBetween(2, 5), $floorplan);

        // bathrooms
        update_field('field_5dfd517f0dd88', $faker->numberBetween(1, 5), $floorplan);

        // square footage
        update_field('field_5dfd518b0dd89', $faker->numberBetween(1500, 3000), $floorplan);

        // starting price
        update_field('field_5e0248c3ece0e', $faker->numberBetween(250000, 400000), $floorplan);

        $progress->tick();

      }

      $progress->finish();
      WP_CLI::success($amount . ' Floorplans Generated!!!!');

    }

  }

  WP_CLI::add_command('wpc', 'WPC_CLI');

  // wp wpc generate_floorplans foo bar --amount=10

}