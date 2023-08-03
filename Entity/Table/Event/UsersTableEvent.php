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

namespace BaksDev\Users\UsersTable\Entity\Table\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Table\Modify\UsersTableModify;
use BaksDev\Users\UsersTable\Entity\Table\UsersTable;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingUid;
use BaksDev\Users\UsersTable\Type\Table\Event\UsersTableEventUid;
use BaksDev\Users\UsersTable\Type\Table\Id\UsersTableUid;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* UsersTableEvent */

#[ORM\Entity]
#[ORM\Table(name: 'users_table_event')]
class UsersTableEvent extends EntityEvent
{
    public const TABLE = 'users_table_event';

    /**
     * Идентификатор
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UsersTableEventUid::TYPE)]
    private UsersTableEventUid $id;


    /**
     * Идентификатор UsersTable.
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UsersTableUid::TYPE)]
    private ?UsersTableUid $main = null;

    /**
     * Профиль пользователя.
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private UserProfileUid $profile;

    /**
     * Дата табеля.
     */
    #[Assert\NotBlank]
    #[ORM\Column(name: 'date_table', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $date;

    /**
     * Действие.
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UsersTableActionsWorkingUid::TYPE)]
    private UsersTableActionsWorkingUid $working;

    /**
     * Количество.
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::INTEGER)]
    private int $quantity;

    /**
     * Модификатор
     */
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: UsersTableModify::class, cascade: ['all'])]
    private UsersTableModify $modify;

    public function __construct()
    {
        $this->id = new UsersTableEventUid();
        $this->modify = new UsersTableModify($this);
    }

    public function __clone()
    {
        $this->id = new UsersTableEventUid();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): UsersTableEventUid
    {
        return $this->id;
    }

    public function setMain(UsersTableUid|UsersTable $main): void
    {
        $this->main = $main instanceof UsersTable ? $main->getId() : $main;
    }

    public function getMain(): ?UsersTableUid
    {
        return $this->main;
    }

    public function getDto($dto): mixed
    {
        if ($dto instanceof UsersTableEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if ($dto instanceof UsersTableEventInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    /**
     * Профиль пользователя.
     */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

    /**
     * Дата табеля.
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * Количество.
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Действие.
     */
    public function getWorking(): UsersTableActionsWorkingUid
    {
        return $this->working;
    }




//	public function isModifyActionEquals(ModifyActionEnum $action) : bool
//	{
//		return $this->modify->equals($action);
//	}

//	public function getUploadClass() : UsersTableImage
//	{
//		return $this->image ?: $this->image = new UsersTableImage($this);
//	}

//	public function getNameByLocale(Locale $locale) : ?string
//	{
//		$name = null;
//
//		/** @var UsersTableTrans $trans */
//		foreach($this->translate as $trans)
//		{
//			if($name = $trans->name($locale))
//			{
//				break;
//			}
//		}
//
//		return $name;
//	}
}
