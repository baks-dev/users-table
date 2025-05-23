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

namespace BaksDev\Users\UsersTable\Repository\Actions\UsersTableActionsWorkingChoice;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\UsersTable\Entity\Actions\Working\Trans\UsersTableActionsWorkingTrans;
use BaksDev\Users\UsersTable\Entity\Actions\Working\UsersTableActionsWorking;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingUid;

final class UsersTableActionsWorkingChoiceRepository implements UsersTableActionsWorkingChoiceInterface
{

    public function __construct(private readonly ORMQueryBuilder $ORMQueryBuilder) {}

    /**
     * Метод возвращает коллекцию идентификаторов действий активного события UsersTableActions
     */
    public function getCollection(?UsersTableActionsEventUid $event = null): ?array
    {
        $qb = $this
            ->ORMQueryBuilder->createQueryBuilder(self::class)
            ->bindLocal();


        $select = sprintf('new %s(working.id, working.const, trans.name)', UsersTableActionsWorkingUid::class);

        $qb->addSelect($select);

        $qb->from(UsersTableActionsWorking::class, 'working');

        $qb->leftJoin(
            UsersTableActionsWorkingTrans::class,
            'trans',
            'WITH',
            'trans.working = working.id AND trans.local = :local'
        );

        if($event)
        {
            $qb
                ->where('working.event = :event')
                ->setParameter(
                    key: 'event',
                    value: $event,
                    type: UsersTableActionsEventUid::TYPE
                );
        }

        /* Кешируем результат ORM */
        return $qb->getResult();
    }
}
