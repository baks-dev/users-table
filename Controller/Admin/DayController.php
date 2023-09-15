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

namespace BaksDev\Users\UsersTable\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\UsersTable\Forms\DayUsersTableFilter\Admin\DayTableFilterDTO;
use BaksDev\Users\UsersTable\Forms\DayUsersTableFilter\Admin\DayTableFilterFilterForm;
use BaksDev\Users\UsersTable\Repository\DayUsersTable\DayUsersTableInterface;
use DateInterval;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_USERS_TABLE')]
final class DayController extends AbstractController
{
    #[Route('/admin/tables/day/{page<\d+>}', name: 'admin.day', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        DayUsersTableInterface $allUsersTable,
        int $page = 0,
    ): Response
    {
        // Поиск
        $search = new SearchDTO();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($request);

        // Фильтр
        //$ROLE_ADMIN = $this->isGranted('ROLE_ADMIN');
        $filter = new DayTableFilterDTO($request, $this->getProfileUid());

        $filterForm = $this->createForm(DayTableFilterFilterForm::class, $filter);
        $filterForm->handleRequest($request);

        if($filterForm->isSubmitted())
        {
            if($filterForm->get('back')->isClicked())
            {
                $filter->setDate($filter->getDate()?->sub(new DateInterval('P1D')));
                return $this->redirectToReferer();
            }

            if($filterForm->get('next')->isClicked())
            {
                $filter->setDate($filter->getDate()?->add(new DateInterval('P1D')));
                return $this->redirectToReferer();
            }
        }

        // Получаем список
        $UsersTable = $allUsersTable->fetchDayUsersTableAssociative(
            $search,
            $this->getCurrentProfileUid(),
            $filter,
            $this->isGranted('ROLE_MANUFACTURE_PART_OTHER') ? $this->getProfileUid() : null
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
