<?php
if (!class_exists('My_Trail_Map')) {

    class My_Trail_Map
    {

        public function __construct()
        {
            add_shortcode('trail_map', array($this, 'render_map'));
        }

        public function run()
        {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        }

        public function enqueue_scripts()
        {
            // Enqueue Leaflet CSS
            wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet/dist/leaflet.css');

            // Enqueue Leaflet JS
            wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet/dist/leaflet.js', array(), null, true);

            // Enqueue Leaflet GPX JS
            wp_enqueue_script('leaflet-gpx-js', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.4.0/gpx.min.js', array('leaflet-js'), null, true);

            // Enqueue FontAwesome CSS
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');

            // Enqueue Google Fonts
            wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap', false);

            // Enqueue Trail Map JS only if the shortcode is present on the page
            if (is_page() && has_shortcode(get_post()->post_content, 'trail_map')) {
                wp_enqueue_script('trail-map', plugin_dir_url(__FILE__) . '../js/trail-map.js', array('leaflet-js', 'leaflet-gpx-js'), null, true);
            }

            // Enqueue front-end CSS
            wp_enqueue_style('my-plugin-css', plugin_dir_url(__FILE__) . '../assets/css/style.css');

            // Localize script to pass the plugin directory URL and map settings
            $map_settings = get_option('trail_map_settings', array(
                'latitude' => '43.2743',
                'longitude' => '3.16982',
                'zoom' => '13'
            ));

            $button_color = get_option('trail_map_button_color', '#0073aa'); // Default WordPress button color
            $button_text_color = get_option('trail_map_button_text_color', '#ffffff');
            $button_hover_color = get_option('trail_map_button_hover_color', '#005177');
            $button_focus_color = get_option('trail_map_button_focus_color', '#005177');

            wp_localize_script('trail-map', 'pluginDirUrl', array(
                'url' => trailingslashit(plugin_dir_url(__FILE__) . '../'),
                'mapSettings' => $map_settings,
                'buttonColor' => $button_color,
                'buttonTextColor' => $button_text_color,
                'buttonHoverColor' => $button_hover_color,
                'buttonFocusColor' => $button_focus_color,
                'showAllButton' => get_option('trail_map_show_all_button', true) // Ajoutez cette ligne
            ));
        }

        public function render_map($atts)
        {
            $gpx_files = get_option('trail_map_gpx_files', array());
            $show_all_button = get_option('trail_map_show_all_button', 1);

            // Filtrer les fichiers visibles
            $gpx_files = array_filter($gpx_files, function ($file) {
                return isset($file['visible']) && $file['visible'];
            });

            if (empty($gpx_files)) {
                return 'No GPX files available.';
            }

            $section_title = get_option('trail_map_section_title', 'Trail Map');
            $section_title_color = get_option('trail_map_section_title_color', '#000000');

            $upload_dir = wp_upload_dir();
            $gpx_file_urls = array_map(function ($file) use ($upload_dir) {
                return [
                    'name' => isset($file['title']) ? $file['title'] : '',
                    'url' => $upload_dir['baseurl'] . '/gpx/' . (isset($file['name']) ? $file['name'] : ''),
                    'description' => isset($file['description']) ? $file['description'] : '',
                    'distance' => isset($file['distance']) ? $file['distance'] : '',
                    'difficulty' => isset($file['difficulty']) ? $file['difficulty'] : '',
                    'duration' => isset($file['duration']) ? $file['duration'] : '',
                    'precautions' => isset($file['precautions']) ? $file['precautions'] : ''
                ];
            }, $gpx_files);

            $gpx_file_urls = array_filter($gpx_file_urls);

            ob_start();
?>
            <h2 class="plugin-section-title" style="color: <?php echo esc_attr($section_title_color); ?>;"><?php echo esc_html($section_title); ?></h2>
            <p class="geoloc-explanation">Cliquez sur l'icône de géolocalisation pour trouver votre position sur la carte. L'icône devient verte lorsque la géolocalisation est active.</p>
            <div id="trail-map-controls">
                <?php if ($show_all_button) : ?>
                    <button id="show-all">Tous les itinéraires</button>
                <?php endif; ?>


                <?php foreach ($gpx_file_urls as $file) : ?>
                    <button class="show-trail" data-url="<?php echo esc_url($file['url']); ?>" data-description="<?php echo esc_html($file['description']); ?>" data-distance="<?php echo esc_html($file['distance']); ?>" data-difficulty="<?php echo esc_html($file['difficulty']); ?>" data-duration="<?php echo esc_html($file['duration']); ?>" data-precautions="<?php echo esc_html($file['precautions']); ?>"><?php echo esc_html($file['name']); ?></button>
                <?php endforeach; ?>
            </div>
            <div id="map" style="height: 600px;"></div>
            <div id="trail-description" class="trail-description">
                <h3>Description de l'itinéraire</h3>
                <p id="description"></p>
                <p id="distance"></p>
                <p id="difficulty"></p>
                <p id="duration"></p>
                <p id="precautions"></p>
            </div>
            <script>
                var gpxFiles = <?php echo json_encode($gpx_file_urls); ?>;
                var pluginDirUrl = "<?php echo trailingslashit(plugin_dir_url(__FILE__) . '../'); ?>";
                var mapSettings = <?php echo json_encode(get_option('trail_map_settings', array(
                                        'latitude' => '43.2743',
                                        'longitude' => '3.16982',
                                        'zoom' => '13'
                                    ))); ?>;
                console.log("pluginDirUrl in PHP:", pluginDirUrl);
            </script>
<?php
            return ob_get_clean();
        }
    }
}
