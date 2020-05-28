<?php
function show_form() {
    ?>
    <p><b>No URLs received.</b></p>
    <?php die();
}

if (!isset($_GET['urls'])) {
    show_form();
}

$urls = $_GET['urls'];

if (!$urls || !is_array($urls)) {
    show_form();
}

foreach ($urls as $url) {
    echo $url . "<br>";
}
