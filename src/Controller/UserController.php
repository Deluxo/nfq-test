<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\UserGroup;
use App\Form\UserType;
use App\Form\UserGroupType;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    public function renderUsersList(Request $request)
    {
        $doctrine = $this->getDoctrine();
        return $this->render(
            'user/list.html.twig',
            [
                'users' => $doctrine->getRepository(User::class)->findAll(),
                'groups' => $doctrine->getRepository(UserGroup::class)->findAll()
            ]
        );
    }

    public function renderEditUser(Request $request, User $user = null)
    {
        if (!($user instanceof User)) {
            $user = new User();
        }

        $form = $this->createForm(UserType::class, $user);
        $entityManager = $this->getDoctrine()->getManager();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'User ' . $user->getName() . ' was saved successfully.');

            return $this->redirectToRoute('users');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
            'title' => 'Edit User',
        ]);
    }

    public function renderEditGroup(Request $request, UserGroup $userGroup = null)
    {
        if (!($userGroup instanceof UserGroup)) {
            $userGroup = new UserGroup();
        }

        $form = $this->createForm(UserGroupType::class, $userGroup);
        $entityManager = $this->getDoctrine()->getManager();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userGroup = $form->getData();

            $entityManager->persist($userGroup);
            $entityManager->flush();
            $this->addFlash('success', 'Group ' . $userGroup->getTitle() . ' was saved successfully.');

            return $this->redirectToRoute('users');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
            'title' => 'Edit Group',
        ]);
    }

    public function deleteUser(User $user)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush($user);

        return $this->redirectToRoute('users');
    }

    public function deleteUserGroup(UserGroup $userGroup)
    {
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($userGroup);
            $entityManager->flush($userGroup);
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlash(
                'danger',
                'Group '
                . $userGroup->getTitle()
                . ' could not be deleted, because it\'s used by some user.'
            );
        }

        return $this->redirectToRoute('users');
    }
}
