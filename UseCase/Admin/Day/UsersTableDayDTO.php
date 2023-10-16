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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Day;

use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Working\UsersTableActionsWorking;
use BaksDev\Users\UsersTable\Entity\UsersTableDayInterface;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingUid;
use DateTimeImmutable;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

/** @see UsersTableDay */
final class UsersTableDayDTO implements UsersTableDayInterface
{
    /** ID профиля пользователя */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly UserProfileUid $profile;


    /** Действие */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly  UsersTableActionsWorkingUid $working;

    /**
     * Дата табеля (timestamp).
     */
    #[Assert\NotBlank]
    private readonly  int $date;

    /**
     * Количество.
     */
    #[Assert\NotBlank]
    private int $total = 0;

    /**
     * Стоимость с учетом коэффициента.
     */
    #[Assert\NotBlank]
    private Money $money;

    /**
     * Премия за переработку с учетом дневной нормы.
     */
    #[Assert\NotBlank]
    private Money $premium;


    public function __construct() {
        $this->money = new Money(0);
        $this->premium = new Money(0);
    }

    /**
     * Profile.
     */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

    public function setProfile(UserProfile|UserProfileUid $profile): void
    {
        if(!(new ReflectionProperty(self::class, 'profile'))->isInitialized($this))
        {
            $this->profile = $profile instanceof UserProfile ? $profile->getId() : $profile;
        }
    }

    /**
     * Дата табеля (timestamp).
     */
    public function getDate(): int
    {

        return $this->date;
    }

    public function setDate(DateTimeImmutable|int $date): void
    {
        if(!(new ReflectionProperty(self::class, 'date'))->isInitialized($this))
        {
            if ($date instanceof DateTimeImmutable)
            {
                $date = $date->setTime(0, 0)->getTimestamp();
            }

            $this->date = $date;
        }
    }

    /**
     * Количество
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    // Увеличиваем количество
    public function addTotal(int $total): void
    {
        $this->total += $total;
    }

    /**
     * Стоимость с учетом коэффициента.
     */
    public function getMoney(): Money
    {
        return $this->money;
    }

    public function setMoney(Money $money): void
    {
        $this->money = $money;
    }

    /**
     * Премия за переработку с учетом дневной нормы.
     */
    public function getPremium(): Money
    {
        return $this->premium;
    }

    public function setPremium(Money $premium): void
    {
        $this->premium = $premium;
    }

    /**
     * Working
     */
    public function getWorking(): UsersTableActionsWorkingUid
    {
        return $this->working;
    }

    public function setWorking(UsersTableActionsWorking|UsersTableActionsWorkingUid $working): void
    {
        if(!(new ReflectionProperty(self::class, 'working'))->isInitialized($this))
        {
            $this->working = $working instanceof UsersTableActionsWorking ? $working->getId() : $working;
        }
    }

}
