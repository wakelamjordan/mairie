function handleScreenSizeChange() {
  const accordeons = document.querySelectorAll(".accordion-collapse");
    // console.log(accordeons);
  const accordeonsButton = document.querySelectorAll(".accordion-button");
    // console.log(accordeonsButton);
  if (window.matchMedia("(min-width: 768px)").matches) {
    // Code à exécuter lorsque la taille d'écran est MD ou supérieure
    // Ajoutez ici le code que vous souhaitez exécuter pour les écrans MD et plus grands
    // {# show et pas collapsed
    // et pas show et collapsed #}
    accordeons.forEach((accordeon) => {
      accordeon.classList.add("show");
    });
    accordeonsButton.forEach((accordeonButton) => {
      accordeonButton.classList.remove("collapsed");
    });
  } else {
    // Code à exécuter lorsque la taille d'écran est inférieure à MD
    accordeons.forEach((accordeon) => {
      accordeon.classList.remove("show");
    });
    accordeonsButton.forEach((accordeonButton) => {
      accordeonButton.classList.add("collapsed");
    });
    // Ajoutez ici le code que vous souhaitez exécuter pour les écrans plus petits que MD
  }
}

// Écoutez les changements de taille d'écran en utilisant MediaQueryList
const mediaQueryList = window.matchMedia("(min-width: 768px)");
mediaQueryList.addEventListener("change", handleScreenSizeChange);

// // Appeler la fonction handleScreenSizeChange au chargement de la page pour vérifier l'état initial
handleScreenSizeChange();

// const iframe = document.getElementById("pocket");

// const iframeDocument = iframe.contentDocument;

// const htmlInFrame = iframeDocument.querySelector("html");

// console.log(htmlInFrame, iframe);

// htmlInFrame.classList.add("d-flex");
