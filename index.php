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

function obtain_now_page($url) {
    return "foo";
}

if (!isset($_GET['urls'])) {
    show_form();
}

$urls = $_GET['urls'];

if (!$urls || !is_array($urls)) {
    show_form();
}

// Parse all pages
$all_pages = [];
foreach ($urls as $url) {
    $all_pages[$url] = obtain_now_page($url);
}

// Render Table of Contents
echo "<h1>Table of Contents</h1>";
echo "<ul>";
$i = 0;
foreach ($all_pages as $url => $content) {
    $i++;?>
    <li><a href="#<?=$i?>"><?=$url?></a></li>
<?php }
echo "</ul>";


// Render actual pages
$i = 0;
foreach ($all_pages as $url => $content) {
    $i++;?>
    <hr>
    <article>
    <h1 name="<?=$i?>">From: <?=$url?></h1>
    <?php echo $content; ?>
    </article>
<?php }
