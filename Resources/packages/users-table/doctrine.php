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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use BaksDev\Users\UsersTable\BaksDevUsersTableBundle;
use BaksDev\Users\UsersTable\Type\Actions\Const\UsersTableActionsWorkingConst;
use BaksDev\Users\UsersTable\Type\Actions\Const\UsersTableActionsWorkingConstType;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventType;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use BaksDev\Users\UsersTable\Type\Actions\Id\UsersTableActionsType;
use BaksDev\Users\UsersTable\Type\Actions\Id\UsersTableActionsUid;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingType;
use BaksDev\Users\UsersTable\Type\Actions\Working\UsersTableActionsWorkingUid;
use BaksDev\Users\UsersTable\Type\Table\Event\UsersTableEventType;
use BaksDev\Users\UsersTable\Type\Table\Event\UsersTableEventUid;
use BaksDev\Users\UsersTable\Type\Table\Id\UsersTableType;
use BaksDev\Users\UsersTable\Type\Table\Id\UsersTableUid;
use Symfony\Config\DoctrineConfig;

return static function(ContainerConfigurator $container, DoctrineConfig $doctrine)
{

    $doctrine->dbal()->type(UsersTableUid::TYPE)->class(UsersTableType::class);
    $doctrine->dbal()->type(UsersTableEventUid::TYPE)->class(UsersTableEventType::class);

    $doctrine->dbal()->type(UsersTableActionsUid::TYPE)->class(UsersTableActionsType::class);
    $doctrine->dbal()->type(UsersTableActionsEventUid::TYPE)->class(UsersTableActionsEventType::class);
    $doctrine->dbal()->type(UsersTableActionsWorkingUid::TYPE)->class(UsersTableActionsWorkingType::class);
    $doctrine->dbal()->type(UsersTableActionsWorkingConst::TYPE)->class(UsersTableActionsWorkingConstType::class);

    $emDefault = $doctrine->orm()->entityManager('default')->autoMapping(true);


    $emDefault->mapping('users-table')
		->type('attribute')
		->dir(BaksDevUsersTableBundle::PATH.'Entity')
		->isBundle(false)
		->prefix('BaksDev\Users\UsersTable')
		->alias('users-table')
	;
};