<?php

$url = urldecode($_GET['url']);
$buf = parse_url($url);
$url = str_replace($buf['scheme'].'://'.$buf['host'], '', $url);

include './L.php';
echo '<pre>';
echo LF('file://./logs')->read($url);
echo '</pre>';