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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Actions\Delete\Tests;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Type\Actions\Const\UsersTableActionsWorkingConst;
use BaksDev\Users\UsersTable\Type\Actions\Id\UsersTableActionsUid;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\Delete\UsersTableActionsDeleteDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\Delete\UsersTableActionsDeleteHandler;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Products\UsersTableActionsProductDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Tests\UserTableEditHandleTest;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Tests\UserTableNewHandleTest;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Trans\UsersTableActionsTransDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\UsersTableActionsDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Working\Trans\UsersTableActionsWorkingTransDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Working\UsersTableActionsWorkingDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Table\Delete\Tests\DeleteHandleTest;
use BaksDev\Wildberries\Products\Controller\Admin\Settings\Tests\DeleteControllerTest;
use BaksDev\Wildberries\Products\Entity\Barcode\Event\WbBarcodeEvent;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('users-table')]
#[When(env: 'test')]
final class UserTableDeleteHandleTest extends KernelTestCase
{
    #[DependsOnClass(UserTableNewHandleTest::class)]
    #[DependsOnClass(UserTableEditHandleTest::class)]
    #[DependsOnClass(DeleteControllerTest::class)]
    #[DependsOnClass(DeleteHandleTest::class)]
    public function testUseCase(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var ORMQueryBuilder $ORMQueryBuilder */
        $ORMQueryBuilder = $container->get(ORMQueryBuilder::class);
        $qb = $ORMQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(UsersTableActions::class, 'main')
            ->where('main.id = :main')
            ->setParameter('main', UsersTableActionsUid::TEST, UsersTableActionsUid::TYPE);

        $qb
            ->select('event')
            ->leftJoin(
                UsersTableActionsEvent::class,
                'event',
                'WITH',
                'event.id = main.event',
            );

        /** @var WbBarcodeEvent $UsersTableActionsEvent */
        $UsersTableActionsEvent = $qb->getQuery()->getOneOrNullResult();


        /** @var UsersTableActionsDTO $UsersTableActionsDTO */

        $UsersTableActionsDTO = new UsersTableActionsDTO();
        $UsersTableActionsEvent->getDto($UsersTableActionsDTO);
        self::assertNotEquals(CategoryProductUid::TEST, (string) $UsersTableActionsDTO->getCategory());


        //        /** @var UsersTableActionsProductDTO $UsersTableActionsProductDTO */
        //
        //        $UsersTableActionsProductDTO = $UsersTableActionsDTO->getProduct()->current();
        //        self::assertNotEquals(ProductUid::TEST, (string) $UsersTableActionsProductDTO->getProduct());


        /** @var UsersTableActionsTransDTO $UsersTableActionsTransDTO */

        $UsersTableActionsTransDTO = $UsersTableActionsDTO->getTranslate();

        foreach($UsersTableActionsTransDTO as $actionTrans)
        {
            self::assertEquals('AqAkerfsLc', $actionTrans->getName());
        }


        /** @var UsersTableActionsWorkingDTO $UsersTableActionsWorkingDTO */

        $UsersTableActionsWorkingDTO = $UsersTableActionsDTO->getWorking()->current();
        self::assertEquals(UsersTableActionsWorkingConst::TEST, (string) $UsersTableActionsWorkingDTO->getConst());
        self::assertEquals(200, $UsersTableActionsWorkingDTO->getSort());
        self::assertEquals(100, $UsersTableActionsWorkingDTO->getPremium());
        self::assertEquals(400, $UsersTableActionsWorkingDTO->getNorm());
        self::assertEquals(2.2, $UsersTableActionsWorkingDTO->getCoefficient());


        /** @var UsersTableActionsWorkingTransDTO $UsersTableActionsWorkingTransDTO */

        $UsersTableActionsWorkingTransDTO = $UsersTableActionsWorkingDTO->getTranslate();

        foreach($UsersTableActionsWorkingTransDTO as $workingTrans)
        {
            self::assertEquals('zkJQyYdqGl', $workingTrans->getName());
        }


        /** DELETE */

        $UsersTableActionsDeleteDTO = new UsersTableActionsDeleteDTO();
        $UsersTableActionsEvent->getDto($UsersTableActionsDeleteDTO);

        /** @var UsersTableActionsDeleteHandler $UsersTableActionsDeleteHandler */
        $UsersTableActionsDeleteHandler = $container->get(UsersTableActionsDeleteHandler::class);
        $handle = $UsersTableActionsDeleteHandler->handle($UsersTableActionsDeleteDTO, new UserProfileUid());
        self::assertTrue(($handle instanceof UsersTableActions), $handle.': Ошибка UsersTableActions');

    }

    #[Depends('testUseCase')]
    public function testComplete(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $UsersTableActions = $em->getRepository(UsersTableActions::class)
            ->findOneBy(['id' => UsersTableActionsUid::TEST]);

        if($UsersTableActions)
        {
            $em->remove($UsersTableActions);
        }

        $UsersTableActionsEvent = $em->getRepository(UsersTableActionsEvent::class)
            ->findBy(['main' => UsersTableActionsUid::TEST]);

        foreach($UsersTableActionsEvent as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();

        self::assertNull($UsersTableActions);

        $em->clear();
        //$em->close();

    }

}
