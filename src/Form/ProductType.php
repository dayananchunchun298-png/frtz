<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Product Name',
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Enter product name'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-input',
                    'rows' => 4,
                    'placeholder' => 'Enter product description'
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
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'choices' => [
                    'Food' => 'food',
                    'Toys' => 'toys',
                    'Accessories' => 'accessories',
                    'Health & Care' => 'health',
                    'Grooming' => 'grooming',
                    'Bedding' => 'bedding',
                    'Training' => 'training',
                    'Other' => 'other'
                ],
                'attr' => [
                    'class' => 'form-input'
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
            ->add('stock', IntegerType::class, [
                'label' => 'Stock Quantity',
                'attr' => [
                    'class' => 'form-input',
                    'min' => 0,
                    'placeholder' => '0'
                ]
            ])
            ->add('image', UrlType::class, [
                'label' => 'Image URL',
                'required' => false,
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'https://example.com/image.jpg'
                ]
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active (available for purchase)',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
