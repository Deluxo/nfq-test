<?php

namespace App\Interfaces;

interface ApiableInterface
{
    /**
     * Return in a format suitable for json_encode
     *
     * @return array
     */
    public function toApiResponse(): array;
}
