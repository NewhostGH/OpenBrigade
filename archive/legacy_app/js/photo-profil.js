$(document).ready(function(){
    $('a.form-submit').click(function(event) {
        $(this).parent('form').submit();
    });
    $image_crop = $('#image_demo').croppie({
        enableExif: true,
        viewport: {
          width:148,
          height:177,
          type:'square' //circle
        },
        boundary:{
          width:300,
          height:300
        }
    });

    $('#upload').on('change', function(){
        var f=this.files[0];
        var fileName=f.name;
        var fileExt = fileName.substr(fileName.lastIndexOf('.') + 1);
        var blnValid = false;
        for (var j = 0; j < _validFileExtensions.length; j++) {
            var sCurExtension = _validFileExtensions[j];
            if ( "." + fileExt.toLowerCase() == sCurExtension.toLowerCase()) {
                blnValid = true;
                break;
            }
        }
        if ( ! blnValid) {
            swal("Le fichier n'est pas valide. Seuls les fichiers en " + _validFileExtensions.join(", ")+" sont autorisés.");
            return false;
        }
        if ( f.size > max || f.fileSize > max ) {
            swal("Le fichier photo est trop gros. La taille maximum est de "+ max_mb+ "Mo");
            this.value='';
            return false;
        }
        if ((f.size/1000)<min_kb) {
            swal("Le fichier photo est trop petit. La taille minimum est de "+ min_kb+ "Ko");
            this.value='';
            return false;
        }
        var img = new Image();
        img.src=URL.createObjectURL(this.files[0]); 
        console.log(img);
        var reader = new FileReader();
        img.onload = function() {
            console.log("Image loaded");
            var imgWidth = img.width;
            var imgHeight = img.height;
            if (imgWidth<min_width|| imgHeight<min_height) {
              swal("Le fichier choisi est de trop petites dimensions ("+imgHeight+","+imgWidth+"), minimum requis ("+min_height+","+min_width+")");
              this.value='';
              return false;
            }
            if (imgWidth>max_width || imgHeight>max_height) {
              swal("Le fichier choisi est de trop grandes dimensions ("+imgHeight+","+imgWidth+"), maximum permis ("+max_height+","+max_width+")");
              this.value='';
              return false;
            }

            reader.onload = function (event) {
              $image_crop.croppie('bind', {
                url: event.target.result
              }).then(function(){
                console.log('jQuery bind complete');
              });
            }
            reader.readAsDataURL(f);
            $('#uploadimageModal').modal('show');
        }
        
        img.onerror = function() {
            swal("Le contenu du fichier ne semble pas correspondre à son extension.");
            this.value='';
            return false;
        }
    });

    $('.crop_image').click(function(event){
        $image_crop.croppie('result', {
          type: 'canvas',
          size: 'viewport'
        }).then(function(response){
          $.ajax({
            url:"upd_personnel.php",
            type: "POST",
            data:{"images": response},
            success:function(data)
            {
              $('#uploadimageModal').modal('hide');
              $('#uploaded_image').html(data);
            }
          });
        })
    });
});

function delpic(pid) {
    cible="upd_personnel.php?pompier="+pid+"&a=suppr";
    self.location.href=cible;
    return true;
}