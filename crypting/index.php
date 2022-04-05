<?php
function pr($var)
{
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}

$index = 0;
foreach (range(chr(0xC0), chr(0xDF)) as $b) {
    if ($index == 6) {
        $alphabet[] =  iconv('CP1251', 'UTF-8', chr(168));
    }
    $alphabet[] = iconv('CP1251', 'UTF-8', $b);

    $index++;
}

for ($i = 0; $i * $i < count($alphabet); $i++) {
    $size = $i + 1;
}

if (isset($_POST['word'])) {
    mb_regex_encoding('UTF-8');
    mb_internal_encoding("UTF-8");

    $word = $_POST['word'];
    $slogan = $_POST['slogan'];
    $event = $_POST['event'];

    function sep($word)
    {
        $letters = str_replace(" ", "", $word);
        $letters = preg_split('/(?<!^)(?!$)/u', $letters);

        return $letters;
    }

    function keysLetters($letters, $alphabet)
    {
        foreach ($letters as $letter) {
            $key = array_search(mb_strtoupper($letter, 'UTF-8'), $alphabet);
            $keysLetters[] =  $key;
        }

        return $keysLetters;
    }

    function fillAdditional($list, $initial)
    {
        for ($i = 0; count($list) < count($initial); $i++) {
            $list[] = $list[$i];
        }

        return $list;
    }

    function mod($list1, $list2, $alphabet, $event)
    {
        for ($i = 0; $i < count($list1); $i++) {

            if ($event == 'crypt') {
                $calc = $list1[$i] + $list2[$i];
            } else {
                $calc = $list1[$i] - $list2[$i] + count($alphabet);
            }
            $mod = $calc % (count($alphabet));
            /*             $s = $list1[$i];
            $d = $list2[$i];
            $count = count($alphabet);
            echo "$s + $d = $sum; $sum % $count = $mod; <br>"; */
            $keys[] =  $mod;
        }

        return $keys;
    }

    function newLetters($keys, $alphabet)
    {
        foreach ($keys as $key) {
            /*             $newLetterKey =  array_search($key, array_keys($alphabet)); */
            $newLetter = $alphabet[$key];
            $newLetters[] =  $newLetter;
        }

        return $newLetters;
    }

    $lettersMessage = sep(mb_strtolower($word, 'UTF-8'));
    $lettersSlogan = sep(mb_strtoupper($slogan, 'UTF-8'));

    $keysLettersMess = keysLetters($lettersMessage, $alphabet);
    $keysLettersSlog = keysLetters($lettersSlogan, $alphabet);

    $lettersSlogan = fillAdditional($lettersSlogan, $lettersMessage);
    $keysLettersSlog = fillAdditional($keysLettersSlog, $keysLettersMess);

    $keysCommon = mod($keysLettersMess, $keysLettersSlog, $alphabet, $event);

    $newLetters = newLetters($keysCommon, $alphabet);

    echo json_encode(array(
        'lettersMessage' => $lettersMessage,
        'keysLettersMess' => $keysLettersMess,
        'lettersSlogan' => $lettersSlogan,
        'keysLettersSlog' => $keysLettersSlog,
        'keysCommon' => $keysCommon,
        'newLetters' => $newLetters,
    ));

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.11.0/mdb.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="common/style.css">
</head>

<body>
    <div class="container-xxl mt-3">
        <div>
            <h3>Шифрование по методу "Виженера"</h3>
        </div>

        <hr>
        <!-- Матрица 6х6 -->
        <!--         <table class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr>
                    <th>#</th>
                    <?php for ($i = 1; $i < $size + 1; $i++) : ?>
                        <th><?= $i ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < count($alphabet); $i += $size) : ?>
                    <tr class="text-center">
                        <th class="index-row"></th>
                        <?php for ($j = $i; $j < $i + $size; $j++) : ?>
                            <td><?= $alphabet[$j] ?></td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table> -->

        <table class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr>
                    <?php foreach ($alphabet as $letter => $value) : ?>
                        <th><?= $letter ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr class="text-center">
                    <?php foreach ($alphabet as $letter) : ?>
                        <td><?= $letter ?></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>

        <div class="wrapper-form-control text-center mt-3">
            <div class="error-wrapper"></div>
            <h3>Сообщение:</h3>
            <input type="text" name='word' class="form-control word" placeholder="Введите слово" pattern="^(?=.*[A-Za-z])[A-Za-z0-9]{3,9}" required>
            <h3 class="mt-3">Лозунг:</h3>
            <input type="text" name='slogan' class="form-control slogan" placeholder="Введите лозунг" pattern="^(?=.*[A-Za-z])[A-Za-z0-9]{3,9}" required>

            <button type='submit' name="crypt" title="Шифрования" class='btn btn-primary btn-submit'>Зашифровать</button>
            <button type='submit' name="decrypt" title="Расшифрования" class='btn btn-primary btn-submit'>Расшифровать</button>
        </div>

        <table class="table table-hover table-bordered table-vijener mt-4">
            <thead class="thead-dark text-center">
            </thead>
            <tbody class="text-center">
                <tr class="table-vijener-letters-message table-warning"></tr>
                <tr class="table-vijener-index table-primary">
                <tr class="table-vijener-letters-slogan table-warning"></tr>
                <tr class="table-vijener-keys-slogan table-primary"></tr>
                <tr class="table-vijener-keys-common table-primary"></tr>
                <tr class="table-vijener-new-message table-success"></tr>
            </tbody>
        </table>

    </div>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.11.0/mdb.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>

    <script type="text/javascript" src="common/script.js"></script>
</body>

</html>