/**
 * Affiche le spinner de chargement.
 */
function showSpinner() {
  document.getElementById("spinner").style.display = "block";
}

/**
 * Masque le spinner de chargement.
 */
function hideSpinner() {
  document.getElementById("spinner").style.display = "none";
}

/**
 * Effectue une requête AJAX vers l'URL spécifiée avec les options données.
 *
 * @param {string} url - L'URL cible pour la requête.
 * @param {string} method - La méthode HTTP pour la requête (GET, POST, DELETE).
 * @param {Object} data - Les données à envoyer avec la requête (pour POST et DELETE).
 * @param {function} callback - La fonction de rappel pour traiter la réponse de la requête.
 * @param {boolean} spinner - Indique si le spinner de chargement doit être affiché (par défaut true).
 */
function makeRequest(
  url,
  method = "GET",
  data = null,
  callback = null,
  spinner = true
) {
  let xhr = new XMLHttpRequest();
  xhr.open(method, url, true);

  // Affichage du spinner si nécessaire
  if (spinner) {
    showSpinner();
  }

  xhr.onload = function () {
    // console.log(xhr.status);
    hideSpinner();
    if (xhr.status >= 200 && xhr.status < 300) {
      callback(null, xhr.responseText); // Appel du callback avec succès
    } else {
      if (xhr.status === 401) {
        window.location.href = "/";
      }
      callback(xhr.statusText, null); // Appel du callback avec erreur
    }
  };

  xhr.onerror = function () {
    hideSpinner();
    callback("Erreur réseau", null); // Appel du callback en cas d'erreur réseau
  };

  // Configuration de l'en-tête pour les requêtes POST et DELETE
  if (method === "POST" || method === "DELETE") {
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest"); // Détecte une requête AJAX côté serveur
    xhr.send(JSON.stringify(data));
  } else {
    xhr.send(); // Envoi de la requête pour les méthodes GET
  }
}

// Exemple d'utilisation :

// // GET request
// makeRequest("https://exemple.com/api/data", "GET", null, function (err, data) {
//   if (err) {
//     console.error("Erreur :", err);
//     // Gérer l'erreur
//   } else {
//     console.log("Réponse du serveur :", data);
//     // Traiter la réponse
//   }
// });

// // POST request
// const postData = { param1: "valeur1", param2: "valeur2" };
// makeRequest("https://exemple.com/api/data", "POST", postData, function (err, data) {
//   if (err) {
//     console.error("Erreur :", err);
//     // Gérer l'erreur
//   } else {
//     console.log("Réponse du serveur :", data);
//     // Traiter la réponse
//   }
// });

// // DELETE request
// makeRequest("https://exemple.com/api/data/123", "DELETE", null, function (err, data) {
//   if (err) {
//     console.error("Erreur :", err);
//     // Gérer l'erreur
//   } else {
//     console.log("Réponse du serveur :", data);
//     // Traiter la réponse
//   }
// });
