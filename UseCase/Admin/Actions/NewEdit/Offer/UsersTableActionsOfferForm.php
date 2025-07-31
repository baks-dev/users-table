<?php

namespace BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Offer;

use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Profile\UsersTableActionsProfileDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersTableActionsOfferForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('offer', CheckboxType::class, [
            'label'    => 'Товары в категории с торговым предложением',
            'required' => false,
        ]);

        $builder->add('variation', CheckboxType::class, [
            'label'    => 'Множественные Варианты торгового предложения',
            'required' => false,
        ]);

        $builder->add('modification', CheckboxType::class, [
            'label'    => 'Модификации множественных вариантов',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UsersTableActionsOfferDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}