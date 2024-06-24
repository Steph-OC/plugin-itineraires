document.addEventListener('DOMContentLoaded', function () {
    console.log('admin-trail-map.js is loaded');

    var modal = document.getElementById('readmeModal');
    var overlay = document.getElementById('readmeOverlay');
    var btn = document.getElementById('open-readme-modal');
    var span = document.getElementsByClassName('readme-close')[0];
    var content = document.getElementById('readmeContent');

    btn.onclick = function (event) {
        event.preventDefault(); // Empêche le lien de rediriger la page
        fetchReadmeContent();
        modal.style.display = 'block';
        overlay.style.display = 'block';
    }

    span.onclick = function () {
        modal.style.display = 'none';
        overlay.style.display = 'none';
    }

    window.onclick = function (event) {
        if (event.target == overlay) {
            modal.style.display = 'none';
            overlay.style.display = 'none';
        }
    }

    function fetchReadmeContent() {
        fetch(pluginDirUrl.readmeUrl)
            .then(response => response.text())
            .then(data => {
                content.textContent = data;
            })
            .catch(error => {
                content.textContent = 'Erreur lors du chargement du fichier readme.';
            });
    }
});
