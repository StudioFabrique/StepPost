import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    /* 
        À chaque fois qu'un message est activé, il est animé de la façon suivante :
        translation de la droite vers la gacuhe pendant une demi seconde puis le message
        disparaît au bout de 5 secondes.
    */
    connect() {
        const errorAlert = this.element;
        errorAlert.animate([
            {
                transform: "translateX(600px)"
            },
            {
                transform: "translateX(0px)"
            }
            ], {
            duration: 500
        });

        setTimeout(() => {
            errorAlert.remove();
          }, 5000);
    }
}