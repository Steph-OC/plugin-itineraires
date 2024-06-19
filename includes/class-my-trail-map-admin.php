<?php
if (!class_exists('My_Trail_Map_Admin')) {

    class My_Trail_Map_Admin
    {

        public function run()
        {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_post_upload_gpx', array($this, 'handle_gpx_upload'));
            add_action('admin_post_delete_gpx_file', array($this, 'handle_delete_gpx_file'));
            add_action('admin_post_save_map_settings', array($this, 'save_map_settings'));
            add_action('admin_post_save_section_title', array($this, 'handle_save_section_title'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        }

        public function add_admin_menu()
        {
            add_menu_page(
                'Trail Map',
                'Trail Map',
                'manage_options',
                'trail-map',
                array($this, 'create_admin_page'),
                'dashicons-location-alt'
            );
        }

        
        public function create_admin_page()
        {
            $map_settings = get_option('trail_map_settings', array(
                'latitude' => '43.2743',
                'longitude' => '3.16982',
                'zoom' => '13'
            ));
            $section_title = get_option('trail_map_section_title', 'Trail Map - Gestion des itinéraires');
            $section_title_color = get_option('trail_map_section_title_color', '#000000');
        ?>
            <div class="wrap">
                <div class="section-plugin">
                    <h1>Trail Map - Gestion des itinéraires</h1>
                    <h2>Personnalisation</h2>
                    <form method="post" action="<?php echo admin_url('admin-post.php?action=save_section_title'); ?>">
                        <?php wp_nonce_field('save_section_title', 'save_section_title_nonce'); ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Titre de la section</th>
                                <td>
                                    <input type="text" name="trail_map_section_title" value="<?php echo esc_attr($section_title); ?>" />
                                    <small class="form-text">Entrez le titre de la section contenant le plugin.</small>
                                </td>
                            </tr>
                            <tr valign="top">
                        <th scope="row">Couleur du titre</th>
                        <td>
                            <input type="color" name="trail_map_section_title_color" value="<?php echo esc_attr($section_title_color); ?>" />
                            <small class="form-text">Choisissez la couleur du titre de la section.</small>
                        </td>
                    </tr>
                        </table>
                        <?php submit_button('Enregistrer le titre'); ?>
                    </form>
                </div>
        
                <div class="section-plugin">
                    <h2>Fichiers d'itinéraires GPX</h2>
                    <form method="post" action="<?php echo admin_url('admin-post.php?action=upload_gpx'); ?>" enctype="multipart/form-data">
                        <?php wp_nonce_field('upload_gpx', 'upload_gpx_nonce'); ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Téléverser un fichier GPX</th>
                                <td>
                                    <input type="file" name="trail_map_gpx_file" />
                                    <small class="form-text">Choisissez un fichier GPX à télécharger.</small>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Titre de l'itinéraire</th>
                                <td>
                                    <input type="text" name="trail_map_gpx_title" />
                                    <small class="form-text">Donnez un titre descriptif à l'itinéraire.</small>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button('Téléverser un fichier GPX'); ?>
                    </form>
                </div>
        
                <div class="section-plugin">
                    <h2>Fichiers enregistrés</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th scope="col">Titre</th>
                                <th scope="col">Nom du fichier</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $gpx_files = get_option('trail_map_gpx_files', array());
                            if (!empty($gpx_files)) {
                                foreach ($gpx_files as $file) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($file['title']) . '</td>';
                                    echo '<td>' . esc_html($file['name']) . '</td>';
                                    echo '<td><a href="' . esc_url(admin_url('admin-post.php?action=delete_gpx_file&file=' . urlencode($file['name']))) . '" class="supp">Supprimer</a></td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="3">Aucun fichier GPX disponible.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
        
                <div class="section-plugin">
                    <h2>Paramètres de la carte</h2>
                    <form method="post" action="<?php echo admin_url('admin-post.php?action=save_map_settings'); ?>">
                        <?php wp_nonce_field('save_map_settings', 'save_map_settings_nonce'); ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Latitude</th>
                                <td>
                                    <input type="text" name="trail_map_latitude" value="<?php echo esc_attr($map_settings['latitude']); ?>" />
                                    <small class="form-text">Entrez la latitude initiale pour centrer la carte.</small>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Longitude</th>
                                <td>
                                    <input type="text" name="trail_map_longitude" value="<?php echo esc_attr($map_settings['longitude']); ?>" />
                                    <small class="form-text">Entrez la longitude initiale pour centrer la carte.</small>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Zoom</th>
                                <td>
                                    <input type="text" name="trail_map_zoom" value="<?php echo esc_attr($map_settings['zoom']); ?>" />
                                    <small class="form-text">Entrez le niveau de zoom initial pour la carte.</small>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button('Enregistrer les paramètres'); ?>
                    </form>
                </div>
            </div>
        <?php
        }
  
        public function handle_save_section_title()
{
    if (!isset($_POST['save_section_title_nonce']) || !wp_verify_nonce($_POST['save_section_title_nonce'], 'save_section_title')) {
        wp_die('Nonce verification failed');
    }

    $section_title = sanitize_text_field($_POST['trail_map_section_title']);
    $section_title_color = sanitize_hex_color($_POST['trail_map_section_title_color']);
    update_option('trail_map_section_title', $section_title);
    update_option('trail_map_section_title_color', $section_title_color);

    wp_redirect(admin_url('admin.php?page=trail-map'));
    exit;
}




        public function handle_gpx_upload()
        {
            if (!isset($_POST['upload_gpx_nonce']) || !wp_verify_nonce($_POST['upload_gpx_nonce'], 'upload_gpx')) {
                wp_die('Nonce verification failed');
            }

            if (!empty($_FILES['trail_map_gpx_file']['name'])) {
                $uploaded_file = $_FILES['trail_map_gpx_file'];
                $uploaded_title = sanitize_text_field($_POST['trail_map_gpx_title']);

                if ($uploaded_file['error'] == UPLOAD_ERR_OK) {
                    $upload_dir = wp_upload_dir();
                    $target_dir = $upload_dir['basedir'] . '/gpx/';
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }
                    $target_file = $target_dir . basename($uploaded_file['name']);

                    if (move_uploaded_file($uploaded_file['tmp_name'], $target_file)) {
                        $gpx_files = get_option('trail_map_gpx_files', array());
                        $gpx_files[] = array(
                            'name' => basename($uploaded_file['name']),
                            'title' => $uploaded_title
                        );
                        update_option('trail_map_gpx_files', $gpx_files);
                    } else {
                        wp_die('Error moving uploaded file.');
                    }
                } else {
                    wp_die('Upload error: ' . $uploaded_file['error']);
                }
            }
            wp_redirect(admin_url('admin.php?page=trail-map'));
            exit;
        }

        public function handle_delete_gpx_file()
        {
            if (isset($_GET['file']) && !empty($_GET['file'])) {
                $file = urldecode($_GET['file']);
                $gpx_files = get_option('trail_map_gpx_files', array());
                $updated_gpx_files = array_filter($gpx_files, function ($gpx_file) use ($file) {
                    return $gpx_file['name'] !== $file;
                });
                update_option('trail_map_gpx_files', $updated_gpx_files);
                $upload_dir = wp_upload_dir();
                if (file_exists($upload_dir['basedir'] . '/gpx/' . $file)) {
                    unlink($upload_dir['basedir'] . '/gpx/' . $file);
                }
            }
            wp_redirect(admin_url('admin.php?page=trail-map'));
            exit;
        }

        public function save_map_settings()
        {
            if (!isset($_POST['save_map_settings_nonce']) || !wp_verify_nonce($_POST['save_map_settings_nonce'], 'save_map_settings')) {
                wp_die('Nonce verification failed');
            }
        
            $latitude = sanitize_text_field($_POST['trail_map_latitude']);
            $longitude = sanitize_text_field($_POST['trail_map_longitude']);
            $zoom = sanitize_text_field($_POST['trail_map_zoom']);
            $section_title = sanitize_text_field($_POST['trail_map_section_title']);
            $section_title_color = sanitize_hex_color($_POST['trail_map_section_title_color']);
        
            $map_settings = array(
                'latitude' => $latitude,
                'longitude' => $longitude,
                'zoom' => $zoom
            );
        
            update_option('trail_map_settings', $map_settings);
            update_option('trail_map_section_title', $section_title);
            update_option('trail_map_section_title_color', $section_title_color);
        
            wp_redirect(admin_url('admin.php?page=trail-map'));
            exit;
        }



        public function enqueue_admin_styles()
        {
            wp_enqueue_style('my-plugin-admin-css', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css');
            wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap', false);
        }
    }
}
