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

namespace BaksDev\Users\UsersTable\Repository\Actions\UsersTableActionsChoice;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\Trans\UsersTableActionsTrans;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;

final class UsersTableActionsChoiceRepository implements UsersTableActionsChoiceInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;

    public function __construct(
        ORMQueryBuilder $ORMQueryBuilder,
    )
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }

    /**
     * Метод возвращает коллекцию идентификаторов активных процессов производства
     */
    public function getCollection(UserProfileUid $profile, ?ProductCategoryUid $category = null): ?array
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class)->bindLocal();

        $select = sprintf('new %s(actions.event, trans.name)', UsersTableActionsEventUid::class);

        $qb->addSelect($select);

        $qb->from(UsersTableActions::class, 'actions');

        $qb->where('actions.profile = :profile')
            ->setParameter('profile', $profile, UserProfileUid::TYPE);

        $qb->join(

            UsersTableActionsEvent::class,
            'event',
            'WITH',
            'event.id = actions.event'
        );


        if($category)
        {

            $qb
                ->andWhere('event.category = :category')
                ->setParameter('category', $category, ProductCategoryUid::TYPE);

        }


        $qb->leftJoin(
            UsersTableActionsTrans::class,
            'trans',
            'WITH',
            'trans.event = actions.event AND trans.local = :local'
        );


        /* Кешируем результат ORM */
        return $qb->enableCache('users-table', 86400)->getResult();
    }
}