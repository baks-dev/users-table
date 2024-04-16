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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit;


use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UsersTableActionsForm extends AbstractType
{

    private CategoryChoiceInterface $category;


    public function __construct(CategoryChoiceInterface $category)
    {
        $this->category = $category;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

//        /**
//         * Коллекция продукции для привязки к процессу
//         */
//        $builder->add('product', CollectionType::class, [
//            'entry_type' => Products\UsersTableActionsProductForm::class,
//            'entry_options' => ['label' => false],
//            'label' => false,
//            'by_reference' => false,
//            'allow_delete' => true,
//            'allow_add' => true,
//            'prototype_name' => '__product__',
//        ]);

        /**
         * Категория производства
         */

        $builder
            ->add('category', ChoiceType::class, [
                'choices' => $this->category->getCategoryCollection(),
                'choice_value' => function(?CategoryProductUid $category) {
                    return $category?->getValue();
                },
                'choice_label' => function(CategoryProductUid $category) {
                    return $category->getOptions();
                },

                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ]);

        /**
         * Действия
         */
        
        $builder->add('working', CollectionType::class, [
            'entry_type' => Working\UsersTableActionsWorkingForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__actions_working__',
        ]);


        /* CollectionType */
        $builder->add('translate', CollectionType::class, [
            'entry_type' => Trans\UsersTableActionsTransForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
        ]);


        /* Сохранить ******************************************************/
        $builder->add(
            'users_table_actions',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UsersTableActionsDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}