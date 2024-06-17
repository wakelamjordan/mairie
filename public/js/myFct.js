/**
 * Effectue une requête GET AJAX vers l'URL spécifiée.
 *
 * @param {string} url - L'URL cible pour la requête.
 */
function fetchDataGet(url) {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", url, true);

  // Gestion de la réponse de la requête
  xhr.onload = function () {
    if (xhr.status >= 200 && xhr.status < 400) {
      // Succès de la requête, traiter les données
      var data = JSON.parse(xhr.responseText);
      showMessage("alertError", data.message.error);
      console.log(data); // Affichage des données dans la console
      // Manipulation des données ou mise à jour de l'interface utilisateur
    } else {
      // Erreur lors de la requête
      console.error("Request failed with status " + xhr.status);
    }
  };

  // Gestion d'erreur réseau
  xhr.onerror = function () {
    console.error("Network error occurred");
  };

  // Envoi de la requête
  xhr.send();
}

/**
 * Effectue une requête POST AJAX vers l'URL spécifiée.
 *
 * @param {string} url - L'URL cible pour la requête.
 */
function fetchDataPost(url) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);

  // Gestion de la réponse de la requête
  xhr.onload = function () {
    if (xhr.status >= 200 && xhr.status < 400) {
      // Succès de la requête, traiter les données
      var data = JSON.parse(xhr.responseText);
      showMessage("alertSuccess", data.message.success);
      console.log(data.message); // Affichage des données dans la console
      // Manipulation des données ou mise à jour de l'interface utilisateur
    } else {
      // Erreur lors de la requête
      console.error("Request failed with status " + xhr.status);
    }
  };

  // Gestion d'erreur réseau
  xhr.onerror = function () {
    console.error("Network error occurred");
  };

  // Envoi de la requête
  xhr.send();
}

/**
 * Affiche un message dans un élément spécifié et le retire après un certain temps.
 *
 * @param {string} elementId - L'ID de l'élément où le message sera affiché.
 * @param {string} message - Le message à afficher dans l'élément.
 * @param {number} duration - La durée en millisecondes pendant laquelle le message sera visible (par défaut 6000ms).
 */
function showMessage(elementId, message, duration = 6000) {
  var element = document.getElementById(elementId);

  // Vérifier si l'élément existe
  if (element) {
    element.classList.remove("d-none");
    element.style.display = "block";
    element.innerText = message;

    // Utiliser setTimeout pour effacer le message après la durée spécifiée
    setTimeout(function () {
      element.innerText = "";
    }, duration);
  } else {
    console.error('Element with ID "' + elementId + '" not found.');
  }
}

/**
 * Effectue une requête POST AJAX vers l'URL spécifiée lors du clic sur un bouton.
 *
 * @param {HTMLButtonElement} button - Le bouton qui déclenche la requête.
 * @param {string} url - L'URL cible pour la requête.
 */
function requestEditProfile(button, url) {
  button.disabled = true; // Désactiver le bouton pour éviter les clics multiples
  fetchDataPost(url);
}

/**
 * Effectue une requête GET AJAX vers l'URL spécifiée lors du clic sur un bouton.
 *
 * @param {HTMLButtonElement} button - Le bouton qui déclenche la requête.
 * @param {string} url - L'URL cible pour la requête.
 */
function requestDelete(button, url) {
  button.disabled = true; // Désactiver le bouton pour éviter les clics multiples
  fetchDataGet(url);
}
