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

namespace BaksDev\Users\UsersTable\Repository\Actions\AllUsersTableActions;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Cover\CategoryProductCover;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\Trans\UsersTableActionsTrans;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;

final class AllUsersTableActionsRepository implements AllUsersTableActionsInterface
{

    private ?SearchDTO $search = null;

    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
    ) {}

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    /**
     * Profile
     */
    public function profile(UserProfile|UserProfileUid|string $profile): self
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }



    /** Метод возвращает пагинатор AllUsersTableActions */
    public function findPaginator(): PaginatorInterface
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->select('actions.id');
        $dbal->addSelect('actions.event');

        $dbal->from(UsersTableActions::class, 'actions');

        if($this->profile)
        {
            $dbal
                ->where('actions.profile = :profile')
                ->setParameter('profile', $this->profile, UserProfileUid::TYPE);
        }


        /** Ответственное лицо (Профиль пользователя) */

        $dbal->leftJoin(
            'actions',
            UserProfile::class,
            'users_profile',
            'users_profile.id = actions.profile'
        );

        $dbal->addSelect('users_profile_personal.username AS users_profile_username');

        $dbal->leftJoin(
            'users_profile',
            UserProfilePersonal::class,
            'users_profile_personal',
            'users_profile_personal.event = users_profile.event'
        );


        $dbal->leftJoin(
            'actions',
            UsersTableActionsEvent::class,
            'event',
            'event.id = actions.event'
        );


        $dbal->addSelect('actions_trans.name AS action_name');
        $dbal->leftJoin(
            'actions',
            UsersTableActionsTrans::class,
            'actions_trans',
            'actions_trans.event = actions.event AND actions_trans.local = :local'
        );


        $dbal->leftJoin(
            'event',
            CategoryProduct::class,
            'category',
            'category.id = event.category'
        );

        $dbal->addSelect('trans.name AS category_name');

        $dbal->leftJoin(
            'category',
            CategoryProductTrans::class,
            'trans',
            'trans.event = category.event AND trans.local = :local'
        );


        // Обложка

        $dbal->addSelect(
            "
			CASE
			   WHEN category_cover.name IS NOT NULL THEN
					CONCAT ( '/upload/".$dbal->table(CategoryProductCover::class)."' , '/', category_cover.name)
			   ELSE NULL
			END AS category_cover_name
		"
        );

        $dbal->addSelect('category_cover.ext AS category_cover_ext');
        $dbal->addSelect('category_cover.cdn AS category_cover_cdn');


        $dbal->leftJoin(
            'category',
            CategoryProductCover::class,
            'category_cover',
            'category_cover.event = category.event'
        );


        /* Поиск */
        if($this->search->getQuery())
        {

            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchEqualUid('actions.id')
                ->addSearchEqualUid('actions.event')
                ->addSearchEqualUid('actions.profile')
                ->addSearchLike('trans.name')
                ->addSearchLike('users_profile_personal.username');

        }

        return $this->paginator->fetchAllAssociative($dbal);
    }
}
