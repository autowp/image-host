<?php

namespace Autowp\ImageHost\Validator;

use Zend\Validator\AbstractValidator;

use Autowp\Image;

class ImageHostFormat extends AbstractValidator
{
    const NOT_FOUND = 'imageHostFormatNotFound';

    protected $messageTemplates = [
        self::NOT_FOUND => "Format '%value%' not found"
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

        $found = $this->storage->hasFormat($value);

        if (! $found) {
            $this->error(self::NOT_FOUND);
            return false;
        }
        return true;
    }
}
