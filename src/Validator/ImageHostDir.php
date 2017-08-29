<?php

namespace Autowp\ImageHost\Validator;

use Zend\Validator\AbstractValidator;

use Autowp\Image;

class ImageHostDir extends AbstractValidator
{
    const NOT_FOUND = 'imageHostDirNotFound';

    protected $messageTemplates = [
        self::NOT_FOUND => "Dir '%value%' not found"
    ];

    /**
     * @var Image\StorageInterface
     */
    private $storage;

    public function setStorage(Image\StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    public function isValid($value)
    {
        $this->setValue($value);

        $dir = $this->storage->getDir($value);

        if (! $dir) {
            $this->error(self::NOT_FOUND);
            return false;
        }
        return true;
    }
}
