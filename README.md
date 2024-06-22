=== My Trail Map ===
Contributors: Stephanie Quibel
Tags: gpx, maps, trail, hiking, routes
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.0
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A custom plugin to display GPX trail maps on your WordPress site.

== Description ==

My Trail Map is a custom WordPress plugin designed to display GPX trail maps on your site. With this plugin, you can upload GPX files, display individual trails, and customize the appearance of your trail buttons.

= Features =
* Upload and manage GPX files.
* Display individual trail maps with descriptions, distances, difficulties, durations, and precautions.
* Customizable trail buttons (color, text color, hover color, focus color).
* Optional "Show All Trails" button to display all trails on a single map.
* Admin interface for managing trails and customizing display settings.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/my-trail-map` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->Trail Map screen to configure the plugin.
4. Add the `[trail_map]` shortcode to any page or post to display the trail map.

== Frequently Asked Questions ==

= How do I upload a GPX file? =

1. Navigate to the Trail Map settings page in the WordPress admin.
2. Use the "Upload a GPX File" section to upload your GPX file and fill in the trail details.
3. Click "Save" to add the GPX file to your trail list.

= How do I customize the trail button colors? =

1. Navigate to the Trail Map settings page in the WordPress admin.
2. Use the "Customization" section to select your desired button colors.
3. Click "Save" to apply the changes.

= How do I convert other file formats to GPX? =

If you have files in other formats (such as KML, TCX, etc.), you can convert them to GPX using the following methods:

1. **Using GPSBabel**:
   - Download and install GPSBabel from [here](https://www.gpsbabel.org/download.html).
   - Open GPSBabel.
   - In the `Input` tab, select the format of your source file (e.g., KML, TCX).
   - Click `File Name` and select the file you want to convert.
   - In the `Output` tab, select `GPX XML` as the output format.
   - Click `File Name` and choose where to save the converted GPX file.
   - Click `OK` to start the conversion.

2. **Using an online service**:
   - Visit [GPS Visualizer](https://www.gpsvisualizer.com/).
   - Click on `Convert a GPS file`.
   - Upload your source file using the `Choose File` button.
   - Select `GPX` as the output format.
   - Click `Convert` to convert the file and download the GPX file.

   Alternatively, you can use [MyGeodata Cloud](https://mygeodata.cloud/converter/):
   - Visit the website.
   - Click on `Upload your data` and upload your source file.
   - Select `GPX` as the output format.
   - Click `Convert Now` to start the conversion and download the GPX file.

Once you have your GPX file, you can upload it to the plugin as described above.

== Screenshots ==

1. **Admin Interface** - Manage your GPX files and customize the map display settings.
2. **Trail Map Display** - Example of a trail map displayed on a WordPress page.

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
* Initial release of My Trail Map plugin.

== License ==

This plugin is licensed under the GPLv2 or later. For more information, see [License URI](https://www.gnu.org/licenses/gpl-2.0.html).

== Author Information ==

This plugin was developed by Stephanie Quibel. For more information, visit [Stephanie Quibel's Professional Site](https://www.stephaniequibel.fr/).



== Description (Français) ==

My Trail Map est un plugin WordPress personnalisé conçu pour afficher des cartes de sentiers GPX sur votre site. Avec ce plugin, vous pouvez télécharger des fichiers GPX, afficher des itinéraires individuels et personnaliser l'apparence de vos boutons d'itinéraires.

= Fonctionnalités =
* Téléchargez et gérez des fichiers GPX.
* Affichez des cartes de sentiers individuels avec des descriptions, distances, difficultés, durées et précautions.
* Boutons d'itinéraires personnalisables (couleur, couleur du texte, couleur au survol, couleur au focus).
* Bouton optionnel "Tous les itinéraires" pour afficher tous les itinéraires sur une seule carte.
* Interface d'administration pour gérer les itinéraires et personnaliser les paramètres d'affichage.

== Installation ==

1. Téléchargez les fichiers du plugin dans le répertoire `/wp-content/plugins/my-trail-map`, ou installez le plugin directement via l'écran des plugins de WordPress.
2. Activez le plugin via l'écran 'Extensions' dans WordPress.
3. Utilisez l'écran Paramètres->Trail Map pour configurer le plugin.
4. Ajoutez le shortcode `[trail_map]` à n'importe quelle page ou article pour afficher la carte des itinéraires.

== Questions fréquentes ==

= Comment puis-je télécharger un fichier GPX ? =

1. Accédez à la page des paramètres Trail Map dans l'administration WordPress.
2. Utilisez la section "Téléverser un fichier GPX" pour téléverser votre fichier GPX et remplir les détails de l'itinéraire.
3. Cliquez sur "Enregistrer" pour ajouter le fichier GPX à votre liste d'itinéraires.

= Comment puis-je personnaliser les couleurs des boutons d'itinéraires ? =

1. Accédez à la page des paramètres Trail Map dans l'administration WordPress.
2. Utilisez la section "Personnalisation" pour sélectionner les couleurs souhaitées des boutons.
3. Cliquez sur "Enregistrer" pour appliquer les modifications.

= Comment puis-je convertir d'autres formats de fichiers en GPX ? =

Si vous avez des fichiers dans d'autres formats (comme KML, TCX, etc.), vous pouvez les convertir en GPX en utilisant les méthodes suivantes :

1. **Utiliser GPSBabel** :
   - Téléchargez et installez GPSBabel depuis [ce lien](https://www.gpsbabel.org/download.html).
   - Ouvrez GPSBabel.
   - Dans l'onglet `Input`, sélectionnez le format de votre fichier source (par exemple, KML, TCX).
   - Cliquez sur `File Name` et sélectionnez le fichier que vous souhaitez convertir.
   - Dans l'onglet `Output`, sélectionnez `GPX XML` comme format de sortie.
   - Cliquez sur `File Name` et choisissez où enregistrer le fichier GPX converti.
   - Cliquez sur `OK` pour démarrer la conversion.

2. **Utiliser un service en ligne** :
   - Allez sur [GPS Visualizer](https://www.gpsvisualizer.com/).
   - Cliquez sur `Convert a GPS file`.
   - Téléchargez votre fichier source en utilisant le bouton `Choose File`.
   - Sélectionnez `GPX` comme format de sortie.
   - Cliquez sur `Convert` pour convertir le fichier et télécharger le fichier GPX.

   Vous pouvez également utiliser [MyGeodata Cloud](https://mygeodata.cloud/converter/) :
   - Allez sur le site web.
   - Cliquez sur `Upload your data` et téléchargez votre fichier source.
   - Sélectionnez `GPX` comme format de sortie.
   - Cliquez sur `Convert Now` pour démarrer la conversion et télécharger le fichier GPX.

Une fois que vous avez votre fichier GPX, vous pouvez le télécharger dans le plugin comme décrit ci-dessus.

== Captures d'écran ==

1. **Interface d'administration** - Gérez vos fichiers GPX et personnalisez les paramètres d'affichage de la carte.
2. **Affichage de la carte des itinéraires** - Exemple d'une carte d'itinéraire affichée sur une page WordPress.

== Journal des modifications ==

= 1.0 =
* Première version.

== Avis de mise à jour ==

= 1.0 =
* Première version du plugin My Trail Map.

== Licence ==

Ce plugin est sous ou ultérieure. Pour plus d'informations, voir [URI de la licence](https://www.gnu.org/licenses/gpl-2.0.html).

== Informations sur l'auteur ==

Ce plugin a été développé par Stéphanie Quibel. Pour plus d'informations, visitez [le site professionnel de Stéphanie Quibel](https://www.stephaniequibel.fr/).
