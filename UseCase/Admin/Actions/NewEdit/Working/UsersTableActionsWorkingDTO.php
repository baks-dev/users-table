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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Working;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\UsersTable\Entity\Actions\Working\UsersTableActionsWorkingInterface;
use BaksDev\Users\UsersTable\Type\Actions\Const\UsersTableActionsWorkingConst;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

/** @see UsersTableActionsWorking */
final class UsersTableActionsWorkingDTO implements UsersTableActionsWorkingInterface
{
    /** Постоянный неизменяемый идентификатор */
    #[Assert\Uuid]
    private readonly UsersTableActionsWorkingConst $const;

    /**
     * Сортировка
     */
    private int $sort = 500;

    /**
     * Коэффициент
     */
    #[Assert\NotBlank]
    private float $coefficient;

    /**
     * Дневная норма
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: 1)]
    private int $norm = 1;

    /**
     * Процент премии переработки
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 100)]
    private int $premium = 0;

    /**
     * Перевод
     */
    private ArrayCollection $translate;


    public function __construct()
    {
        $this->translate = new ArrayCollection();
    }


    /** Постоянный неизменяемый идентификатор */
    public function getConst(): UsersTableActionsWorkingConst
    {
        if (!(new ReflectionProperty(self::class, 'const'))->isInitialized($this)) {
            $this->const = new UsersTableActionsWorkingConst();
        }

        return $this->const;
    }


    public function setConst(UsersTableActionsWorkingConst $const): void
    {
        /** Запрет на изменение readonly   */
        if (!(new ReflectionProperty(self::class, 'const'))->isInitialized($this)) {
            $this->const = $const;
        }
    }


    /**
     * Сортировка
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }


    /**
     * Перевод
     */

    public function setTranslate(ArrayCollection $trans): void
    {
        $this->translate = $trans;
    }


    public function getTranslate(): ArrayCollection
    {
        /* Вычисляем расхождение и добавляем неопределенные локали */
        foreach(Locale::diffLocale($this->translate) as $locale)
        {
            $UsersTableActionsWorkingDTO = new Trans\UsersTableActionsWorkingTransDTO;
            $UsersTableActionsWorkingDTO->setLocal($locale);
            $this->addTranslate($UsersTableActionsWorkingDTO);
        }

        return $this->translate;
    }


    public function addTranslate(Trans\UsersTableActionsWorkingTransDTO $trans): void
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

    public function removeTranslate(Trans\UsersTableActionsWorkingTransDTO $trans): void
    {
        $this->translate->removeElement($trans);
    }

    /**
     * Коэффициент
     */
    public function getCoefficient(): int|float
    {
        return $this->coefficient;
    }

    public function setCoefficient(int|float $coefficient): void
    {
        $this->coefficient = $coefficient;
    }


    /**
     * Дневная норма
     */
    public function getNorm(): int
    {
        return $this->norm;
    }

    public function setNorm(int $norm): void
    {
        $this->norm = $norm;
    }


    /**
     * Процент премии переработки
     */
    public function getPremium(): int
    {
        return $this->premium;
    }

    public function setPremium(int $premium): void
    {
        $this->premium = $premium;
    }




}