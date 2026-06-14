
function openUploadFiles(functionAddFile,functionRemoveFile,typesFiles,maxFiles,title) {
  if (functionAddFile == null) functionAddFile = "";
  if (functionRemoveFile == null) functionRemoveFile = "";
  if (typesFiles == null) typesFiles = "";
  if (maxFiles == null) maxFiles = 0;
  if (title == null) title = "Subir archivos";

  modalMessage('dropzone',title,null,null,null,null,"closeModalMessage()",true,"initializeDropzone('"+functionAddFile+"','"+functionRemoveFile+"','"+typesFiles+"',"+maxFiles+")",null,600);
}


function initializeDropzone(functionAddFile,functionRemoveFile,typesFiles,maxFiles) {
  if (functionAddFile == null) functionAddFile = "";
  if (functionRemoveFile == null) functionRemoveFile = "";
  if (maxFiles == null || maxFiles == "" || maxFiles <= 0) maxFiles = 25;
  if (typesFiles == null || typesFiles == "") typesFiles = ".jpg, .jpeg, .png, .gif, .pdf, .doc, .docx, .xls, .xlsx, .txt, .avi, .mpg, .mpeg, .eml, .msg ";
  
  Dropzone.autoDiscover = false;
  
  try {
      var myDropzone = new Dropzone("#frmUploadFile" , {
          paramName: "file", // The name that will be used to transfer the file
          maxFiles: maxFiles,
          maxFilesize: 10, // MB*/
          addRemoveLinks : true,
          dictRemoveFile: "Eliminar",
          dictCancelUpload: "Cancelar",
          dictDefaultMessage: "Arrastre aquí para subir.",
          dictFallbackMessage: "Su navegador no soporta arrastrar archivos.",
          dictFileTooBig: "El archivo es muy grande ({{filesize}}MB). Tamaño Máximo: {{maxFilesize}}MB.",
          dictInvalidFileType: "El tipo de archivo no es válido.",
          dictResponseError: "El servidor respondió con código {{statusCode}}.",
          dictCancelUploadConfirmation: "Realmente quiere cancelar la subida del archivo?",
          dictMaxFilesExceeded: "No puede subir más archivos.",
          acceptedFiles: typesFiles,
          dictDefaultMessage :
              '<span class="bigger-150 bolder">Arrastre los archivos </span> a subir \
              <span class="smaller-80 grey">(o haga click)</span> <br /> \
              <i class="upload-icon fa fa-cloud-upload blue fa-3x"></i>'
          ,
          dictResponseError: 'Ocurrió un error al intentar subir el archivo!'
      });
     
     myDropzone.on("success", function(file, serverFilename) {                        
                        $(file.previewTemplate).append('<span class="server_file hide">'+serverFilename+'</span>');                        
                        if (functionAddFile != "") {                          
                          eval(functionAddFile + "('" + file.name + "','" + serverFilename + "')");                          
                        }
                    });
     
     myDropzone.on("removedfile", function(file) {                        
                        var serverFilename = $(file.previewTemplate).children('.server_file').text();  
                        removeFileTemp(serverFilename,functionRemoveFile);                     
                    });
                    
  } catch(e) {
      alert('No se pudo cargar la pantalla.')
  }
}

function removeFileTemp(serverFilename,functionCallback) {
    if (serverFilename == null) serverFilename = "";
    if (functionCallback == null) functionCallback = "";

    if (serverFilename != "") {
      $.ajax({
          url: $('#baseUrl').val()+"dropzone/delete",
          type: "POST",
          data: { "filename" : serverFilename },
          success: function(data){
            if (functionCallback != "") {                          
              eval(functionCallback + "('" + serverFilename + "')");                          
            }
          }
      });   
    }
}