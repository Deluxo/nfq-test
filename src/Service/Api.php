<?php

namespace App\Service;

use App\Interfaces\ApiableInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Tests\DependencyInjection\Fixtures\php\merge_import;
use Symfony\Component\Cache\Simple\DoctrineCache;
use Symfony\Component\HttpFoundation\Request;

class Api
{
    private $managerRegistry;
    private $request;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function findAll($repositoryName)
    {
        return array_map(
            function($user) {
                if ($user instanceof ApiableInterface) {
                    return $user->toApiResponse();
                }
            },
            $this->managerRegistry->getRepository($repositoryName)->findAll()
        );
    }

    public function paramsToEntityAttributes($entity, Request $request, array $watchParams = [])
    {
        foreach ($watchParams as $param => $setter) {
            $paramValue = $request->query->get($param);

            if (!empty($paramValue) && is_callable($setter)) {
                $setter($entity, $paramValue);
            }
        }

        return $entity;
    }
}
