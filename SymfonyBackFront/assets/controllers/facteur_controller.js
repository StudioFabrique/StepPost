import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  /* 
        Quand le bouton est appuyé par l'utilisateur, la méthode vérifie que les valeurs rentrés
        sont bien correctes (email, mot de passe à 4 chiffres).
        Puis un fetch est effetué vers l'api de création du facteur.

    */
  connect() {
    const bcrypt = require("bcryptjs");

    let mode = this.element.dataset.mode;

    if ((mode = "add")) {
      const btn = document.getElementById("facteur_submit");
      const mailRegex = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
      const numberRegex = /^[0-9]|[0-9]|[0-9]|[0-9]$/;
      const nameRegex = /^[a-zA-Z0-9\s,.'+éàèù]{0,}$/;
      const inputs = document.querySelectorAll("input");

      btn.addEventListener("click", () => {
        const email = inputs[0].value;
        const nom = inputs[1].value;
        const password = inputs[2].value;
        var hashedPassword = bcrypt.hashSync(password, 10);

        console.log(password + " " + hashedPassword);

        const checkEmail = mailRegex.test(email);
        const checkPassword = numberRegex.test(password);
        const checkNom = nameRegex.test(nom);
        if (checkNom && checkEmail && password.length === 4 && checkPassword) {
          const fd = new FormData();
          fd.append("email", email);
          fd.append("nom", nom);
          fd.append("password", hashedPassword);

          fetch("api/newFacteur", { method: "POST", body: fd }).then(
            (response) =>
              response.json().then((result) => {
                console.log(response);
                console.log(result);
                if (result) {
                  window.location =
                    "/facteurs?errorMessage=Le%20facteur%20a%20bien%20été%20crée";
                } else {
                  console.log("Problème serveur");
                }
              })
          );
        } else {
          console.log("Erreur de saisie");
          window.location =
            "/nouveauFacteur?errorMessage=Erreur%20de%20saisie&isAdding=1&isEdit=0&isError=true";
        }
      });
    }
    if ((mode = "editPassword")) {
      const id = this.element.dataset.id;
      const btn = document.getElementById("facteur_submit");
      const numberRegex = /^[0-9]|[0-9]|[0-9]|[0-9]$/;
      const input = document.querySelector("input");
      console.log(input);

      btn.addEventListener("click", () => {
        const password = input.value;
        var hashedPassword = bcrypt.hashSync(password, 10);

        const checkPassword = numberRegex.test(password);
        if (password.length === 4 && checkPassword) {
          const fd = new FormData();
          fd.append("password", hashedPassword);
          fd.append("id", id);

          fetch("api/editPasswordFacteur", { method: "POST", body: fd }).then(
            (response) =>
              response.json().then((result) => {
                console.log(response);
                console.log(result);
                if (result) {
                  window.location =
                    "/facteurs?errorMessage=Le%20mot%20de%20passe%20a%20été%20changé";
                } else {
                  console.log("Problème serveur");
                }
              })
          );
        } else {
          console.log("Erreur de saisie");
          window.location =
            "/facteurs?errorMessage=Erreur%20de%20saisie&isError=true";
        }
      });
    }
  }
}
