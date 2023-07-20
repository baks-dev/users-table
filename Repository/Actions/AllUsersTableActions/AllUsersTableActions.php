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
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Entity\Cover\ProductCategoryCover;
use BaksDev\Products\Category\Entity\ProductCategory;
use BaksDev\Products\Category\Entity\Trans\ProductCategoryTrans;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\Trans\UsersTableActionsTrans;
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
        Connection $connection,
        PaginatorInterface $paginator,
        SwitcherInterface $switcher,
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
        $locale = new Locale($this->translator->getLocale());

        $qb->select('actions.id');
        $qb->addSelect('actions.event');

        $qb->from(UsersTableActions::TABLE, 'actions');


        $qb->leftJoin(
            'actions',
            UsersTableActionsEvent::TABLE,
            'event',
            'event.id = actions.event'
        );


        $qb->leftJoin(
            'event',
            ProductCategory::TABLE,
            'category',
            'category.id = event.category'
        );

        $qb->addSelect('trans.name AS category_name');

        $qb->leftJoin(
            'category',
            ProductCategoryTrans::TABLE,
            'trans',
            'trans.event = category.event AND trans.local = :local'
        );

        $qb->setParameter('local', $locale, Locale::TYPE);


        // Обложка

        $qb->addSelect("
			CASE
			   WHEN category_cover.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductCategoryCover::TABLE."' , '/', category_cover.dir, '/', category_cover.name, '.')
			   ELSE NULL
			END AS category_cover_name
		"
        );

        $qb->addSelect('category_cover.ext AS category_cover_ext');
        $qb->addSelect('category_cover.cdn AS category_cover_cdn');


        $qb->leftJoin(
            'category',
            ProductCategoryCover::TABLE,
            'category_cover',
            'category_cover.event = category.event'
        );




        /* Поиск */
        if($search->query)
        {

            $search->query = mb_strtolower($search->query);
            $searcher = $this->connection->createQueryBuilder();

            if($search->isUid())
            {
                $searcher->orWhere('actions.id = :query');
                $searcher->orWhere('actions.event = :query');

                $qb->setParameter('query', $search->query);
            }
            else
            {
                $searcher->orWhere('LOWER(trans.name) LIKE :query');
                $searcher->orWhere('LOWER(trans.name) LIKE :switcher');

                $qb->setParameter('query', '%'.$this->switcher->toRus($search->query).'%');
                $qb->setParameter('switcher', '%'.$this->switcher->toEng($search->query).'%');
            }

            $qb->andWhere('('.$searcher->getQueryPart('where').')');
        }

        return $this->paginator->fetchAllAssociative($qb);
    }
}
