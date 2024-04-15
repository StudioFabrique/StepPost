<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateMin', DateTimeType::class, ['label' => 'Date Minimum',
            'constraints'=>new NotNull([true,'message'=>'La date mainimum doit être remplis' ])])
            ->add('DateMax', DateTimeType::class, ['label' => 'Date Maximum',
            'constraints'=>new NotNull([true,'message'=>'La date maximum doit être remplis' ])])
            ->add('submit', SubmitType::class, ['label' => 'Rechercher']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
