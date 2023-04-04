<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                "attr" => array("class" => "bg-transparent block border-b-2 w-full", "placeholder" => "Article title"),
                "label" => false,
                "required" => false
            ])
            ->add('author', TextType::class, [
                "attr" => array("class" => "bg-transparent block border-b-2 w-full", "placeholder" => "Author name"),
                "label" => false,
                "required" => false
            ])
            ->add('description', TextareaType::class, [
                "attr" => array("class" => "bg-transparent block border-b-2 h-60 w-full", "placeholder" => "Write content here"),
                "label" => false,
                "required" => false
            ])
            ->add('imagePath', FileType::class, array(
                "required" => false,
                "mapped" => false,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
