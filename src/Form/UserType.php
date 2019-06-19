<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\UserGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserType
 * @author Lukas Levickas
 */
class UserType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
                ->add('slug', HiddenType::class)
                ->add('userGroup', EntityType::class, [
                    'class' => UserGroup::class,
                    'label' => 'Group',
                    'placeholder' => 'None',
                    'required' => false,
                ])
                ->add('save', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-success',
                    ]
                ])
                ->add('delete', ButtonType::class, [
                    'attr' => [
                        'class' => 'btn btn-danger',
                        'data-action' => 'deleteUser',
                    ],
                ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use ($builder) {
            $user = $event->getData();

            if (!empty($user['name'])) {
                $user['slug'] = self::createSlugFromName($user['name']);
                $event->setData($user);
            }
        });
    }

    public static function createSlugFromName($name)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }
}
