<?php

namespace App\Form;

use App\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name', TextType::class, [
                'label' => 'Pet Name',
                'constraints' => [new NotBlank(['message' => 'Please enter your pet\'s name.'])],
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Enter your pet\'s name'
                ]
            ])
            ->add('PetType', ChoiceType::class, [
                'label' => 'Pet Type',
                'choices' => [
                    'Dog 🐕' => 'dog',
                    'Cat 🐱' => 'cat'
                ],
                'attr' => [
                    'class' => 'form-input pet-type-selector'
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('AppointmentDate', DateTimeType::class, [
                'label' => 'Appointment Date & Time',
                'widget' => 'single_text',
                'constraints' => [new NotBlank(['message' => 'Please choose a date and time.'])],
                'attr' => [
                    'class' => 'form-input datetime-input'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
