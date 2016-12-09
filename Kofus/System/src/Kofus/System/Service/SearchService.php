<?php
namespace Kofus\System\Service;

//require 'vendor/zendframework/zendsearch/library/ZendSearch/Lucene/Lucene.php';
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Kofus\System\Node\NodeInterface;
use Kofus\System\Service\AbstractService;


class SearchService extends AbstractService
{
    protected $indexPath = 'data/lucene';
    protected $indexes = array();
   
    /**
     * Get existing index.
     * Build a new index if it does not exist yet.
     * @return \ZendSearch\Lucene\SearchIndexInterface
     */
    public function getIndex($locale=null)
    {
    	if (! $locale) $locale = \Locale::getDefault();
        if (! isset($this->indexes[$locale])) {
            
            \ZendSearch\Lucene\Analysis\Analyzer\Analyzer::setDefault(
                new \ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8Num\CaseInsensitive()
            );
            \ZendSearch\Lucene\Search\QueryParser::setDefaultEncoding('utf-8');
            
            $path = $this->indexPath . '/' . $locale;
        	if (is_dir($path)) {
        		$this->indexes[$locale] = new \Kofus\System\Search\Index($path, false);
        	} else {
        		$this->indexes[$locale] = new \Kofus\System\Search\Index($path, true);
        	}
        }
        return $this->indexes[$locale];
    }
    
    public function updateNode(NodeInterface $node)
    {
        $this->deleteNode($node);
        
        // Get indexes
        $indexes = array();
        foreach ($this->config()->get('locales.enabled') as $locale) 
        	$indexes[$locale] = $this->getIndex($locale);
        
        $classnames = $this->config()->get('nodes.available.'.$node->getNodeType().'.search_documents', array());
        foreach ($classnames as $classname) {
        	foreach ($indexes as $locale => $index) {
	    		$document = new $classname();
	    		if ($document instanceof ServiceLocatorAwareInterface)
	    			$document->setServiceLocator($this->getServiceLocator());
	    		$document->populateNode($node, $locale);
	    		$index->addDocument($document);
        	}
        }
        
        foreach ($indexes as $index) {
        	$index->commit();
        	$index->optimize();
        }
    }
    
    public function deleteNode(NodeInterface $node)
    {
        // Get indexes
        $indexes = array();
        foreach ($this->config()->get('locales.enabled') as $locale) {
        	$index = $this->getIndex($locale);
        	$hits = $index->find("node_id: '" . $node->getNodeId() . "'");
        	foreach ($hits as $hit)
        		$index->delete($hit);
        	$index->commit();
        	$index->optimize();
        } 
    }
    
    public function deleteNodeType($nodeType)
    {
    	foreach ($this->config()->get('locales.enabled') as $locale) {
	    	$index = $this->getIndex($locale);
	    	$hits = $index->find("node_type: '" . $nodeType . "'");
	    	foreach ($hits as $hit)
	    		$index->delete($hit);
	    	$index->commit();
	    	$index->optimize();    	
    	}
    }
    
    public function reindex($nodeTypes=null)
    {
        ini_set('max_execution_time', 0);
        
        if (is_string($nodeTypes))
            $nodeTypes = array($nodeTypes);
        
        if (null === $nodeTypes)
            $nodeTypes = $this->config()->get('nodes.enabled');
            
        foreach ($this->config()->get('locales.enabled') as $locale) {
        	$index = $this->getIndex($locale);
        
	        foreach ($nodeTypes as $nodeType) {
	        
	            // Delete existing entries
	            $hits = $index->find("node_type: '" . $nodeType . "'");
	            foreach ($hits as $hit)
	            	$index->delete($hit);
	            $index->commit();
	            $index->optimize();
	            
	            // Add new entries
	            switch ($nodeType) {
	            	case 'LANGUAGE':
	            		$languages = $this->config()->get('nodes.available.LANGUAGE.values');
	            		foreach ($languages as $id) {
	            			$label = \Locale::getDisplayLanguage($id, $locale);
	            			$document = new \Kofus\System\Search\Document\LanguageDocument();
	            			$document->populate($id, $label);
	            			$index->addDocument($document);
	            		}
	            		break;
	            		
	            	case 'COUNTRY':
	            		$countries = $this->config()->get('nodes.available.COUNTRY.values');
	            		foreach ($countries as $id) {
	            			$label = \Locale::getDisplayRegion('-' . $id, $locale);
	            			$document = new \Kofus\System\Search\Document\CountryDocument();
	            			$document->populate($id, $label);
	            			$index->addDocument($document);
	            		}
	            		break;
	            		
	            	default:
	            		$classnames = $this->config()->get('nodes.available.'.$nodeType.'.search_documents', array());
	            		foreach ($classnames as $classname) {
	            			$nodes = $this->nodes()->getRepository($nodeType)->findAll();
	            			foreach ($nodes as $node) {
	            				$document = new $classname();
	            				if ($document instanceof ServiceLocatorAwareInterface)
	            					$document->setServiceLocator($this->getServiceLocator());
	            				$document->populateNode($node);
	            				$index->addDocument($document);
	            			}
	            		}
	            }
	            
	            $index->commit();
	        }
	        $index->optimize();
        }
    }
}