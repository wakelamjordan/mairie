const footerLinksGenerate = document.getElementById("footerLinksGenerate");
// console.log(navLinksGenerate);

var cachedFooterMenu = localStorage.getItem("cachedFooterMenu");

if (cachedFooterMenu) {
  footerLinksGenerate.innerHTML = JSON.parse(cachedFooterMenu);
}

makeRequest(
  "/api/categories/footer",
  "GET",
  null,
  function (err, data) {
    if (err) {
      console.error("Erreur :", err);
      // Gérer l'erreur
    } else {
      // console.log("Réponse du serveur :", data);
      // console.log(navLinksGenerate);
      // console.log(data);

      localStorage.setItem("cachedFooterMenu", JSON.stringify(data));

      footerLinksGenerate.innerHTML = data;
      // Traiter la réponse
    }
  },
  false
);
// console.log(navLinksGenerate);
