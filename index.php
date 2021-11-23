<?php
/**
 * Данный код позволяет очистить файл от комметариев и добавить их в отдельный файл "<name>_com.<extension>"
 * Создает в корне директорию "<name>"
 *
 * Принимаются файлы с расширением: .php
 */

/* !Инициализация основных переменных! */

/* метод передачи файла: командной строкой */
if ($argc < 2) {
    exit('Ошибка переданных параметров');
}
$openFileString = $argv[1];                                             // Исходный файл
                                                                        // Наимнование нового файла
$nameFileString = ($argc > 2) ? ((str_contains($argv[2], '.') === true) ? strstr($argv[2], '.', true) : $argv[2]) : 'default';

$extensionFile = strstr($openFileString, '.');                   // Расширение файла
$nameComString = $nameFileString . '_com';                              // Наименование файла с комментариями
$dirFiles = '\xampp\htdocs\comments\working\\' . $nameFileString;       // Наименование директории

/**
 * Проверка и открытие/создание всех необходимых для работы файлов
 */
$openedFile = fopen($openFileString, 'r');
if ($openedFile === false) {
    exit('Ошибка чтения файла');
}
if (mkdir($dirFiles) === false) {
    fclose($openedFile);
    exit('Ошибка создания директории');
}
$madeFile = fopen($dirFiles .'\\'. $nameFileString . $extensionFile, 'w');
if ($madeFile === false) {
    fclose($openedFile);
    exit('Ошибка открытия основного файла записи');
}
$madeFileCom = fopen($dirFiles .'\\'. $nameComString . '.txt', 'w');
if ($madeFileCom === false) {
    fclose($openedFile);
    fclose($madeFile);
    exit('Ошибка открытия файла комментариев');
}

/**
 * //Основной код//
 * Перебирает каждый символ исходного файла в цикле, производитит проверку и записывает в нужный файл
 */
$readString = null;
$writeString = null;
$checkup = null;
$registers = [
    'php_flags' => [
        'code' => false,
        'comm' => false,
        'strings' => ['quotes' => false, 'apostrophe' => false,]
    ],
    'html_com_flag' => false,
];                                                                      // Массив с флагами для корректной работы цикла

if (strcmp('.php', $extensionFile) === 0) {

    while (($readString = fgets($openedFile, 8192)) !== false) {
        $writeString .= $readString;
        if ($registers['html_com_flag'] === true) {

            if (($checkup = mb_strpos($writeString, '-->')) !== false) {
                $registers['html_com_flag'] = false;
                fwrite($madeFileCom, mb_substr($writeString, 0, $checkup+3));
                $writeString = mb_substr($writeString, $checkup+3);
            } else {
                fwrite($madeFileCom, $writeString);
                $writeString = '';
            }

        } elseif ($registers['php_flags']['code'] === true) {

            if ($registers['php_flags']['comm'] === true) {

                if (($checkup = mb_strpos($writeString, '*/')) !== false) {
                    $registers['php_flags']['comm'] = false;
                    fwrite($madeFileCom, mb_substr($writeString, 0, $checkup+2));
                    $writeString = mb_substr($writeString, $checkup+2);
                } else {
                    fwrite($madeFileCom, $writeString);
                    $writeString = '';
                }

            } elseif ($registers['php_flags']['strings']['apostrophe'] === true) {

                if (($checkup = mb_strpos($writeString, "'")) !== false) {
                    $registers['php_flags']['strings']['apostrophe'] = false;
                    fwrite($madeFile, mb_substr($writeString, 0, $checkup+1));
                    $writeString = mb_substr($writeString, $checkup+1);
                } else {
                    fwrite($madeFile, $writeString);
                    $writeString = '';
                }

            } elseif ($registers['php_flags']['strings']['quotes'] === true) {

                if (($checkup = mb_strpos($writeString, '"')) !== false) {
                    $registers['php_flags']['strings']['quotes'] = false;
                    fwrite($madeFile, mb_substr($writeString, 0, $checkup+1));
                    $writeString = mb_substr($writeString, $checkup+1);
                } else {
                    fwrite($madeFile, $writeString);
                    $writeString = '';
                }

            } else {

                if (($checkup = mb_strpos($writeString, '/*')) !== false) {
                    $registers['php_flags']['comm'] = true;
                    fwrite($madeFile, mb_substr($writeString, 0, $checkup));
                    $writeString = mb_substr($writeString, $checkup);

                } elseif (($checkup = mb_strpos($writeString, '//')) !== false) {
                    fwrite($madeFile, mb_substr($writeString, 0, $checkup) . PHP_EOL);
                    fwrite($madeFileCom, mb_substr($writeString, $checkup));
                    $writeString = '';

                } elseif (($checkup = mb_strpos($writeString, "'")) !== false) {
                    $registers['php_flags']['strings']['apostrophe'] = true;
                    fwrite($madeFile, mb_substr($writeString, 0, $checkup));
                    $writeString = mb_substr($writeString, $checkup);

                } elseif (($checkup = mb_strpos($writeString, '"')) !== false) {
                    $registers['php_flags']['strings']['quotes'] = true;
                    fwrite($madeFile, mb_substr($writeString, 0, $checkup));
                    $writeString = mb_substr($writeString, $checkup);

                } else {
                    fwrite($madeFile, $writeString);
                    $writeString = '';
                }
            }

        } else {

            if (($checkup = mb_strpos($writeString, '<?')) !== false) {
                $registers['php_flags']['code'] = true;
                fwrite($madeFile, mb_substr($writeString, 0, $checkup+2));
                $writeString = mb_substr($writeString, $checkup+2);

            } elseif (($checkup = mb_strpos($writeString, '<!--')) !== false) {
                $registers['html_com_flag'] = true;
                fwrite($madeFile, mb_substr($writeString, 0, $checkup));
                $writeString = mb_substr($writeString, $checkup);
            } else {
                fwrite($madeFile, $writeString);
                $writeString = '';
            }
        }
    }
} //else

/**
 * Закрытие ранее открытых файлов
 */
fclose($openedFile);
fclose($madeFile);
fclose($madeFileCom);
