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

namespace BaksDev\Users\UsersTable\Repository\Actions\ActionByProduct;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Users\UsersTable\Entity\Actions\Products\UsersTableActionsProduct;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;

final class ActionByProduct implements ActionByProductInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;

    public function __construct(ORMQueryBuilder $ORMQueryBuilder)
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }

    /**
     * Метод возвращает идентификатор события UsersTableActionsEventUid привязанного продукта
     */
    public function findUsersTableActionsByProduct(ProductUid $product) : ?UsersTableActionsEventUid
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $select = sprintf('new %s(action.event)', UsersTableActionsEventUid::class);

        $qb->select($select);
        $qb->from(UsersTableActionsProduct::class, 'product');
        $qb->where('product.product = :product');
        $qb->setParameter('product', $product, ProductUid::TYPE);

        $qb->join(UsersTableActions::class,
            'action',
            'WITH',
            'action.event = product.event'
        );

        return $qb->enableCache('UsersTable', 86400)->getOneOrNullResult();
    }
}