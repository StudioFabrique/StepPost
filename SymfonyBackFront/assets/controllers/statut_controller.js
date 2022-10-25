import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const button = document.querySelector("#button");
        const boxContent = document.querySelector("#boxContent");
        boxContent.hidden = true;

        button.addEventListener("click", () => { this.openBoxContent(boxContent) })
    }

    openBoxContent(boxContent) {
        boxContent.hidden = false;
        boxContent.animate([
            {transform: "scaleY(0)"},
            {transform: "scaleY(1.2)"},
            {transform: "scaleY(1)"}
            ] , {
            duration: 300
            }
        );
    }
}