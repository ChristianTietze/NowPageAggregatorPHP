<?php
function show_form() {
    ?>
    <h1>Enter URLs below</h1>
    <form action="/" method="get">
    <input type="text" name="urls[]" placeholder="URL 1"/><br>
    <input type="text" name="urls[]" placeholder="URL 2"/><br>
    <input type="text" name="urls[]" placeholder="URL 3"/><br>
    <input type="submit" title="Submit"/>
    </form>
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
