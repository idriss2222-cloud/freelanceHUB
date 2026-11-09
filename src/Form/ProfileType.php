<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/** @extends AbstractType<User> */
class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, ['label' => 'Nom complet'])
            ->add('email', EmailType::class, ['label' => 'Adresse courriel'])
            ->add('company', TextType::class, [
                'label' => 'Entreprise',
                'required' => false,
                'empty_data' => null,
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Présentation',
                'required' => false,
                'empty_data' => null,
                'attr' => ['rows' => 5, 'maxlength' => 2000],
            ])
            ->add('skillsText', TextareaType::class, [
                'mapped' => false,
                'label' => 'Compétences',
                'required' => false,
                'data' => $options['skills_text'],
                'help' => 'Séparez les compétences par des virgules ou placez-en une par ligne.',
                'attr' => ['rows' => 4, 'maxlength' => 2000],
                'constraints' => [
                    new Assert\Length(max: 2000, maxMessage: 'La liste ne peut pas dépasser {{ limit }} caractères.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'skills_text' => '',
        ]);
        $resolver->setAllowedTypes('skills_text', 'string');
    }
}
