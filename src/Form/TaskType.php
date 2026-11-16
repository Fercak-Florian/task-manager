<?php

namespace App\Form;

use App\Entity\Task;

use App\Enum\TaskPriority;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre'
            ])
            ->add('details', TextareaType::class, [
                'label' => 'Details'
            ])
            ->add('deadline', DateType::class, [
                'label' => 'Date limite'
            ])
            ->add('priority', EnumType::class, [
                'class' => TaskPriority::class,
                'label' => 'Priorité',
                'choice_label' => fn(TaskPriority $priority) => $priority->getLabel(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
