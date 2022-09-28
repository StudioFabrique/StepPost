<?php

namespace App\Form;


use App\Entity\StatutCourrier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatutCourrierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // 
            // ->add('courrier')
            ->add('statut')
            ->add('date', DateTimeType::class, $options['SetDatetimeNow'] == true ? [
                'data' => new \DateTime("now")
            ] : []);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StatutCourrier::class,
            'SetDatetimeNow' => false
        ]);
    }
}
