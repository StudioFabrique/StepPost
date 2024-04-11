import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  /* 
        Pour afficher le contenu des statut avec une animation après le clic sur un bouton.
    */
  connect() {
    const button = document.querySelector("#button");
    const boxContent = document.querySelector("#boxContent");
    boxContent.style.transformOrigin = "top";
    boxContent.hidden = true;

    button.addEventListener("click", () => {
      this.openBoxContent(boxContent);
    });
  }

  openBoxContent(boxContent) {
    boxContent.hidden = !boxContent.hidden;
    boxContent.animate(
      [
        { transform: "scaleY(0)" },
        { transform: "scaleY(1.2)" },
        { transform: "scaleY(1)" },
      ],
      {
        duration: 300,
      }
    );
  }
}
