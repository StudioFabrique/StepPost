import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["fileInput", "editButton"];

  connect() {
    if (this.hasEditButtonTarget) {
      this.editButtonTarget.addEventListener('click', () => this.putImage());
    } else {
      console.error('Edit button target not found.');
    }
  }

  putImage() {
    const courrierId = this.element.dataset.id;
    const file = this.fileInputTarget.files[0];
    const maxsize = 5 * 1024 * 1024
    if(file.size>maxsize){
      window.location =
            `/suivi/${courrierId}?errorMessage=Taille%20maximum%205%20MB`;
    }
    else{
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        const img = new Image();
        img.src = e.target.result;

        img.onload = () =>{

          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d');

          canvas.width = img.width;
          canvas.height = img.height;

          ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

          canvas.toBlob((blob) => {

            this.uploadBlob(blob);
          }, file.type, 0);
        }
        // const blob = new Blob([e.target.result], { type: file.type });
        // this.uploadBlob(blob);
      };
      reader.readAsDataURL(file);
    } else {
      console.error('No file selected.');
    }
  }
}

  uploadBlob(blob) {
    const courrierId = this.element.dataset.id;
    console.log('Blob:', blob);
    const formData = new FormData();
    formData.append('file', blob, 'image.png');
    const filesize = blob.size;
      console.log(filesize);
    fetch(`/upload/${courrierId}`, {
      method: 'POST',
      body: formData,
      // headers: {
      //   'X-Requested-With': 'XMLHttpRequest'
      // }
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Response data:', data);
      if (data.success) {
        console.log('Image uploaded successfully:', data);
        // window.location.reload(); 
      } else {
        console.error('Upload failed:', data.error);
        
      }
      
    })
    .catch(error => {
      console.error('Error uploading image:', error);
    });
  }
}