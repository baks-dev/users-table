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

namespace BaksDev\Users\UsersTable\Repository\AllUsersTable;

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Services\Switcher\SwitcherInterface;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Entity\ProductCategory;
use BaksDev\Products\Category\Entity\Trans\ProductCategoryTrans;
use BaksDev\Users\Groups\Group\Entity\Group;
use BaksDev\Users\Groups\Group\Entity\Trans\GroupTrans;
use BaksDev\Users\Groups\Users\Entity\CheckUsers;
use BaksDev\Users\Groups\Users\Entity\Event\CheckUsersEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\Trans\UsersTableActionsTrans;
use BaksDev\Users\UsersTable\Entity\Actions\Working\Trans\UsersTableActionsWorkingTrans;
use BaksDev\Users\UsersTable\Entity\Actions\Working\UsersTableActionsWorking;
use BaksDev\Users\UsersTable\Entity\Table\Event\UsersTableEvent;
use BaksDev\Users\UsersTable\Entity\Table\UsersTable;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AllUsersTable implements AllUsersTableInterface
{
    private Connection $connection;

    private PaginatorInterface $paginator;

    private SwitcherInterface $switcher;

    private TranslatorInterface $translator;

    public function __construct(
        Connection $connection,
        PaginatorInterface $paginator,
        SwitcherInterface $switcher,
        TranslatorInterface $translator,
    ) {
        $this->connection = $connection;
        $this->paginator = $paginator;
        $this->switcher = $switcher;
        $this->translator = $translator;
    }

    /** Метод возвращает пагинатор UsersTable */
    public function fetchAllUsersTableAssociative(SearchDTO $search): PaginatorInterface
    {
        $qb = $this->connection->createQueryBuilder();

        $local = new Locale($this->translator->getLocale());

        $qb->addSelect('users_table.id');
        $qb->addSelect('users_table.event');
        $qb->addSelect('event.quantity AS table_quantity');
        $qb->addSelect('event.date_table AS table_date');

        $qb->from(UsersTable::TABLE, 'users_table');

        $qb->leftJoin(
            'users_table',
            UsersTableEvent::TABLE,
            'event',
            'event.id = users_table.event'
        );


        /**
         * Действие
         */
        $qb->leftJoin(
            'event',
            UsersTableActionsWorking::TABLE,
            'working',
            'working.id = event.working'
        );

        $qb->addSelect('working_trans.name AS table_working');
        $qb->leftJoin(
            'working',
            UsersTableActionsWorkingTrans::TABLE,
            'working_trans',
            'working_trans.working = working.id AND working_trans.local = :local'
        );

        $qb->leftJoin(
            'working',
            UsersTableActionsEvent::TABLE,
            'action_event',
            'action_event.id = working.event'
        );



/*        $qb->addSelect('action_trans.name AS table_action');
        $qb->leftJoin(
            'action_event',
            UsersTableActionsTrans::TABLE,
            'action_trans',
            'action_trans.event = action_event.id AND action_trans.local = :local'
        );*/


        $qb->leftJoin(
            'action_event',
            ProductCategory::TABLE,
            'category',
            'category.id = action_event.category'
        );

        $qb->addSelect('trans.name AS table_action');

        $qb->leftJoin(
            'category',
            ProductCategoryTrans::TABLE,
            'trans',
            'trans.event = category.event AND trans.local = :local'
        );


        $qb->setParameter('local', $local, Locale::TYPE);

        // ОТВЕТСТВЕННЫЙ

        // UserProfile
        $qb->addSelect('users_profile.event as users_profile_event');
        $qb->join(
            'event',
            UserProfile::TABLE,
            'users_profile',
            'users_profile.id = event.profile'
        );

        // Info
        $qb->join(
            'event',
            UserProfileInfo::TABLE,
            'users_profile_info',
            'users_profile_info.profile = event.profile'
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

        $qb->addSelect("CONCAT ( '/upload/".UserProfileAvatar::TABLE."' , '/', users_profile_avatar.dir, '/', users_profile_avatar.name, '.') AS users_profile_avatar");
        $qb->addSelect("CASE WHEN users_profile_avatar.cdn THEN  CONCAT ( 'small.', users_profile_avatar.ext) ELSE users_profile_avatar.ext END AS users_profile_avatar_ext");
        $qb->addSelect('users_profile_avatar.cdn AS users_profile_avatar_cdn');

        $qb->leftJoin(
            'users_profile_event',
            UserProfileAvatar::TABLE,
            'users_profile_avatar',
            'users_profile_avatar.event = users_profile_event.id'
        );

        // Группа

        $qb->join(
            'users_profile_info',
            CheckUsers::TABLE,
            'check_user',
            'check_user.user_id = users_profile_info.user_id'
        );

        $qb->join(
            'check_user',
            CheckUsersEvent::TABLE,
            'check_user_event',
            'check_user_event.id = check_user.event'
        );

        $qb->leftJoin(
            'check_user_event',
            Group::TABLE,
            'groups',
            'groups.id = check_user_event.group_id'
        );

        $qb->addSelect('groups_trans.name AS group_name'); // Название группы

        $qb->leftJoin(
            'groups',
            GroupTrans::TABLE,
            'groups_trans',
            'groups_trans.event = groups.event AND groups_trans.local = :local'
        );



        $qb->setParameter('local', $local, Locale::TYPE);
        $qb->orderBy('event.date_table', 'DESC');


        /* Поиск */
        if ($search->query)
        {
//            $search->query = mb_strtolower($search->query);

//            $searcher = $this->connection->createQueryBuilder();

//            $searcher->orWhere('LOWER(trans.name) LIKE :query');
//            $searcher->orWhere('LOWER(trans.name) LIKE :switcher');

//            $qb->andWhere('('.$searcher->getQueryPart('where').')');
//            $qb->setParameter('query', '%'.$this->switcher->toRus($search->query).'%');
//            $qb->setParameter('switcher', '%'.$this->switcher->toEng($search->query).'%');
        }

        return $this->paginator->fetchAllAssociative($qb);
    }
}
