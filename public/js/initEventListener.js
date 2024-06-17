/**
 * Initialise le comportement par défaut des cases à cocher.
 * Lorsqu'une case à cocher est cliquée, elle bascule entre coché et non coché.
 */
function initDefaultCheckboxBehavior() {
  // Sélectionne toutes les cases à cocher dans le document
  const checkboxes = document.querySelectorAll('input[type="checkbox"]');

  // Ajoute un écouteur d'événement à chaque case à cocher
  checkboxes.forEach(function (checkbox) {
    checkbox.addEventListener("click", (event) => {
      // Inverse l'état de la case à cocher lorsqu'elle est cliquée
      checkbox.checked = !checkbox.checked;
    });
  });
}
