<?php

namespace App\Form;

use App\Entity\Expediteur;
use App\Repository\ClientRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpediteurType extends AbstractType
{
    private $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['type'] == 'create') {
            $builder
                ->add('email', EmailType::class, [
                    'label' => 'Email *',
                    'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                    'attr' => [
                        'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                    ],
                ]);
        }
        if ($options['type'] == 'create' || $options['type'] == 'edit')
            $builder
                ->add('nom', TextType::class, [
                    'label' => "Nom ou nom de l'entreprise *",
                    'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                    'attr' => [
                        'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                    ],
                ])
                ->add('prenom', TextType::class, [
                    'required'   => false,
                    'label' => 'Prénom ou nom du service',
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
                    'required' => false,
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
                ]);
        if ($options['type'] == 'edit') {
            $builder
                ->add(
                    'existClient',
                    TextType::class,
                    [
                        'label' => 'Raison sociale temporaire renseigné par le client',
                        'data' => str_replace('tmp_', '', $options["clientTemp"]),
                        'mapped' => false,
                        'required' => false,
                        'disabled' => true,
                        'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                        'attr' => [
                            'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                        ],
                    ]
                );
        }

        $builder
            ->add(
                'addClient',
                ChoiceType::class,
                [
                    'label' => 'Associer ' . $options['nom'] . ' à la raison sociale :',
                    'choices' => $this->clientRepository->findActiveClients(),
                    'choice_label' => 'raisonSociale',
                    'mapped' => false,
                    'required' => true,
                    'label_attr' => ['class' => 'block text-gray-700 text-sm font-bold mb-2'],
                    'attr' => [
                        'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4',
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expediteur::class,
            'type' => 'edit',
            'clientTemp' => 'Aucune raison sociale définie',
            'nom' => ''
        ]);
    }
}
