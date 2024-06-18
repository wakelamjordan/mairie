function resetPasswordAdmin(id, btn = null) {
  const confirmationMessage = "Cronfirmez la réinitialisation du mot de passe?";

  if (btn) {
    btn.disabled = true;
  }

  if (confirm(confirmationMessage)) {
    var url = "/reset-password/admin/{id}";
    url = url.replace("{id}", id);
    console.log(url);
    //   return;
    makeRequest(url, "GET", null, function (err, data) {
      if (err) {
        console.error("Erreur :", err);
        // Gérer l'erreur
      } else {
        console.log("Réponse du serveur :", data);
        // Traiter la réponse
      }
    });
  }
}
