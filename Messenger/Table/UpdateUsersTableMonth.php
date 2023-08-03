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


use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\UsersTable\Entity\Actions\Working\UsersTableActionsWorking;
use BaksDev\Users\UsersTable\Entity\Table\Event\UsersTableEvent;
use BaksDev\Users\UsersTable\Entity\UsersTableDay;
use BaksDev\Users\UsersTable\Entity\UsersTableMonth;
use BaksDev\Users\UsersTable\Repository\DayUsersTable\PremiumCurrentMonth\PremiumCurrentMonthRepositoryInterface;
use BaksDev\Users\UsersTable\UseCase\Admin\Month\UsersTableMonthDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Month\UserTableMonthHandler;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)] // В начале выполняется дневной табель
final class UpdateUsersTableMonth
{
    private EntityManagerInterface $entityManager;

    private LoggerInterface $logger;

    private UserTableMonthHandler $tableMonthHandler;
    private PremiumCurrentMonthRepositoryInterface $premiumCurrentMonthRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $messageDispatchLogger,
        UserTableMonthHandler $tableMonthHandler,
        PremiumCurrentMonthRepositoryInterface $premiumCurrentMonthRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $messageDispatchLogger;
        $this->tableMonthHandler = $tableMonthHandler;
        $this->premiumCurrentMonthRepository = $premiumCurrentMonthRepository;
    }

    /**
     * Ежемесячный табель
     */
    public function __invoke(UsersTableMessage $message): void
    {
        $this->logger->info('MessageHandler', ['handler' => self::class]);
        $this->logger->info('MessageData', [$message->getId(), $message->getEvent()]);


        /** Получаем добавленный табель пользователя */
        $UsersTableEvent = $this->entityManager->getRepository(UsersTableEvent::class)->find($message->getEvent());

        if($UsersTableEvent)
        {
            $UsersTableMonthDTO = new UsersTableMonthDTO();
            $UsersTableMonthDTO->setProfile($UsersTableEvent->getProfile());
            $UsersTableMonthDTO->setWorking($UsersTableEvent->getWorking());
            $UsersTableMonthDTO->setDate($UsersTableEvent->getDate());


            /** Получаем Ежемесячный табель сотрудника */

            $UsersTableMonth = $this->entityManager->getRepository(UsersTableMonth::class)->findOneBy([
                'profile' => $UsersTableMonthDTO->getProfile(),
                'working' => $UsersTableMonthDTO->getWorking(),
                'date' => $UsersTableMonthDTO->getDate()
            ]);

            $UsersTableMonth?->getDto($UsersTableMonthDTO);


            /** Получаем действие и его настройку */
            $UsersTableActionWorking = $this->entityManager->getRepository(UsersTableActionsWorking::class)->find(
                $UsersTableEvent->getWorking()
            );


            if(!$UsersTableActionWorking)
            {
                return;
            }


            //$UsersTableDayDTO = new UsersTableDayDTO();

            /** Получаем дневной табель сотрудника */
            $UsersTableDateDay = $UsersTableEvent->getDate()->setTime(0, 0)->getTimestamp();
            $UsersTableDay = $this->entityManager->getRepository(UsersTableDay::class)->findOneBy([
                'profile' => $UsersTableMonthDTO->getProfile(),
                'working' => $UsersTableMonthDTO->getWorking(),
                'date' => $UsersTableDateDay
            ]);


            if(empty($UsersTableEvent->getQuantity()))
            {
                return;
            }

            /* Добавляем количество выполненной работы */
            $UsersTableMonthDTO->addTotal($UsersTableEvent->getQuantity());

            /* Делаем расчет стоимости действий пользователя */
            $moneyCoefficient = ($UsersTableMonthDTO->getTotal() * $UsersTableActionWorking->getCoefficient());

            if($UsersTableEvent->getQuantity() > 0)
            {
                $UsersTableMonthDTO->addMoney(new Money($moneyCoefficient));
            }

            if($UsersTableEvent->getQuantity() < 0)
            {
                $UsersTableMonthDTO->subMoney(new Money(abs($moneyCoefficient)));
            }

            if($UsersTableEvent->getQuantity() === 0)
            {
                $UsersTableMonthDTO->setMoney(new Money(0));
            }

            /**
             *
             * Если была Выполнена дневная норма - делаем перерасчет премии
             */
            if($UsersTableDay && $UsersTableDay->getTotal() > $UsersTableActionWorking->getNorm())
            {
                $moneyPremium = $this->premiumCurrentMonthRepository
                    ->getSumPremium($UsersTableMonthDTO->getProfile(), $UsersTableMonthDTO->getWorking());

                $UsersTableMonthDTO->setPremium(new Money($moneyPremium));
            }

            $UserTableDayHandler = $this->tableMonthHandler->handle($UsersTableMonthDTO);

            if(!$UserTableDayHandler instanceof UsersTableMonth)
            {
                throw new DomainException(sprintf('%s: Ошибка при обновлении дневного табеля', $UserTableDayHandler));
            }

        }

        $this->logger->info('MessageHandlerSuccess', ['handler' => self::class]);
    }
}
