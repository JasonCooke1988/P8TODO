<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $passRequired = true;
        if (in_array('edit',$options['validation_groups'])) {
            $passRequired = false;
        }

            $builder
                ->add('username', TextType::class, [
                    'label' => "Nom d'utilisateur"
                ])
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                    'required' => $passRequired,
                    'mapped' => $passRequired,
                    'first_options' => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Tapez le mot de passe à nouveau'],
                ])
                ->add('email', EmailType::class, ['label' => 'Adresse email'])
                ->add('roles', ChoiceType::class, [
                    'label' => 'Choix rôle : ',
                    'required' => true,
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => [
                        'ROLE_USER' => 'ROLE_USER',
                        'ROLE_ADMIN' => 'ROLE_ADMIN'
                    ]
                ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => ['create','edit'],
        ]);
    }
}
