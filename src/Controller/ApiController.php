<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserGroup;
use App\Form\UserGroupType;
use App\Form\UserType;
use App\Interfaces\ApiableInterface;
use App\Service\Api;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Test\Fixtures\web\index;

class ApiController extends AbstractController
{
    const IGNORE_ROUTES = [
        '_profiler',
        '_profiler_exception',
        '_profiler_exception_css',
        '_profiler_home',
        '_profiler_open_file',
        '_profiler_phpinfo',
        '_profiler_router',
        '_profiler_search',
        '_profiler_search_bar',
        '_profiler_search_results',
        '_twig_error_test',
        '_wdt',
        'apiRoutes',
    ];

    public function routes(RouterInterface $router)
    {
        return $this->js(
            array_map(
                function($item) {
                    return $item->getPath();
                },
                array_filter(
                    $router->getRouteCollection()->all(),
                    function($key) {
                        return !in_array($key, self::IGNORE_ROUTES);
                    },
                    ARRAY_FILTER_USE_KEY
                )
            )
        );
    }

    public function getUsers(Api $api)
    {

        return $this->js($api->findAll(User::class));
    }

    public function getUserGroups(Api $api)
    {
        return $this->js($api->findAll(UserGroup::class));
    }


    public function addUser(Request $request, Api $api, ValidatorInterface $validator)
    {
        $user = new User();

        $api->paramsToEntityAttributes(
            $user,
            $request,
            [
                'userGroup' => function($user, $value) {
                    $userGroup = $this->getDoctrine()->getRepository(UserGroup::class)->find((int) $value);
                    $user->setUserGroup($userGroup);
                },
                'name' => function($user, $value) {
                    $user->setName($value);
                    $user->setSlug(UserGroupType::createSlugFromTitle($user->getName()));
                },
            ]
        );

        return $this->returnManagedResponse($user, $validator);
    }

    public function addUserGroup(Request $request, Api $api, ValidatorInterface $validator)
    {
        $userGroup = new UserGroup();

        $api->paramsToEntityAttributes(
            $userGroup,
            $request,
            [
                'title' => function($userGroup, $value) {
                    $userGroup->setTitle($value);
                    $userGroup->setSlug(UserGroupType::createSlugFromTitle($userGroup->getTitle()));
                },
            ]
        );

        return $this->returnManagedResponse($userGroup, $validator);
    }

    public function editUser(Request $request, Api $api, ValidatorInterface $validator)
    {
        $id = $request->query->get('id');

        if (empty($id)) {
            return $this->returnErrorMessages(['id' => 'Parameter is missing.']);
        }

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if (!($user instanceof User)) {
            return $this->returnErrorMessages(['id' => 'No user with such id found.']);
        }

        $api->paramsToEntityAttributes(
            $user,
            $request,
            [
                'userGroup' => function($user, $value) {
                    $userGroup = $this->getDoctrine()->getRepository(UserGroup::class)->find((int) $value);
                    $user->setUserGroup($userGroup);
                },
                'name' => function($user, $value) {
                    $user->setName($value);
                    $user->setSlug(UserType::createSlugFromName($user->getName()));
                },
            ]
        );

        return $this->returnManagedResponse($user, $validator);
    }

    public function editUserGroup(Request $request, Api $api, ValidatorInterface $validator)
    {
        $id = $request->query->get('id');

        if (empty($id)) {
            return $this->returnErrorMessages(['id' => 'Parameter is missing.']);
        }

        $userGroup = $this->getDoctrine()->getRepository(UserGroup::class)->find($id);

        if (!($userGroup instanceof UserGroup)) {
            return $this->returnErrorMessages(['id' => 'No user group with such id found.']);
        }

        $api->paramsToEntityAttributes(
            $userGroup,
            $request,
            [
                'title' => function($userGroup, $value) {
                    $userGroup->setTitle($value);
                    $userGroup->setSlug(UserGroupType::createSlugFromTitle($userGroup->getTitle()));
                },
            ]
        );

        return $this->returnManagedResponse($userGroup, $validator);
    }

    public function deleteUser(Request $request)
    {
        $id = $request->query->get('id');

        if (empty($id)) {
            return $this->returnErrorMessages(['id' => 'Parameter is missing.']);
        }

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if (!($user instanceof User)) {
            return $this->returnErrorMessages(['id' => 'No user with such id found.']);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush($user);

        return $this->js('ok');
    }

    public function deleteUserGroup(Request $request)
    {
        $id = $request->query->get('id');

        if (empty($id)) {
            return $this->returnErrorMessages(['id' => 'Parameter is missing.']);
        }

        $userGroup = $this->getDoctrine()->getRepository(UserGroup::class)->find($id);

        if (!($userGroup instanceof UserGroup)) {
            return $this->returnErrorMessages(['id' => 'No user group with such id found.']);
        }

        try {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($userGroup);
            $entityManager->flush($userGroup);
        } catch (ForeignKeyConstraintViolationException $e) {
            return $this->returnErrorMessages([
                'id' => 'User group '
                . $userGroup->getId()
                . ' could not be deleted, because it\'s used by some user.'
            ]);
        }

        return $this->js('ok');
    }

    protected function returnManagedResponse($entity, ValidatorInterface $validator)
    {
        $errors = $validator->validate($entity);

        if ($errors->count()) {
            $output = [];

            foreach ($errors as $error) {
                $output[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->returnErrorMessages($output);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($entity);
        $entityManager->flush();

        if ($entity instanceof ApiableInterface) {
            return $this->js($entity->toApiResponse());
        }

        return $this->js('ok');
    }

    protected function js($data)
    {
        return new Response(json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }

    protected function returnErrorMessages($messages = ['General error'])
    {
        return $this->js(['errors' => $messages]);
    }

    protected function entityToApiResponse($entity)
    {
        return null;
    }
}
