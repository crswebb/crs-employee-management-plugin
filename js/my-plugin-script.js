// Access the ajaxUrl value passed from PHP
var ajaxUrl = myPluginData.ajaxUrl;

document.getElementById('upload_image_button').addEventListener('click', function() {
    var fileInput = document.getElementById('employee_photo');
    var file = fileInput.files[0];
    var formData = new FormData();
    formData.append('employee_photo', file);
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', ajaxUrl, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    // Set the action parameter in the AJAX request
    xhr.setRequestHeader('action', 'upload_employee_photo');
    // Set the appropriate headers for file upload
    xhr.setRequestHeader('Content-Type', 'multipart/form-data');
    xhr.setRequestHeader('Cache-Control', 'no-cache');
    xhr.setRequestHeader('X-File-Name', file.name);
    xhr.setRequestHeader('X-File-Size', file.size);
    xhr.setRequestHeader('X-File-Type', file.type);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                var imageUrl = xhr.responseText;
                var imagePreview = document.createElement('img');
                imagePreview.src = imageUrl;
                document.getElementById('employee_photo_preview').appendChild(imagePreview);
                document.getElementById('employee_photo').value = imageUrl;
            } else {
                console.error('Error uploading image: ' + xhr.statusText);
            }
        }
    };
    xhr.send(formData);
});
