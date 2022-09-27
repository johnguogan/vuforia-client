<?php
include('firebaseclient.php');
/*
* 2017 Patrick Münster / DaDracoDesign
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at

* http://www.apache.org/licenses/LICENSE-2.0

* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

class VuforiaClient {
    const JSON_CONTENT_TYPE = 'application/json';
    // ------------------ DEV KEYS -----------------------
    const ACCESS_KEY = 'b2902ad02b5b0f5657003c5dd54b556ec54f16eb';
    const SECRET_KEY = 'd7f6b1596d787f3360e3329d95bf032ba7083758';
    
    // ------------------ PROD KEYS -----------------------
    // const ACCESS_KEY = '4cc393ae96ed74ef0539266724aa206f1b8d9d1f';
    // const SECRET_KEY = '993e0606907649b2dfd29b35350ea68026374533';
    const BASE_URL = 'https://vws.vuforia.com';
    const TARGETS_PATH = '/targets';
    public $imagePath = '';
    public $imageName = '';

    public function addTargetImage($imageFile, $imageName, $width) {
        $this->imageName = $imageName;

        $ch = curl_init(self::BASE_URL . self::TARGETS_PATH);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $image = file_get_contents($imageFile);
        $image_base64 = base64_encode($image);
        
        // Use date to create unique filenames on server
        $date = new DateTime();
        $dateTime = $date->getTimestamp();
        $file = pathinfo($this->imageName);
        $filename      = $file['filename'];
        $fileextension = $file['extension'];
        
        $trimmed = trim($filename);
        $underlined = preg_replace('/\s+/', '_', $trimmed);

        $post_data = array(
            'name' => $underlined. "_" .$dateTime. "." .$fileextension,
            'width' => $width,
            'image' => $image_base64,
            'active_flag' => 1
        );

        $body = json_encode($post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders('POST', self::TARGETS_PATH, self::JSON_CONTENT_TYPE, $body));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        // print_r($info['request_header']);
        if ($info['http_code'] !== 201) {

            echo json_encode(['status' => 'fail', 'message' => 'Failed to add target http code is ' . $info['http_code'] . $response]);
            // print 'Failed to add target: ' . $response;
            return 'none';
        } else {

            $vuforiaTargetID = json_decode($response)->target_id;

            // echo json_encode(['status' => 'success', 'message' => 'Successfully added target ' . $vuforiaTargetID]);
            // print "\n" . 'Successfully added target: ' . $vuforiaTargetID . "\n";
            return json_encode(array('target_id' => $vuforiaTargetID, 'timestamp' => $dateTime));
            // return array('target_id' => $vuforiaTargetID, 'timestamp' => $dateTime);
        }
    }

     /**
     * Get the target record of a target from database accessed by the given keys.
     * @param vuforiaTargetID - ID of a target in Vuforia database
     * @return [String] - Vuforia Target Record
     */
     public function getTargetRecord($vuforiaTargetID) {
        $ch = curl_init(self::BASE_URL . self::TARGETS_PATH. "/" .$vuforiaTargetID);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders('GET', $path = self::TARGETS_PATH. '/' .$vuforiaTargetID, $content_type = '', $body = ''));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if ($info['http_code'] !== 200) {
            //return "No target with ID: " .$vuforiaTargetID;
            die('Failed to list targets: ' . $response . "\n");
        }
        // $trackinRate = json_decode($response)->target_record->tracking_rating;
        return json_decode($response);
    }

     /**
     * Delete a target from database accessed by the given keys.
     * @param vuforiaTargetID - ID of a target in Vuforia database
     * @return [String] Vuforia result_code
     */
     public function deleteTarget($vuforiaTargetID) {
        $path = self::TARGETS_PATH . "/" . $vuforiaTargetID;
        $ch = curl_init(self::BASE_URL . $path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders('DELETE', $path));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if ($info['http_code'] !== 200) {
            //return json_decode($response);
            die('Failed to delete target: ' . $response . "\n");
        }
        return json_decode($response);
    }

    /**
    * Delete all targets from database accessed by the given keys.
    * @return [String] Vuforia result_code
    */
    public function deleteAllTargets() {
        $ch = curl_init(self::BASE_URL . self::TARGETS_PATH);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders('GET'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if ($info['http_code'] !== 200) {
            die('Failed to list targets: ' . $response . "\n");
        }
        $targets = json_decode($response);
        foreach ($targets->results as $index => $id) {
            $path = self::TARGETS_PATH . "/" . $id;
            $ch = curl_init(self::BASE_URL . $path);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders('DELETE', $path));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $info = curl_getinfo($ch);
            if ($info['http_code'] !== 200) {
                die('Failed to delete target: ' . $response . "\n");
            }
            print "Deleted target $index of " . count($targets->results);
            return json_decode($response);
        }
    }
    
   /**
    * Get all targets from database accessed by the given keys.
    * @return [JSON String] Vuforia targets
    */
    public function getAllTargets() {
        $ch = curl_init(self::BASE_URL . self::TARGETS_PATH);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders('GET'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if ($info['http_code'] !== 200) {
            die('Failed to list targets: ' . $response . "\n");
        }
        return $targets = json_decode($response);
    }

    /**
    * Create a request header.
    * @return [Array] Header for request.
    */
    private function getHeaders($method, $path = self::TARGETS_PATH, $content_type = '', $body = '') {
        $headers = array();
        $date = new DateTime("now", new DateTimeZone("GMT"));
        $dateString = $date->format("D, d M Y H:i:s") . " GMT";
        $md5 = md5($body, false);
        $string_to_sign = $method . "\n" . $md5 . "\n" . $content_type . "\n" . $dateString . "\n" . $path;
        $signature = $this->hexToBase64(hash_hmac("sha1", $string_to_sign, self::SECRET_KEY));
        $headers[] = 'Authorization: VWS ' . self::ACCESS_KEY . ':' . $signature;
        $headers[] = 'Content-Type: ' . $content_type;
        $headers[] = 'Date: ' . $dateString;
        return $headers;
    }

    private function hexToBase64($hex){
        $return = "";
        foreach(str_split($hex, 2) as $pair){
            $return .= chr(hexdec($pair));
        }
        return base64_encode($return);
    }

    /**
    * Create a metadata for request. You can write any information into the metadata array you want to store.
    * @return [Array] Metadata for request.
    */
    private function createMetadata() {
        $metadata = array(
            // 'id' => 1,
            // 'image_url' => $this->imagePath.$this->imageName
        );
        return base64_encode(json_encode($metadata));
    }
}

// $vforia = new  VuforiaClient();

// $target = $vforia->deleteAllTargets();

// $target_id = $vforia->addTargetImage("screen_footer.png", "asdf.png", 1);
// $target = $vforia->getAllTargets();
// print_r($target);
// $data = $vforia->getTargetRecord(end($target->results));
// print_r($data);
// $firebase = new FirebaseClient();
// $firebase->insertData($target_id, $data);

// print_r($vforia->getAllTargets());

?>
