<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<Project> */
class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre du projet'])
            ->add('category', TextType::class, ['label' => 'Catégorie'])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 7, 'maxlength' => 10000],
            ])
            ->add('budget', NumberType::class, [
                'label' => 'Budget',
                'input' => 'string',
                'scale' => 2,
                'html5' => true,
                'attr' => ['min' => '0.01', 'step' => '0.01'],
            ])
            ->add('deadline', DateType::class, [
                'label' => 'Date limite',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => Project::STATUSES,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Project::class]);
    }
}
