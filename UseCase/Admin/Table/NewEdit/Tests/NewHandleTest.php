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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Table\NewEdit\Tests;

use BaksDev\Orders\Order\UseCase\Admin\Edit\Tests\OrderNewTest;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Entity\Table\Event\UsersTableEvent;
use BaksDev\Users\UsersTable\Entity\Table\UsersTable;
use BaksDev\Users\UsersTable\Type\Actions\Const\UsersTableActionsWorkingConst;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use BaksDev\Users\UsersTable\Type\Actions\Id\UsersTableActionsUid;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingUid;
use BaksDev\Users\UsersTable\Type\Table\Id\UsersTableUid;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Products\UsersTableActionsProductDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Trans\UsersTableActionsTransDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\UsersTableActionsDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\UsersTableActionsHandler;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Working\Trans\UsersTableActionsWorkingTransDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Working\UsersTableActionsWorkingDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Table\NewEdit\UsersTableDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Table\NewEdit\UsersTableHandler;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @group users-table
 * @group users-table-table
 */
#[When(env: 'test')]
final class NewHandleTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $UsersTable = $em->getRepository(UsersTable::class)->find(UsersTableUid::TEST);

        if($UsersTable)
        {
            $em->remove($UsersTable);
        }


        $UsersTableActionsEvent = $em->getRepository(UsersTableEvent::class)
            ->findBy(['main' => UsersTableUid::TEST]);

        foreach($UsersTableActionsEvent as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();
        $em->clear();
        //$em->close();
    }


    public function testUseCase(): void
    {

        /** @var UsersTableDTO $UsersTableDTO */

        $Authority = new UserProfileUid();
        $UsersTableDTO = new UsersTableDTO($Authority);
        self::assertSame($Authority, $UsersTableDTO->getAuthority());

        $UserProfileUid = new UserProfileUid();
        $UsersTableDTO->setProfile($UserProfileUid);
        self::assertSame($UserProfileUid, $UsersTableDTO->getProfile());


        $UsersTableActionsWorkingUid = new UsersTableActionsWorkingUid();
        $UsersTableDTO->setWorking($UsersTableActionsWorkingUid);
        self::assertSame($UsersTableActionsWorkingUid, $UsersTableDTO->getWorking());

        $DateTimeImmutable = new DateTimeImmutable('2022-01-01 00:00:00');
        $UsersTableDTO->setDate($DateTimeImmutable);
        self::assertSame($DateTimeImmutable, $UsersTableDTO->getDate());

        /** Вспомогательные свойства */

        $CategoryProductUid = new CategoryProductUid();
        $UsersTableDTO->category = $CategoryProductUid;
        self::assertSame($CategoryProductUid, $UsersTableDTO->category);

        $UsersTableActionsEventUid = new UsersTableActionsEventUid();
        $UsersTableDTO->setAction($UsersTableActionsEventUid);
        self::assertSame($UsersTableActionsEventUid, $UsersTableDTO->getAction());


        $UsersTableDTO->setQuantity(100);
        self::assertEquals(100, $UsersTableDTO->getQuantity());


        //self::bootKernel();


        /** @var UsersTableHandler $UsersTableHandler */
        $UsersTableHandler = self::getContainer()->get(UsersTableHandler::class);
        $handle = $UsersTableHandler->handle($UsersTableDTO);

        self::assertTrue(($handle instanceof UsersTable), $handle.': Ошибка UsersTable');

    }

    /** @depends testUseCase */
    public function testComplete(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $UsersTable = $em->getRepository(UsersTable::class)->find(UsersTableUid::TEST);
        self::assertNotNull($UsersTable);

        $em->clear();
        //$em->close();

    }


}
