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

// Render minimal CSS
?>
<style type="text/css">
body {
    font-size: 18px;
    background: #ddd;
}
body > article,
body > section {
    max-width: 50em;
    margin: 3em auto;
}
.now_page_item {
    background: #fff;
    padding: 1em 1em;
    border-radius: 0.2em;
}
</style>
<?php

// Render Table of Contents.
echo '<section class="item toc">';
echo "<h1>Table of Contents</h1>";
echo "<ul>";
$i = 0;
foreach ($all_pages as $url => $content) {
    $i++;?>
    <li><a href="#_<?=$i?>"><?=$url?></a></li>
<?php }
echo "</ul>";
echo "</section>";


// Render actual pages.
$i = 0;
foreach ($all_pages as $url => $content) {
    $i++;?>
    <section>
      <h1 id="_<?=$i?>">From: <?=$url?></h1>
      <article class="now_page_item">
        <?php echo $content; ?>
      </article>
    </section>
<?php }
