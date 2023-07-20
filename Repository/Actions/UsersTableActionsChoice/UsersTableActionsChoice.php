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

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Entity\ProductCategory;
use BaksDev\Products\Category\Entity\Trans\ProductCategoryTrans;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\Trans\UsersTableActionsTrans;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UsersTableActionsChoice implements UsersTableActionsChoiceInterface
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Метод возвращает коллекцию идентификаторов активных событий UsersTableActions
     */
    public function getCollection(): ?array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $locale = new Locale($this->translator->getLocale());

        $select = sprintf('new %s(actions.event, trans.name)', UsersTableActionsEventUid::class);

        $qb->addSelect($select);

        $qb->from(UsersTableActions::class, 'actions');

        $qb->leftJoin(

            UsersTableActionsEvent::class,
            'event',
            'WITH',
            'event.id = actions.event'
        );

        $qb->leftJoin(
            ProductCategory::class,
            'category',
            'WITH',
            'category.id = event.category'
        );
        

        $qb->leftJoin(
            ProductCategoryTrans::class,
            'trans',
            'WITH',
            'trans.event = category.event AND trans.local = :local'
        );

        $qb->setParameter('local', $locale, Locale::TYPE);


        /*$qb->leftJoin(
            UsersTableActionsTrans::class,
            'trans',
            'WITH',
            'trans.event = actions.event AND trans.local = :local'
        );*/


        // Кешируем результат ORM
        $cacheQueries = new FilesystemAdapter('UsersTable');

        $query = $this->entityManager->createQuery($qb->getDQL());
        $query->setQueryCache($cacheQueries);
        $query->setResultCache($cacheQueries);
        $query->enableResultCache();
        $query->setLifetime(60 * 60 * 24);
        $query->setParameters($qb->getParameters());

        return $query->getResult();
    }
}