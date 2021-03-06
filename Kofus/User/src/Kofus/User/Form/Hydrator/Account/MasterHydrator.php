<?php
namespace Kofus\User\Form\Hydrator\Account;

use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class MasterHydrator implements HydratorInterface, ServiceLocatorAwareInterface
{

    public function extract($object)
    {
        $roleId = null;
        if ($object->getRole()) $roleId = $object->getRole()->getNodeId();
        
        return array(
            'name' => $object->getName(),
            'status' => $object->getStatus(),
            'role' => $roleId
        );
    }

    public function hydrate(array $data, $object)
    {
        $role = null;
        if ($data['role']) $role = $this->nodes()->getNode($data['role'], 'UR');
        
        $object->setName($data['name']);
        $object->setStatus($data['status']);
        $object->setRole($role);
        return $object;
    }
    
    
    protected function nodes()
    {
        return $this->getServiceLocator()->get('KofusNodeService');
    }
    
    protected $sm;
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
    }
    
    public function getServiceLocator()
    {
        return $this->sm;
    }
    
}