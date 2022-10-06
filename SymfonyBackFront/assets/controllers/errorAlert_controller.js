import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
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