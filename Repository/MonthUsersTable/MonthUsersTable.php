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

namespace BaksDev\Users\UsersTable\Repository\MonthUsersTable;

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Services\Switcher\SwitcherInterface;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Groups\Group\Entity\Group;
use BaksDev\Users\Groups\Group\Entity\Trans\GroupTrans;
use BaksDev\Users\Groups\Users\Entity\CheckUsers;
use BaksDev\Users\Groups\Users\Entity\Event\CheckUsersEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\UsersTable\Entity\UsersTableMonth;
use BaksDev\Users\UsersTable\Forms\DayUsersTableFilter\DayUsersTableFilterInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MonthUsersTable implements MonthUsersTableInterface
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

    /** Метод возвращает пагинатор UsersTableDay */
    public function fetchMonthUsersTableAssociative(SearchDTO $search, DayUsersTableFilterInterface $filter = null): PaginatorInterface
    {
        $qb = $this->connection->createQueryBuilder();

        $local = new Locale($this->translator->getLocale());

        $qb->addSelect('users_table_day.total AS table_total');
        $qb->addSelect('users_table_day.date_table AS table_date');
        $qb->from(UsersTableMonth::TABLE, 'users_table_day');

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
        
        $date = (new DateTimeImmutable())
            ->modify('first day of') // Устанавливает первый день текущего месяца
            ->setTime(0, 0)
            ->getTimestamp();

        if ($filter && $filter->getDate())
        {
            $date = $filter->getDate()->getTimestamp();
        }

        $qb->where('users_table_day.date_table = :date');
        $qb->setParameter('date', $date, ParameterType::INTEGER);

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
