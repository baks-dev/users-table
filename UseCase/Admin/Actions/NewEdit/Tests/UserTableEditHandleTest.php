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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Tests;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Type\Actions\Const\UsersTableActionsWorkingConst;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Products\UsersTableActionsProductDTO;
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
 *
 * @depends BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Tests\NewHandleTest::class
 * @depends BaksDev\Users\UsersTable\Controller\Admin\Actions\Tests\DeleteControllerTest::class
 * @depends BaksDev\Users\UsersTable\UseCase\Admin\Table\Delete\Tests\DeleteHandleTest::class
 *
 * @see     UserNewUserProfileHandleTest
 * @see     DeleteAdminControllerTest
 */
#[When(env: 'test')]
final class UserTableEditHandleTest extends KernelTestCase
{
    public function testUseCase(): void
    {

        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $UsersTableActionsEvent = $em->getRepository(UsersTableActionsEvent::class)->find(UsersTableActionsEventUid::TEST);


        /** @var UsersTableActionsDTO $UsersTableActionsDTO */


        $UsersTableActionsDTO = new UsersTableActionsDTO();
        $UsersTableActionsEvent->getDto($UsersTableActionsDTO);

        self::assertEquals(CategoryProductUid::TEST, (string) $UsersTableActionsDTO->getCategory());
        $UsersTableActionsDTO->setCategory(clone $UsersTableActionsDTO->getCategory());


        //        /** @var UsersTableActionsProductDTO $UsersTableActionsProductDTO */
        //
        //
        //        $UsersTableActionsProductDTO = $UsersTableActionsDTO->getProduct()->current();
        //
        //        self::assertEquals(ProductUid::TEST, (string) $UsersTableActionsProductDTO->getProduct());
        //        $UsersTableActionsProductDTO->setProduct(clone $UsersTableActionsProductDTO->getProduct());


        /** @var UsersTableActionsTransDTO $UsersTableActionsTransDTO */


        $UsersTableActionsTransDTO = $UsersTableActionsDTO->getTranslate();

        foreach($UsersTableActionsTransDTO as $actionTrans)
        {
            self::assertEquals('EhIAumuUOL', $actionTrans->getName());
            $actionTrans->setName('AqAkerfsLc');
        }


        /** @var UsersTableActionsWorkingDTO $UsersTableActionsWorkingDTO */


        $UsersTableActionsWorkingDTO = $UsersTableActionsDTO->getWorking()->current();

        self::assertEquals(UsersTableActionsWorkingConst::TEST, (string) $UsersTableActionsWorkingDTO->getConst());
        $UsersTableActionsWorkingDTO->setConst(clone $UsersTableActionsWorkingDTO->getConst());

        self::assertEquals(100, $UsersTableActionsWorkingDTO->getSort());
        $UsersTableActionsWorkingDTO->setSort(200);

        self::assertEquals(50, $UsersTableActionsWorkingDTO->getPremium());
        $UsersTableActionsWorkingDTO->setPremium(100);

        self::assertEquals(300, $UsersTableActionsWorkingDTO->getNorm());
        $UsersTableActionsWorkingDTO->setNorm(400);

        self::assertEquals(1.1, $UsersTableActionsWorkingDTO->getCoefficient());
        $UsersTableActionsWorkingDTO->setCoefficient(2.2);


        /** @var UsersTableActionsWorkingTransDTO $UsersTableActionsWorkingTransDTO */


        $UsersTableActionsWorkingTransDTO = $UsersTableActionsWorkingDTO->getTranslate();

        foreach($UsersTableActionsWorkingTransDTO as $workingTrans)
        {
            self::assertEquals('kRFqgNIlqA', $workingTrans->getName());
            $workingTrans->setName('zkJQyYdqGl');
        }


        /** @var UsersTableActionsHandler $UsersTableActionsHandler */

        $UsersTableActionsHandler = self::getContainer()->get(UsersTableActionsHandler::class);
        $handle = $UsersTableActionsHandler->handle($UsersTableActionsDTO, new UserProfileUid());
        self::assertTrue(($handle instanceof UsersTableActions), $handle.': Ошибка UsersTableActions');

        $em->clear();
        //$em->close();
    }

    public function testComplete(): void
    {
        self::assertTrue(true);
    }

}
