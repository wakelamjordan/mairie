// Sélectionner le formulaire par son nom 'profil'
// const formProfil = document.querySelector("form[name='profil']");

// // Sélectionner le champ email dans le formulaire
// const input = document.querySelector("input[type='email']");

// // Vérifier que le formulaire existe sur la page
// if (formProfil) {
//   // Ajouter un gestionnaire d'événement pour l'événement de soumission du formulaire
//   formProfil.addEventListener("submit", (e) => {
//     // Empêcher le comportement par défaut de soumission du formulaire
//     e.preventDefault();

//     // Afficher dans la console pour débogage
//     console.log(formProfil);

//     // Vérifier si le champ email a une valeur
//     if (input.value) {
//       // Créer une nouvelle requête XMLHttpRequest
//       var xhr = new XMLHttpRequest();

//       // Ouvrir une connexion POST à l'URL spécifiée
//       xhr.open("POST", url, true);

//       // Créer un objet FormData pour envoyer les données du formulaire
//       var data = new FormData();
//       data.append("email", input.value);

//       // Gérer la réponse de la requête
//       xhr.onload = function () {
//         if (xhr.status >= 200 && xhr.status < 400) {
//           // Requête réussie, traiter les données reçues
//           var responseData = JSON.parse(xhr.responseText);
//           showMessage("alertSuccess", responseData.message.success);
//           console.log(responseData.message); // Afficher la réponse dans la console
//         } else {
//           // La requête a échoué, afficher une erreur
//           console.error("Request failed with status " + xhr.status);
//         }
//       };

//       // Gérer les erreurs réseau
//       xhr.onerror = function () {
//         console.error("Network error occurred");
//       };

//       // Envoyer la requête avec les données
//       xhr.send(data);
//     }
//   });
// } else {
//   // Afficher une erreur si le formulaire n'est pas trouvé
//   console.error(`Form with name "profil" not found.`);
// }

// function checkMail(button, url, formName) {
//   // Sélectionner le formulaire par son nom
//   var form = document.querySelector(`form[name="${formName}"]`);

//   if (form) {
//     // Ajouter un gestionnaire d'événement pour l'événement de soumission du formulaire
//     form.addEventListener("submit", function (event) {
//       // Empêcher le comportement par défaut (soumission du formulaire)
//       event.preventDefault();

//       // Ici, vous pouvez ajouter votre logique pour gérer la soumission du formulaire
//       // console.log("Form submission prevented");
//       // console.log(url, button);

//       var input = document.querySelector(`input[type="email"]`);

//       // var data = { email: input.value };
//       // var data = input.value;
//       // console.log(input.value);
//       var xhr = new XMLHttpRequest();

//       // Configuration de la requête avec la méthode GET et l'URL cible
//       xhr.open("POST", url, true);
//       var data = new FormData();
//       data.append("email", input.value);
//       // Gestion de la réponse
//       xhr.onload = function () {
//         if (xhr.status >= 200 && xhr.status < 400) {
//           // Succès de la requête
//           var data = JSON.parse(xhr.responseText);
//           showMessage("alertSuccess", data.message.success);
//           console.log(data.message); // Affichage des données dans la console (à adapter selon le besoin)
//           // Manipulation des données ou mise à jour de l'interface utilisateur
//         } else {
//           // Erreur pendant la requête
//           console.error("Request failed with status " + xhr.status);
//         }
//       };

//       // Gestion d'erreurs réseau
//       xhr.onerror = function () {
//         console.error("Network error occurred");
//       };

//       // Envoi de la requête
//       xhr.send(data);
//       // Par exemple, envoyer les données du formulaire via AJAX ou fetch
//       // fetch(url, {
//       //     method: 'POST',
//       //     body: new FormData(form)
//       // }).then(response => {
//       //     // Gérer la réponse du serveur
//       // }).catch(error => {
//       //     // Gérer les erreurs
//       // });
//     });
//   } else {
//     console.error(`Form with name "${formName}" not found.`);
//   }
// }
function requestEditProfile(button, url) {
  // button.disabled = true;
  fetchDataPost(url);
}
function requestDelete(button, url) {
  button.disabled = true; // Désactiver le bouton

  // Appeler fetchDataGet pour effectuer la requête
  fetchDataGet(url);
}

function fetchDataGet(url) {
  // Création de l'objet XMLHttpRequest
  var xhr = new XMLHttpRequest();

  // Configuration de la requête avec la méthode GET et l'URL cible
  xhr.open("GET", url, true);

  // Gestion de la réponse
  xhr.onload = function () {
    if (xhr.status >= 200 && xhr.status < 400) {
      // Succès de la requête
      var data = JSON.parse(xhr.responseText);
      showMessage("alertError", data.message.error);
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
function fetchDataPost(url) {
  // Création de l'objet XMLHttpRequest
  var xhr = new XMLHttpRequest();

  // Configuration de la requête avec la méthode GET et l'URL cible
  xhr.open("POST", url, true);

  // Gestion de la réponse
  xhr.onload = function () {
    if (xhr.status >= 200 && xhr.status < 400) {
      // Succès de la requête
      var data = JSON.parse(xhr.responseText);
      showMessage("alertSuccess", data.message.success);
      console.log(data.message); // Affichage des données dans la console (à adapter selon le besoin)
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
  xhr.send(data);
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

  console.log(element, message);
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
