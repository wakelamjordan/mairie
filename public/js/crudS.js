// const { error } = require("jquery");

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
        var modalBody = document.getElementById("contentModalLg");

        modalBody.innerHTML = data;

        // Affiche la modal
        var buttonActionModal = document.getElementById("btnActionModalLg");
        buttonActionModal.click();

        const formEdit = document.querySelector("form[name='admin_user']");
        formEdit.addEventListener("submit", (e) => {
          e.preventDefault();
          // const btn = formEdit.querySelector("button[type='submit']");
          // formEdit.submit();
          // data-id-user="{{user.id}}"
          const url = "/user/edit/{id}";
          const userId = formEdit.querySelector("button[type='submit']").dataset
            .idUser;

          const newUrl = url.replace("{id}", userId);

          submitEdit(newUrl, formEdit);
          // submitEdit(formEdit);
          // console.log(formEdit);
        });
        // document
        //   .getElementById("admin_user")
        //   .addEventListener("submit", (e) => {
        //     e.preventDefault();
        //     console.log("save");
        //   });
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
  // data-id-user="{{user.id}}"
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
        var modalBody = document.getElementById("contentModalLg");
        modalBody.innerHTML = data;

        // Rouvre la modal
        var buttonActionModal = document.getElementById("btnActionModalLg");
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

function submitEdit(url, form) {
  const xhr = new XMLHttpRequest();

  xhr.open("POST", url, true);
  // xhr.open("POST", url, true);

  // xhr.setRequestHeader("Content-Type", "application/json");
  // xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

  // Écouter l'événement chargement de la réponse XHR
  xhr.onload = function () {
    if (xhr.status >= 200 && xhr.status < 300) {
      // Succès : traitement de la réponse
      btnReturn = document.getElementById("closeModalEdit");
      btnReturn.click();
      research();

      // console.log("Réponse du serveur :", xhr.responseText);
    } else {
      if (xhr.status === 400) {
        var response = JSON.parse(xhr.responseText);

        const errorEdit = document.getElementById("errorEdit");

        document.querySelectorAll("input").forEach((e) => {
          e.classList.remove("is-invalide");
        });

        for (let field in response.errors) {
          // Récupérer l'élément du formulaire correspondant
          var element = document.getElementById(form.name + "_" + field);

          if (element) {
            // Ajouter une classe d'erreur à l'élément
            element.classList.add("is-invalid");

            // Ajouter les messages d'erreur à la div errorEdit
            response.errors[field].forEach((message) => {
              errorEdit.innerHTML += `<p>Error in ${field}: ${message}</p>`;
            });
          }
        }
        errorEdit.classList.replace("hidden", "visible");
        setTimeout(() => {
          errorEdit.classList.replace("visible", "hidden");
        }, 3000);
      }
      // Erreur : gestion de l'erreur
      console.error("Erreur lors de la requête XHR :", xhr.statusText);
    }
  };

  // Gérer les erreurs réseau
  xhr.onerror = function () {
    console.error("Erreur réseau lors de la requête XHR.");
  };

  const formData = new FormData(form);

  xhr.send(formData);
}
