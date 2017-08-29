<?php 

namespace Autowp\ImageHost\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Autowp\Image;
use Zend\InputFilter\InputFilter;

class FormatController extends AbstractRestfulController
{
    /**
     * @var Image\StorageInterface
     */
    private $storage;
    
    /**
     * @var InputFilter
     */
    private $listInputFilter;
    
    /**
     * @var InputFilter
     */
    private $deleteInputFilter;
    
    public function __construct(
        Image\StorageInterface $storage,
        InputFilter $listInputFilter,
        InputFilter $deleteInputFilter
    ) {
        $this->storage = $storage;
        $this->listInputFilter = $listInputFilter;
        $this->deleteInputFilter = $deleteInputFilter;
    }
    
    public function indexAction()
    {
        $this->listInputFilter->setData($this->params()->fromQuery());
        
        if (! $this->listInputFilter->isValid()) {
            return $this->inputFilterResponse($this->listInputFilter);
        }
        
        $data = $this->listInputFilter->getValues();
        
        $id = $data['id'];
        if (! is_array($id)) {
            $id = (array)$id;
        }
        
        $result = [];
        foreach ($this->storage->getFormatedImages($id, $data['format']) as $image) {
            $result[] = $image->toArray();
        }
        
        return new JsonModel([
            'items' => $result
        ]);
    }
    
    public function deleteAction()
    {
        $this->deleteInputFilter->setData($this->params()->fromQuery());
        
        if (! $this->deleteInputFilter->isValid()) {
            return $this->inputFilterResponse($this->deleteInputFilter);
        }
        
        $data = $this->deleteInputFilter->getValues();
        
        $options = [];
        
        if ($data['id']) {
            $options['image'] = $data['id'];
        }
        
        if ($data['format']) {
            $options['format'] = $data['format'];
        }
        
        $this->storage->flush($options);
        
        return $this->getResponse()->setStatusCode(204);
    }
}
