import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const newFacteurEndpoint = this.element.dataset.newFacteurEndpoint;
        const btn = document.getElementById("facteur_submit");
        const mailRegex = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
        const numberRegex = /^[0-9]$/;
        const nameRegex = /^[a-zA-Z0-9\s,.'+éàèù]{0,}$/;
        const inputs = document.querySelectorAll("input");

        btn.addEventListener("click", () => {
            const email = inputs[0].value;
            const nom = inputs[1].value;
            const password = inputs[2].value;
            const hash = bcrypt.hash(password, 10, function(err, hash) {
                // Store hash in your password DB.
            });

            const checkEmail = mailRegex.test(email);
            const checkPassword = password.length === 4;
            const checkNom = nameRegex.test(nom);
            if (checkPassword && checkNom && checkEmail) {
                const fd = new FormData();
                fd.append("email", email);
                fd.append("nom", nom);
                fd.append("password", hash);
    
                
                fetch('/api/newFacteur',
                     {method: 'POST', body: fd})
                     .then((response) =>
                        response.json()
                            .then((result) => {
                                console.log(response);
                                console.log(result);
                                if (result) {
                                    console.log("Facteur créé");
                                    window.location = '/facteurs';
                                } else {
                                    console.log("Problème serveur");
                                }
                        })
                    );
            } else {
                // generate error alert
            }
        });
    }
}