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

namespace BaksDev\Users\UsersTable\Controller\Admin\Actions;


use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\UsersTableActionsDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\UsersTableActionsForm;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\UsersTableActionsHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[RoleSecurity('ROLE_USERS_TABLE_ACTIONS_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/users/table/action/edit/{id}', name: 'admin.action.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request                             $request,
        #[MapEntity] UsersTableActionsEvent $UsersTableActionsEvent,
        UsersTableActionsHandler            $UsersTableActionsHandler,
    ): Response
    {
        $UsersTableActionsDTO = new UsersTableActionsDTO();
        $UsersTableActionsEvent->getDto($UsersTableActionsDTO);

        // Форма
        $form = $this->createForm(UsersTableActionsForm::class, $UsersTableActionsDTO, [
            'action' => $this->generateUrl('UsersTableActions:admin.edit', ['id' => $UsersTableActionsDTO->getEvent()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->has('users_table_actions')) {
            $UsersTableActions = $UsersTableActionsHandler->handle($UsersTableActionsDTO);

            if ($UsersTableActions instanceof UsersTableActions) {
                $this->addFlash('success', 'admin.success.new', 'admin.table.actions');

                return $this->redirectToRoute('UsersTableActions:admin.index');
            }

            $this->addFlash('danger', 'admin.danger.new', 'admin.table.actions', $UsersTableActions);

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}
