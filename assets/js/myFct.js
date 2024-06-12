console.log("je suis là");

function requestDelete(button, url) {
  console.log(button);
  button.disabled = true; // Désactiver le bouton

  // Appeler fetchData pour effectuer la requête
  fetchData(url);
}

function fetchData(url) {
  // Création de l'objet XMLHttpRequest
  var xhr = new XMLHttpRequest();

  // Configuration de la requête avec la méthode GET et l'URL cible
  xhr.open("GET", url, true);

  // Gestion de la réponse
  xhr.onload = function () {
    if (xhr.status >= 200 && xhr.status < 400) {
      // Succès de la requête
      var data = JSON.parse(xhr.responseText);
      showMessage("alertSuccess", data.response);
      console.log(data); // Affichage des données dans la console (à adapter selon le besoin)
      // Manipulation des données ou mise à jour de l'interface utilisateur
    } else {
      // Erreur pendant la requête
      console.error("Request failed with status " + xhr.status);
    }
  };

  // Gestion d'erreurs réseau
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
 * @param {number} duration - La durée en millisecondes pendant laquelle le message sera visible (par défaut 3000ms).
 */
function showMessage(elementId, message, duration = 6000) {
  // Trouver l'élément par ID
  var element = document.getElementById(elementId);

  console.log(element);
  // Vérifier si l'élément existe
  if (element) {
    element.classList.remove("d-none");

    element.style.display = "block";

    element.innerText = message;

    // Utiliser setTimeout pour retirer le message après la durée spécifiée
    setTimeout(function () {
      element.innerText = "";
    }, duration);
  } else {
    console.error('Element with ID "' + elementId + '" not found.');
  }
}
