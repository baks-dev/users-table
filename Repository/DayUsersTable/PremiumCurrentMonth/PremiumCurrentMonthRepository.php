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

namespace BaksDev\Users\UsersTable\Repository\DayUsersTable\PremiumCurrentMonth;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\UsersTableDay;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingUid;
use DateTimeImmutable;
use Doctrine\DBAL\ParameterType;

final class PremiumCurrentMonthRepository implements PremiumCurrentMonthInterface
{

    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(DBALQueryBuilder $DBALQueryBuilder)
    {

        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод возвращает сумму премии за текущий месяц за указанное действие
     */
    public function getSumPremium(UserProfileUid $profile, UsersTableActionsWorkingUid $working): int|float
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->select('SUM(table_day.premium)');

        $qb->from(UsersTableDay::TABLE, 'table_day');
        $qb->where('table_day.profile = :profile');
        $qb->setParameter('profile', $profile, UserProfileUid::TYPE);

        $qb->andWhere('table_day.working = :working');
        $qb->setParameter('working', $working, UsersTableActionsWorkingUid::TYPE);

        // Устанавливает первый день текущего месяца
        $current = (new DateTimeImmutable())->modify('first day of')
        ->setTime(0, 0)->getTimestamp();

        $qb->andWhere('table_day.date_table > :current');
        $qb->setParameter('current', $current, ParameterType::INTEGER);

        return $qb->fetchOne() / 100;
    }
}