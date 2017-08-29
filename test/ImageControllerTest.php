<?php

namespace AutowpImageHostTest\Controller;

use Zend\Http\Request;

use Autowp\ImageHost\Controller\ImageController;
use Autowp\ImageHost\Test\AbstractHttpControllerTestCase;
use Zend\Json\Json;

class ImageControllerTest extends AbstractHttpControllerTestCase
{
    protected $applicationConfigPath = __DIR__ . '/../config/application.config.php';

    public function testPostGetDelete()
    {
        /**
         * @var \Zend\Http\PhpEnvironment\Request $request
         */
        $request = $this->getRequest();
        $request->getHeaders()->addHeaderLine('Content-Type', 'multipart/form-data');

        $file = tempnam(sys_get_temp_dir(), 'upl');
        $filename = 'image.jpg';
        copy(__DIR__ . '/_files/' . $filename, $file);

        $request->getFiles()->fromArray([
            'file' => [
                'tmp_name' => $file,
                'name'     => $filename,
                'error'    => UPLOAD_ERR_OK,
                'type'     => 'image/jpeg'
            ]
        ]);
        $this->dispatch('/api/image', Request::METHOD_POST, [
            'dir' => 'bar'
        ], true);


        $this->assertResponseStatusCode(201);
        $this->assertControllerName(ImageController::class);
        $this->assertMatchedRouteName('api/image/post');
        $this->assertActionName('post');


        $headers = $this->getResponse()->getHeaders();
        $uri = $headers->get('Location')->uri();
        $parts = explode('/', $uri->getPath());
        $imageId = $parts[count($parts) - 1];


        // get single image
        $this->reset();

        $this->dispatch('/api/image/' . $imageId, Request::METHOD_GET, [
            'fields' => 'iptc,exif,resolution'
        ]);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName(ImageController::class);
        $this->assertMatchedRouteName('api/image/item/get');
        $this->assertActionName('get');

        $json = Json::decode($this->getResponse()->getContent(), Json::TYPE_ARRAY);

        //$this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('width', $json);
        $this->assertArrayHasKey('height', $json);
        $this->assertArrayHasKey('src', $json);
        $this->assertArrayHasKey('filesize', $json);
        $this->assertArrayHasKey('iptc', $json);
        $this->assertArrayHasKey('exif', $json);
        $this->assertArrayHasKey('resolution', $json);


        // get list
        $this->reset();

        $this->dispatch('/api/image', Request::METHOD_GET, [
            'fields' => 'iptc,exif,resolution',
            'id'     => [$imageId]
        ]);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName(ImageController::class);
        $this->assertMatchedRouteName('api/image/get');
        $this->assertActionName('index');

        $json = Json::decode($this->getResponse()->getContent(), Json::TYPE_ARRAY);

        foreach ($json['items'] as $item) {
            //$this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('width', $item);
            $this->assertArrayHasKey('height', $item);
            $this->assertArrayHasKey('src', $item);
            $this->assertArrayHasKey('filesize', $item);
            $this->assertArrayHasKey('iptc', $item);
            $this->assertArrayHasKey('exif', $item);
            $this->assertArrayHasKey('resolution', $item);
        }

        // flop&normalize image
        $this->reset();

        $this->dispatch('/api/image/' . $imageId, Request::METHOD_PUT, [
            'flop'      => true,
            'normalize' => true
        ]);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName(ImageController::class);
        $this->assertMatchedRouteName('api/image/item/put');
        $this->assertActionName('put');

        // delete
        $this->reset();

        $this->dispatch('/api/image/' . $imageId, Request::METHOD_DELETE);

        $this->assertResponseStatusCode(204);
        $this->assertControllerName(ImageController::class);
        $this->assertMatchedRouteName('api/image/item/delete');
        $this->assertActionName('delete');
    }

    public function testName()
    {
        /**
         * @var \Zend\Http\PhpEnvironment\Request $request
         */
        $request = $this->getRequest();
        $request->getHeaders()->addHeaderLine('Content-Type', 'multipart/form-data');

        $file = tempnam(sys_get_temp_dir(), 'upl');
        $filename = 'image.jpg';
        copy(__DIR__ . '/_files/' . $filename, $file);

        $request->getFiles()->fromArray([
            'file' => [
                'tmp_name' => $file,
                'name'     => $filename,
                'error'    => UPLOAD_ERR_OK,
                'type'     => 'image/jpeg'
            ]
        ]);
        $this->dispatch('/api/image', Request::METHOD_POST, [
            'name' => 'Example Image Name',
            'dir'  => 'foo'
        ], true);


        $this->assertResponseStatusCode(201);
        $this->assertControllerName(ImageController::class);
        $this->assertMatchedRouteName('api/image/post');
        $this->assertActionName('post');


        $headers = $this->getResponse()->getHeaders();
        $uri = $headers->get('Location')->uri();
        $parts = explode('/', $uri->getPath());
        $imageId = $parts[count($parts) - 1];

        // get single image
        $this->reset();

        $this->dispatch('/api/image/' . $imageId, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);

        $json = Json::decode($this->getResponse()->getContent(), Json::TYPE_ARRAY);

        $this->assertContains('example_image_name', $json['src']);

        // change name
        $this->reset();

        $this->dispatch('/api/image/' . $imageId, Request::METHOD_PUT, [
            'name' => 'Second Image Name !@#$%^&*()_+'
        ]);

        $this->assertResponseStatusCode(200);


        // get single image
        $this->reset();

        $this->dispatch('/api/image/' . $imageId, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);

        $json = Json::decode($this->getResponse()->getContent(), Json::TYPE_ARRAY);

        $this->assertContains('second_image_name_s_()', $json['src']);
    }
}
