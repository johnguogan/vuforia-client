<?php

    include('firebaseclient.php');
    if (isset($_POST['targetID'])) {
        $target_id = $_POST['targetID'];
        $expired = isset($_POST['expired']) && $_POST['expired'] === 'on' ? true : false;
        $preview = isset($_POST['preview']) && $_POST['preview'] === 'on' ? true : false;
        $enabled = isset($_POST['enabled']) && $_POST['enabled'] === 'on' ? true : false;
        $private = isset($_POST['private']) ? $_POST['private'] : '';
    
        $firebase = new FirebaseClient();
        $firebase->updateData($target_id, array('enabled' => $enabled, 'isPreview' => $preview, 'isExpired' => $expired, 'isPrivate' => $private));
        unset($_POST['targetID']);
    }
    
?>
<html>
<head>
<style>
label {
    font: 1rem 'Fira Sans', sans-serif;
}

input,
label {
    margin: .4rem 0;
}
</style>
</head>
<body>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <h2>Update target status</h2>
        <div>
            <label for="targetID">Target ID:</label>
            <input type="text" id="targetID" name="targetID" required>
        </div>
        <div>
            <input type="checkbox" id="enabled" name="enabled">
            <label for="enabled">enabled</label>
        </div>
        <div>
            <input type="checkbox" id="expired" name="expired">
            <label for="expired">isExpired</label>
        </div>
        <div>
            <input type="checkbox" id="preview" name="preview">
            <label for="preview">isPreview</label>
        </div>
        <div>
            <label for="private">isPrivate:</label>
            <input type="text" id="private" name="private">
        </div>
        <input type="submit" value="Update" name="submit">
    </form>
</body>
