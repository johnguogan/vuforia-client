<html>

<head>
<meta charset="UTF-8">
  <meta name="description" content="Vuforia Client">
  <meta name="keywords" content="HTML, CSS, JavaScript">
  <meta name="author" content="John Doe">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
label {
    display: block;
    font: 1rem 'Fira Sans', sans-serif;
}

input,
label {
    margin: .4rem 0;
}
</style>

<script src="https://code.jquery.com/jquery-3.5.0.js"></script>

</head>

<body>

<form action="javascript:alert('success!');" method="post" enctype="multipart/form-data">
    <h2>Add Target</h2>
    <label for="picture">Target Picture File:</label>
    <input type="file"
        id="picture" name="picture"
        accept="image/png, image/jpeg"
        onchange="loadPictureFile(event)"
        required>
        
    <br />
    <span>.jpg or .png (max file 2mb)</span>
    <br />
    <br />
    <img id="picturePreview" width="320" />
    <br />
    <label for="imageWidth">Width:</label>
    <input type="number" id="imageWidth" name="width" value="1" disabled required>
    <br />
    <span>Enter the width of your target in scene units. The size of the target should be on the same scale as  your augmented virtual content. Vuforia uses meters as the default unit scale. The target's height will be calculated when you upload your image.</span>
    <br />
    <label for="video">Select video to upload to firebase:</label>
    <input type="file"
        id="video" name="video"
        accept="video/*"
        onchange="loadVideoFile(event)"
        required>
    <br />
    <video width="320" controls>
        <source id="videoPreview">
        Your browser does not support HTML5 video.
    </video>
    <br />
    <span id="warning" style="color: red"></span>
    <br />
    <input type="submit" value="Upload" name="submit">
</form>

<script>    
    var loadPictureFile = function(event) {
        var picturePreview = document.getElementById('picturePreview');
        var filepath = URL.createObjectURL(event.target.files[0]);
        picturePreview.src = filepath;
        picturePreview.onload = function() {
            URL.revokeObjectURL(picturePreview.src) // free memory
        }
    };
    var loadVideoFile = function(event) {
        var videoPreview = document.getElementById('videoPreview');
        var video = document.getElementsByTagName('video');
        video[0].load();
        var filepath = URL.createObjectURL(event.target.files[0]);
        videoPreview.src = filepath;
        videoPreview.onload = function() {
            URL.revokeObjectURL(videoPreview.src) // free memory
        }
    };

    // document ready
    $(function() {
        // document.querySelector("video").addEventListener("error", function(e) {
        //     console.log("<video> error");
        //     console.log(e.target.error);
        // });
        // document.querySelector("source:last-child").addEventListener("error", function(e) {
        //     console.log("<source> error");
        //     console.log(e.target.error);
        //     $('#warning').text('Empty or corrupted video, try another');
        //     setTimeout(() => {
        //         $('#warning').text('');
        //     }, 3000);
        //     document.getElementById('video').value = "";
        // });
        // Variable to hold request
        var request;

        // Bind to the submit event of our form
        $("form").submit(function(event){

            // Prevent default posting of form - put here to work in case of errors
            event.preventDefault();

            // Abort any pending request
            if (request) {
                request.abort();
            }
            // setup some local variables
            var $form = $(this);

            // Let's select and cache all the fields
            var $inputs = $form.find("input, select, button, textarea");

            // Find disabled inputs, and remove the "disabled" attribute
            var disabled = $form.find(':input:disabled').removeAttr('disabled');

            // Serialize the data in the form
            var serializedData = new FormData(this);

            disabled.attr('disabled', 'disabled');
            // Let's disable the inputs for the duration of the Ajax request.
            // Note: we disable elements AFTER the form data has been serialized.
            // Disabled form elements will not be serialized.
            $inputs.prop("disabled", true);
            
            // Fire off the request to /upload.php
            request = $.ajax({
                url: "./upload.php",
                type: "post",
                data: serializedData,
                contentType: false,
                processData: false
            });
            
            // Callback handler that will be called on success
            request.done(function (response, textStatus, jqXHR){
                // Log a message to the console
                console.log("Hooray, it worked!");
                console.log(response);
                var preres = JSON.parse(response);
                if (preres.status === 'success') {
                    alert(preres.message);
                    serializedData.append('timestamp', preres.timestamp);
                    $.ajax({
                        // url: "http://localhost:3000/v1/upload",
                        url: "https://api.surtiled.nousproyect.com/v1/upload",
                        type: "post",
                        data: serializedData,
                        contentType: false,
                        processData: false
                    }).done(res => {
                        alert(res.message);
                        location.reload();
                    }).fail().always();
                } else {
                    alert(preres.message);
                    location.reload();
                }
            });

            // Callback handler that will be called on failure
            request.fail(function (jqXHR, textStatus, errorThrown){
                // Log the error to the console
                console.error(
                    "The following error occurred: "+
                    textStatus, errorThrown
                );
            });

            // Callback handler that will be called regardless
            // if the request failed or succeeded
            request.always(function () {
                // Reenable the inputs
                $inputs.prop("disabled", false);
            });

        });
    });
    
</script>

</body>
