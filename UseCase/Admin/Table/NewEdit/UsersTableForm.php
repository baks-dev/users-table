<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Users\UsersTable\UseCase\Admin\Table\NewEdit;

use BaksDev\Users\Profile\UserProfile\Repository\UserProfileChoice\UserProfileChoiceInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UsersTableForm extends AbstractType
{
    private UserProfileChoiceInterface $profileChoice;

    public function __construct(UserProfileChoiceInterface $profileChoice)
    {
        $this->profileChoice = $profileChoice;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*
         * Профиль пользователя.
         */
        $builder
            ->add('profile', ChoiceType::class, [
                'choices' => $this->profileChoice->getActiveUserProfile(),
                'choice_value' => function (?UserProfileUid $status) {
                    return $status?->getValue();
                },
                'choice_label' => function (UserProfileUid $status) {
                    return $status->getAttr();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false
            ]);

        /*
         * Количество.
         */
        $builder->add('total', IntegerType::class, [
            'attr' => ['min' => 1]
        ]);

        /*
         * Дата
         */
        $builder->add('date', DateType::class, [
            'widget' => 'single_text',
            'html5' => false,
            'required' => false,
            'format' => 'dd.MM.yyyy',
            'input' => 'datetime_immutable',
        ]);

        /* Сохранить */
        $builder->add(
            'users_table',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UsersTableDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}
