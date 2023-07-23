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

namespace BaksDev\Users\UsersTable\UseCase\Admin\Month;

use BaksDev\Users\UsersTable\Entity\UsersTableDay;
use BaksDev\Users\UsersTable\Entity\UsersTableMonth;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserTableMonthHandler
{
    private EntityManagerInterface $entityManager;

    private ValidatorInterface $validator;

    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->logger = $logger;
    }


    /**
     * Метод получает/создает табель на указанный месяц и обновляет его количество
     * @see UsersTableDay
     */
    public function handle(UsersTableMonthDTO $command): string|UsersTableMonth
    {
        /**
         *  Валидация UsersTableMonthDTO.
         */
        $errors = $this->validator->validate($command);

        if (count($errors) > 0)
        {
            $uniqid = uniqid('', false);
            $errorsString = (string) $errors;
            $this->logger->error($uniqid.': '.$errorsString);
            return $uniqid;
        }

        $UsersTableMonth = $this->entityManager->getRepository(UsersTableMonth::class)->findOneBy(
            [
                'profile' => $command->getProfile(),
                'working' => $command->getWorking(),
                'date' => $command->getDate()
            ]
        );

        if ($UsersTableMonth === null)
        {
            $UsersTableMonth = new UsersTableMonth();
        }
        
        $UsersTableMonth->setEntity($command);

        /**
         * Валидация UsersTableMonth.
         */
        $errors = $this->validator->validate($UsersTableMonth);

        if (count($errors) > 0)
        {
            $uniqid = uniqid('', false);
            $errorsString = (string) $errors;
            $this->logger->error($uniqid.': '.$errorsString);
            return $uniqid;
        }

        $this->entityManager->persist($UsersTableMonth);
        $this->entityManager->flush();

        return $UsersTableMonth;
    }
}
