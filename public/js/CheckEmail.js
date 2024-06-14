// Sélectionner le formulaire par son nom 'profil'
const formProfil = document.querySelector("form[name='profil']");

// Sélectionner le champ email dans le formulaire
const input = document.querySelector("input[type='email']");

// Vérifier que le formulaire existe sur la page
if (formProfil) {
  // Ajouter un gestionnaire d'événement pour l'événement de soumission du formulaire
  formProfil.addEventListener("submit", (e) => {
    // Empêcher le comportement par défaut de soumission du formulaire
    e.preventDefault();

    // Afficher dans la console pour débogage
    console.log(formProfil);

    // Vérifier si le champ email a une valeur
    if (input.value) {
      // Créer une nouvelle requête XMLHttpRequest
      var xhr = new XMLHttpRequest();

      // Ouvrir une connexion POST à l'URL spécifiée
      // Remplacer "/profil/check_mail" par l'URL correcte de votre backend
      xhr.open("POST", "/profil/check_mail", true);

      // Créer un objet FormData pour envoyer les données du formulaire
      var data = new FormData();
      data.append("email", input.value);

      // Gérer la réponse de la requête
      xhr.onload = function () {
        // Vérifier si le statut HTTP est 200 (OK) - L'email est disponible
        if (xhr.status == 200) {
          var responseData = JSON.parse(xhr.responseText);

          // Ajouter une classe de validation CSS au champ email
          input.classList.remove("is-invalid");
          input.classList.add("is-valid");

          // Afficher un message de succès
          const messageSuccessMail =
            document.getElementById("messageSuccessMail");
          if (messageSuccessMail) {
            messageSuccessMail.classList.remove("d-none");
            messageSuccessMail.style.display = "block";
            messageSuccessMail.innerText = responseData.message;
          }

          // Soumettre le formulaire si l'email est valide
          formProfil.submit();
        }

        // Vérifier si le statut HTTP est 409 (Conflict) - L'email est déjà pris
        if (xhr.status == 409) {
          var responseData = JSON.parse(xhr.responseText);

          // Ajouter une classe d'erreur CSS au champ email
          input.classList.remove("is-valid");
          input.classList.add("is-invalid");

          // Afficher un message d'erreur
          const messageErrorMail = document.getElementById("messageErrorMail");
          if (messageErrorMail) {
            messageErrorMail.classList.remove("d-none");
            messageErrorMail.style.display = "block";
            messageErrorMail.innerText = responseData.message;
          }
        }
      };

      // Gérer les erreurs réseau
      xhr.onerror = function () {
        console.error("Network error occurred");
      };

      // Envoyer la requête avec les données
      xhr.send(data);
    }
  });
} else {
  // Afficher une erreur si le formulaire n'est pas trouvé
  console.error(`Form with name "profil" not found.`);
}
