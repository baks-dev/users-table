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

namespace BaksDev\Users\UsersTable\Messenger\Actions;

use BaksDev\Users\UsersTable\Type\Actions\Id\UsersTableActionsUid;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;

final class UsersTableActionsMessage
{
    /** Идентификатор */
    private UsersTableActionsUid $id;

    /** Идентификатор события */
    private UsersTableActionsEventUid $event;

    /** Идентификатор предыдущего события */
    private ?UsersTableActionsEventUid $last;

    public function __construct(UsersTableActionsUid $id, UsersTableActionsEventUid $event, ?UsersTableActionsEventUid $last = null)
    {
        $this->last = $last;
        $this->id = $id;
        $this->event = $event;
    }

    /** Идентификатор */
    public function getId(): UsersTableActionsUid
    {
        return $this->id;
    }

    /** Идентификатор события */
    public function getEvent(): UsersTableActionsEventUid
    {
        return $this->event;
    }

    /** Идентификатор предыдущего события */
    public function getLast(): ?UsersTableActionsEventUid
    {
        return $this->last;
    }
}
