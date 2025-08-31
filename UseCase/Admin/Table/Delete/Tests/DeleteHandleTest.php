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
 */

declare(strict_types=1);

namespace BaksDev\Users\UsersTable\UseCase\Admin\Table\Delete\Tests;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Tests\UserNewUserProfileHandleTest;
use BaksDev\Users\UsersTable\Controller\Admin\Table\Tests\DeleteControllerTest;
use BaksDev\Users\UsersTable\Entity\Table\Event\UsersTableEvent;
use BaksDev\Users\UsersTable\Entity\Table\UsersTable;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingUid;
use BaksDev\Users\UsersTable\Type\Table\Id\UsersTableUid;
use BaksDev\Users\UsersTable\UseCase\Admin\Table\Delete\UsersTableDeleteDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Table\Delete\UsersTableDeleteHandler;
use BaksDev\Users\UsersTable\UseCase\Admin\Table\NewEdit\Tests\NewHandleTest;
use BaksDev\Users\UsersTable\UseCase\Admin\Table\NewEdit\UsersTableDTO;
use BaksDev\Wildberries\Products\Entity\Barcode\Event\WbBarcodeEvent;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('users-table')]
#[When(env: 'test')]
final class DeleteHandleTest extends KernelTestCase
{
    #[DependsOnClass(NewHandleTest::class)]
    #[DependsOnClass(DeleteControllerTest::class)]
    public function testUseCase(): void
    {
        //self::bootKernel();
        $container = self::getContainer();

        /** @var ORMQueryBuilder $ORMQueryBuilder */
        $ORMQueryBuilder = $container->get(ORMQueryBuilder::class);
        $qb = $ORMQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(UsersTable::class, 'main')
            ->where('main.id = :main')
            ->setParameter('main', UsersTableUid::TEST, UsersTableUid::TYPE);

        $qb
            ->select('event')
            ->leftJoin(
                UsersTableEvent::class,
                'event',
                'WITH',
                'event.id = main.event'
            );

        /** @var WbBarcodeEvent $UsersTableEvent */
        $UsersTableEvent = $qb->getQuery()->getOneOrNullResult();
        self::assertNotNull($UsersTableEvent);


        /** @var UsersTableDTO $UsersTableDTO */

        $UsersTableDTO = new UsersTableDTO(new UserProfileUid());
        $UsersTableEvent->getDto($UsersTableDTO);

        self::assertEquals(UserProfileUid::TEST, (string) $UsersTableDTO->getAuthority());
        self::assertEquals(UserProfileUid::TEST, (string) $UsersTableDTO->getProfile());
        self::assertEquals(UsersTableActionsWorkingUid::TEST, (string) $UsersTableDTO->getWorking());
        self::assertEquals(new DateTimeImmutable('2022-01-01 00:00:00'), $UsersTableDTO->getDate());
        self::assertEquals(100, $UsersTableDTO->getQuantity());


        /** DELETE */

        $UsersTableDeleteDTO = new UsersTableDeleteDTO();
        $UsersTableEvent->getDto($UsersTableDeleteDTO);

        /** @var UsersTableDeleteHandler $UsersTableDeleteHandler */
        $UsersTableDeleteHandler = $container->get(UsersTableDeleteHandler::class);
        $handle = $UsersTableDeleteHandler->handle($UsersTableDeleteDTO);
        self::assertTrue(($handle instanceof UsersTable), $handle.': Ошибка UsersTable');

    }

    #[Depends('testUseCase')]
    public function testComplete(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $UsersTable = $em->getRepository(UsersTable::class)
            ->find(UsersTableUid::TEST);

        if($UsersTable)
        {
            $em->remove($UsersTable);
        }

        $UsersTableEvent = $em->getRepository(UsersTableEvent::class)
            ->findBy(['main' => UsersTableUid::TEST]);

        foreach($UsersTableEvent as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();

        self::assertNull($UsersTable);

        $em->clear();
        //$em->close();
    }

}
