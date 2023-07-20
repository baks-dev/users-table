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

$addButtonWorking = document.getElementById('workingAddCollection');

if ($addButtonWorking) {
    /* Блок для новой коллекции */
    let $blockCollectionCall = document.getElementById('collection-working');

    if ($blockCollectionCall) {

        $addButtonWorking.addEventListener('click', function () {

            let $addButtonWorking = this;
            /* получаем прототип коллекции  */
            let newForm = $addButtonWorking.dataset.prototype;
            let index = $addButtonWorking.dataset.index * 1;

            /* Замена '__name__' в HTML-коде прототипа
            вместо этого будет число, основанное на том, сколько коллекций */
            newForm = newForm.replace(/__actions_working__/g, index);

            /* Вставляем новую коллекцию */
            let div = document.createElement('div');
            div.id = 'item_users_table_actions_form_working_' + index;

            // div.classList.add('align-items-center');
            // div.classList.add('gap-3');
            // div.classList.add('item-collection-file');

            div.innerHTML = newForm;
            $blockCollectionCall.append(div);


            /* Добавить контактный номер телефона */
            /*(div.querySelector('.phone-add-collection'))?.addEventListener('click', addPhone);*/


            /* Удаляем контактный номер телефона */
            (div.querySelector('.del-item-working'))?.addEventListener('click', deletePhone);


            /* Увеличиваем data-index на 1 после вставки новой коллекции */
            $addButtonWorking.dataset.index = (index + 1).toString();

            /* Плавная прокрутка к элементу */
            div.scrollIntoView({block: "center", inline: "center", behavior: "smooth"});



        });
    }
}

/*document.querySelectorAll('.del-item-call').forEach(function (item) {
    item.addEventListener('click', deleteCall);
});*/

/*function deleteCall() {

   if (document.getElementById('collection-call').childElementCount === 1)
   {
       return;
   }

   document.getElementById(this.dataset.delete).remove();
}*/



document.querySelectorAll('.del-item-working').forEach(function (item) {
    item.addEventListener('click', deletePhone);
});

function deletePhone() {

    console.log(this.dataset.delete);

    document.getElementById(this.dataset.delete).remove();
}



