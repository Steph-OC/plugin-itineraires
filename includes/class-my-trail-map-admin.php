<?php
if (!class_exists('My_Trail_Map_Admin')) {

    class My_Trail_Map_Admin
    {

        public function run()
        {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_post_upload_gpx', array($this, 'handle_gpx_upload'));
            add_action('admin_post_delete_gpx_file', array($this, 'handle_delete_gpx_file'));
            add_action('admin_post_edit_gpx_file', array($this, 'handle_edit_gpx_file'));
            add_action('admin_post_save_map_settings', array($this, 'save_map_settings'));
            add_action('admin_post_save_section_title', array($this, 'handle_save_section_title'));
            add_action('admin_post_toggle_gpx_visibility', array($this, 'handle_toggle_gpx_visibility'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
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

            add_submenu_page(
                null,
                'Modifier l\'itinéraire GPX',
                'Modifier l\'itinéraire GPX',
                'manage_options',
                'trail-map-edit',
                array($this, 'create_edit_page')
            );
        }

        public function create_admin_page()
        {
            // Afficher un message de confirmation en fonction de l'action effectuée
            if (isset($_GET['message'])) {
                $message = '';
                switch ($_GET['message']) {
                    case 'updated':
                        $message = 'Itinéraire mis à jour avec succès.';
                        break;
                    case 'deleted':
                        $message = 'Fichier GPX supprimé avec succès.';
                        break;
                    case 'shown':
                        $message = 'Fichier GPX affiché avec succès.';
                        break;
                    case 'hidden':
                        $message = 'Fichier GPX masqué avec succès.';
                        break;
                    case 'uploaded':
                        $message = 'Fichier GPX téléversé avec succès.';
                        break;
                    case 'map_settings_updated':
                        $message = 'Paramètres de la carte enregistrés avec succès.';
                        break;
                    case 'section_title_updated':
                        $message = 'Personnalisation enregistrée avec succès.';
                        break;
                }

                if ($message) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
                }
            }

            $map_settings = get_option('trail_map_settings', array(
                'latitude' => '43.2743',
                'longitude' => '3.16982',
                'zoom' => '13'
            ));
            $section_title = get_option('trail_map_section_title', 'Trail Map - Gestion des itinéraires');
            $section_title_color = get_option('trail_map_section_title_color', '#000000');
            $button_color = get_option('trail_map_button_color', '#0073aa'); // Default WordPress button color
            $button_text_color = get_option('trail_map_button_text_color', '#ffffff');
            $button_hover_color = get_option('trail_map_button_hover_color', '#005177');
            $button_focus_color = get_option('trail_map_button_focus_color', '#005177');
            $show_all_button = get_option('trail_map_show_all_button', 1); // Récupère l'option pour le bouton "Tous les itinéraires"
?>
            <div class="wrap trail-map-admin">
                <h1 class="plugin-title">Trail Map - Gestion des itinéraires</h1>

                <div class="section-plugin">
                    <h2 class="plugin-h2">Shortcode</h2>
                    <p>Utilisez le shortcode suivant pour afficher la carte des itinéraires sur vos pages ou articles :</p>
                    <code id="trail-map-shortcode">[trail_map]</code> <button id="copy-shortcode" class="button-copy">Copier le shortcode</button>
                </div>

                <div class="section-plugin">
                    <h2 class="plugin-h2">Fichiers enregistrés</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th scope="col">Titre</th>
                                <th scope="col">Nom du fichier</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $gpx_files = get_option('trail_map_gpx_files', array());
                            if (!empty($gpx_files)) {
                                foreach ($gpx_files as $index => $file) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($file['title']) . '</td>';
                                    echo '<td>' . esc_html($file['name']) . '</td>';
                                    echo '<td>';
                                    echo '<a href="' . esc_url(admin_url('admin-post.php?action=toggle_gpx_visibility&index=' . $index)) . '" class="button toggle-visibility">';
                                    echo $file['visible'] ? 'Masquer' : 'Afficher';
                                    echo '</a> ';
                                    echo '<a href="' . esc_url(admin_url('admin.php?page=trail-map-edit&edit=' . $index)) . '" class="button">Modifier</a> ';
                                    echo '<a href="' . esc_url(admin_url('admin-post.php?action=delete_gpx_file&file=' . urlencode($file['name']))) . '" class="button">Supprimer</a>';
                                    echo '</td>';
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
                    <h2 class="plugin-h2">Nouvel itinéraire</h2>
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
                                    <small class="form-text">Ce titre servira de libellé du bouton itinéraire.</small>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Nombre de kilomètres</th>
                                <td><input type="text" name="trail_map_gpx_distance" />
                                    <small class="form-text">Kms</small>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Niveau de difficulté</th>
                                <td>
                                    <select name="trail_map_gpx_difficulty">
                                        <option value="facile">Facile</option>
                                        <option value="moyen">Moyen</option>
                                        <option value="difficile">Difficile</option>
                                        <option value="tres-difficile">Très difficile</option>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Durée de la randonnée</th>
                                <td><input type="text" name="trail_map_gpx_duration" /></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Description de l'itinéraire</th>
                                <td><textarea name="trail_map_gpx_description" rows="4" cols="50"></textarea></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Précautions à prendre lors de cet itinéraire</th>
                                <td><textarea name="trail_map_gpx_precautions" rows="4" cols="50"></textarea></td>
                            </tr>
                        </table>
                        <?php submit_button('Enregistrer cette itinéraire'); ?>
                    </form>
                </div>

                <div class="section-plugin">
                    <h2 class="plugin-h2">Personnalisation</h2>
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
                            <tr valign="top">
                                <th scope="row">Afficher le bouton "Tous les itinéraires"</th>
                                <td>
                                    <input type="checkbox" name="trail_map_show_all_button" <?php checked($show_all_button, 1); ?> />
                                    <small class="form-text">Cochez cette case pour afficher le bouton "Tous les itinéraires".</small>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Couleur des boutons des itinéraires</th>
                                <td>
                                    <input type="color" name="trail_map_button_color" value="<?php echo esc_attr($button_color); ?>" />
                                    <small class="form-text">Choisissez la couleur des boutons des itinéraires.</small>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Couleur du texte des boutons</th>
                                <td>
                                    <input type="color" name="trail_map_button_text_color" value="<?php echo esc_attr($button_text_color); ?>" />
                                    <small class="form-text">Choisissez la couleur du texte des boutons des itinéraires.</small>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Couleur de hover des boutons</th>
                                <td>
                                    <input type="color" name="trail_map_button_hover_color" value="<?php echo esc_attr($button_hover_color); ?>" />
                                    <small class="form-text">Choisissez la couleur des boutons des itinéraires lorsque vous passer la souris dessus.</small>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Couleur de focus des boutons</th>
                                <td>
                                    <input type="color" name="trail_map_button_focus_color" value="<?php echo esc_attr($button_focus_color); ?>" />
                                    <small class="form-text">Choisissez la couleur des boutons des itinéraires lorsqu'ils sont activés.</small>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button('Enregistrer la personnalisation'); ?>
                    </form>
                </div>

                <div class="section-plugin">
                    <h2 class="plugin-h2">Paramètres de la carte</h2>
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
                        <p>Pour obtenir les coordonnées GPS de n'importe quel endroit, vous pouvez utiliser le site <a href="https://www.latlong.net/" target="_blank">LatLong.net</a>.</p>
                        <?php submit_button('Enregistrer les paramètres'); ?>
                    </form>
                </div>

                <div class="section-plugin">
                    <h2 class="plugin-h2">Documentation</h2>
                    <p>Pour plus d'informations sur l'utilisation du plugin, veuillez consulter le <a href="#" id="open-readme-modal">fichier readme</a>.</p>
                </div>

                <!-- L'overlay -->
                <div id="readmeOverlay" class="readme-overlay"></div>

                <!-- La modale -->
                <div id="readmeModal" class="readme-modal">
                    <div class="readme-modal-content">
                        <span class="readme-close">&times;</span>
                        <pre id="readmeContent"></pre>
                    </div>
                </div>
            </div>
        <?php
        }

        public function create_edit_page()
        {
            if (!isset($_GET['edit']) || !is_numeric($_GET['edit'])) {
                wp_die('Paramètre d\'édition invalide.');
            }

            $index = intval($_GET['edit']);
            $gpx_files = get_option('trail_map_gpx_files', array());

            if (!isset($gpx_files[$index])) {
                wp_die('Fichier GPX non trouvé.');
            }

            $file = $gpx_files[$index];
        ?>
            <div class="wrap trail-map-admin">
                <h1 class="plugin-title">Modifier l'itinéraire GPX</h1>
                <a href="<?php echo admin_url('admin.php?page=trail-map'); ?>" class="button back-button">Retour à la gestion des itinéraires</a>
                <form method="post" action="<?php echo admin_url('admin-post.php?action=edit_gpx_file'); ?>">
                    <?php wp_nonce_field('edit_gpx_file', 'edit_gpx_file_nonce'); ?>
                    <input type="hidden" name="trail_map_gpx_index" value="<?php echo esc_attr($index); ?>" />
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Titre de l'itinéraire</th>
                            <td><input type="text" name="trail_map_gpx_title" value="<?php echo esc_attr($file['title']); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Description de l'itinéraire</th>
                            <td><textarea name="trail_map_gpx_description" rows="4" cols="50"><?php echo esc_textarea($file['description']); ?></textarea></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Nombre de kilomètres</th>
                            <td><input type="text" name="trail_map_gpx_distance" value="<?php echo esc_attr($file['distance']); ?>" />
                                <small class="form-text">Kms</small>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Niveau de difficulté</th>
                            <td>
                                <select name="trail_map_gpx_difficulty">
                                    <option value="facile" <?php selected($file['difficulty'], 'facile'); ?>>Facile</option>
                                    <option value="moyen" <?php selected($file['difficulty'], 'moyen'); ?>>Moyen</option>
                                    <option value="difficile" <?php selected($file['difficulty'], 'difficile'); ?>>Difficile</option>
                                    <option value="tres-difficile" <?php selected($file['difficulty'], 'tres-difficile'); ?>>Très difficile</option>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Durée de la randonnée</th>
                            <td><input type="text" name="trail_map_gpx_duration" value="<?php echo esc_attr($file['duration']); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Précautions à prendre lors de cet itinéraire</th>
                            <td><textarea name="trail_map_gpx_precautions" rows="4" cols="50"><?php echo esc_textarea($file['precautions']); ?></textarea></td>
                        </tr>
                    </table>
                    <?php submit_button('Enregistrer les modifications'); ?>
                </form>
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
            $button_color = sanitize_hex_color($_POST['trail_map_button_color']); // Save button color
            $button_text_color = sanitize_hex_color($_POST['trail_map_button_text_color']); // Save button text color
            $button_hover_color = sanitize_hex_color($_POST['trail_map_button_hover_color']); // Save button hover color
            $button_focus_color = sanitize_hex_color($_POST['trail_map_button_focus_color']); // Save button focus color
            $show_all_button = isset($_POST['trail_map_show_all_button']) ? 1 : 0; // Capture la valeur de la case à cocher

            update_option('trail_map_section_title', $section_title);
            update_option('trail_map_section_title_color', $section_title_color);
            update_option('trail_map_button_color', $button_color); // Save button color
            update_option('trail_map_button_text_color', $button_text_color); // Save button text color
            update_option('trail_map_button_hover_color', $button_hover_color); // Save button hover color
            update_option('trail_map_button_focus_color', $button_focus_color); // Save button focus color
            update_option('trail_map_show_all_button', $show_all_button);

            wp_redirect(admin_url('admin.php?page=trail-map&message=section_title_updated'));
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
                $uploaded_description = sanitize_textarea_field($_POST['trail_map_gpx_description']);
                $uploaded_distance = sanitize_text_field($_POST['trail_map_gpx_distance']);
                $uploaded_difficulty = sanitize_text_field($_POST['trail_map_gpx_difficulty']);
                $uploaded_duration = sanitize_text_field($_POST['trail_map_gpx_duration']);
                $uploaded_precautions = sanitize_textarea_field($_POST['trail_map_gpx_precautions']);

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
                            'title' => $uploaded_title,
                            'description' => $uploaded_description,
                            'distance' => $uploaded_distance,
                            'difficulty' => $uploaded_difficulty,
                            'duration' => $uploaded_duration,
                            'precautions' => $uploaded_precautions,
                            'visible' => true // par défaut, l'itinéraire est visible
                        );
                        update_option('trail_map_gpx_files', $gpx_files);
                    } else {
                        wp_die('Error moving uploaded file.');
                    }
                } else {
                    wp_die('Upload error: ' . $uploaded_file['error']);
                }
            }
            wp_redirect(admin_url('admin.php?page=trail-map&message=uploaded'));
            exit;
        }

        public function handle_edit_gpx_file()
        {
            if (!isset($_POST['edit_gpx_file_nonce']) || !wp_verify_nonce($_POST['edit_gpx_file_nonce'], 'edit_gpx_file')) {
                wp_die('Nonce verification failed');
            }

            if (!isset($_POST['trail_map_gpx_index']) || !is_numeric($_POST['trail_map_gpx_index'])) {
                wp_die('Paramètre d\'édition invalide.');
            }

            $index = intval($_POST['trail_map_gpx_index']);
            $gpx_files = get_option('trail_map_gpx_files', array());

            if (!isset($gpx_files[$index])) {
                wp_die('Fichier GPX non trouvé.');
            }

            $gpx_files[$index] = array(
                'name' => $gpx_files[$index]['name'],
                'title' => sanitize_text_field($_POST['trail_map_gpx_title']),
                'description' => sanitize_textarea_field($_POST['trail_map_gpx_description']),
                'distance' => sanitize_text_field($_POST['trail_map_gpx_distance']),
                'difficulty' => sanitize_text_field($_POST['trail_map_gpx_difficulty']),
                'duration' => sanitize_text_field($_POST['trail_map_gpx_duration']),
                'precautions' => sanitize_textarea_field($_POST['trail_map_gpx_precautions']),
                'visible' => $gpx_files[$index]['visible'] // conserve l'état de visibilité
            );

            update_option('trail_map_gpx_files', $gpx_files);

            wp_redirect(admin_url('admin.php?page=trail-map&message=updated'));
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
            wp_redirect(admin_url('admin.php?page=trail-map&message=deleted'));
            exit;
        }

        public function handle_toggle_gpx_visibility()
        {
            if (!isset($_GET['index']) || !is_numeric($_GET['index'])) {
                wp_die('Paramètre d\'édition invalide.');
            }

            $index = intval($_GET['index']);
            $gpx_files = get_option('trail_map_gpx_files', array());

            if (!isset($gpx_files[$index])) {
                wp_die('Fichier GPX non trouvé.');
            }

            $gpx_files[$index]['visible'] = !$gpx_files[$index]['visible'];
            update_option('trail_map_gpx_files', $gpx_files);

            $message = $gpx_files[$index]['visible'] ? 'shown' : 'hidden';
            wp_redirect(admin_url('admin.php?page=trail-map&message=' . $message));
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

            wp_redirect(admin_url('admin.php?page=trail-map&message=map_settings_updated'));
            exit;
        }

        public function enqueue_admin_styles()
        {
            wp_enqueue_style('my-plugin-admin-css', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css');
            wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap', false);
        }

        public function enqueue_admin_scripts()
        {
            $screen = get_current_screen();
            if ($screen->id === 'toplevel_page_trail-map') {
                wp_enqueue_script('my-plugin-admin-js', plugin_dir_url(__FILE__) . '../js/trail-map.js', array('jquery'), null, true);
                wp_enqueue_script('my-plugin-admin-admin-js', plugin_dir_url(__FILE__) . '../js/admin-trail-map.js', array('jquery'), null, true);
                wp_localize_script('my-plugin-admin-js', 'pluginDirUrl', array(
                    'buttonColor' => get_option('trail_map_button_color', '#0073aa'),
                    'buttonTextColor' => get_option('trail_map_button_text_color', '#ffffff'),
                    'buttonHoverColor' => get_option('trail_map_button_hover_color', '#005177'),
                    'buttonFocusColor' => get_option('trail_map_button_focus_color', '#005177'),
                    'sectionTitleColor' => get_option('trail_map_section_title_color', '#000000'),
                    'readmeUrl' => plugin_dir_url(__FILE__) . '../README.md'
                ));
            }
        }
    }
}
?>