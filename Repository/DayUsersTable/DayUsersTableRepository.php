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

namespace BaksDev\Users\UsersTable\Repository\DayUsersTable;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\Trans\UsersTableActionsTrans;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Entity\Actions\Working\Trans\UsersTableActionsWorkingTrans;
use BaksDev\Users\UsersTable\Entity\Actions\Working\UsersTableActionsWorking;
use BaksDev\Users\UsersTable\Entity\UsersTableDay;
use BaksDev\Users\UsersTable\Forms\DayUsersTableFilter\DayUsersTableFilterInterface;
use DateTimeImmutable;
use Doctrine\DBAL\ParameterType;

final class DayUsersTableRepository implements DayUsersTableInterface
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

    /** Метод возвращает пагинатор UsersTableDay */
    public function fetchDayUsersTableAssociative(
        SearchDTO $search,
        DayUsersTableFilterInterface $filter,
        UserProfileUid $profile,
        ?UserProfileUid $authority = null,
        bool $other = false
    ): PaginatorInterface {
        $qb = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $qb->addSelect('users_table_day.total AS table_total');
        $qb->addSelect('users_table_day.money AS table_money');
        $qb->addSelect('users_table_day.premium AS table_premium');
        $qb->addSelect('users_table_day.date_table AS table_date');
        $qb->from(UsersTableDay::TABLE, 'users_table_day');


        /** Табели других пользователей */


        if($authority)
        {
            $qb->leftJoin(
                'users_table_day',
                ProfileGroupUsers::TABLE,
                'profile_group_users',
                'profile_group_users.authority = :authority '.($other ? '' : ' AND profile_group_users.profile = :profile')
            );

            $qb
                ->andWhere('users_table_day.profile = profile_group_users.profile')
                ->setParameter('authority', $authority, UserProfileUid::TYPE)
                ->setParameter('profile', $filter?->getProfile() ?: $profile, UserProfileUid::TYPE);

            /** Если пользователь авторизован - и передан фильтр по профилю  */
            if($filter?->getProfile())
            {
                $qb
                    ->andWhere('users_table_day.profile = :profile')
                    ->setParameter('profile', $filter?->getProfile(), UserProfileUid::TYPE);
            }

        }
        else
        {
            $qb
                ->andWhere('users_table_day.profile = :profile')
                ->setParameter('profile', $profile, UserProfileUid::TYPE);
        }


        /**
         * Действие
         */
        $qb->addSelect('working.id AS table_working_id');
        $qb->leftJoin(
            'users_table_day',
            UsersTableActionsWorking::TABLE,
            'working',
            'working.id = users_table_day.working'
        );

        $qb->addSelect('working_trans.name AS table_working');

        $qb->leftJoin(
            'working',
            UsersTableActionsWorkingTrans::TABLE,
            'working_trans',
            'working_trans.working = working.id AND working_trans.local = :local'
        );


        $qb->addSelect('action_event.id AS table_action_id');
        $qb->leftJoin(
            'working',
            UsersTableActionsEvent::TABLE,
            'action_event',
            'action_event.id = working.event'
        );


        if($authority)
        {
            $qb->join(
                'action_event',
                UsersTableActions::TABLE,
                'actions',
                'actions.id = action_event.main AND actions.profile = :authority'
            );

            $qb->setParameter('authority', $authority, UserProfileUid::TYPE);
        }
        else
        {

            $qb->andWhere('users_table_day.profile = :profile')
                ->setParameter('profile', $profile, UserProfileUid::TYPE);

            //$qb->setParameter('authority', $profile, UserProfileUid::TYPE);
        }


        $qb->addSelect('action_trans.name AS table_action');

        $qb->leftJoin(
            'action_event',
            UsersTableActionsTrans::TABLE,
            'action_trans',
            'action_trans.event = action_event.id AND action_trans.local = :local'
        );


        $qb->addSelect('category.id AS table_category_id');
        $qb->leftJoin(
            'action_event',
            CategoryProduct::TABLE,
            'category',
            'category.id = action_event.category'
        );

        $qb->addSelect('category_trans.name AS table_category');

        $qb->leftJoin(
            'category',
            CategoryProductTrans::TABLE,
            'category_trans',
            'category_trans.event = category.event AND category_trans.local = :local'
        );


        // ОТВЕТСТВЕННЫЙ

        // UserProfile
        $qb->addSelect('users_profile.event as users_profile_event');
        $qb->join(
            'users_table_day',
            UserProfile::TABLE,
            'users_profile',
            'users_profile.id = users_table_day.profile'
        );

        // Info
        $qb->join(
            'users_table_day',
            UserProfileInfo::TABLE,
            'users_profile_info',
            'users_profile_info.profile = users_table_day.profile'
        );

        // Event
        $qb->join(
            'users_profile',
            UserProfileEvent::TABLE,
            'users_profile_event',
            'users_profile_event.id = users_profile.event'
        );

        // Personal
        $qb->addSelect('users_profile_personal.username AS users_profile_username');

        $qb->join(
            'users_profile_event',
            UserProfilePersonal::TABLE,
            'users_profile_personal',
            'users_profile_personal.event = users_profile_event.id'
        );

        // Avatar


        $qb->addSelect("CASE WHEN users_profile_avatar.name IS NULL THEN users_profile_avatar.name ELSE CONCAT ( '/upload/".UserProfileAvatar::TABLE."' , '/', users_profile_avatar.name) END AS users_profile_avatar");
        $qb->addSelect("users_profile_avatar.ext AS users_profile_avatar_ext");
        $qb->addSelect('users_profile_avatar.cdn AS users_profile_avatar_cdn');

        $qb->leftJoin(
            'users_profile_event',
            UserProfileAvatar::TABLE,
            'users_profile_avatar',
            'users_profile_avatar.event = users_profile_event.id'
        );


        $date = (new DateTimeImmutable())->setTime(0, 0)->getTimestamp();

        if($filter && $filter->getDate())
        {
            $date = $filter->getDate()->getTimestamp();
        }

        $qb->andWhere('users_table_day.date_table = :date');
        $qb->setParameter('date', $date, ParameterType::INTEGER);

        //$qb->addOrderBy('users_profile.id', 'DESC');
        $qb->addOrderBy('category.id', 'DESC');
        $qb->addOrderBy('action_event.id', 'DESC');
        $qb->addOrderBy('working.id', 'DESC');
        //$qb->orderBy('users_table_day.total', 'DESC');

        return $this->paginator->fetchAllAssociative($qb);

    }
}
