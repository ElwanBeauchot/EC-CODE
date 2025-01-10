<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\BookRead;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddReadBookType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('book_id', EntityType::class, [
				'class' => Book::class,
				'label' => 'Livre',
				'choice_label' => 'name',
				'attr' => ['placeholder' => 'Sélectionnez un livre', 'class' => 'select'],
			])
			->add('rating', ChoiceType::class, [
				'choices' => [
					'1' => 1,
					'1.5' => 1,
					'2' => 2,
					'2.5' => 2,
					'3' => 3,
					'3.5' => 3,
					'4' => 4,
					'4.5' => 4,
					'5' => 5,
				],
				'label' => 'Note',
				'attr' => ['class' => 'select'],
			])
			->add('description', TextareaType::class, [
				'attr' => ['placeholder' => "Notez-ici les idées importantes de l'oeuvre", 'class' => 'textarea'],
				'label' => 'Mes notes'
			])
			->add('is_read', CheckboxType::class, [
				'attr' => ['class' => 'checkbox'],
				'label' => 'Lecture terminée',
				'required' => false,
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => BookRead::class,
		]);
	}
}
