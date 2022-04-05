$(document).ready(function () {
    $(document).on('click', '.btn-submit', function () {
        var wordValue = $('.word').val();
        var sloganValue = $('.slogan').val();
        var eventValue = $(this).attr('name');
        var eventTitle = $(this).attr('title');
        var dt = new Date();
        var now = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();

        $.ajax({
            method: 'POST',
            dataType: 'json',
            data: {
                word: wordValue,
                slogan: sloganValue,
                event: eventValue,
            },
            success: function (e) {
/*                 console.log(e['lettersMessage']);
                console.log(e['keysLettersMess']);
                console.log(e['lettersSlogan']);
                console.log(e['keysLettersSlog']);
                console.log(e['keysCommon']);
                console.log(e['newLetters']); */

                var markupKeysLettersMess = '<th class="title-thead">Значения</th>';
                var markupLettersMess = '<td class="title-thead">Сообщение</td>';
                var markupKeysLettersSlog = '<th class="title-thead">Поток ключей</th>';
                var markupLettersSlogan = '<td class="title-thead">Лозунг</td>';
                var markupKeysCommon = '<td class="title-thead">Новое значение</td>';
                var markupNewMessage = '<td class="title-thead">Зашифрованный текст</td>';
                var markupResult = '';

                var letterMessage = e['lettersMessage'];
                var keysLettersMess = e['keysLettersMess'];
                var letterSlogan = e['lettersSlogan'];
                var keysLettersSlog = e['keysLettersSlog'];
                var keysCommon = e['keysCommon'];
                var newMessage = e['newLetters'];


                for (let index = 0; index < letterMessage.length; index++) {
                    markupLettersMess = markupLettersMess +
                        '<td>' + letterMessage[index] + '</td>';

                    markupKeysLettersMess = markupKeysLettersMess +
                        '<th>' + keysLettersMess[index] + '</th>';

                    markupLettersSlogan = markupLettersSlogan +
                        '<td>' + letterSlogan[index] + '</td>';

                    markupKeysLettersSlog = markupKeysLettersSlog +
                        '<th>' + keysLettersSlog[index] + '</th>';

                    markupKeysCommon = markupKeysCommon +
                        '<th>' + keysCommon[index] + '</th>';

                    markupNewMessage = markupNewMessage +
                        '<th>' + newMessage[index] + '</th>';

                    markupResult = markupResult +
                        '<th>' + newMessage[index] + '</th>';

                }

                $('.table-vijener thead').html('<th colspan = ' + (letterMessage.length + 1) + '>' + eventTitle + '</th>');
                $('.table-vijener-letters-message').html(markupLettersMess);
                $('.table-vijener-index').html(markupKeysLettersMess);
                $('.table-vijener-letters-slogan').html(markupLettersSlogan);
                $('.table-vijener-keys-slogan').html(markupKeysLettersSlog);
                $('.table-vijener-keys-common').html(markupKeysCommon);
                $('.table-vijener-new-message').html(markupNewMessage);
                $('.table-vijener').after('<div class="note note-success"><h3>Результат <b>' + eventTitle + "</b> // " + now + ' </h3><div>' + wordValue + ' <b>-> ' + markupResult + '</b></div></div><hr>');
            },
        });
    });
});
