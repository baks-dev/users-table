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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Products;


use BaksDev\Products\Product\Repository\ProductChoice\ProductChoiceInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UsersTableActionsProductForm extends AbstractType
{
    private ProductChoiceInterface $productChoice;

    public function __construct(ProductChoiceInterface $productChoice)
    {
        $this->productChoice = $productChoice;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', ChoiceType::class, [
                'choices' => $this->productChoice->fetchAllProduct(),
                'choice_value' => function(?ProductUid $product) {
                    return $product?->getValue();
                },
                'choice_label' => function(ProductUid $product) {
                    return $product->getAttr();
                },

                'choice_attr' => function (?ProductUid $product) {
                    return $product ? ['data-filter' => $product->getOption()] : [];
                },

                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ]);

        return;



//        $builder->add('product', TextType::class, ['label' => false]);
//
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
//
//            $product = $event->getData();
//            $form = $event->getForm();
//
//            if(!$product)
//            {
//                $form
//                    ->add('product', ChoiceType::class, [
//                        'choices' => $this->productChoice->fetchAllProduct(),
//                        'choice_value' => function(?ProductUid $product) {
//                            return $product?->getValue();
//                        },
//                        'choice_label' => function(ProductUid $product) {
//                            return $product->getAttr();
//                        },
//
//                        'choice_attr' => function (?ProductUid $product) {
//                            return $product ? ['data-filter' => $product->getOption()] : [];
//                        },
//
//                        'label' => false,
//                        'expanded' => false,
//                        'multiple' => false,
//                        'required' => true,
//                    ]);
//            }
//        });
//
//
//        $builder->get('product')->addModelTransformer(
//            new CallbackTransformer(
//                function($price) {
//                    return $price instanceof ProductUid ? $price : new ProductUid($price);
//                },
//                function($price) {
//                    return new ProductUid($price);
//                }
//            )
//        );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UsersTableActionsProductDTO::class,
        ]);
    }
}