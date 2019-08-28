<?php

namespace Kofus\System\Service;

use Kofus\System\Service\AbstractService;
use Kofus\System\Node\NodeInterface;

class TranslationService extends AbstractService
{
    public function translateNode($node, $method, $arguments=array(), $locale=null)
    {
        $value = null;
        if (is_a($node, 'Kofus\System\Node\TranslatableNodeInterface')) {
        	$methods = $node->getTranslatableMethods();
        	$msgId = 'KOFUS_NODE_' . $node->getNodeId() . ':' . $method;
        	if (isset($methods[$method]))
        		$value = $this->getTranslator()->translate($msgId, 'default', $locale);
        	if ($value == $msgId) $value = null;
        }
        if (! $value)
        	$value = $node->$method($arguments);
        return $value;
    }	
    
    protected $translator;
    
    public function getTranslator()
    {
        if (! $this->translator)
            $this->translator = $this->getServiceLocator()->get('translator');
        return $this->translator;
    }
    
    public function addNodeTranslation(NodeInterface $node, $method, $value, $locale)
    {
        $msgId = $node->getNodeId() . ':' . $method;
        $msg = $this->em()->getRepository('Kofus\System\Entity\NodeTranslationEntity')->findOneBy(array('msgId' => $msgId, 'locale' => $locale));
        if (! $msg) {
        	$msg = new \Kofus\System\Entity\NodeTranslationEntity();
        	$msg->setMsgId($msgId)->setLocale($locale);
        }
        $msg->setValue($value);
        $this->em()->persist($msg);
        $this->em()->flush();
    }
    
    public function deleteNodeTranslations($nodeId)
    {
        if ($nodeId instanceof NodeInterface)
            $nodeId = $nodeId->getNodeId();
        $qb = $this->em()->createQueryBuilder()
            ->delete('\Kofus\System\Entity\NodeTranslationEntity', 't')
            ->where('t.msgId LIKE :msgId')
            ->setParameter('msgId' , $nodeId . ':%')
            ->getQuery()
            ->execute();
    }
}