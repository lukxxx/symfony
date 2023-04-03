<?php

namespace App\Form;

use App\Entity\Movie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
class MovieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                "attr" => array("class" => "bg-transparent block border-b-2 w-full", "placeholder" => "Názov článku"),
                "label" => false
            ])
            ->add('releaseYear', IntegerType::class, [
                "attr" => array("class" => "bg-transparent block border-b-2 w-full", "placeholder" => "Popiči rok"),
                "label" => false
            ])
            ->add('description', TextareaType::class, [
                "attr" => array("class" => "bg-transparent block border-b-2 h-60 w-full", "placeholder" => "Napíš o tom niečo"),
                "label" => false
            ])
            ->add('imagePath', FileType::class, array(
                "required" => false,
                "mapped" => false
            ))
            //->add('actors')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movie::class,
        ]);
    }
}
