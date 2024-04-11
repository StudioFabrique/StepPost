import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    console.log("salut");
    // Définit la date minimale de dateEnd basée sur dateStart
    document.getElementById("DateMin").addEventListener("change", function () {
      var inputDateMin = new Date(this.value);
      var inputDateMax = new Date(inputDateMin);

      inputDateMax.setDate(inputDateMax.getDate() + 1);

      var dd = String(inputDateMax.getDate()).padStart(2, "0");
      var mm = String(inputDateMax.getMonth() + 1).padStart(2, "0");
      var yyyy = inputDateMax.getFullYear();

      inputDateMax = yyyy + "-" + mm + "-" + dd;

      document.getElementById("DateMax").setAttribute("min", inputDateMax);
    });
  }
}
