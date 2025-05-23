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

namespace BaksDev\Users\UsersTable\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\UsersTable\Forms\MonthUsersTableFilter\Admin\MonthTableFilterDTO;
use BaksDev\Users\UsersTable\Forms\MonthUsersTableFilter\Admin\MonthTableFilterFilterForm;
use BaksDev\Users\UsersTable\Repository\MonthUsersTable\MonthUsersTableInterface;
use DateInterval;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_USERS_TABLE')]
final class MonthController extends AbstractController
{
    #[Route('/admin/tables/month/{page<\d+>}', name: 'admin.month', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        MonthUsersTableInterface $allUsersTable,
        int $page = 0,
    ): Response
    {
        // Поиск
        $search = new SearchDTO();

        $searchForm = $this
            ->createForm(
                type: SearchForm::class,
                data: $search,
                options: ['action' => $this->generateUrl('users-table:admin.month')]
            )
            ->handleRequest($request);

        // Фильтр

        $filter = new MonthTableFilterDTO($request, $this->getProfileUid());
        $filterForm = $this->createForm(MonthTableFilterFilterForm::class, $filter);
        $filterForm->handleRequest($request);


        if($filterForm->isSubmitted())
        {
            if($filterForm->get('back')->isClicked())
            {
                $filter->setDate($filter->getDate()?->sub(new DateInterval('P1M')));
                return $this->redirectToReferer();
            }

            if($filterForm->get('next')->isClicked())
            {
                $filter->setDate($filter->getDate()?->add(new DateInterval('P1M')));
                return $this->redirectToReferer();
            }
        }


        // Получаем список
        $UsersTable = $allUsersTable->fetchMonthUsersTableAssociative(
            $search,
            $filter,
            $this->getCurrentProfileUid(),
            $this->getProfileUid(),
            $this->isGranted('ROLE_USERS_TABLE_OTHER')
        );


        return $this->render(
            [
                'query' => $UsersTable,
                'search' => $searchForm->createView(),
                'filter' => $filterForm->createView(),
            ]
        );
    }
}
