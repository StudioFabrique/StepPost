import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    /* 
        Pour controller les confirmation Box de suppression
    */
    connect() {
        const showButtons = document.querySelectorAll("#showButton");
        const hideButtons = document.querySelectorAll("#hideButton");
        const confirmBoxes = document.querySelectorAll("#confirmBox");

        confirmBoxes.forEach(element => {
            element.hidden = true;
        });

        for (let i = 0; i < confirmBoxes.length; i++) {
            showButtons[i].addEventListener("click", () => { this.ShowConfirmation(confirmBoxes, confirmBoxes[i]) });
            hideButtons[i].addEventListener("click",() => { this.HideConfirmation(confirmBoxes[i])});
        }
    }

    /* 
        Méthode pour afficher une box de confirmation
    */

    ShowConfirmation(confirmBoxes ,confirmBox) {
        confirmBoxes.forEach(element => {
            element.hidden = true;
        });
        confirmBox.hidden = false;
        confirmBox.animate([
            {transform: "scale(0)"},
            {transform: "scale(1.2)"},
            {transform: "scale(1)"}
            ] , {
            duration: 300
            }
        );
    }
    
    /* 
        Méthode pour cacher une box de confirmation
    */

    HideConfirmation(confirmBox) {
        confirmBox.hidden = true;
    }
}