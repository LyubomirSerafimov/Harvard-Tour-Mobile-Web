<?php
/**
  * @package Core
  */

/**
  * @package Core
  */
class Sanitizer
{
    static private $tagTypes = array(
        // Groups of tags by type which can be combined by separating with '|' (eg: 'inline|block')
        'inline' => array('<b>', '<strong>', '<i>', '<em>', '<span>', '<code>'),
        'block'  => array('<div>', '<blockquote>', '<p>', '<hr>', '<br>', '<pre>'),
        'link'   => array('<a>'),
        'media'  => array('<img>', '<video>', '<audio>', '<iframe>'),
        'list'   => array('<ol>', '<ul>', '<li>', '<dl>', '<dd>', '<dt>'),
        'table'  => array('<table>', '<thead>', '<tbody>', '<tr>', '<th>', '<td>'),
        
        // Groups of tags by use case
        'editor' => array(
            '<b>', '<strong>', '<i>', '<em>', '<span>', '<code>', 
            '<div>', '<blockquote>', '<p>', '<hr>', '<br>', '<pre>', 
            '<a>', 
            '<img>', '<video>', '<audio>', '<iframe>', 
            '<ol>', '<ul>', '<li>', '<dl>', '<dd>', '<dt>', 
            '<table>', '<thead>', '<tbody>', '<tr>', '<th>', '<td>'),
    );
    
    static private $blockNodeNames = array(
        'div', 'blockquote', 'p', 'pre', 'ul', 'dl', 'table'
    );
    
    //
    // Filter to attempt to remove XSS injection attacks without removing HTML
    //
    public static function sanitizeHTML($string, $allowedTags='editor') {
        $useTagWhitelist = true;
        $tagWhitelist = array();
        
        if (is_array($allowedTags)) {
          $tagWhitelist = $allowedTags;
          
        } else if (is_string($allowedTags)) {
            $allowedArray = explode('|', $allowedTags);
            
            foreach ($allowedArray as $type) {
                if (isset(self::$tagTypes[$type])) {
                    $tagWhitelist = array_merge($tagWhitelist, self::$tagTypes[$type]);
                } else if ($type == 'all') {
                    $useTagWhitelist = false;
                    break;
                }
            }
        }
        $strippedString = $useTagWhitelist ? strip_tags($string, implode('', array_unique($tagWhitelist))) : $string;
        
        return preg_replace_callback('/<(.*?)>/i', array(get_class(), 'tagPregReplaceCallback'), $strippedString);
    }

    //
    // HTML-safe sanitization and truncation
    // $length is length to truncate at.
    // $margin is the amount greater than $length which the text must be before it truncates
    // $charset is the meta tag charset encoding
    //
    public static function sanitizeAndTruncateHTML($string, $length, $margin, $minLineLength=40, $allowedTags='editor', $encoding='utf-8') {
        $sanitized = self::sanitizeHTML($string, $allowedTags);
        
        $dom = new DOMDocument();
        @$dom->loadHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"><html><head><meta http-equiv="Content-Type" content="text/html; charset='.$encoding.'"/></head><body>'.$sanitized.'</body></html>');
        $dom->normalizeDocument();
        
        $bodies = $dom->getElementsByTagName('body');
        if ($bodies->length) {
            $count = self::walkForTruncation($dom, $bodies->item(0), $length, $margin, $minLineLength, $encoding, $lastTextNode);
            
            if ($count >= $length + $margin) {
                if ($lastTextNode) {
                    self::appendTruncationSuffix($dom, $lastTextNode);
                }
                // use truncated version if we have exceeded the margin:
                $parts = preg_split(';</?body[^>]*>;', $dom->saveHTML());
                if (count($parts) > 1) { // should be 3
                    $sanitized = $parts[1];
                }
            }
        }
        
        return $sanitized;
    }
    
    private static function walkForTruncation($dom, $node, $length, $margin, $minLineLength, $encoding, 
                                              &$lastTextNode, $count=0, &$currentBlock=null, &$currentBlockCount=0) {
        // We only truncate once the margin is exceeded.  This avoids the problem where
        // the truncated version is only a couple words less than the full version.
        // If we have exceeded the margin, we can stop counting and just delete nodes.
        // Otherwise we keep counting and truncate as needed.
        if ($count > ($length + $margin)) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
            
        } else if ($node->nodeType != XML_TEXT_NODE) {
            // only remove after counting text for margins
            if ($node->hasChildNodes()) {
                if (self::nodeIsBlock($node)) {
                    // new block started causing a newline, reset
                    $currentBlockCount = 0;
                    $currentBlock = $node;
                }
                
                // walk the children
                // because this function can change node's child count, figure out
                // which nodes we need to look at before calling ourselves recursively
                $childNodes = array();
                $nodeCount = $node->childNodes->length;
                for ($i = 0; $i < $nodeCount; $i++) {
                    $childNodes[] = $node->childNodes->item($i);
                }
                
                foreach ($childNodes as $childNode) {
                    $count = self::walkForTruncation($dom, $childNode, $length, $margin, $minLineLength, 
                        $encoding, $lastTextNode, $count, &$currentBlock, &$currentBlockCount);
                }
                
                if (self::nodeIsBlock($node)) {
                    // block ended causing another newline, reset
                    $currentBlockCount = 0;
                    $currentBlock = null;
                }
            }
            
        } else {
            // Text node!
            //
            // remove newlines and replace runs of whitespace with single space
            $text = preg_replace('/\s+/', ' ', str_replace("\n", '', $node->wholeText)); 
            $textLength = mb_strlen($text, $encoding);
            $remaining = $length - $count;
            if ($remaining > 0 && $currentBlockCount < $minLineLength) {
                $remaining = max($remaining, $minLineLength - $currentBlockCount);
            }
            
            if ($remaining > 0) {
                if (mb_strlen(trim($text), $encoding) > 0) {
                    // text node contains non-whitespace so can take ellipsis
                    $lastTextNode = $node;
                }
                
                if ($textLength > $remaining) {
                    // need to clip text node
                    $basicClipped = mb_substr($text, 0, $remaining + 1, $encoding);
                    
                    // truncate text node at a word nearest to $length
                    $clipped = preg_replace('/\s+?(\S+)?$/', '', $basicClipped);
                    $node->replaceData(0, $node->length, $clipped);
                    
                } else if (isset($currentBlock)) {
                    $currentBlockCount += $textLength;
                }
            } else {
                // past length, remove node but keep counting
                // since we haven't hit the limit
                $node->parentNode->removeChild($node);
            }
            
            $count += $textLength;
        }
        
        return $count;
    }
    
    private static function appendTruncationSuffix(&$dom, &$node, $replacementText=null) {
        static $truncationSuffix = null;
        
        if (!isset($truncationSuffix)) {
            $truncationSuffix = $dom->createElement('span');
            $truncationSuffix->appendChild($dom->createTextNode(
                Kurogo::getLocalizedString('SANITIZER_HTML_TRUNCATION_SUFFIX')));
            $truncationSuffix->setAttribute('class', 'trunctation-suffix');
        }
        
        $text = isset($replacementText) ? $replacementText : $node->wholeText;
        $clipped = preg_replace('/[.\s]*$/', '', $text);
        if (trim($clipped)) {
            $node->replaceData(0, $node->length, $clipped);
            
            $suffix = $truncationSuffix->cloneNode(true);
            if ($node->nextSibling) {
                $node->parentNode->insertBefore($suffix, $node->nextSibling);
            } else {
                $node->parentNode->appendChild($suffix);
            }
        }
    }
    
    private static function nodeIsBlock($node) {
        return $node->nodeType == XML_ELEMENT_NODE && 
               in_array(strtolower($node->nodeName), self::$blockNodeNames);
    }
    
    protected static function tagPregReplaceCallback($matches) {
        // From http://us3.php.net/manual/en/function.strip-tags.php
        static $regexps = array();
        static $replacements = array();
        
        if (!$regexps || !$replacements) {
            // Build these so the code is easier to read
            $jsAttributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
            $anyJSAttr = implode('|', $jsAttributes);
            
            $regexps = array(
                '/=\s*"\s*javascript:[^"]*"/i',                           // double-quoted attr with value containing js
                '/=\s*\'\s*javascript:[^\']*\'/i',                        // single-quoted attr with value containing js
                '/=\s*javascript:[^\s]*/i',                               // quoteless attr with value containing js
                '/('.$anyJSAttr.')\s*=\s*(["][^"]*["]|[\'][^\']*[\'])/i', // attr that triggers js
            );
            $replacements = array(
                '=""', // remove js in attr value
                '=""', // remove js in attr value
                '=""', // remove js in attr value
                '',    // remove attr that triggers js
            );
        }
        
        return preg_replace($regexps, $replacements, $matches[0]);
    }

    //
    // Filter to remove javascript from urls
    // Assumes URL is dumped into href or src attr as-is
    //
    public static function sanitizeURL($string) {
        return preg_replace('/javascript:.*/i', '', strip_tags($string));
    }
}
