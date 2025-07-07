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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEventInterface;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use BaksDev\Users\UsersTable\Type\Actions\Id\UsersTableActionsUid;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Profile\UsersTableActionsProfileDTO;
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


    /** Идентификатор производства по заявкам */
    #[Assert\Uuid]
    private ?UsersTableActionsUid $application = null;

    /**
     * Категория производства
     */
    #[Assert\Uuid]
    private ?CategoryProductUid $category = null;

    /**
     * Действия
     */
    #[Assert\Valid]
    private ArrayCollection $working;


    /**
     * Продукция для привязки к процессу
     */
    #[Assert\Valid]
    private ArrayCollection $translate;

    /**
     * Идентификатор бизнес-профиля
     */
    private UsersTableActionsProfileDTO $profile;


    public function __construct()
    {
        $this->working = new ArrayCollection();
        $this->translate = new ArrayCollection();
        $this->profile = new UsersTableActionsProfileDTO();
    }


    public function getApplication(): ?UsersTableActionsUid
    {
        return $this->application;
    }

    public function setApplication(UsersTableActionsUid $application): self
    {
        $this->application = $application;
        return $this;
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

    public function getCategory(): ?CategoryProductUid
    {
        return $this->category;
    }

    public function setCategory(CategoryProductUid|string|null $category): void
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

    public function getProfile(): UsersTableActionsProfileDTO
    {
        return $this->profile;
    }


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
