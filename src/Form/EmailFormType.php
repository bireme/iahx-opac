<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('from_name', TextType::class, [
                'label' => 'MAIL_FROM_NAME',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'MAIL_FROM_NAME',
                    'autocomplete' => 'on'
                ]
            ])
            ->add('from_email', EmailType::class, [
                'label' => 'MAIL_FROM_EMAIL',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'MAIL_FROM_EMAIL'
                ]
            ])
            ->add('to_email', EmailType::class, [
                'label' => 'SEND_RESULT_TO',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'MAIL_TO_EMAIL'
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => 'MAIL_SUBJECT',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'MAIL_SUBJECT'
                ]
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'MAIL_COMMENT',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'MAIL_COMMENT'
                ]
            ])
            ->add('selection', ChoiceType::class, [
                'choices' => [
                    'page' => 'page',
                    'selection' => 'selection',
                    'all' => 'all'
                ],
                'expanded' => true,
                'multiple' => false,
                'data' => 'page',
                'label' => false,
                'required' => true
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'SEND',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'email_form',
        ]);
    }
}
