<?php
if (!class_exists('My_Trail_Map')) {

    class My_Trail_Map {

        public function __construct() {
            add_shortcode('trail_map', array($this, 'render_map'));
        }

        public function run() {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        }

        public function enqueue_scripts() {
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

            // Enqueue Trail Map JS
            wp_enqueue_script('trail-map', plugin_dir_url(__FILE__) . '../js/trail-map.js', array('leaflet-js', 'leaflet-gpx-js'), null, true);

            // Enqueue front-end CSS
            wp_enqueue_style('my-plugin-css', plugin_dir_url(__FILE__) . '../assets/css/style.css');

            // Localize script to pass the plugin directory URL and map settings
            $map_settings = get_option('trail_map_settings', array(
                'latitude' => '43.2743',
                'longitude' => '3.16982',
                'zoom' => '13'
            ));

            wp_localize_script('trail-map', 'pluginDirUrl', array(
                'url' => trailingslashit(plugin_dir_url(__FILE__) . '../'),
                'mapSettings' => $map_settings
            ));
        }

        public function render_map($atts) {
            $gpx_files = get_option('trail_map_gpx_files', array());

            if (empty($gpx_files)) {
                return 'No GPX files available.';
            }

            $section_title = get_option('trail_map_section_title', 'Trail Map'); // Récupérer le titre de la section
            $section_title_color = get_option('trail_map_section_title_color', '#000000'); // Récupérer la couleur du titre

            // URL des fichiers GPX
            $upload_dir = wp_upload_dir();
            $gpx_file_urls = array_map(function($file) use ($upload_dir) {
                if (is_array($file) && isset($file['name']) && isset($file['title'])) {
                    return [
                        'name' => $file['title'],
                        'url' => $upload_dir['baseurl'] . '/gpx/' . $file['name']
                    ];
                }
                return null;
            }, $gpx_files);

            // Filtrer les valeurs nulles résultant d'une mauvaise structure de données
            $gpx_file_urls = array_filter($gpx_file_urls);

            ob_start();
            ?>
            <h2 class="plugin-title" style="color: <?php echo esc_attr($section_title_color); ?>;"><?php echo esc_html($section_title); ?></h2> <!-- Afficher le titre de la section avec la couleur choisie -->
             <p class="geoloc-explanation">Cliquez sur l'icône de géolocalisation pour trouver votre position sur la carte. L'icône devient verte lorsque la géolocalisation est active.</p> <!-- Explication de la géolocalisation -->
            <div id="trail-map-controls">
                <button id="show-all">Tous les itinéraires</button>
                <?php foreach ($gpx_file_urls as $file) : ?>
                    <button class="show-trail" data-url="<?php echo esc_url($file['url']); ?>"><?php echo esc_html($file['name']); ?></button>
                <?php endforeach; ?>
                           </div>
        
            <div id="map" style="height: 600px;"></div>
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
?>
