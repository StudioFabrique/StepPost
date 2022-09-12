<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Expediteur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpediteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email *',
                'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom *',
                'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                ],
            ])
            ->add('prenom', TextType::class, [
                'required'   => false,
                'label' => 'Prenom',
                'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                ],
            ])
            ->add('civilite', TextType::class, [
                'required'   => false,
                'label' => 'Civilité',
                'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse *',
                'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                ],
            ])
            ->add('complement', TextType::class, [
                'required'   => false,
                'label' => "Complement d'adresse",
                'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                ],
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal *',
                'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                ],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville *',
                'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                ],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Telephone *',
                'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                ],
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'raisonSociale',
                'label' => 'Raison sociale',
                'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expediteur::class,
        ]);
    }
}
