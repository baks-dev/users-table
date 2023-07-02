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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Month;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;
use BaksDev\Users\UsersTable\Entity\UsersTableDayInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

/** @see UsersTableDay */
final class UsersTableMonthDTO implements UsersTableDayInterface
{
    /** ID профиля пользователя */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private UserProfileUid $profile;

    /**
     * Дата табеля (timestamp).
     */
    #[Assert\NotBlank]
    private int $date;

    /**
     * Количество.
     */
    #[Assert\NotBlank]
    private int $total;

    /**
     * Profile.
     */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

    public function setProfile(UserProfileUid $profile): void
    {
        $this->profile = $profile;
    }

    /**
     * Дата табеля (timestamp).
     */
    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int|DateTimeImmutable $date): void
    {
        if ($date instanceof DateTimeImmutable)
        {
            $date = $date
                ->modify('first day of') // Устанавливает первый день текущего месяца
                ->setTime(0, 0)
                ->getTimestamp();
        }

        $this->date = $date;
    }

    /**
     * Количество.
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
}
