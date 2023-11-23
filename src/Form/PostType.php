<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Title cannot be empty.']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Title must be at least {{ limit }} characters long.',
                        'maxMessage' => 'Title cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('content', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Content cannot be empty.']),
                    new Length([
                        'min' => 10,
                        'max' => 500,
                        'minMessage' => 'Content must be at least {{ limit }} characters long.',
                        'maxMessage' => 'Content cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('status')
            ->add('likes')
            ->add('image', FileType::class, [
                'label' => 'Picture (JPEG, PNG or GIF file)',
                'mapped' => false,
                'required' => false,

                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image',
                    ])
                ],
            ])
            ->add('location')
            ->add('categories')
            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
