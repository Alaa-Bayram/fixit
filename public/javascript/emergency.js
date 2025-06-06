let videoStream;

// Access the camera and start capturing
function showCamera() {
    const video = document.getElementById('video');
    const captureButton = document.getElementById('captureButton');
    const startCameraButton = document.getElementById('startCameraButton');
    const videoContainer = document.getElementById('videoContainer');

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function (stream) {
                video.srcObject = stream;
                video.play();
                startCameraButton.style.display = 'none';
                captureButton.style.display = 'block';
                videoContainer.style.display = 'block'; // Show the video container
            })
            .catch(function (err) {
                console.error("Error accessing the camera: " + err);
                alert("Unable to access the camera. Please check your device settings.");
            });
    } else {
        alert("Camera not supported on this device.");
    }
}

function captureImage() {
    const video = document.getElementById('video');
    const capturedImage = document.getElementById('capturedImage');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    const videoContainer = document.getElementById('videoContainer');
    const startCameraButton = document.getElementById('startCameraButton');
    const captureButton = document.getElementById('captureButton');
    const deleteImageButton = document.getElementById('deleteImageButton');
    const imageInput = document.getElementById('image'); // Get the file input element

    // Set canvas size to match the video dimensions
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Get the image data URL from the canvas
    const dataURL = canvas.toDataURL('image/png');
    capturedImage.src = dataURL;
    capturedImage.style.display = 'block'; // Ensure the image is displayed

    // Set the data URL to the hidden input field
    document.getElementById('capturedImageData').value = dataURL;

    // Update the image input value with the captured image URL
    imageInput.value = dataURL; // This will not work directly for file inputs

    // Hide video container and buttons
    videoContainer.style.display = 'none';
    startCameraButton.style.display = 'block';
    captureButton.style.display = 'none';
    deleteImageButton.style.display = 'block';

    // Stop the video stream
    let stream = video.srcObject;
    if (stream) {
        let tracks = stream.getTracks();
        tracks.forEach(track => track.stop());
    }

    video.srcObject = null;
}



// Delete the captured image
function deleteImage() {
    document.getElementById('capturedImage').src = '';
    document.getElementById('capturedImage').style.display = 'none';
    document.getElementById('capturedImageData').value = '';
    document.getElementById('deleteImageButton').style.display = 'none';
}
