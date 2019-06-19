<?php

namespace App\Form;

use App\Entity\UserGroup;
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
 * Class UserGroupType
 * @author Lukas Levickas
 */
class UserGroupType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserGroup::class
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class)
                ->add('slug', HiddenType::class)
                ->add('save', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-success',
                    ]
                ])
                ->add('delete', ButtonType::class, [
                    'attr' => [
                        'class' => 'btn btn-danger',
                        'data-action' => 'deleteGroup',
                    ],
                ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $userGroup = $event->getData();

            if (!empty($userGroup['title'])) {
                $userGroup['slug'] = self::createSlugFromTitle($userGroup['title']);
                $event->setData($userGroup);
            }
        });
    }

    public static function createSlugFromTitle($title)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
}
