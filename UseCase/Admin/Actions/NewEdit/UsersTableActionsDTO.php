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

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEventInterface;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see UsersTableActionsEvent */
final class UsersTableActionsDTO implements UsersTableActionsEventInterface
{
    /**
     * Идентификатор события.
     */
    #[Assert\Uuid]
    private ?UsersTableActionsEventUid $id = null;

    /**
     * Категория производства
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private CategoryProductUid $category;

    /**
     * Действия
     */
    #[Assert\Valid]
    private ArrayCollection $working;


    //    /**
    //     * Продукция для привязки к процессу
    //     */
    //    #[Assert\Valid]
    //    private ArrayCollection $product;

    /**
     * Продукция для привязки к процессу
     */
    #[Assert\Valid]
    private ArrayCollection $translate;


    public function __construct()
    {
        $this->working = new ArrayCollection();
        //$this->product = new ArrayCollection();
        $this->translate = new ArrayCollection();
    }


    /**
     * Идентификатор события.
     */

    public function setId(?UsersTableActionsEventUid $id): void
    {
        $this->id = $id;
    }

    public function getEvent(): ?UsersTableActionsEventUid
    {
        return $this->id;
    }

    /**
     * Категория производства
     */

    public function getCategory(): CategoryProductUid
    {
        return $this->category;
    }

    public function setCategory(CategoryProductUid|string $category): void
    {

        if(is_string($category))
        {
            $category = new CategoryProductUid($category);
        }

        $this->category = $category;
    }


    /**
     * Действия
     */

    public function getWorking(): ArrayCollection
    {
        return $this->working;
    }

    public function setWorking(ArrayCollection $working): void
    {
        $this->working = $working;
    }

    public function addWorking(Working\UsersTableActionsWorkingDTO $working): void
    {
        $filter = $this->working->filter(function (Working\UsersTableActionsWorkingDTO $element) use ($working) {
            return $working->getConst()->equals($element->getConst());
        });

        if($filter->isEmpty())
        {
            $this->working->add($working);
        }
    }


    public function removeWorking(Working\UsersTableActionsWorkingDTO $working): void
    {
        $this->working->removeElement($working);
    }



    //    /**
    //     * Продукция для привязки к процессу
    //     */
    //    public function getProduct(): ArrayCollection
    //    {
    //        return $this->product;
    //    }
    //
    //    public function setProduct(ArrayCollection $product): self
    //    {
    //        $this->product = $product;
    //        return $this;
    //    }

    //    public function addProduct(Products\UsersTableActionsProductDTO $product): void
    //    {
    //        $filter = $this->product->filter(function(Products\UsersTableActionsProductDTO $element) use ($product)
    //        {
    //            return $product->getProduct()->equals($element->getProduct());
    //        });
    //
    //        if($filter->isEmpty())
    //        {
    //            $this->product->add($product);
    //        }
    //    }
    //
    //    public function removeProduct(Products\UsersTableActionsProductDTO $product): void
    //    {
    //        $this->product->removeElement($product);
    //    }


    /** Перевод */

    public function setTranslate(ArrayCollection $trans): void
    {
        $this->translate = $trans;
    }


    public function getTranslate(): ArrayCollection
    {
        /* Вычисляем расхождение и добавляем неопределенные локали */
        foreach(Locale::diffLocale($this->translate) as $locale)
        {
            $UsersTableActionsTransDTO = new Trans\UsersTableActionsTransDTO();
            $UsersTableActionsTransDTO->setLocal($locale);
            $this->addTranslate($UsersTableActionsTransDTO);
        }

        return $this->translate;
    }


    public function addTranslate(Trans\UsersTableActionsTransDTO $trans): void
    {
        if(empty($trans->getLocal()->getLocalValue()))
        {
            return;
        }

        if(!$this->translate->contains($trans))
        {
            $this->translate->add($trans);
        }
    }

    public function removeTranslate(Trans\UsersTableActionsTransDTO $trans): void
    {
        $this->translate->removeElement($trans);
    }
}
