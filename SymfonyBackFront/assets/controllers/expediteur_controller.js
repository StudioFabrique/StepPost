import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        const button = document.getElementById('expediteurButton');
        const elementBox = document.getElementById('elementBox');
        elementBox.style.transformOrigin = "top";
        let toggle = false;
        button.addEventListener("click", () => {
            toggle = this.toggleWindow(elementBox, toggle);
            this.animateWindow(elementBox);
        });
    }

    toggleWindow(elementBox, toggle) {
        toggle ? elementBox.style.visibility = "hidden" : elementBox.style.visibility = "visible";
        toggle = toggle ? false : true;
        return toggle;
    }

    animateWindow(elementBox) {
        elementBox.animate([
            {transform: "scale(0)"},
            {transform: "scale(1.2)"},
            {transform: "scale(1)"}
            ], {
            duration: 300
            });
    }
}