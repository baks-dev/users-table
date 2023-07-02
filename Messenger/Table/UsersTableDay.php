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

namespace BaksDev\Users\UsersTable\Messenger\Table;


use BaksDev\Users\UsersTable\Entity\Table\Event\UsersTableEvent;
use BaksDev\Users\UsersTable\UseCase\Admin\Day\UsersTableDayDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Day\UserTableDayHandler;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 100)]
final class UsersTableDay
{
    private EntityManagerInterface $entityManager;

    private LoggerInterface $logger;

    private UserTableDayHandler $tableDayHandler;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $messageDispatchLogger,
        UserTableDayHandler $tableDayHandler
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $messageDispatchLogger;
        $this->tableDayHandler = $tableDayHandler;
    }

    /**
     * Дневной табель
     */
    public function __invoke(UsersTableMessage $message): void
    {
        return;
        
        $this->logger->info('MessageHandler', ['handler' => self::class]);

        /** Получаем дневной табель пользователя */
        $UsersTableEvent = $this->entityManager->getRepository(UsersTableEvent::class)->find($message->getEvent());

        if ($UsersTableEvent)
        {
            $UserTableDayDTO = new UsersTableDayDTO();
            $UserTableDayDTO->setProfile($UsersTableEvent->getProfile());
            $UserTableDayDTO->setDate($UsersTableEvent->getDate());
            $UserTableDayDTO->setTotal($UsersTableEvent->getTotal());

            $UserTableDayHandler = $this->tableDayHandler->handle($UserTableDayDTO);

            if (!$UserTableDayHandler instanceof \BaksDev\Users\UsersTable\Entity\UsersTableDay)
            {
                throw new DomainException(sprintf('%s: Ошибка при обновлении дневного табеля', $UserTableDayHandler));
            }
        }

        $this->logger->info('MessageHandlerSuccess', ['handler' => self::class]);
    }
}
