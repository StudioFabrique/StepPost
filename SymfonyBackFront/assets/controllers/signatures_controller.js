import { Controller } from "@hotwired/stimulus";
import { fabric } from 'fabric';

export default class extends Controller {

    connect(){
        console.log("On rentre dedans");
        console.log('ça marche enfin');
        var canvas = this.canvas = new fabric.Canvas('signatureCanvas', {
            isDrawingMode: true
        });
        this.editButton = document.getElementById('editerSignatureButton');
        this.imageElement = document.getElementById('imageCanvas');
        this.editButton.addEventListener('click', () => this.editImage());

        const ok = this.element.dataset.id;
        console.log(ok);
        if(ok){
            document.getElementById('save-canva').addEventListener('click', function(){
    
                if (canvas) {
                    canvas.getElement().toBlob(  (blob)=>{
        
                    
                        var formData = new FormData();
                        console.log("test",blob);
                        formData.append('image', blob, 'signature.png');
                        formData.append("id",ok);
                        // console.log("Le véritable ID:", this.element.dataset.id);
                
                        // console.log(ok);
            
                            fetch(`/signature`, { 
                                method: "POST",  
                                body: formData
                            })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            window.location.reload();
                            return response.json();
                        })
                        .then(result => {
                            console.log(result);
                            
                            // window.location = "/";
                        })
                        .catch(error => {
                            console.error('Fetch Error:', error);
                            console.log("Problème serveur");
                        });
                    })
                }
            })  
        } 
    }


    editImage() {
        fabric.Image.fromURL(this.imageElement.src, (img) => {
            this.canvas.clear();
            img.scaleToWidth(this.canvas.width);
            img.set({
                left: 0,
                top: 0,
                angle: 0,
                selectable: true
            });
            this.canvas.add(img);
            this.canvas.centerObject(img);
            this.canvas.renderAll();
            document.getElementById('affichercanva').style.display="block";
            document.getElementById('cacher').style.display="none";
            document.getElementById('tests').style.display="none";
            document.getElementById('save-canva').style.display="block";
            document.getElementById('editerSignatureButton').style.display="none";
            
        })
    }
}