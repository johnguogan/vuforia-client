<?php
  
  include('vuforiaclient.php');
  define('KB', 1024);
  define('MB', 1048576);
  define('GB', 1073741824);
  define('TB', 1099511627776);

  $target_dir = "targets/";
  $uploads = "./uploads/";
  $fileBaseName = basename($_FILES["picture"]["name"]);
  $videofileBaseName = basename($_FILES["video"]["name"]);
  $upload_file = $uploads . $fileBaseName;
  $pictureTempPath = $_FILES["picture"]["tmp_name"];

  // Check if image file is a actual image or fake image
  $check = getimagesize($pictureTempPath);
  if ($check == false) {
    echo json_encode(['status' => 'fail', 'message' => "File is not an image."]);
    return;
  }

  // Check if file already exists
  // if (file_exists($target_file)) {
  //   echo json_encode(['status' => 'fail', 'message' => "Sorry, file already exists."]);
  //   return;
  // }
  
  $compressedImage = null;
  // Check file size
  if ($_FILES["picture"]["size"] > 2 * MB) {
    $compressedImage = compress_image($pictureTempPath, $upload_file);
  }
    
  $vforia = new VuforiaClient();
  $realPath = realpath($compressedImage ? $upload_file : $pictureTempPath);
  $realPathVideo = realpath($_FILES["video"]["tmp_name"]);
  $fileName = $_FILES["video"]["name"];

  $width = floatval($_POST["width"]);
  $response = $vforia->addTargetImage($realPath, $fileBaseName, $width);

  $response = json_decode($response);
  
  if ($response->target_id == 'none') return;

  $file = pathinfo($fileName);
  $folder = $file['filename'];
  $videoExtension = $file['extension'];
  $multiplier = $width / $check[0];

  $folder = preg_replace('/\s+/', '_', trim($folder));
  $folder = $folder . '_' . $response->timestamp;

  $data = array(
      'enabled' => true,
      'imageProperties' => array('height' => floatval(number_format($check[1] * $multiplier, 1, '.', '')), 'width' => $width),
      'isExpired' => false,
      'isPreview' => false,
      'isPrivate' => "",
      'pathToVideo' => $target_dir . $folder . '/' . $folder . "." . $videoExtension,
      'targetName' => $folder,
  );

  $firebase = new FirebaseClient();
  $firebase->insertData($response->target_id, $data);

  echo json_encode(['status' => 'success', 'message' => 'Vuforia success, target id is '. $response->target_id .' now firebase...', 'timestamp' => $response->timestamp]);  

  function compress_image($tempPath, $originalPath, $imageQuality = 60) {
  
    // Get image info 
    $imgInfo = getimagesize($tempPath); 
    $mime = $imgInfo['mime']; 
     
    // Create a new image from file 
    switch($mime){ 
        case 'image/jpeg': 
            $image = imagecreatefromjpeg($tempPath); 
            break; 
        case 'image/png': 
            $image = imagecreatefrompng($tempPath);
            break; 
        case 'image/gif': 
            $image = imagecreatefromgif($tempPath); 
            break; 
        default: 
            $image = imagecreatefromjpeg($tempPath); 
    } 
     
    // Save image 
    imagejpeg($image, $originalPath, $imageQuality);    
    // Return compressed image 
    return $originalPath; 
  }
?>