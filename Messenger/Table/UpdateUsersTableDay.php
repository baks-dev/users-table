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

namespace BaksDev\Users\UsersTable\Messenger\Table;

use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\UsersTable\Entity\Actions\Working\UsersTableActionsWorking;
use BaksDev\Users\UsersTable\Entity\Table\Event\UsersTableEvent;
use BaksDev\Users\UsersTable\Entity\UsersTableDay;
use BaksDev\Users\UsersTable\UseCase\Admin\Day\UsersTableDayDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Day\UserTableDayHandler;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 100)]
final readonly class UpdateUsersTableDay
{
    public function __construct(
        #[Target('usersTableLogger')] private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private UserTableDayHandler $tableDayHandler
    ) {}

    /**
     * Дневной табель
     */
    public function __invoke(UsersTableMessage $message): void
    {
        /** Получаем Добавленный табель пользователя */
        $UsersTableEvent = $this->entityManager
            ->getRepository(UsersTableEvent::class)
            ->find($message->getEvent());

        if(!$UsersTableEvent)
        {
            $this->logger->error(
                'Событие UsersTableEvent не найдено',
                [
                    __LINE__.':'.__FILE__,
                    $message->getEvent()
                ]
            );

            return;
        }

        $this->entityManager->clear();


        $UserTableDayDTO = new UsersTableDayDTO();
        $UserTableDayDTO->setProfile($UsersTableEvent->getProfile());
        $UserTableDayDTO->setWorking($UsersTableEvent->getWorking());
        $UserTableDayDTO->setDate($UsersTableEvent->getDate());

        /** Получаем дневной табель */
        $UsersTableDay = $this->entityManager->getRepository(UsersTableDay::class)->findOneBy([
            'profile' => $UserTableDayDTO->getProfile(),
            'working' => $UserTableDayDTO->getWorking(),
            'date' => $UserTableDayDTO->getDate()
        ]);

        $UsersTableDay?->getDto($UserTableDayDTO);


        /** Получаем действие и его настройку */
        $UsersTableActionWorking = $this->entityManager
            ->getRepository(UsersTableActionsWorking::class)
            ->find($UsersTableEvent->getWorking());

        if(!$UsersTableActionWorking)
        {
            $this->logger->error(
                'Действие UsersTableActionsWorking не найдено',
                [
                    self::class.':'.__LINE__,
                    $UsersTableEvent->getWorking()
                ]
            );

            return;
        }

        /**
         * Количество выполненной работы.
         */
        $UserTableDayDTO->addTotal($UsersTableEvent->getQuantity());

        /**
         * Стоимость работы с учетом коэффициента.
         */
        $moneyCoefficient = ($UserTableDayDTO->getTotal() * $UsersTableActionWorking->getCoefficient());
        $UserTableDayDTO->setMoney(new Money($moneyCoefficient));


        /**
         * Премия за переработку с учетом дневной нормы.
         */
        if($UserTableDayDTO->getTotal() > $UsersTableActionWorking->getNorm())
        {
            $premiumCoefficient = ($UsersTableActionWorking->getCoefficient() * $UsersTableActionWorking->getPremium()) / 100;
            $premiumTotal = $UserTableDayDTO->getTotal() - $UsersTableActionWorking->getNorm();
            $moneyPremium = $premiumTotal * $premiumCoefficient;

            $UserTableDayDTO->setPremium(new Money($moneyPremium));
        }

        $UserTableDayHandler = $this->tableDayHandler->handle($UserTableDayDTO);

        if(!$UserTableDayHandler instanceof UsersTableDay)
        {
            throw new DomainException(sprintf('%s: Ошибка при обновлении дневного табеля', $UserTableDayHandler));
        }

        $this->logger->info('MessageHandlerSuccess', ['handler' => self::class]);
    }
}
