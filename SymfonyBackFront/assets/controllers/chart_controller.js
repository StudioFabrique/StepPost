import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        const numbers = document.querySelectorAll('#number');
        numbers.forEach(element => {
            const numberSaved = parseInt(element.textContent);
            let numberCurrent = 0;
            element.textContent = 0;
            if(numberSaved > 0)
            {
                let interval = setInterval(() => {
                    numberCurrent++;
                    element.textContent = numberCurrent;
                    if(numberCurrent >= numberSaved) {clearInterval(interval);}
                }, 100);
            }
        });
        
    }
}