HTML5---CSS-Parser-PHP-Class
============================

A class to parse html5 and css. Outputs html with inlined css for newsletters etc. (includes automatic base64 encoding of images). Feel free to contribute. There are still many flaws in the script, it's not really optimized and some parts are not really well coded.

##Basic Usage
=============

The htmlparser was intended to be used to quickly transform a simple html page to a newsletter template. It has the following features:

* Stripping out undesired tags as script, style or link
* Transforming relative paths of images / links etc. to absolute paths
* Automatic base64 encoding of images
* Generating an automated plain version
* Adding inline css of style tags and external stylesheets

You actually only need two things, a website you want to parse and the absolute base url. An example:

```php
<?php
// include the class
include('htmlparser.class.php');
// parse the website
$parser = new htmlparser('/newsletters/test1.html','http://www.testurl.de');
// output html version
echo $parser->get_html();
// output plain text version
echo $parser->get_plain();
?>
```
