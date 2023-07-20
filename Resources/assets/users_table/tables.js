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


inputTableActions = document.getElementById('users_table_form_action');

if (inputTableActions)
{
    inputTableActions.addEventListener('change', function () {


        let replaceId = 'users_table_form_working';

        let replaceElement = document.getElementById(replaceId + '_select2');
        if (replaceElement) {
            replaceElement.classList.add('disabled');
        }


        /* Создаём объект класса XMLHttpRequest */
        const requestModalName = new XMLHttpRequest();
        requestModalName.responseType = "document";

        /* Имя формы */
        let incomingForm = document.forms.users_table_form;
        let formData = new FormData();

        //let materialName = document.getElementById(replaceId);




        formData.append(this.getAttribute('name'), this.value);

        //console.log(materialName.getAttribute('name'));

        requestModalName.open(incomingForm.getAttribute('method'), incomingForm.getAttribute('action'), true);
        /* Указываем заголовки для сервера */
        requestModalName.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        /* Получаем ответ от сервера на запрос*/
        requestModalName.addEventListener("readystatechange", function () {
            /* request.readyState - возвращает текущее состояние объекта XHR(XMLHttpRequest) */
            if (requestModalName.readyState === 4 && requestModalName.status === 200) {

                let result = requestModalName.response.getElementById(replaceId);

                // if (index !== 0) {
                //     result.name = 'incoming_material_defect_form[material][' + index + '][offer]';
                //     result.id = 'incoming_material_defect_form_material_' + index + '_offer'
                // }


                /* Удаляем предыдущий Select2 */
                let select2 = document.getElementById(replaceId + '_select2');
                if (select2) {
                    select2.remove();
                }


                document.getElementById(replaceId).replaceWith(result);


                // document.querySelectorAll('[data-select="select2"]').forEach(function (item) {
                if (result.disabled === false) {
                    new NiceSelect(result, {searchable: true, id: 'select2-' + replaceId});
                }


                //});
            }

            return false;
        });

        requestModalName.send(formData);

    })

}

