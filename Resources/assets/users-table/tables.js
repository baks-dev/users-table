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


form = document.forms.users_table_form;

users_table_form_category = document.getElementById('users_table_form_category');
//const users_table_form_action = document.getElementById('users_table_form_action');
//const users_table_form_working = document.getElementById('users_table_form_working');

updateForm = async (key, value) =>
{

    const data = new FormData(form);
    const url = form.getAttribute('action');
    const method = form.getAttribute('method');

    //let requestBody = e.target.getAttribute('name') + '=' + e.target.value;
    let requestBody = [];
    requestBody.push(key + "=" + value);

    // for (const pair of data.entries()) {
    //     if (pair[1].trim().length !== 0) {
    //         /!* Кроме токена CORS *!/
    //         if (pair[0] !== "users_table_form[_token]" && pair[0] != key) {
    //             requestBody.push(pair[0] + "=" + pair[1]);
    //         }
    //     }
    // }


    requestBody = requestBody.join("&");

    const req = await fetch(url, {
        method: method,
        body: requestBody,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'charset': 'utf-8'
        }
    });

    const text = await req.text();

    return text;
};

parseTextToHtml = (text) =>
{
    const parser = new DOMParser();
    const html = parser.parseFromString(text, 'text/html');

    return html;
};

/**
 * Производственный процесс после выбора Категория производства
 */
changeCategory = async (e) =>
{

    const updateFormResponse = await updateForm(e.target.getAttribute('name'), e.target.value);
    const html = parseTextToHtml(updateFormResponse);

    //console.log(requestBody);

    /* Удаляем предыдущий Select2 */
    let select2 = document.getElementById('users_table_form_action_select2');

    if(select2)
    {
        select2.remove();
    }

    const new_users_table_form_action = html.getElementById('users_table_form_action');
    document.getElementById('users_table_form_action').replaceWith(new_users_table_form_action);

    const new_users_table_form_working = html.getElementById('users_table_form_working');
    document.getElementById('users_table_form_working').replaceWith(new_users_table_form_working);


    const users_table_form_action = document.getElementById('users_table_form_action');
    users_table_form_action.addEventListener('change', (e) => changeActions(e));


};


/**
 * Действие сотрудника после выбора Производственный процесс
 */
changeActions = async (e) =>
{

    //const requestBody = e.target.getAttribute('name') + '=' + e.target.value;
    const updateFormResponse = await updateForm(e.target.getAttribute('name'), e.target.value);
    const html = parseTextToHtml(updateFormResponse);

    //console.log(requestBody);

    /* Удаляем предыдущий Select2 */
    let select2 = document.getElementById('users_table_form_working_select2');

    if(select2)
    {
        select2.remove();
    }

    const new_users_table_form_working = html.getElementById('users_table_form_working');
    //form_select_position.innerHTML = new_form_select_position.innerHTML;
    document.getElementById('users_table_form_working').replaceWith(new_users_table_form_working);

};

users_table_form_category.addEventListener('change', (e) => changeCategory(e));


//
//
//
//
//
//
//
// inputTableCategory = document.getElementById('users_table_form_category');
//
// if (inputTableCategory)
// {
//     cahgeCategory(inputTableCategory);
// }
//
//
// function cahgeCategory(inputTableCategory) {
//     inputTableCategory.addEventListener('change', function () {
//
//
//         let replaceId = 'users_table_form_action';
//
//         let replaceElement = document.getElementById(replaceId + '_select2');
//         if (replaceElement) {
//             replaceElement.classList.add('disabled');
//         }
//
//
//         /* Создаём объект класса XMLHttpRequest */
//         const requestModalName = new XMLHttpRequest();
//         requestModalName.responseType = "document";
//
//         /* Имя формы */
//         let incomingForm = document.forms.users_table_form;
//         let formData = new FormData();
//
//         //let materialName = document.getElementById(replaceId);
//
//         formData.append(this.getAttribute('name'), this.value);
//
//         //console.log(materialName.getAttribute('name'));
//
//         requestModalName.open(incomingForm.getAttribute('method'), incomingForm.getAttribute('action'), true);
//         /* Указываем заголовки для сервера */
//         requestModalName.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
//
//         /* Получаем ответ от сервера на запрос*/
//         requestModalName.addEventListener("readystatechange", function () {
//             /* request.readyState - возвращает текущее состояние объекта XHR(XMLHttpRequest) */
//             if (requestModalName.readyState === 4 && requestModalName.status === 200) {
//
//                 let result = requestModalName.response.getElementById(replaceId);
//
//                 // if (index !== 0) {
//                 //     result.name = 'incoming_material_defect_form[material][' + index + '][offer]';
//                 //     result.id = 'incoming_material_defect_form_material_' + index + '_offer'
//                 // }
//
//
//                 /* Удаляем предыдущий Select2 */
//                 let select2 = document.getElementById(replaceId + '_select2');
//
//                 if (select2) {
//                     select2.remove();
//                 }
//
//
//                 document.getElementById(replaceId).replaceWith(result);
//
//                 // document.querySelectorAll('[data-select="select2"]').forEach(function (item) {
//                 if (result.disabled === false) {
//                     new NiceSelect(result, {searchable: true, id: 'select2-' + replaceId});
//                 }
//
//
//                 inputTableActions = document.getElementById('users_table_form_action');
//
//                 if (inputTableActions)
//                 {
//
//                     changeActions(inputTableActions);
//                 }
//
//                 //});
//             }
//
//             return false;
//         });
//
//         requestModalName.send(formData);
//
//     });
// }
//
//
// inputTableActions = document.getElementById('users_table_form_action');
//
// if (inputTableActions)
// {
//
//     changeActions(inputTableActions);
// }
//
//
// function changeActions(inputTableActions) {
//     inputTableActions.addEventListener('change', function () {
//
//
//         let replaceId = 'users_table_form_working';
//
//         let replaceElement = document.getElementById(replaceId + '_select2');
//         if (replaceElement) {
//             replaceElement.classList.add('disabled');
//         }
//
//
//         /* Создаём объект класса XMLHttpRequest */
//         const requestModalName = new XMLHttpRequest();
//         requestModalName.responseType = "document";
//
//         /* Имя формы */
//         let incomingForm = document.forms.users_table_form;
//         let formData = new FormData();
//
//         //let materialName = document.getElementById(replaceId);
//
//
//
//
//         formData.append(this.getAttribute('name'), this.value);
//
//         //console.log(materialName.getAttribute('name'));
//
//         requestModalName.open(incomingForm.getAttribute('method'), incomingForm.getAttribute('action'), true);
//         /* Указываем заголовки для сервера */
//         requestModalName.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
//
//         /* Получаем ответ от сервера на запрос*/
//         requestModalName.addEventListener("readystatechange", function () {
//             /* request.readyState - возвращает текущее состояние объекта XHR(XMLHttpRequest) */
//             if (requestModalName.readyState === 4 && requestModalName.status === 200) {
//
//                 let result = requestModalName.response.getElementById(replaceId);
//
//                 // if (index !== 0) {
//                 //     result.name = 'incoming_material_defect_form[material][' + index + '][offer]';
//                 //     result.id = 'incoming_material_defect_form_material_' + index + '_offer'
//                 // }
//
//
//                 /* Удаляем предыдущий Select2 */
//                 let select2 = document.getElementById(replaceId + '_select2');
//                 if (select2) {
//                     select2.remove();
//                 }
//
//
//                 document.getElementById(replaceId).replaceWith(result);
//
//
//                 // document.querySelectorAll('[data-select="select2"]').forEach(function (item) {
//                 if (result.disabled === false) {
//                     new NiceSelect(result, {searchable: true, id: 'select2-' + replaceId});
//                 }
//
//
//                 //});
//             }
//
//             return false;
//         });
//
//         requestModalName.send(formData);
//
//     });
// }
//
