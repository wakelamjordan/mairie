const navLinksGenerate = document.getElementById("navLinksGenerate");
const footerLinksGenerate = document.getElementById("footerLinksGenerate");

// console.log(navLinksGenerate);

var cachedNavbarMenu = localStorage.getItem("cachedNavbarMenu");

if (cachedNavbarMenu) {
  navLinksGenerate.innerHTML = JSON.parse(cachedNavbarMenu);
}

var cachedFooterMenu = localStorage.getItem("cachedFooterMenu");

if (cachedFooterMenu) {
  footerLinksGenerate.innerHTML = JSON.parse(cachedFooterMenu);
}

makeRequest(
  "/api/categories/navbar",
  "GET",
  null,
  function (err, data) {
    if (err) {
      console.error("Erreur :", err);
      // Gérer l'erreur
    } else {
      const response = JSON.parse(data);
      // console.log(response);
      // console.log("Réponse du serveur :", data);
      // console.log(navLinksGenerate);
      // console.log(data);

      localStorage.setItem("cachedNavbarMenu", JSON.stringify(response.navbar));

      localStorage.setItem("cachedFooterMenu", JSON.stringify(response.footer));

      navLinksGenerate.innerHTML = response.navbar;

      footerLinksGenerate.innerHTML = response.footer;

      // Traiter la réponse
    }
  },
  false
);
// console.log(navLinksGenerate);
