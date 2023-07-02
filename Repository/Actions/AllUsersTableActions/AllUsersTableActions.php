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

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Services\Switcher\SwitcherInterface;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AllUsersTableActions implements AllUsersTableActionsInterface
{
    private Connection $connection;

    private PaginatorInterface $paginator;

    private SwitcherInterface $switcher;

    private TranslatorInterface $translator;

    public function __construct(
        Connection          $connection,
        PaginatorInterface  $paginator,
        SwitcherInterface   $switcher,
        TranslatorInterface $translator,
    )
    {
        $this->connection = $connection;
        $this->paginator = $paginator;
        $this->switcher = $switcher;
        $this->translator = $translator;
    }

    /** Метод возвращает пагинатор AllUsersTableActions */
    public function fetchAllUsersTableActionsAssociative(SearchDTO $search): PaginatorInterface
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*');
        $qb->from(UsersTableActions::TABLE, 'actions');

        /* Поиск */
        if ($search->query) {
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
