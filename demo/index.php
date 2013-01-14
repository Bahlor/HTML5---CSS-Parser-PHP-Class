<?php

define('WEB_URL',	YOUR_WEBSITE_URL_HERE);

include('../htmlparser.class.php');

$parser	=	new htmlparser('testpage/index.html',WEB_URL);

echo $parser->get_html();
?>