import { Controller } from "@hotwired/stimulus";
import { fabric } from 'fabric';

export default class extends Controller {
  
    connect(){
      this.StepupVisible();
      var canvas = this.__canvas = new fabric.Canvas('c', {
        isDrawingMode: true
      });
      fabric.Object.prototype.transparentCorners = false;
      document.getElementById('clear-canvas').addEventListener('click', () => {
        if (canvas) {
          canvas.clear();
        } else {
          console.log("Canvas not loaded");
        }
      });
      

      const ok = this.element.dataset.id;
      console.log(ok);
      if(ok){
        document.getElementById('save-canvas').addEventListener('click', function(){

          if (canvas) {
            canvas.getElement().toBlob(  (blob)=>{
              var formData = new FormData();
              console.log("test",blob);
              formData.append('image', blob, 'signature.png');
              formData.append("id",ok);
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
   

      
      

    StepupVisible(){
      document.getElementById("signature").addEventListener("click",  () => {
        console.log("test");
        document.getElementById("canvastest").style.display="block";
        document.getElementById("canvastests").style.display="block";
        document.getElementById("signature").style.display="none";
      })
    };    
}
  