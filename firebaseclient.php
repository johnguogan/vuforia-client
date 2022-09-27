<?php

require_once __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;

class FirebaseClient {
    const SERVICE_ACCOUNT = "europe-west-6729e-firebase-adminsdk-ruawc-0eb7da6061.json";
    const DATABASE_URL = "https://europe-west-6729e-default-rtdb.firebaseio.com";
    // const SERVICE_ACCOUNT = "play-ar-ingsoft-firebase-adminsdk-785rr-b3cbbb8810.json";
    // const DATABASE_URL = "https://play-ar-ingsoft-default-rtdb.europe-west1.firebasedatabase.app";
    
    public function addFile($filePath, $folder, $fileName) {
        $trimmed = trim(basename($fileName));
        $underlined = preg_replace('/\s+/', '_', $trimmed);
        $targetPath = 'targets/' . $folder . '/' . $underlined;
        $factory = (new Factory)->withServiceAccount(self::SERVICE_ACCOUNT);
        $storage = $factory->createStorage();
        $bucket = $storage->getBucket();
        $file = fopen($filePath, 'r');
        $object = $bucket->upload($file, [
            'name' => $targetPath
        ]);
        // echo $object->info()["mediaLink"];
        // echo $object->signedUrl(time() + 3600);
    }
        
    public function insertData($target_id, $data) {
        $factory = (new Factory)->withServiceAccount(self::SERVICE_ACCOUNT)->withDatabaseUri(self::DATABASE_URL);
        $database = $factory->createDatabase();
        $result = $database->getReference('imageTargets/'.$target_id)->set($data);
    }

    public function updateData($target_id, $data) {
        $factory = (new Factory)->withServiceAccount(self::SERVICE_ACCOUNT)->withDatabaseUri(self::DATABASE_URL);
        $database = $factory->createDatabase();
        $result = $database->getReference('imageTargets/'.$target_id)->update($data);
    }
    
    public function clearData() {
        $factory = (new Factory)->withServiceAccount(self::SERVICE_ACCOUNT)->withDatabaseUri(self::DATABASE_URL);
        $database = $factory->createDatabase();
        $result = $database->getReference()->set(null);
    }

}

// $firebase = new FirebaseClient();
// $firebase->clearData();
// $firebase->updateData('asdfss', array('isPrivate' => 'dddd'));

?>