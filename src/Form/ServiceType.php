<?php

namespace App\Form;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('name', TextType::class, [
				'label' => 'Service Name',
				'attr' => [
					'class' => 'form-input',
					'placeholder' => 'Enter service name'
				]
			])
			->add('description', TextareaType::class, [
				'label' => 'Description',
				'required' => false,
				'attr' => [
					'class' => 'form-input',
					'rows' => 4,
					'placeholder' => 'Enter service description'
				]
			])
			->add('price', MoneyType::class, [
				'label' => 'Price',
				'currency' => 'PHP',
				'attr' => [
					'class' => 'form-input',
					'placeholder' => '0.00'
				]
			])
			->add('petType', ChoiceType::class, [
				'label' => 'Pet Type',
				'choices' => [
					'Dog' => 'dog',
					'Cat' => 'cat',
					'Bird' => 'bird',
					'Fish' => 'fish',
					'Small Pet' => 'small_pet',
					'All Pets' => 'all'
				],
				'attr' => [
					'class' => 'form-input'
				]
			])
			->add('isActive', CheckboxType::class, [
				'label' => 'Active (available for booking)',
				'required' => false,
				'attr' => [
					'class' => 'form-check-input'
				]
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Service::class,
		]);
	}
}


