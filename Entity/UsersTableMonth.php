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

namespace BaksDev\Users\UsersTable\Entity;

use BaksDev\Core\Entity\EntityState;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingUid;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* UsersTableMonth */

#[ORM\Entity]
#[ORM\Table(name: 'users_table_month')]
class UsersTableMonth extends EntityState
{
    /** ID профиля пользователя */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private readonly UserProfileUid $profile;

    /** Действие */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UsersTableActionsWorkingUid::TYPE)]
    private readonly UsersTableActionsWorkingUid $working;

    /**
     * Дата ежемесячного табеля (timestamp).
     */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\Column(name: 'date_table', type: Types::INTEGER)]
    private readonly int $date;

    /**
     * Флаг выплаты
     */
    #[ORM\Id]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $payment = false;


    /** Дата выплаты */
    #[ORM\Column(name: 'date_payment', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $datePayment = null;


    /**
     * Количество выполненной работы
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $total = 0;


    /**
     * Стоимость работы
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Money::TYPE)]
    private Money $money;


    /**
     * Премия за переработку
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Money::TYPE)]
    private Money $premium;


    public function __toString(): string
    {
        return (string) $this->profile;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof UsersTableDayInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof UsersTableDayInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
