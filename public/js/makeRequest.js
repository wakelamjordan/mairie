// console.log("youhou!");

function showSpinner() {
  console.log(document.getElementById("spinner"));
  document.getElementById("spinner").style.display = "block";
}

function hideSpinner() {
  document.getElementById("spinner").style.display = "none";
}

function makeRequest(url, method = "GET", data = null, callback = null) {
  let xhr = new XMLHttpRequest();
  xhr.open(method, url, true);

  showSpinner();

  xhr.onload = function () {
    hideSpinner();
    if (xhr.status >= 200 && xhr.status < 300) {
      callback(null, xhr.responseText);
    } else {
      callback(xhr.statusText, null);
    }
  };

  xhr.onerror = function () {
    hideSpinner();
    callback("Erreur réseau", null);
  };

  if (method === "POST" || method === "DELETE") {
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(JSON.stringify(data));
  } else {
    xhr.send();
  }
}

// // Exemple d'utilisation :
// // GET request
// makeRequest("GET", "https://exemple.com/api/data", null, function (err, data) {
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
// makeRequest(
//   "POST",
//   "https://exemple.com/api/data",
//   postData,
//   function (err, data) {
//     if (err) {
//       console.error("Erreur :", err);
//       // Gérer l'erreur
//     } else {
//       console.log("Réponse du serveur :", data);
//       // Traiter la réponse
//     }
//   }
// );

// // DELETE request
// makeRequest(
//   "DELETE",
//   "https://exemple.com/api/data/123",
//   null,
//   function (err, data) {
//     if (err) {
//       console.error("Erreur :", err);
//       // Gérer l'erreur
//     } else {
//       console.log("Réponse du serveur :", data);
//       // Traiter la réponse
//     }
//   }
// );
