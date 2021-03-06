<?php

namespace Kofus\System\View\Helper;
use Zend\View\Helper\AbstractHelper;

class BodyTagHelper extends AbstractHelper
{
	protected $css = array();
	protected $html = array();
	protected $styles = array();
	protected $attribs = array();
	
    public function __invoke()
    {
    	return $this;
    }
    
    public function addCss($className)
    {
    	$this->css[] = $className; return $this;
    }
    
    public function hasCss($className)
    {
        return in_array($className, $this->css);
    }
    
    public function appendHtml($html, $position='beforeClose')
    {
        $this->html[$position][] = $html;
    }
    
    public function setStyle($key, $value)
    {
        $this->styles[$key] = $value;
    }
    
    public function getClassNames()
    {
    	return $this->css;
    }
    
    public function setAttribute($key, $value)
    {
        $this->attribs[$key] = $value; return $this;
    }
    
    public function getAttribute($key)
    {
        if (isset($this->attribs[$key])) return $this->attribs[$key];
    }
    
    public function openTag()
    {
    	$s = '<body';
    	if ($this->css) {
    		$css = implode(' ', $this->css);
    		$s .= ' class="' . $this->view->escapeHtmlAttr($css) . '"';
    	}
    	if ($this->styles) {
    	    $styles = array();
    	    foreach ($this->styles as $key => $value) {
    	        $styles[] = $key . ': ' . $value;
    	    }
    	    $s .= ' style="' . $this->view->escapeHtmlAttr(implode('; ', $styles)). '"';
    	}
    	
    	foreach ($this->attribs as $key => $value) {
    	    $s .= ' ' . $key . '="' . $this->view->escapeHtmlAttr($value) . '"';
    	}
    	 
    	return $s . '>';
    }
    
    public function closeTag()
    {
        $html = '';
        if (isset($this->html['beforeClose']))
            $html .= implode("\n", $this->html['beforeClose']);    
        
        $html .= "\n</body>";
        
    	return $html;
    }
    
    public function __toString()
    {
    	return $this->openTag();
    }
}