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
 *
 */

declare(strict_types=1);

namespace BaksDev\Users\UsersTable\Repository\AllUsersTable;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Entity\Actions\Working\Trans\UsersTableActionsWorkingTrans;
use BaksDev\Users\UsersTable\Entity\Actions\Working\UsersTableActionsWorking;
use BaksDev\Users\UsersTable\Entity\Table\Event\UsersTableEvent;
use BaksDev\Users\UsersTable\Entity\Table\UsersTable;
use BaksDev\Users\UsersTable\Forms\UserTableFilter\UserTableFilterDTO;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;

final class AllUsersTableRepository implements AllUsersTableInterface
{
    private ?SearchDTO $search = null;

    private UserTableFilterDTO $filter;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
        private readonly UserProfileTokenStorageInterface $profileTokenStorage
    ) {}

    public function search(SearchDTO $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function filter(UserTableFilterDTO $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /** Метод возвращает пагинатор UsersTable */
    public function findAll(bool $other = false): PaginatorInterface
    {

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->addSelect('users_table.id')
            ->addSelect('users_table.event')
            ->from(UsersTable::class, 'users_table');

        $dbal
            ->addSelect('event.quantity AS table_quantity')
            ->addSelect('event.date_table AS table_date')
            ->leftJoin(
                'users_table',
                UsersTableEvent::class,
                'event',
                'event.id = users_table.event',
            );


        /** Только текущего пользователя */

        if(false === $other)
        {
            $dbal
                ->andWhere('event.profile = :profile')
                ->setParameter(
                    key: 'profile',
                    value: $this->profileTokenStorage->getProfileCurrent(),
                    type: UserProfileUid::TYPE,
                );
        }

        $dbal
            ->andWhere('DATE(event.date_table) = :date_between')
            ->setParameter(
                key: 'date_between',
                value: ($this->filter && $this->filter->getDate()) ? $this->filter->getDate() : new DateTimeImmutable(),
                type: Types::DATE_IMMUTABLE,
            );


        /**
         * Действие
         */
        $dbal->leftJoin(
            'event',
            UsersTableActionsWorking::class,
            'working',
            'working.id = event.working',
        );

        $dbal
            ->addSelect('working_trans.name AS table_working')
            ->leftJoin(
                'working',
                UsersTableActionsWorkingTrans::class,
                'working_trans',
                'working_trans.working = working.id AND working_trans.local = :local',
            );

        $dbal->leftJoin(
            'working',
            UsersTableActionsEvent::class,
            'action_event',
            'action_event.id = working.event',
        );

        /** Только действия по магазину */

        $dbal
            ->join(
                'action_event',
                UsersTableActions::class,
                'actions',
                'actions.id = action_event.main AND actions.profile = :authority',
            )
            ->setParameter(
                'authority',
                $this->profileTokenStorage->getProfile(),
                UserProfileUid::TYPE,
            );


        $dbal->leftJoin(
            'action_event',
            CategoryProduct::class,
            'category',
            'category.id = action_event.category',
        );

        $dbal->addSelect('trans.name AS table_action');

        $dbal->leftJoin(
            'category',
            CategoryProductTrans::class,
            'trans',
            'trans.event = category.event AND trans.local = :local',
        );


        // ОТВЕТСТВЕННЫЙ

        // UserProfile
        $dbal
            ->addSelect('users_profile.event as users_profile_event')
            ->join(
                'event',
                UserProfile::class,
                'users_profile',
                'users_profile.id = event.profile',
            );

        // Info
        $dbal->join(
            'event',
            UserProfileInfo::class,
            'users_profile_info',
            'users_profile_info.profile = event.profile',
        );

        // Event
        $dbal->join(
            'users_profile',
            UserProfileEvent::class,
            'users_profile_event',
            'users_profile_event.id = users_profile.event',
        );

        // Personal
        $dbal->addSelect('users_profile_personal.username AS users_profile_username');

        $dbal->join(
            'users_profile_event',
            UserProfilePersonal::class,
            'users_profile_personal',
            'users_profile_personal.event = users_profile_event.id',
        );

        // Avatar

        $dbal->addSelect("
            CASE 
                WHEN users_profile_avatar.name IS NULL 
                THEN users_profile_avatar.name 
                ELSE CONCAT ( '/upload/".$dbal->table(UserProfileAvatar::class)."' , '/', users_profile_avatar.name) 
            END AS users_profile_avatar
        ");

        $dbal->addSelect("users_profile_avatar.ext AS users_profile_avatar_ext");
        $dbal->addSelect('users_profile_avatar.cdn AS users_profile_avatar_cdn');

        $dbal->leftJoin(
            'users_profile_event',
            UserProfileAvatar::class,
            'users_profile_avatar',
            'users_profile_avatar.event = users_profile_event.id',
        );

        if($this->search->getQuery())
        {
            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchLike('users_profile_personal.username');
        }


        $dbal->orderBy('event.date_table', 'DESC');

        return $this->paginator->fetchAllAssociative($dbal);

    }
}
