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

namespace BaksDev\Users\UsersTable\Entity\Table;

use BaksDev\Users\UsersTable\Entity\Table\Event\UsersTableEvent;
use BaksDev\Users\UsersTable\Type\Table\Event\UsersTableEventUid;
use BaksDev\Users\UsersTable\Type\Table\Id\UsersTableUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/* UsersTable */

#[ORM\Entity]
#[ORM\Table(name: 'users_table')]
class UsersTable
{
    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UsersTableUid::TYPE)]
    private UsersTableUid $id;


    /** ID События */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UsersTableEventUid::TYPE, unique: true)]
    private UsersTableEventUid $event;

    public function __construct()
    {
        $this->id = new UsersTableUid();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): UsersTableUid
    {
        return $this->id;
    }

    public function getEvent(): UsersTableEventUid
    {
        return $this->event;
    }

    public function setEvent(UsersTableEventUid|UsersTableEvent $event): void
    {
        $this->event = $event instanceof UsersTableEvent ? $event->getId() : $event;
    }
}
