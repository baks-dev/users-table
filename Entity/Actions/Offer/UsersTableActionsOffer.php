<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\UsersTable\Entity\Actions\Offer;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UsersTableActionsOffer
 *
 * @see UsersTableActionsEvent
 */
#[ORM\Entity]
#[ORM\Table(name: 'users_table_actions_offer')]
class UsersTableActionsOffer extends EntityEvent
{
    /** Связь на событие */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: UsersTableActionsEvent::class, inversedBy: 'offer')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private UsersTableActionsEvent $event;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $offer = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $variation = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $modification = false;

    public function __construct(UsersTableActionsEvent $event)
    {
        $this->event = $event;
    }

    public function __toString(): string
    {
        return (string) $this->event;
    }

    /** @return UsersTableActionsOfferInterface */
    public function getDto($dto): mixed
    {
        if(is_string($dto) && class_exists($dto))
        {
            $dto = new $dto();
        }

        if($dto instanceof UsersTableActionsOfferInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    /** @var UsersTableActionsOfferInterface $dto */
    public function setEntity($dto): mixed
    {
        if($dto instanceof UsersTableActionsOfferInterface)
        {
//            if($dto->getValue() instanceof UserProfileUid)
//            {
//                return parent::setEntity($dto);
//            }
//
//            return false;
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

}