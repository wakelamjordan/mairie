const navLinksGenerate = document.getElementById("navLinksGenerate");
// console.log(navLinksGenerate);

var cachedMenu = localStorage.getItem("cachedMenu");

if (cachedMenu) {
  navLinksGenerate.innerHTML = JSON.parse(cachedMenu);
}

makeRequest("/api/categories", "GET", null, function (err, data) {
  if (err) {
    console.error("Erreur :", err);
    // Gérer l'erreur
  } else {
    // console.log("Réponse du serveur :", data);
    // console.log(navLinksGenerate);
    // console.log(data);

    localStorage.setItem("cachedMenu", JSON.stringify(data));

    navLinksGenerate.innerHTML = data;
    // Traiter la réponse
  }
},false);
// console.log(navLinksGenerate);
