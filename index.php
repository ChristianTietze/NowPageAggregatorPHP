<?php
namespace HNow;

use DOMDocument;
use DOMElement;
use DOMXPath;
use DOMNode;
use DOMNodeList;


/******************************************************************************
 * Helper functions
 ******************************************************************************/

function head() {
    ?>
    <html>
    <head>
    <title>/now Page Aggregator</title>
    <style type="text/css">
    <!--
    body {
        font-size: 18px;
        background: #ddd;
    }
    article, section, footer {
        max-width: 50em;
        margin: 3em auto;
    }
    .error { background: red; color: white; }
    .now_page_item {
        background: #fff;
        padding: 1em 1em;
        border-radius: 0.2em;
    }
    -->
    </style>
    </head>
    <body>
    <?php
}

function foot() {
    ?>
    <footer>
      <a href="https://github.com/ChristianTietze/NowPageAggregatorPHP">Source on GitHub</a>
    </footer>
    </body>
    </html>
    <?php
}

function show_form() {
    ?>
    <section class="form">
    <h1>Enter URLs below</h1>
    <p>You can pass up to 10 as URL parameters. 11+ will be ignored. (We want to be good internet citizens in this demo.)</p>
    <form action="/" method="get">
    <input type="text" name="urls[]" placeholder="URL 1"/><br>
    <input type="text" name="urls[]" placeholder="URL 2"/><br>
    <input type="text" name="urls[]" placeholder="URL 3"/><br>
    <input type="text" name="urls[]" placeholder="URL 4"/><br>
    <input type="text" name="urls[]" placeholder="URL 5"/><br>
    <input type="text" name="urls[]" placeholder="URL 6"/><br>
    <input type="text" name="urls[]" placeholder="URL 7"/><br>
    <input type="text" name="urls[]" placeholder="URL 8"/><br>
    <input type="text" name="urls[]" placeholder="URL 9"/><br>
    <input type="text" name="urls[]" placeholder="URL 10"/><br>
    <input type="submit" value="Fetch Pages"/>
    </form>
    </section>
    <?php
}

// From https://github.com/microformats/php-mf2/blob/master/Mf2/Parser.php
function fetch($url, &$curlInfo=null) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Accept: text/html'
	));
	$html = curl_exec($ch);
	$info = $curlInfo = curl_getinfo($ch);
	curl_close($ch);
    
    // var_dump($info);
    
	if (strpos(strtolower($info['content_type']), 'html') === false) {
		// The content was not delivered as HTML, do not attempt to parse it.
		return null;
	}

    // Eventually report final URL after redirects?
	// $url = $info['url'];

    return $html;
}

// From https://github.com/microformats/php-mf2/blob/master/Mf2/Parser.php
function unicodeToHtmlEntities($input) {
    return mb_convert_encoding($input, 'HTML-ENTITIES', mb_detect_encoding($input));
}

function extract_content($html) {
    $doc = new DOMDocument();
    $doc->loadHTML(unicodeToHtmlEntities($html));
    $xpath = new DOMXPath($doc);

    // Ignore <template> elements as per the HTML5 spec
    foreach ($xpath->query("//template") as $templateEl) {
        $templateEl->parentNode->removeChild($templateEl);
    }

    // Ignore multiple `h-now` items. Remember, this is not a feed!
    $h_now_element = $xpath->query(".//*[contains(@class,\"h-now\")]")->item(0);
    if ($h_now_element) {
        return $doc->saveHTML($h_now_element);
    }

    // Fall back to generic content identifiers.
    $content_wrapper_node = $doc->getElementById("content")
                          ?? $doc->getElementsByTagName("main")->item(0)
                          ?? $doc->getElementsByTagName("article")->item(0);

    if ($content_wrapper_node) {
        return $doc->saveHTML($content_wrapper_node);
    }

    return null;
}
                   
function obtain_now_page($url) {
    // Hide DOM parsing errors from the page output.
    libxml_use_internal_errors(true);
    
    $html = fetch($url);
    if (!$html) {
        return "(Could not resolve URL)";
    }

    $content = extract_content($html);
    if (!$content) {
        return "(No content wrapper found)";
    }

    // Remove all but the essential content tags for reading.
    $allowed_tags = array(
        "h1", "h2", "h3", "h4", "h5", "h6",
        "table", "tr", "td", "th", "tbody", "thead", "tfoot",
        "li", "ol", "ul", "dl", "dt", "dh",
        "br", "hr",
        "blockquote", "p", 
        "code", "abbr", "del", "a", "span", "mark", "small",
        "b", "i", "u", "s", "strong", "em", "strike"
    );
    return strip_tags($content, $allowed_tags);
}

function show_error($msg) {
    ?>
    <section class="error">
    <p><?= $msg; ?></p>
    </section>
    <?php
}


/******************************************************************************
 * Main script to process /now pages
 ******************************************************************************/

if (!isset($_GET['urls'])) {
    head();
    show_form();
    foot();
    die();
}

$urls = $_GET['urls'];

if (!$urls || !is_array($urls)) {
    head();
    show_error("Invalid <code>urls</code> parameter. Array expected.");
    show_form();
    foot();
    die();
}

// Ignore empty form input
$urls = array_filter($urls, function ($url) { return !empty($url); });

if (empty($urls)) {
    head();
    show_error("URLs array is empty.");
    show_form();
    foot();
    die();
}

// Limit to 10 and remove duplicates.
$urls = array_unique(array_slice($urls, 0, 10));

// Parse all pages
$all_pages = [];
foreach ($urls as $url) {
    // Only request each URL once.
    if (array_key_exists($url, $urls)) {
        continue;
    }
    $all_pages[$url] = obtain_now_page($url);
}

head();

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

foot();
