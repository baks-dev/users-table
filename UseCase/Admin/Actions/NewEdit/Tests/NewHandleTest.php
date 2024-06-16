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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Tests;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Type\Actions\Const\UsersTableActionsWorkingConst;
use BaksDev\Users\UsersTable\Type\Actions\Id\UsersTableActionsUid;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Trans\UsersTableActionsTransDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\UsersTableActionsDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\UsersTableActionsHandler;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Working\Trans\UsersTableActionsWorkingTransDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Working\UsersTableActionsWorkingDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group users-table
 * @group users-table-action
 */
#[When(env: 'test')]
final class NewHandleTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $UsersTableActions = $em->getRepository(UsersTableActions::class)
            ->findOneBy(['id' => UsersTableActionsUid::TEST]);

        if($UsersTableActions)
        {
            $em->remove($UsersTableActions);

            $UsersTableActionsEvent = $em->getRepository(UsersTableActionsEvent::class)
                ->findBy(['main' => UsersTableActionsUid::TEST]);

            foreach($UsersTableActionsEvent as $remove)
            {
                $em->remove($remove);
            }

            $em->flush();
        }

        $em->clear();
        //$em->close();
    }


    public function testUseCase(): void
    {

        /** @var UsersTableActionsDTO $UsersTableActionsDTO */

        $UsersTableActionsDTO = new UsersTableActionsDTO();

        $CategoryProductUid = new CategoryProductUid();
        $UsersTableActionsDTO->setCategory($CategoryProductUid);
        self::assertSame($CategoryProductUid, $UsersTableActionsDTO->getCategory());


        //        /** @var UsersTableActionsProductDTO $UsersTableActionsProductDTO */
        //
        //
        //        $UsersTableActionsProductDTO = new UsersTableActionsProductDTO();
        //        $UsersTableActionsDTO->addProduct($UsersTableActionsProductDTO );
        //        self::assertTrue($UsersTableActionsDTO->getProduct()->contains($UsersTableActionsProductDTO));
        //
        //        $ProductUid = new ProductUid();
        //        $UsersTableActionsProductDTO->setProduct($ProductUid);
        //        self::assertSame($ProductUid, $UsersTableActionsProductDTO->getProduct());


        /** @var UsersTableActionsTransDTO $UsersTableActionsTransDTO */

        $UsersTableActionsTransDTO = $UsersTableActionsDTO->getTranslate();

        foreach($UsersTableActionsTransDTO as $actionTrans)
        {
            $actionTrans->setName('EhIAumuUOL');
            self::assertEquals('EhIAumuUOL', $actionTrans->getName());
        }


        /** @var UsersTableActionsWorkingDTO $UsersTableActionsWorkingDTO */


        $UsersTableActionsWorkingDTO = new UsersTableActionsWorkingDTO();
        $UsersTableActionsDTO->addWorking($UsersTableActionsWorkingDTO);
        self::assertTrue($UsersTableActionsDTO->getWorking()->contains($UsersTableActionsWorkingDTO));

        $UsersTableActionsWorkingConst = new UsersTableActionsWorkingConst();
        $UsersTableActionsWorkingDTO->setConst($UsersTableActionsWorkingConst);
        self::assertSame($UsersTableActionsWorkingConst, $UsersTableActionsWorkingDTO->getConst());

        $UsersTableActionsWorkingDTO->setSort(100);
        self::assertEquals(100, $UsersTableActionsWorkingDTO->getSort());

        $UsersTableActionsWorkingDTO->setPremium(50);
        self::assertEquals(50, $UsersTableActionsWorkingDTO->getPremium());

        $UsersTableActionsWorkingDTO->setNorm(300);
        self::assertEquals(300, $UsersTableActionsWorkingDTO->getNorm());

        $UsersTableActionsWorkingDTO->setCoefficient(1.1);
        self::assertEquals(1.1, $UsersTableActionsWorkingDTO->getCoefficient());


        /** @var UsersTableActionsWorkingTransDTO $UsersTableActionsWorkingTransDTO */


        $UsersTableActionsWorkingTransDTO = $UsersTableActionsWorkingDTO->getTranslate();

        foreach($UsersTableActionsWorkingTransDTO as $workingTrans)
        {
            $workingTrans->setName('kRFqgNIlqA');
            self::assertEquals('kRFqgNIlqA', $workingTrans->getName());
        }


        /** @var UsersTableActionsHandler $UsersTableActionsHandler */

        //self::bootKernel();

        $UsersTableActionsHandler = self::getContainer()->get(UsersTableActionsHandler::class);
        $handle = $UsersTableActionsHandler->handle($UsersTableActionsDTO, new UserProfileUid());

        self::assertTrue(($handle instanceof UsersTableActions), $handle.': Ошибка UsersTableActions');

    }

    /** @depends testUseCase */
    public function testComplete(): void
    {

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $UsersTableActions = $em->getRepository(UsersTableActions::class)
            ->findOneBy(['id' => UsersTableActionsUid::TEST, 'profile' => UserProfileUid::TEST]);
        self::assertNotNull($UsersTableActions);

        $em->clear();
        //$em->close();

    }


}
