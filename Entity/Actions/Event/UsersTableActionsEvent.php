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

namespace BaksDev\Users\UsersTable\Entity\Actions\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Users\UsersTable\Entity\Actions\Modify\UsersTableActionsModify;
use BaksDev\Users\UsersTable\Entity\Actions\Products\UsersTableActionsProduct;
use BaksDev\Users\UsersTable\Entity\Actions\Trans\UsersTableActionsTrans;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Entity\Actions\Working\UsersTableActionsWorking;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use BaksDev\Users\UsersTable\Type\Actions\Id\UsersTableActionsUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;


/* UsersTableActionsEvent */

#[ORM\Entity]
#[ORM\Table(name: 'users_table_actions_event')]
class UsersTableActionsEvent extends EntityEvent
{
    public const TABLE = 'users_table_actions_event';

    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UsersTableActionsEventUid::TYPE)]
    private UsersTableActionsEventUid $id;

    /** ID UsersTableActions */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UsersTableActionsUid::TYPE, nullable: false)]
    private ?UsersTableActionsUid $main = null;

    /**
     * Категория производства
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ProductCategoryUid::TYPE)]
    private ProductCategoryUid $category;

    /**
     * Модификатор
     */
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: UsersTableActionsModify::class, cascade: ['all'])]
    private UsersTableActionsModify $modify;

    /**
     * Действия
     */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: UsersTableActionsWorking::class, cascade: ['all'])]
    #[ORM\OrderBy(['sort' => 'ASC'])]
    private Collection $working;

    /**
     * Привязать продукцию к указанному процессу
     */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: UsersTableActionsProduct::class, cascade: ['all'])]
    private Collection $product;


    public function __construct()
    {
        $this->id = new UsersTableActionsEventUid();
        $this->modify = new UsersTableActionsModify($this);

    }

    public function __clone()
    {
        $this->id = new UsersTableActionsEventUid();
    }

    public function __toString(): string
    {
        return (string)$this->id;
    }

    public function getId(): UsersTableActionsEventUid
    {
        return $this->id;
    }

    public function setMain(UsersTableActionsUid|UsersTableActions $main): void
    {
        $this->main = $main instanceof UsersTableActions ? $main->getId() : $main;
    }


    public function getMain(): ?UsersTableActionsUid
    {
        return $this->main;
    }

    public function getDto($dto): mixed
    {
        if($dto instanceof UsersTableActionsEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof UsersTableActionsEventInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

}