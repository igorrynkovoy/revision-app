<?php

namespace App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types;

class BlockNumber
{
    private $tag;

    public function __construct($tag = 'latest')
    {
        if (is_numeric($tag)) {
            $tag = '0x'.dechex($tag);
        } else {
            if (!in_array($tag, ['latest', 'earliest', 'pending'])) {
                throw new \InvalidArgumentException('wrong BlockNumber');
            }
        }
        $this->tag = $tag;
    }

    public function __toString()
    {
        return $this->tag;
    }

    public function toString()
    {
        return $this->tag;
    }
}
