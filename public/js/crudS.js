// Écouteur d'événement pour le bouton Afficher
document.getElementById("btn-show").addEventListener("click", function () {
  // Récupère toutes les cases à cocher sélectionnées dans le tableau
  const checkboxes = document.querySelectorAll(
    '#tableauProfilTbody input[type="checkbox"]:checked'
  );

  // Vérifie le nombre de cases cochées
  if (checkboxes.length === 0) {
    alert("Veuillez sélectionner au moins un utilisateur à afficher.");
  } else if (checkboxes.length > 1) {
    alert("Veuillez sélectionner un seul utilisateur à afficher.");
  } else if (checkboxes.length === 1) {
    const userId = checkboxes[0].value;

    // URL pour récupérer les détails de l'utilisateur sélectionné
    var url = "/user/show/id";
    url = url.replace("id", userId);

    // Effectue une requête AJAX pour récupérer les données
    makeRequest(url, "GET", null, function (err, data) {
      if (err) {
        console.error("Erreur :", err);
        // Gérer l'erreur ici
      } else {
        // Affiche les données récupérées dans le corps de la modal
        var modalBody = document.getElementById("modalBody");
        modalBody.innerHTML = data;

        // Affiche la modal
        var buttonActionModal = document.getElementById("buttonActionModal");
        buttonActionModal.click();

        // Réinitialise les cases à cocher
        // checkboxes.forEach((checkbox) => {
        //   checkbox.checked = false;
        // });
      }
    });
  }
});

// Écouteur d'événement pour le bouton Modifier
document.getElementById("btn-edit").addEventListener("click", function () {
  // Récupère toutes les cases à cocher sélectionnées dans le tableau
  const checkboxes = document.querySelectorAll(
    '#tableauProfilTbody input[type="checkbox"]:checked'
  );

  // Vérifie le nombre de cases cochées
  if (checkboxes.length === 0) {
    alert("Veuillez sélectionner au moins un utilisateur à modifier.");
  } else if (checkboxes.length > 1) {
    alert("Veuillez sélectionner un seul utilisateur à modifier.");
  } else if (checkboxes.length === 1) {
    const userId = checkboxes[0].value;

    // URL pour rediriger vers la page de modification de l'utilisateur
    var url = "/user/edit/id";
    url = url.replace("id", userId);

    // Effectue une requête AJAX pour récupérer les données
    makeRequest(url, "GET", null, function (err, data) {
      if (err) {
        console.error("Erreur :", err);
        // Gérer l'erreur ici
      } else {
        // Affiche les données récupérées dans le corps de la modal
        var modalBody = document.getElementById("modalBody");
        modalBody.innerHTML = data;

        // Affiche la modal
        var buttonActionModal = document.getElementById("buttonActionModal");
        buttonActionModal.click();

        // Réinitialise les cases à cocher
        // checkboxes[0].checked = false;
      }
    });
  }
});

// Écouteur d'événement pour le bouton Supprimer
document.getElementById("btn-delete").addEventListener("click", function () {
  // Récupère toutes les cases à cocher sélectionnées dans le tableau
  const checkboxes = document.querySelectorAll(
    '#tableauProfilTbody input[type="checkbox"]:checked'
  );

  // Vérifie le nombre de cases cochées
  if (checkboxes.length === 0) {
    alert("Veuillez sélectionner au moins un utilisateur à supprimer.");
  } else {
    // Construit un tableau avec les IDs des utilisateurs sélectionnés
    const selectedIds = Array.from(checkboxes).map(
      (checkbox) => checkbox.value
    );

    // Confirme la suppression des utilisateurs
    const confirmDelete = window.confirm(
      `Êtes-vous sûr de vouloir supprimer ${selectedIds.length} utilisateur(s) ?`
    );

    if (confirmDelete) {
      const postData = { profil: selectedIds };

      // Effectue une requête AJAX pour supprimer les utilisateurs
      makeRequest("/user/delete", "DELETE", postData, function (err, data) {
        if (err) {
          console.error("Erreur :", err);
          // Gérer l'erreur ici
        } else {
          // Effectue une recherche avec la valeur de recherche actuelle
          research(document.querySelector('input[type="search"]').value);
        }
      });
    }
  }
});

// Écouteur d'événement pour le formulaire de recherche
document.getElementById("formSearch").addEventListener("submit", (event) => {
  event.preventDefault();

  // Récupère la valeur de l'input de recherche
  const searchValue = document
    .getElementById("formSearch")
    .querySelector('input[type="search"]').value;

  // Effectue une recherche avec la valeur de recherche
  research(searchValue);
});

// Fonction pour effectuer une recherche AJAX
function research(searchValue) {
  const baseUrl = "/user/api/categories/navbar"; // URL de base pour la recherche

  // Construit l'URL de recherche avec la valeur de recherche encodée
  let urlSearch = searchValue
    ? `${baseUrl}${encodeURIComponent(searchValue)}`
    : baseUrl;

  // Effectue une requête AJAX pour récupérer les résultats de recherche
  makeRequest(urlSearch, "GET", null, function (err, data) {
    if (err) {
      console.error("Erreur :", err);
    } else {
      // Met à jour le contenu du tableau cible avec les résultats de la recherche
      const targetTable = document.getElementById("targetTable");
      targetTable.innerHTML = data;

      // Ajoute des écouteurs d'événements aux cases à cocher
      document
        .querySelectorAll('#tableauProfilTbody input[type="checkbox"]')
        .forEach((checkbox) => {
          checkbox.addEventListener("click", function (event) {
            event.stopPropagation(); // Empêche la propagation de l'événement
          });
        });
    }
  });
}

function editFromShow(btn) {
  // URL pour rediriger vers la page de modification de l'utilisateur
  var url = "/user/edit/id";
  url = url.replace("id", btn.dataset.idUser);

  // Effectue une requête AJAX pour récupérer les données
  makeRequest(url, "GET", null, function (err, data) {
    if (err) {
      console.error("Erreur :", err);
      // Gérer l'erreur ici
    } else {
      btnReturn = document.getElementById("btnReturn");
      btnReturn.click();
      // Affiche les données récupérées dans le corps de la modal
      // Utiliser un délai avant de mettre à jour le contenu et rouvrir la modal
      setTimeout(function () {
        // Met à jour le contenu de la modal
        var modalBody = document.getElementById("modalBody");
        modalBody.innerHTML = data;

        // Rouvre la modal
        var buttonActionModal = document.getElementById("buttonActionModal");
        buttonActionModal.click();
      }, 350);

      // Réinitialise les cases à cocher
      // checkboxes[0].checked = false;
    }
  });
}

function deleteFromShow(btn) {
  // Confirme la suppression des utilisateurs
  const confirmDelete = window.confirm(
    `Êtes-vous sûr de vouloir supprimer cet utilisateur ?`
  );

  if (confirmDelete) {
    const selectedIds = [btn.dataset.idUser];
    const postData = { profil: selectedIds };

    const idUser = btn.dataset.idUser;

    const url = "/user/delete/id";

    const urlWithParam = url.replace("id", idUser);

    // Effectue une requête AJAX pour supprimer les utilisateurs
    makeRequest(urlWithParam, "DELETE", postData, function (err, data) {
      if (err) {
        console.error("Erreur :", err);
        // Gérer l'erreur ici
      } else {
        // Effectue une recherche avec la valeur de recherche actuelle
        document.getElementById("btnReturn").click();
        research(document.querySelector('input[type="search"]').value);
      }
    });
  }
}
