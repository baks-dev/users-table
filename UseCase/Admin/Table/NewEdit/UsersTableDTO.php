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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Table\NewEdit;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Table\Event\UsersTableEventInterface;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingUid;
use BaksDev\Users\UsersTable\Type\Table\Event\UsersTableEventUid;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

/** @see UsersTableEvent */
final class UsersTableDTO implements UsersTableEventInterface
{
    /**
     * Идентификатор события.
     */
    #[Assert\Uuid]
    private ?UsersTableEventUid $id = null;

    /**
     * Профиль пользователя.
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private UserProfileUid $profile;


    /**
     * Действие сотрудника
     */
    #[Assert\Uuid]
    private ?UsersTableActionsWorkingUid $working = null;

    /**
     * Дата.
     */
    #[Assert\NotBlank]
    private DateTimeImmutable $date;

    /**
     * Количество.
     * Может быть отрицательным - означает штраф!
     */
    #[Assert\NotBlank]
    private int $quantity;

    /*
     *
     * Вспомогательное свойство
     *
     */


    /**
     * Категория производства
     */
    #[Assert\Uuid]
    public ?CategoryProductUid $category = null;


    /**
     * Производственный процесс
     */
    #[Assert\Uuid]
    private ?UsersTableActionsEventUid $action = null;


    /**
     * Профиль пользователя фильтрации производственных процессов
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly UserProfileUid $authority;


    public function __construct(UserProfileUid $authority)
    {
        $this->date = new DateTimeImmutable();
        $this->authority = $authority;
    }

    /**
     * Идентификатор события.
     */
    public function getEvent(): ?UsersTableEventUid
    {
        return $this->id;
    }

    public function setId(?UsersTableEventUid $id): void
    {
        $this->id = $id;
    }

    /**
     * Профиль пользователя.
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
     * Количество.
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }


    /**
     * Дата.
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?DateTimeImmutable $date): void
    {
        $this->date = $date ?: new DateTimeImmutable();
    }


    /**
     * Категория производства
     */
    public function getAction(): ?UsersTableActionsEventUid
    {
        return $this->action;
    }

    public function setAction(UsersTableActionsEventUid $action): void
    {
        $this->action = $action;
    }


    /**
     * Действие
     */
    public function getWorking(): ?UsersTableActionsWorkingUid
    {
        return $this->working;
    }

    public function setWorking(UsersTableActionsWorkingUid|string $working): void
    {

        if(is_string($working))
        {
            $working = new UsersTableActionsWorkingUid($working);
        }

        $this->working = $working;
    }


    /**
     * Authority
     */
    public function getAuthority(): UserProfileUid
    {
        return $this->authority;
    }


}
