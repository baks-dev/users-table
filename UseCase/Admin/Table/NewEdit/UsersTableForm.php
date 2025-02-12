<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\Profile\Group\Repository\UserProfileChoice\UserProfileChoiceInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Repository\Actions\UsersTableActionsChoice\UsersTableActionsChoiceInterface;
use BaksDev\Users\UsersTable\Repository\Actions\UsersTableActionsWorkingChoice\UsersTableActionsWorkingChoiceInterface;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UsersTableForm extends AbstractType
{
    private UserProfileChoiceInterface $profileChoice;
    private UsersTableActionsChoiceInterface $actionsChoice;
    private UsersTableActionsWorkingChoiceInterface $workingChoice;
    private CategoryChoiceInterface $categoryChoice;

    public function __construct(
        UserProfileChoiceInterface $profileChoice,
        UsersTableActionsChoiceInterface $actionsChoice,
        UsersTableActionsWorkingChoiceInterface $workingChoice,
        CategoryChoiceInterface $category,
    ) {
        $this->profileChoice = $profileChoice;
        $this->actionsChoice = $actionsChoice;
        $this->workingChoice = $workingChoice;
        $this->categoryChoice = $category;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        /** @var UsersTableDTO $data */
        $data = $builder->getData();


        /**
         * Производственный процесс
         */


        /*$builder->get('action')->addModelTransformer(
            new CallbackTransformer(
                function($action) {
                    return $action instanceof UsersTableActionsEventUid ? $action->getValue() : $action;
                },
                function($action) {
                    return $action ? new UsersTableActionsEventUid($action) : null;
                }
            )
        );*/


        /*$builder->get('working')->addModelTransformer(
            new CallbackTransformer(
                function($working) {
                    return $working instanceof UsersTableActionsWorkingUid ? $working->getValue() : $working;
                },
                function($working) {
                    return $working ? new UsersTableActionsWorkingUid($working) : null;
                }
            )
        );*/

        $choice = $this->actionsChoice->getCollection() ?: [];


        //        $builder
        //            ->add('action', ChoiceType::class, [
        //                'choices' => [],
        //                'label' => false,
        //                'expanded' => false,
        //                'multiple' => false
        //            ]);

        //        $builder
        //            ->add('action', TextType::class, ['disabled' => true]);


        /*$builder
            ->add('working', ChoiceType::class, [
                'choices' => [],
                'label' => false,
                'expanded' => false,
                'multiple' => false
            ]);*/


        /**
         * Категория производства
         */

        $builder
            ->add('category', ChoiceType::class, [
                'choices' => $this->categoryChoice->findAll(),
                'choice_value' => function (?CategoryProductUid $category) {
                    return $category?->getValue();
                },
                'choice_label' => function (CategoryProductUid $category) {
                    return $category->getOptions();
                },

                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ]);


        $builder
            ->add('working', TextType::class, ['disabled' => true]);

        $builder
            ->add('action', ChoiceType::class, [
                'choices' => $choice,
                'choice_value' => function (?UsersTableActionsEventUid $action) {
                    return $action?->getValue();
                },
                'choice_label' => function (UsersTableActionsEventUid $action) {

                    return $action->getAttr();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                //'disabled' => true
            ]);


        /**
         * Действие сотрудника
         */

        $builder
            ->add('working', TextType::class, ['disabled' => true]);

        //        $builder
        //            ->add('working', ChoiceType::class, [
        //                'choices' => [],
        //                'label' => false,
        //                'expanded' => false,
        //                'multiple' => false
        //            ]);

        /**
         * Профиль пользователя.
         */

        $profiles = $this->profileChoice->getCollection($data->getAuthority()) ?: [];

        $builder
            ->add('profile', ChoiceType::class, [
                'choices' => $profiles,
                'choice_value' => function (?UserProfileUid $profile) {
                    return $profile?->getValue();
                },
                'choice_label' => function (UserProfileUid $profile) {
                    return $profile->getAttr();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false
            ]);


        /**
         * Количество.
         */
        $builder->add('quantity', IntegerType::class);

        /**
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


        //add listener to change the default values when loading the form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPresetData']);

        $builder->get('action')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmitAction']);

        $builder->get('category')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmitCategory']);

    }


    public function onPresetData(FormEvent $event): void
    {
        $builder = $event->getForm();

        /** @var UsersTableDTO $data */
        $data = $event->getData();

        //            $choice = $this->actionsChoice->getCollection($data->getAuthority()) ?: [];

        //            $builder
        //                ->add('action', ChoiceType::class, [
        //                    'choices' => $choice,
        //                    'label' => false,
        //                    'expanded' => false,
        //                    'multiple' => false,
        //                    'required' => true,
        //                    'disabled' => true
        //                ]);
    }


    public function onPostSubmitAction(FormEvent $event): void
    {

        $builder = $event->getForm()->getParent();

        if(!$builder || !$event->getData())
        {
            return;
        }

        $action = new UsersTableActionsEventUid($event->getData());

        $choice = $this->workingChoice->getCollection($action) ?: [];

        $builder
            ->add('working', ChoiceType::class, [
                'choices' => $choice,
                'choice_value' => function (?UsersTableActionsWorkingUid $working) {
                    return $working?->getValue();
                },
                'choice_label' => function (UsersTableActionsWorkingUid $working) {
                    return $working->getOption();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'disabled' => !$choice
            ]);
    }


    public function onPostSubmitCategory(FormEvent $event): void
    {

        $builder = $event->getForm()->getParent();


        if(!$builder || !$event->getData())
        {
            return;
        }

        $category = new CategoryProductUid($event->getData());

        /** @var UsersTableDTO $data */
        $data = $builder?->getData();


        $choice = $this->actionsChoice
            ->forCategory($category)
            ->getCollection() ?: [];

        $builder
            ->add('action', ChoiceType::class, [
                'choices' => $choice,
                'choice_value' => function (?UsersTableActionsEventUid $action) {
                    return $action?->getValue();
                },
                'choice_label' => function (UsersTableActionsEventUid $action) {

                    return $action->getAttr();
                },

                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'disabled' => !$choice
            ]);

        $choice = $this->workingChoice->getCollection() ?: [];

        $builder
            ->add('working', ChoiceType::class, [
                'choices' => $choice,
                'choice_value' => function (?UsersTableActionsWorkingUid $working) {
                    return $working?->getValue();
                },
                'choice_label' => function (UsersTableActionsWorkingUid $working) {
                    return $working->getOption();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                //'disabled' => true
            ]);
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
