import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {

        /*
            ANIMATION D'INCREMENTATION DES NOMBRES 
         */

        const numbers = document.querySelectorAll('#number');
        numbers.forEach(element => {
            const numberSaved = parseInt(element.textContent);
            let numberCurrent = 0;
            element.textContent = 0;
            if (numberSaved > 0) {
                let interval = setInterval(() => {
                    numberCurrent += numberSaved > 100 ? 10 : 2;
                    element.textContent = numberCurrent;
                    if (numberCurrent >= numberSaved) { element.textContent = numberSaved; clearInterval(interval); }
                }, 0.5);
            }
        });

        /*
            ACTIVATION/DESACTIVATION BARRE DE RECHERCHE
         */

        const content = document.getElementById('elementBox');
        const button = document.getElementById('button');
        const facteurContent = document.getElementById('facteurContent');
        facteurContent.style.transformOrigin = "top";
        let hideContent = true;
        let clickCounter = 0;

        button.addEventListener('click', () => {
            hideContent = false;
            facteurContent.animate([
                { transform: "scaleY(0)" },
                { transform: "scaleY(1)" }
            ], {
                duration: 400
            }
            );
        });
        content.addEventListener('click', () => {
            hideContent = clickCounter >= 1 ? true : false;
            clickCounter++;
        });
        setInterval(() => {
            content.style.visibility = hideContent ? "hidden" : "visible";
            if (hideContent) {
                clickCounter = 0;
            }
        }, 10);
    }
}