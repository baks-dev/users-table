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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit;

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Messenger\Actions\UsersTableActionsMessage;
use BaksDev\Users\UsersTable\Type\Actions\Id\UsersTableActionsUid;

final  class UsersTableActionsHandler extends AbstractHandler
{
    /** @see UsersTableActions */
    public function handle(UsersTableActionsDTO $command): string|UsersTableActions
    {
        $UsersTableActions = new UsersTableActions();

        /** Если передан идентификатор Application - присваиваем через конструктор корню */
        if(true === ($command->getApplication() instanceof UsersTableActionsUid))
        {
            $UsersTableActions = new UsersTableActions($command->getApplication());
        }

        $this
            ->setCommand($command)
            ->preEventPersistOrUpdate($UsersTableActions, UsersTableActionsEvent::class);

        /* Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new UsersTableActionsMessage($this->main->getId(), $this->main->getEvent(), $command->getEvent()),
            transport: 'users-table',
        );


        return $this->main;


        /**
         *  Валидация UsersTableActionsDTO
         */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);

            return $uniqid;
        }

        if($command->getEvent())
        {
            $EventRepo = $this->entityManager
                ->getRepository(UsersTableActionsEvent::class)
                ->find($command->getEvent());

            if($EventRepo === null)
            {
                $uniqid = uniqid('', false);

                $this->logger->error(
                    $uniqid.': Событие UsersTableActionsEvent не найдено',
                    [
                        self::class.':'.__LINE__,
                        $command->getEvent(),
                    ],
                );

                return $uniqid;
            }

            $EventRepo->setEntity($command);
            $EventRepo->setEntityManager($this->entityManager);
            $Event = $EventRepo->cloneEntity();
        }
        else
        {
            $Event = new UsersTableActionsEvent();
            $Event->setEntity($command);
            $this->entityManager->persist($Event);
        }

        //        $this->entityManager->clear();
        //        $this->entityManager->persist($Event);


        /** @var UsersTableActions $Main */
        if($Event->getMain())
        {
            $findOneBy['event'] = $command->getEvent();

            if(false !== $profile)
            {
                $findOneBy['profile'] = $profile;
            }

            $Main = $this->entityManager
                ->getRepository(UsersTableActions::class)
                ->findOneBy($findOneBy);

            if(empty($Main))
            {
                $uniqid = uniqid('', false);

                $this->logger->error(
                    $uniqid.': Корень агрегата UsersTableActions с указанным событием и профилем не найден',
                    [
                        self::class.':'.__LINE__,
                        $command->getEvent(),
                        $profile,
                    ],
                );

                return $uniqid;
            }

        }
        else
        {
            $Main = new UsersTableActions($profile);
            $this->entityManager->persist($Main);
            $Event->setMain($Main);
        }

        /* присваиваем событие корню */
        $Main->setEvent($Event);


        /**
         * Валидация Event
         */

        $errors = $this->validator->validate($Event);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);

            return $uniqid;
        }


        /**
         * Валидация Main
         */
        $errors = $this->validator->validate($Main);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);
            return $uniqid;
        }


        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new UsersTableActionsMessage($Main->getId(), $Main->getEvent(), $command->getEvent()),
            transport: 'users-table',
        );


        return $Main;
    }
}
