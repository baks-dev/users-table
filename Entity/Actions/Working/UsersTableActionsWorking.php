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

namespace BaksDev\Users\UsersTable\Entity\Actions\Working;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\Working\Trans\UsersTableActionsWorkingTrans;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;


/* UsersTableActionsWorking */

#[ORM\Entity]
#[ORM\Table(name: 'users_table_actions_working')]
class UsersTableActionsWorking extends EntityEvent
{
    public const TABLE = 'users_table_actions_working';

    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UsersTableActionsWorkingUid::TYPE)]
    private UsersTableActionsWorkingUid $id;

    /** Связь на событие */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: UsersTableActionsEvent::class, inversedBy: "working")]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: "id")]
    private UsersTableActionsEvent $event;


    /** Перевод */
    #[ORM\OneToMany(mappedBy: 'working', targetEntity: UsersTableActionsWorkingTrans::class, cascade: ['all'])]
    private Collection $translate;

    /**
     * Коэффициент
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::INTEGER)]
    private int $coefficient;

    /**
     * Дневная норма
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: 1)]
    #[ORM\Column(type: Types::INTEGER)]
    private int $norm;

    /**
     * Процент премии переработки
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 100)]
    #[ORM\Column(type: Types::SMALLINT, length: 3)]
    private int $premium;


    public function __construct(UsersTableActionsEvent $event)
    {
        $this->event = $event;
        $this->id = new UsersTableActionsWorkingUid();
    }

    public function __clone()
    {
        $this->id = new UsersTableActionsWorkingUid();
    }

    public function __toString(): string
    {
        return (string)$this->id;
    }

    public function getId(): UsersTableActionsWorkingUid
    {
        return $this->id;
    }



    public function getDto($dto): mixed
    {
        if ($dto instanceof UsersTableActionsWorkingInterface) {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if ($dto instanceof UsersTableActionsWorkingInterface) {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


//	public function isModifyActionEquals(ModifyActionEnum $action) : bool
//	{
//		return $this->modify->equals($action);
//	}

//	public function getUploadClass() : UsersTableActionsWorkingImage
//	{
//		return $this->image ?: $this->image = new UsersTableActionsWorkingImage($this);
//	}

//	public function getNameByLocale(Locale $locale) : ?string
//	{
//		$name = null;
//		
//		/** @var UsersTableActionsWorkingTrans $trans */
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