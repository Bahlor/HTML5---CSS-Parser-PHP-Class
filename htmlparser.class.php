<?php

/**
 * HTMLParser class
 *
 * This parser is normally used to prepare a website template for mailing. All external resources (stylesheets etc) and links are made to internal ones.
 *
 * @version   0.5
 * @author 		Christian Weber <christian@cw-internetdienste.de>
 * @link		http://www.cw-internetdienste.de
 *
 * freely distributable under the MIT Licence
 *
 */
 
 class htmlparser {
 	 /**
 	  * regex
 	  *
 	  *	Regular expression used to validate urls
 	  * 
 	  * (default value: '@\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))@')
 	  * 
 	  * @var string
 	  * @access private
 	  */
 	 private	$regex	=	'@\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))@';
 	 /**
 	  * encoding
 	  * 
 	  *	Default html encoding used 
 	  *
 	  * (default value: 'UTF-8')
 	  * 
 	  * @var string
 	  * @access private
 	  */
 	 private	$encoding	=	'UTF-8';
	 /**
	  * url
	  *
	  *	The root url used to rewrite existing resources
	  * 
	  * (default value: '')
	  * 
	  * @var string
	  * @access private
	  */
	 private 	$url			=	'';
	 /**
	  * plain
	  *
	  *	Stores the plain text version
	  * 
	  * (default value: '')
	  * 
	  * @var string
	  * @access private
	  */
	 private 	$plain			=	'';
	 /**
	  * finalhtml
	  * 
	  *	Stores the final html code
	  *
	  * (default value: '')
	  * 
	  * @var string
	  * @access private
	  */
	 private	$finalhtml		=	'';
	 /**
	  * htmlfile
	  *
	  *	Stores the users given source code or file
	  * 
	  * (default value: '')
	  * 
	  * @var string
	  * @access private
	  */
	 private	$htmlfile		=	'';
	 /**
	  * node
	  *
	  *	Stores the node object
	  * 
	  * (default value: null)
	  * 
	  * @var mixed
	  * @access private
	  */
	 private	$node			=	null;
	 /**
	  * document
	  *
	  *	Stores the document paht of the node object
	  * 
	  * (default value: null)
	  * 
	  * @var mixed
	  * @access private
	  */
	 private	$document		=	null;
	 /**
	  * nodes
	  *
	  *	Stores everything beginning at the html tag of the dom object
	  * 
	  * (default value: null)
	  * 
	  * @var mixed
	  * @access private
	  */
	 private	$nodes			=	null;
	 /**
	  * source
	  * 
	  *	Toggles whether $htmlfile is a source file or direct source input
	  *
	  * (default value: false)
	  * 
	  * @var bool
	  * @access private
	  */
	 private	$source			=	false;
	 
	 /**
	  * tidy
	  *
	  * Toggles tidy usage
	  * 
	  * (default value: false)
	  * 
	  * @var bool
	  * @access private
	  */
	 private $tidy				=	false;
	 
	 /**
	  * html
	  * 
	  * (default value: array())
	  * 
	  * @var array
	  * @access protected
	  */
	 protected 	$html		=	array();
	 /**
	  * selectors
	  * 
	  * (default value: array())
	  * 
	  * @var array
	  * @access protected
	  */
	 protected 	$selectors	=	array();
	 /**
	  * styles
	  * 
	  * (default value: array())
	  * 
	  * @var array
	  * @access protected
	  */
	 protected	$styles		=	array();
	 
	 public function __construct($htmlfile,$url,$source=false,$encoding='UTF-8') {
	 	 // if there is no html or valid url given, kill this object
		 if(!isset($htmlfile) || empty($htmlfile) || trim($htmlfile) == '' || !isset($url) || empty($url) || trim($url) == '' || !$this->validateURL($url) || !is_bool($source)) {	return false;	}
		 
		 if(substr($url,-1) !== '/') {	$url	.=	'/';	}
		 
		 $this->url	=	$url;
		 
		 // check if html is source or file
		 if($source!=false) {
			 $this->source	=	true;
		 }
		 
		 // check if tidy exists
		 if(function_exists('tidy_repair_string')) {
			 $this->tidy	=	true;
		 }
		 
		 // set html file encoding
		 $this->encoding	=	$encoding;
		 
		 // set html file
		 $this->htmlfile	=	$htmlfile;
		 
		 // load the html file
		 $this->loadHTML();
		
	 }
	 
	 public function get_plain() {
		 return $this->plain;
	 }
	 
	 public function get_html() {
		 return $this->finalhtml;
	 }
	 
	 /**
	 * validateURL function.
	 * 
	 * @access private
	 * @param mixed $url
	 * @return int
	 */
	private function validateURL($url) {
		if(function_exists('filter_var')) {
			return filter_var($url, FILTER_VALIDATE_URL);
		} else {
			return 	preg_match($this->regex,$url);
		}
	}
	 
	 /**
	  * loadHTML function.
	  * 
	  * @access private
	  */
	 private function loadHTML() {
	     // needed to make html5 tags work in domdocument
	 	 libxml_use_internal_errors(true);
	 	 
		 // create new dom object
		 $this->node	=	new DOMDocument();
		 
		 // set base tidy configuration
		 $config	=	array(	
	 						'char-encoding'		=>		$this->encoding,
	 						'output-encoding'	=>		$this->encoding,
	 						'output-xhtml'		=>		true
	 					);
		 
		 $html 	=	'';
		 
		 if($this->source	===	true) {
			 // tidy up html string
			 $html 	=	($this->tidy===true) ? tidy_repair_string($this->htmlfile,$config,$this->encoding):$this->htmlfile;
			 
			 // load source
			 $this->node->loadHTML($html);
		 } else {
			 // load source
			 $this->node->loadHTMLFile($this->htmlfile);
		 }
		 
		 //preserve whitespace
		 $this->node->preserveWhiteSpace	=	true;
		 
		 // get dom tree
		 $this->nodes		=	$this->node->getElementsByTagName('html');
		 
		 // set document root node
		 $this->document	=	$this->node->documentElement;
		 
		 // create a clean array of the domtree
		 $this->html		=	$this->createDomTree($this->nodes);
		 
		 // clean up markup
		 $this->cleanHTML();
		 
		 // save final html
		 $this->finalhtml	=	($this->tidy===true) ? tidy_repair_string($this->node->saveHTML(),$config,$this->encoding):$this->node->saveHTML();
		 
		 // update tidy configuration
		 $config['clean']			=	true;
		 $config['show-body-only']	=	true;
		 
		  // save plain text
		 $this->plain		=	($this->tidy===true) ? tidy_repair_string($this->plain,$config,$this->encoding):$this->plain;
		 libxml_clear_errors();
		 
	 }
	 
	 /**
	  * createDomTree function.
	  *
	  * Recursively generates a clean array of nested source dom tree
	  * 
	  * @access private
	  * @param DOMNodeList $nodes
	  * @return array $data
	  */
	 private function createDomTree(DOMNodeList $nodes) {
		 // node array
		 $data	=	array();
		 
		 // process each node
		 foreach($nodes as $key => $node) {
			 // get childs of current node
			 $childs	=	$node->childNodes;
			 // check if node has child nodes
			 if(!is_null($childs) && count($childs) > 0) {
			 	// add node name and fetch recursively sub-nodes
				 $d 	=	array(
				 					'element'	=>	$node->nodeName,
				 					'children'	=>	$this->createDomTree($childs)
				 				);
				 // check if its a css file. if true, load and parse the file
				 if($node->nodeName === 'link' || $node->nodeName === 'style') {
					 $this->parseCSS($node);
				 }
				 
				 // add plain text version
				 $this->addPlain($node);
				 // apply inline css and remove invalid or no more needed attributes
				 $this->checkNodeStyle($node);
			 } else {
				 // has value?
				 if($node->nodeValue != '') {
					 // save values
					 $d 	=	array(
					 					'element'	=>	$node->nodeName,
					 					'value'		=>	$this->checkData($node->nodeName,$node->nodeValue)
					 				);
					 // check if its a css file. if true, load and parse the file
					 if($node->nodeName === 'link' || $node->nodeName === 'style') {
						 $this->parseCSS($node);
					 }
					 
					 // apply inline css and remove invalid or no more needed attributes
					 $this->checkNodeStyle($node,1);
				 }
			 }
			 
			 // check if node has attributes
			 if($node->hasAttributes()) {
				 // fetch attributes
				 $d['attributes']	=	$this->getAttributes($node);
			 }
			 
			 if($node->nodeName != 'link' && $node->nodeName != 'script' && $node->nodeName != 'style') {
				 $data[]	=	$d;
			 }
		 }
		 
		 return $data;
	 }
	 
	 /**
	  * checkNodeStyle function.
	  * 
	  * @access private
	  * @param object $node
	  * @param int $type (default: 0)
	  */
	 private function checkNodeStyle($node,$type=0) {
	 	if(get_class($node) != 'DOMElement') {	return false; 	}
	 	
	 	if($type) {	$node 	=	$node->parentNode;	}
	 	
		// check what type is needed
		if($node->hasAttribute('class')) {
			// class selection
			// process the selectors
			$this->processClassSelectors($node);
		} elseif($node->hasAttribute('id')) {
			// id selection
			// process the selectors
			$this->processIDSelectors($node);
		} else {
			// tag selection
			// process teh selectors
			$this->processTagSelectors($node);
		}
	 }
	 
	 /**
	  * processClassSelectors function.
	  * 
	  * @access private
	  * @param DOMElement $node
	  */
	 private function processClassSelectors(DOMElement $node) {
		 if(!$node) { return false; }
		 		 
		 foreach($this->selectors as $key => $item) {
			 // check if selector is a valid class selector
			 if(strpos($item,'.') !== false) {
				 // seperate tag and class
				 // explode because class could be foo.bar and not just .bar
				 $class 	=	explode('.',$item);
				 
				 if((count($class) == 2 && !empty($class[0]) && $node->nodeName == $class[0] && $node->getAttribute('class') == $class[1]) || ((count($class) != 2 || empty($class[0])) && $node->getAttribute('class') == $class[1])) {
					 // remove class attribute
					 $node->removeAttribute('class');
					 // set inline css
					 ($node->hasAttribute('style')) ? $node->setAttribute('style',$node->getAttribute('style').$this->styles[$key]):$node->setAttribute('style',$this->styles[$key]);
				 }
				 
			 }
		 }
	 }
	 
	 /**
	  * processIDSelectors function.
	  * 
	  * @access private
	  * @param DOMElement $node
	  */
	 private function processIDSelectors(DOMElement $node) {
		 if(!$node) { return false; }
		 		 
		 foreach($this->selectors as $key => $item) {
			 // check if selector is a valid id selector
			 if(strpos($item,'#') !== false) {
				 // seperate tag and id
				 // explode because id could be foo#bar and not just #bar
				 $id 	=	explode('#',$item);
				 
				 if((count($id) == 2 && !empty($id[0]) && $node->nodeName == $id[0] && $node->getAttribute('id') == $class[1]) || ((count($id) != 2 || empty($id[0])) && $node->getAttribute('id') == $id[1])) {
					 // remove class attribute
					 $node->removeAttribute('id');
					 // set inline css
					 ($node->hasAttribute('style')) ? $node->setAttribute('style',$node->getAttribute('style').$this->styles[$key]):$node->setAttribute('style',$this->styles[$key]);
				 }
				 
			 }
		 }
	 }
	 
	 /**
	  * processTagSelectors function.
	  * 
	  * @access private
	  * @param DOMElement $node
	  */
	 private function processTagSelectors(DOMElement $node) {
		 if(!$node) { return false; }
		 		 
		 foreach($this->selectors as $key => $item) {
			 if($node->nodeName == $item) {
				 // remove class attribute
				 // set inline css
				 ($node->hasAttribute('style')) ? $node->setAttribute('style',$node->getAttribute('style').$this->styles[$key]):$node->setAttribute('style',$this->styles[$key]);
			 }
		 }
	 }
	 
	 /**
	  * checkData function.
	  * 
	  * @access private
	  * @param string $type
	  * @param string $val
	  * @return string $val
	  */
	 private function checkData($type,$val) {
		 if(!isset($type) || empty($type) || trim($type) === '' || !isset($val)) {	return false;	}
				 
		 // check if data needs to be changed
		 // TODO: check if url is already absolute
		 switch($type) {
			 case 'src':	if(substr($val,0,1) == '/') {	$val = substr($val,1);	}
			 				$val 	=	$this->encodeImage($this->url.$val);			//	relative to absolute path + base64 encode
			 				break;
			 case 'href':	if(substr($val,0,1) == '/') {	$val = substr($val,1);	}
			 				$val 	=	$this->url.$val;			//	relative to absolute path
			 				break;
			 case '#text':	$val 	=	htmlentities($val);			//	convert characters to correct entities
			 				break;
		 }
				 
		 return $val;
	 }
	 
	 private function encodeImage($src) {
	 	 // TODO: check for allowed image extension to exlude videos
		 $data	=	file_get_contents($src);
		 $mime	=	getimagesize($src);
		 
		 return 'data:'.$mime['mime'].';base64,'.base64_encode($data);
	 }
	 
	 /**
	  * addPlain function.
	  * 
	  * @access private
	  * @param DOMElement $node
	  */
	 private function addPlain(DOMElement $node) {
		 $text	=	'';
		 
		 // check type of node
		 switch($node->nodeName) {
			 case 'h1':
			 case 'h2':
			 case 'h3':
			 case 'h4':
			 case 'h5':
			 case 'h6':		$text	.=	''.strtoupper($node->nodeValue).PHP_EOL;
			 				for($i=0;$i<strlen($node->nodeValue);$i++) {
				 				$text.= '-';
			 				}
			 				$text	.=	PHP_EOL;
			 				break;
			 case '#text':	
			 case 'p':		$text	.=	$node->nodeValue.' '.PHP_EOL;
			 				break;
			 case 'br':		$text	.=	PHP_EOL;
			 				break;
			 case 'tr':		
			 case 'hr':		$text	.=	'-----------------------------------------------------'.PHP_EOL;
			 				break;
		 }
		 
		 // add to plain text version
		 if(!empty($text) && trim($text) != '') {	$this->plain	.=	$text;	}
	 }
	 
	 /**
	  * cleanHTML function.
	  * 
	  * Removes all unallowed data from source as style, script and link tags
	  * 
	  * @access private
	  */
	 private function cleanHTML() {
		 // clean all unallowed tags
		 $styles 		=	$this->node->getElementsByTagName('style');
		 $scripts		=	$this->node->getElementsByTagName('script');
		 $stylesheets	=	$this->node->getElementsByTagName('link');
		 		 		 
		 // delete style tags
		 foreach($styles as $node) {
			 $node->parentNode->removeChild($node);
		 }
		 
		// delete script tags
		 foreach($scripts as $node) {
			 $node->parentNode->removeChild($node);
		 }
		 
		 // delete link tags
		 foreach($stylesheets as $node) {
			 $node->parentNode->removeChild($node);
		 }
	 }
	 
	 /**
	  * getAttributes function.
	  *
	  * Rewrites the attributes of a node and checks them. Also prepares them for the $html array
	  * 
	  * @access private
	  * @param DOMElement $node
	  * @return array $attributes
	  */
	 private function getAttributes(DOMElement $node) {
		 // attribuets array
		 $attributes	=	array();
		 
		 $_attr			=	$node->attributes;
		 
		 foreach($_attr as $attr) {
		 	$val	=	$this->checkData($attr->nodeName,$attr->nodeValue);
			$attributes[]	=	array(
			 							'name'	=>	$attr->nodeName,
			 							'value'	=>	$val
			 						);
			$node->setAttribute($attr->nodeName,$val);
		 }
		 
		 return $attributes;
	 }
	 
	 /**
	  * parseCSS function.
	  * 
	  * Checks whether a node is a stylesheet or style tag. If true tokenizes the styles and adds css to internal variables
	  *
	  * @access private
	  * @param DOMElement $node
	  * @return int
	  */
	 private function parseCSS(DOMElement $node) {
		 // if node is no style tag or stylesheet return
		 if(!$node->hasAttribute('href') && !$node->nodeName == 'style') {	return false;	}
		 
		 // load the file or style tag value
		 $style	=	($node->nodeName=='style')	?	explode('<br />',nl2br($node->nodeValue)):file($this->checkData('href',$node->getAttribute('href')));
		 
		 $css	=	'';
		 
		 if(!is_array($style)) {	echo 'fail';	return false;	}
		 
		 // read style and strip whitespaces
		 foreach($style as $line) {
		 	if(substr($line,0,1) !== '@') {
			 	$css .= trim($line);
			 }
		 }
		 
		 // remove brackets via tokenizer
		 $token	=	strtok($css,'{}');
		 
		 $tokenized	=	array();
		 
		 // process tokenized elments
		 while($token !== false) {
			 $tokenized[]	=	$token;
			 $token 		=	strtok('{}');
		 }
		 
		 // check amount of elements
		 $size		=	count($tokenized);
		 
		 // process elements
		 for($i=0;$i<$size;$i+=2) {
		 	// can be multiple selectors for the same style, split them
		 	$selectors	=	explode(',',$tokenized[$i]);
		 	// assign selectors and their styles
		 	foreach($selectors as $selector) {
			 	$this->selectors[]	=	trim($selector);
			 	$this->styles[]		=	trim($tokenized[$i+1]);
		 	}
		 }
	 }
 }
?>
