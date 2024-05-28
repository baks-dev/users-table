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

namespace BaksDev\Users\UsersTable\Forms\MonthUsersTableFilter\Admin;

use BaksDev\Users\Profile\Group\Repository\UserProfileChoice\UserProfileChoiceInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Forms\DayUsersTableFilter\Admin\DayTableFilterDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MonthTableFilterFilterForm extends AbstractType
{
    private RequestStack $request;
    private UserProfileChoiceInterface $profileChoice;

    public function __construct(
        UserProfileChoiceInterface $profileChoice,
        RequestStack $request,
    ) {

        $this->request = $request;
        $this->profileChoice = $profileChoice;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        /** @var MonthTableFilterDTO $data */
        $data = $builder->getData();

        $builder->add(
            'back',
            SubmitType::class,
            ['label' => 'Back', 'label_html' => true, 'attr' => ['class' => 'btn-light']]
        );


        /*$builder->add('date', DateType::class, [
            'widget' => 'single_text',
            'html5' => false,
            'attr' => ['class' => 'js-datepicker'],
            'required' => false,
            'format' => 'dd.MM.yyyy',
            'input' => 'datetime_immutable',
        ]);*/


        $builder->add(
            'next',
            SubmitType::class,
            ['label' => 'next', 'label_html' => true, 'attr' => ['class' => 'btn-light']]
        );


       /* $builder->add('date', DateType::class, [
            'widget' => 'single_text',
            'html5' => false,
            'attr' => ['class' => 'js-datepicker'],
            'required' => false,
            'format' => 'dd.MM.yyyy',
            'input' => 'datetime_immutable',
        ]);*/


        $profiles = $this->profileChoice->getCollection($data->getAuthority());

        /* TextType */
        $builder->add('profile', ChoiceType::class, [
            'choices' => $profiles,
            'choice_value' => function(?UserProfileUid $profile) {
                return $profile?->getValue();
            },
            'choice_label' => function(UserProfileUid $profile) {
                return $profile->getAttr();
            },
            'label' => false,
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'attr' => ['data-select' => 'select2',]
        ]);

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event): void {
                /** @var MonthTableFilterDTO $data */
                $data = $event->getData();

                $this->request->getSession()->set(MonthTableFilterDTO::date, $data->getDate());
                $this->request->getSession()->set(DayTableFilterDTO::profile, $data->getProfile());
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MonthTableFilterDTO::class,
                'method' => 'POST',
            ]
        );
    }
}
