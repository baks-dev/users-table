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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Table\Delete;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Users\UsersTable\Entity\Table\Event\UsersTableEvent;
use BaksDev\Users\UsersTable\Entity\Table\UsersTable;
use BaksDev\Users\UsersTable\Messenger\Table\UsersTableMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class UsersTableDeleteHandler
{
    public function __construct(
        #[Target('usersTableLogger')] private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private MessageDispatchInterface $messageDispatch
    ) {}

    public function handle(UsersTableDeleteDTO $command): string|UsersTable
    {
        /**
         *  Валидация UsersTableDeleteDTO
         */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            $uniqid = uniqid('', false);
            $errorsString = (string) $errors;
            $this->logger->error($uniqid.': '.$errorsString);
            return $uniqid;
        }


        /* Обязательно передается идентификатор события */
        if($command->getEvent() === null)
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Not found event id in class: %s',
                $command::class
            );
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }


        /** Получаем событие */
        $Event = $this->entityManager->getRepository(UsersTableEvent::class)
            ->find($command->getEvent());

        if($Event === null)
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Not found %s by id: %s',
                UsersTableEvent::class,
                $command->getEvent()
            );
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }


        /** Получаем корень */
        $Main = $this->entityManager->getRepository(UsersTable::class)
            ->findOneBy(['event' => $command->getEvent()]);

        if(empty($Main))
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Not found %s by event: %s',
                UsersTable::class,
                $command->getEvent()
            );
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }


        /** Применяем изменения к событию */
        $Event->setEntity($command);
        $this->entityManager->persist($Event);

        /* Удаляем корень агрегата */
        $this->entityManager->remove($Main);

        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new UsersTableMessage($Main->getId(), $Main->getEvent(), $command->getEvent()),
            transport: 'users-table'
        );

        return $Main;


    }
}
