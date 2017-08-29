<?php 

namespace Autowp\ImageHost\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Autowp\Image;
use Zend\InputFilter\InputFilter;

class ImageController extends AbstractRestfulController
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
    private $getInputFilter;
    
    /**
     * @var InputFilter
     */
    private $postInputFilter;
    
    /**
     * @var InputFilter
     */
    private $putInputFilter;
    
    public function __construct(
        Image\StorageInterface $storage,
        InputFilter $listInputFilter,
        InputFilter $getInputFilter,
        InputFilter $postInputFilter,
        InputFilter $putInputFilter
    ) {
        $this->storage = $storage;
        $this->listInputFilter = $listInputFilter;
        $this->getInputFilter = $getInputFilter;
        $this->postInputFilter = $postInputFilter;
        $this->putInputFilter = $putInputFilter;
    }
    
    private function parseFields(string $value): array
    {
        $result = [];
        foreach (explode(',', $value) as $field) {
            $result[trim($field)] = true;
        }
        
        return $result;
    }
    
    private function extractImage(Image\Storage\Image $image, array $fields): array
    {
        $data = $image->toArray();
        
        if (isset($fields['iptc']) && $fields['iptc']) {
            $data['iptc'] = $this->storage->getImageIPTC($image->getId());
        }
        
        if (isset($fields['exif']) && $fields['exif']) {
            $data['exif'] = $this->storage->getImageEXIF($image->getId());
        }
        
        if (isset($fields['resolution']) && $fields['resolution']) {
            $data['resolution'] = $this->storage->getImageResolution($image->getId());
        }
        
        return $data;
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
        
        $fields = $this->parseFields((string)$data['fields']);
        
        $result = [];
        foreach ($this->storage->getImages($id) as $image) {
            $result[] = $this->extractImage($image, $fields);
        }
        
        return new JsonModel([
            'items' => $result
        ]);
    }
    
    public function postAction()
    {
        $request = $this->getRequest();
        
        $data = array_merge_recursive(
            $request->getPost()->toArray(),
            $request->getFiles()->toArray()
        );
        
        $this->postInputFilter->setData($data);
        
        if (! $this->postInputFilter->isValid()) {
            return $this->inputFilterResponse($this->postInputFilter);
        }
        
        $data = $this->postInputFilter->getValues();
        
        $options = [];
        if ($data['name']) {
            $options['pattern'] = $data['name'];
        }
        
        $imageId = $this->storage->addImageFromFile($data['file']['tmp_name'], $data['dir'], $options);
        
        $url = $this->url()->fromRoute('api/image/item/get', [
            'id' => $imageId
        ]);
        
        $this->getResponse()->getHeaders()->addHeaderLine('Location', $url);
        return $this->getResponse()->setStatusCode(201);
    }
    
    public function getAction()
    {
        $this->getInputFilter->setData($this->params()->fromQuery());
        
        if (! $this->getInputFilter->isValid()) {
            return $this->inputFilterResponse($this->getInputFilter);
        }
        
        $data = $this->getInputFilter->getValues();
        
        $id = (int)$this->params('id');
        
        $image = $this->storage->getImage($id);
        
        $fields = $this->parseFields((string)$data['fields']);
        
        return new JsonModel(
            $this->extractImage($image, $fields)
        );
    }
    
    public function deleteAction()
    {
        $id = (int)$this->params('id');
        
        $this->storage->removeImage($id);
        
        return $this->getResponse()->setStatusCode(204);
    }
    
    public function putAction()
    {
        $imageId = (int)$this->params('id');
        
        $data = $this->processBodyContent($this->getRequest());
        
        $this->putInputFilter->setData($data);
        
        if (! $this->putInputFilter->isValid()) {
            return $this->inputFilterResponse($this->putInputFilter);
        }
        
        $data = $this->putInputFilter->getValues();
        
        if ($data['flop']) {
            $this->storage->flop($imageId);
        }
        
        if ($data['normalize']) {
            $this->storage->normalize($imageId);
        }
        
        if ($data['name']) {
            $this->storage->changeImageName($imageId, [
                'pattern' => $data['name']
            ]);
        }
        
        return $this->getResponse()->setStatusCode(200);
    }
}