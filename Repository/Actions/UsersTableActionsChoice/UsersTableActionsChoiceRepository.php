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

namespace BaksDev\Users\UsersTable\Repository\Actions\UsersTableActionsChoice;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\Profile\UsersTableActionsProfile;
use BaksDev\Users\UsersTable\Entity\Actions\Trans\UsersTableActionsTrans;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use Generator;

final class UsersTableActionsChoiceRepository implements UsersTableActionsChoiceInterface
{
    private UserProfileUid|false $profile = false;

    private CategoryProductUid|false $category = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage,
    ) {}

    public function forProfile(UserProfile|UserProfileUid|string $profile): self
    {
        if(empty($profile))
        {
            $this->profile = false;
            return $this;
        }

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

    public function forCategory(CategoryProduct|CategoryProductUid|string|null $category): self
    {
        if(empty($category))
        {
            $this->category = false;

            return $this;
        }

        if(is_string($category))
        {
            $category = new CategoryProductUid($category);
        }

        if($category instanceof CategoryProduct)
        {
            $category = $category->getId();
        }

        $this->category = $category;

        return $this;
    }


    /**
     * Метод возвращает коллекцию идентификаторов активных процессов производства
     */
    public function getCollection(): Generator|false
    {

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->from(UsersTableActions::class, 'actions')
            //->where('(actions.profile = :profile OR actions.profile IS NULL)')
            //            ->setParameter(
            //                'profile',
            //                $this->profile ?: $this->UserProfileTokenStorage->getProfile(),
            //                UserProfileUid::TYPE
            //            )
        ;


        $dbal
            ->leftJoin(
                'actions',
                UsersTableActionsProfile::class,
                'event_profile',
                'event_profile.event = actions.event',
            );


        $dbal
            ->where('(event_profile.value = :profile OR event_profile.value IS NULL)')
            ->setParameter(
                'profile',
                $this->profile ?: $this->UserProfileTokenStorage->getProfile(),
                UserProfileUid::TYPE,
            );


        $dbal

            ->leftJoin(
                'actions',
                UsersTableActionsEvent::class,
                'event',
                'event.id = actions.event',
            );


        if($this->category)
        {
            $dbal
                ->setParameter(
                    key: 'category',
                    value: $this->category,
                    type: CategoryProductUid::TYPE,
                );


            $dbal->andWhere('event.category = :category');
        }
        else
        {
            $dbal->andWhere('event.category IS NULL');

        }


        $dbal->leftJoin(
            'actions',
            UsersTableActionsTrans::class,
            'trans',
            'trans.event = actions.event AND trans.local = :local',
        );


        $dbal
            ->select('actions.event AS value')
            ->addSelect('trans.name AS attr');

        return $dbal
            ->enableCache('users-table', 86400)
            ->fetchAllHydrate(UsersTableActionsEventUid::class);

    }
}
