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

namespace BaksDev\Users\UsersTable\Controller\Admin\Table;


use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\UsersTable\Entity\Table\Event\UsersTableEvent;
use BaksDev\Users\UsersTable\UseCase\Admin\Table\Delete\UsersTableDeleteHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_USERS_TABLE_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/users/table/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity] UsersTableEvent $UsersTableEvent,
        UsersTableDeleteHandler $UsersTableDeleteHandler,
    ): Response
    {

        dd('/admin/users/table/delete');

        $UsersTableDeleteDTO = new UsersTableDeleteDTO();
        $UsersTableEvent->getDto($UsersTableDeleteDTO);
        $form = $this->createForm(
            UsersTableDeleteForm::class,
            $UsersTableDeleteDTO,
            ['action' => $this->generateUrl('UsersTable:admin.delete', ['id' => $UsersTableDeleteDTO->getEvent()]),]
        );
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('users_table_delete'))
        {
            $UsersTable = $UsersTableDeleteHandler->handle($UsersTableDeleteDTO);

            if($UsersTable instanceof UsersTable)
            {
                $this->addFlash('admin.page.delete', 'admin.success.delete', 'admin.users.table');

                return $this->redirectToRoute('UsersTable:admin.index');
            }

            $this->addFlash(
                'admin.page.delete',
                'admin.danger.delete',
                'admin.users.table',
                $UsersTable
            );

            return $this->redirectToRoute('UsersTable:admin.index', status: 400);
        }

        return $this->render(
            ['form' => $form->createView(), 'name' => $UsersTableEvent->getNameByLocale(
                $this->getLocale()
            ), // название согласно локали
            ]
        );
    }
}
