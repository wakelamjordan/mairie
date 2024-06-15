// function showAlert(message, type = "danger") {
//   const alertContainer = document.getElementById("alert-container");
//   alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
//         ${message}
//         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
//     </div>`;
// }

document.getElementById("btn-show").addEventListener("click", function () {
  // Logique pour afficher les détails de l'utilisateur sélectionné
  const checkboxes = document.querySelectorAll(
    '#tableauProfil input[type="checkbox"]:checked'
  );

  if (checkboxes.length === 0) {
    alert("Veuillez sélectionner au moins un utilisateur à afficher.");
  } else if (checkboxes.length > 1) {
    alert("Veuillez sélectionner un seul utilisateur à afficher.");
  } else if (checkboxes.length === 1) {
    const userId = checkboxes[0].value;

    var url = "/user/test/show/id";

    url = url.replace("id", userId);

    console.log(url);

    makeRequest("GET", url, null, function (err, data) {
      if (err) {
        console.error("Erreur :", err);
        // Gérer l'erreur
      } else {
        var modalBody = document.getElementById("modalBody");
        var buttonActionModal = document.getElementById("buttonActionModal");

        console.log(modalBody);

        modalBody.innerHTML = data; // Supposant que data est du HTML ou du texte à afficher
        // Afficher la modal
        // var myModal = new bootstrap.Modal(document.getElementById("modalBox"));
        buttonActionModal.click();

        checkboxes.forEach((checkbox) => {
          checkbox.checked = false;
        });
      }
    });
    // alert("afficher id" + userId);
  }
});

document.getElementById("btn-edit").addEventListener("click", function () {
  // Logique pour rediriger vers la page de modification

  const checkboxes = document.querySelectorAll(
    '#tableauProfil input[type="checkbox"]:checked'
  );

  console.log(checkboxes.length, checkboxes);

  if (checkboxes.length === 0) {
    alert("Veuillez sélectionner au moins un utilisateur à modifier.");
  } else if (checkboxes.length > 1) {
    alert("Veuillez sélectionner un seul utilisateur à modifier.");
  } else if (checkboxes.length === 1) {
    const userId = checkboxes[0].value;

    // alert("modifier id" + userId);

    // checkboxes[0].checked = false;

    var url = "/user/test/edit/id";

    url = url.replace("id", userId);

    console.log(url);

    makeRequest("GET", url, null, function (err, data) {
      if (err) {
        console.error("Erreur :", err);
        // Gérer l'erreur
      } else {
        var modalBody = document.getElementById("modalBody");
        var buttonActionModal = document.getElementById("buttonActionModal");

        console.log(modalBody);

        modalBody.innerHTML = data; // Supposant que data est du HTML ou du texte à afficher
        // Afficher la modal
        // var myModal = new bootstrap.Modal(document.getElementById("modalBox"));
        buttonActionModal.click();

        checkboxes = null;
      }
    });
  }
});

document.getElementById("btn-delete").addEventListener("click", function () {
  // Logique pour supprimer les utilisateurs sélectionnés
  console.log("Supprimer les utilisateurs sélectionnés");

  const checkboxes = document.querySelectorAll(
    '#tableauProfil input[type="checkbox"]:checked'
  );

  if (checkboxes.length === 0) {
    // Aucune case cochée
    alert("Veuillez sélectionner au moins un utilisateur à supprimer.");
  } else {
    // Construire un tableau avec les IDs des utilisateurs sélectionnés
    const selectedIds = Array.from(checkboxes).map(
      (checkbox) => checkbox.value
    );

    // Afficher une confirmation pour la suppression
    const confirmDelete = window.confirm(
      `Êtes-vous sûr de vouloir supprimer ${selectedIds.length} utilisateur(s) ?`
    );

    if (confirmDelete) {
      // Procéder à la suppression
      console.log(
        `Les IDs des utilisateurs à supprimer: ${JSON.stringify(selectedIds)}`
      );

      makeRequest(
        "POST",
        "/user/test/delete/",
        selectedIds,
        function (err, data) {
          if (err) {
            console.error("Erreur :", err);
            // Gérer l'erreur
          } else {
            console.log("Réponse du serveur :", data);
            // Traiter la réponse
          }
        }
      );
    }
  }
});

document.getElementById("formSearch").addEventListener("submit", (event) => {
  event.preventDefault(); // Empêche le comportement par défaut du formulaire (rechargement de la page)

  const formSearch = document.getElementById("formSearch"); // Récupère l'élément du formulaire
  const searchValue = formSearch.querySelector('input[type="search"]').value; // Récupère la valeur de l'input de recherche

  console.log(searchValue); // Affiche la valeur dans la console
});
