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

namespace BaksDev\Users\UsersTable\Repository\Actions\AllUsersTableActions;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Products\Category\Entity\Cover\CategoryProductCover;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\Trans\UsersTableActionsTrans;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;

final class AllUsersTableActionsRepository implements AllUsersTableActionsInterface
{
    private PaginatorInterface $paginator;
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        PaginatorInterface $paginator,
    ) {

        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /** Метод возвращает пагинатор AllUsersTableActions */
    public function fetchAllUsersTableActionsAssociative(
        SearchDTO $search,
        ?UserProfileUid $profile
    ): PaginatorInterface {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class)
            ->bindLocal();

        $qb->select('actions.id');
        $qb->addSelect('actions.event');

        $qb->from(UsersTableActions::TABLE, 'actions');

        if($profile)
        {
            $qb
                ->where('actions.profile = :profile')
                ->setParameter('profile', $profile, UserProfileUid::TYPE);
        }


        /** Ответственное лицо (Профиль пользователя) */

        $qb->leftJoin(
            'actions',
            UserProfile::TABLE,
            'users_profile',
            'users_profile.id = actions.profile'
        );

        $qb->addSelect('users_profile_personal.username AS users_profile_username');

        $qb->leftJoin(
            'users_profile',
            UserProfilePersonal::TABLE,
            'users_profile_personal',
            'users_profile_personal.event = users_profile.event'
        );


        $qb->leftJoin(
            'actions',
            UsersTableActionsEvent::TABLE,
            'event',
            'event.id = actions.event'
        );


        $qb->addSelect('actions_trans.name AS action_name');
        $qb->leftJoin(
            'actions',
            UsersTableActionsTrans::TABLE,
            'actions_trans',
            'actions_trans.event = actions.event AND actions_trans.local = :local'
        );


        $qb->leftJoin(
            'event',
            CategoryProduct::TABLE,
            'category',
            'category.id = event.category'
        );

        $qb->addSelect('trans.name AS category_name');

        $qb->leftJoin(
            'category',
            CategoryProductTrans::TABLE,
            'trans',
            'trans.event = category.event AND trans.local = :local'
        );


        // Обложка

        $qb->addSelect(
            "
			CASE
			   WHEN category_cover.name IS NOT NULL THEN
					CONCAT ( '/upload/".CategoryProductCover::TABLE."' , '/', category_cover.name)
			   ELSE NULL
			END AS category_cover_name
		"
        );

        $qb->addSelect('category_cover.ext AS category_cover_ext');
        $qb->addSelect('category_cover.cdn AS category_cover_cdn');


        $qb->leftJoin(
            'category',
            CategoryProductCover::TABLE,
            'category_cover',
            'category_cover.event = category.event'
        );


        /* Поиск */
        if($search->getQuery())
        {

            $qb
                ->createSearchQueryBuilder($search)
                ->addSearchEqualUid('actions.id')
                ->addSearchEqualUid('actions.event')
                ->addSearchEqualUid('actions.profile')
                ->addSearchLike('trans.name')
                ->addSearchLike('users_profile_personal.username');

        }

        return $this->paginator->fetchAllAssociative($qb);
    }
}
