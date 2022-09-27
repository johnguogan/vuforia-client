<?php

$text = " asdf   eedf dfl  sdfl     ";
$text = preg_replace('/\s+/', '_', trim($text));
var_dump($text)

?>

<script>
    var a = " d  df sdf  asdf sdfgd    sdf     ";
    a.trim().replace(/\s\s+/g, ' ').replace(/ /g, "_");
</script>