<?php

namespace AutowpImageHostTest\Controller;

use Zend\Http\Request;
use Zend\Json\Json;

use Autowp\ImageHost\Controller\FormatController;
use Autowp\ImageHost\Test\AbstractHttpControllerTestCase;

class FormatControllerTest extends AbstractHttpControllerTestCase
{
    protected $applicationConfigPath = __DIR__ . '/../config/application.config.php';

    private function uploadImage(): int
    {
        $this->reset();

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
            'dir' => 'foo'
        ], true);

        $this->assertResponseStatusCode(201);


        $headers = $this->getResponse()->getHeaders();
        $uri = $headers->get('Location')->uri();
        $parts = explode('/', $uri->getPath());
        $imageId = $parts[count($parts) - 1];

        return $imageId;
    }

    public function testNotFoundImage()
    {
        $this->dispatch('/api/format', Request::METHOD_GET, [
            'id'     => 9999999,
            'format' => 'baz'
        ]);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName(FormatController::class);
        $this->assertMatchedRouteName('api/format/get');
        $this->assertActionName('index');

        $json = Json::decode($this->getResponse()->getContent(), Json::TYPE_ARRAY);

        $this->assertEquals(['items' => [null]], $json);
    }

    public function testGet()
    {
        $imageId = $this->uploadImage();

        // get formated image
        $this->reset();

        $this->dispatch('/api/format', Request::METHOD_GET, [
            'id'     => $imageId,
            'format' => 'baz'
        ]);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName(FormatController::class);
        $this->assertMatchedRouteName('api/format/get');
        $this->assertActionName('index');

        $json = Json::decode($this->getResponse()->getContent(), Json::TYPE_ARRAY);

        $this->assertArrayHasKey('items', $json);

        $this->assertCount(1, $json['items']);

        foreach ($json['items'] as $item) {
            $this->assertArrayHasKey('width', $item);
            $this->assertArrayHasKey('height', $item);
            $this->assertArrayHasKey('src', $item);
            $this->assertArrayHasKey('filesize', $item);
        }

        // flush
        $this->reset();

        $this->dispatch('/api/format?format=baz&id=' . $imageId, Request::METHOD_DELETE);

        $this->assertResponseStatusCode(204);
        $this->assertControllerName(FormatController::class);
        $this->assertMatchedRouteName('api/format/delete');
        $this->assertActionName('delete');
    }

    public function testInvalidFormat()
    {
        $imageId = $this->uploadImage();

        $this->reset();

        $this->dispatch('/api/format', Request::METHOD_GET, [
            'id'     => $imageId,
            'format' => 'delta'
        ]);

        $this->assertResponseStatusCode(400);
    }
}
