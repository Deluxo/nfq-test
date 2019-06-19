<?php

namespace App\Interfaces;

interface CmsableInterface
{
    /**
     * Returns array designed for cms twig macros
     *
     * Example:
     * [
     *    'attribute name' => [
     *        [
     *            'text' => 'table data text',
     *            'link' => 'table data url',
     *        ],
     *    ]
     * ]
     *
     * @return array
     */
    public function toCmsTable(): Array;
}
