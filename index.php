<?php
/*
 * Данный код позволяет очистить файл от комметариев и добавить их в отдельный файл "<name>_com.<extension>"
 * Создает в корне директорию "<name>"
 *
 * Принимаются файлы с расширением: .php
 */

/* !Инициализация основных переменных! */

/*метод передачи файла: командной строкой*/
if ($argc < 2) {
    exit('Ошибка переданных параметров');
}
$openFileString = $argv[1];                                             // Исходный файл
                                                                        // Наимнование нового файла
$nameFileString = ($argc > 2) ? ((str_contains($argv[2], '.') === true) ? strstr($argv[2], '.', true) : $argv[2]) : 'default';

$extensionFile = strstr($openFileString, '.');                   // Расширение файла
$nameComString = $nameFileString . '_com';                              // Наименование файла с комментариями
$dirFiles = '\xampp\htdocs\skillbox\helpful\\' . $nameFileString;       // Наименование директории

/*
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

/*
 * //Основной код//
 * Перебирает каждый символ исходного файла в цикле, производитит проверку и записывает в нужный файл
 */
$firstChar = '';                                                        // Первый считываемый символ
$secondChar = '';                                                       // Второй считываемый символ
$registers = [
    'php_flags' => [
        'code' => false,
        'comm' => false,
        'strings' => ['quotes' => false, 'apostrophe' => false,]
    ],
    'html_com_flag' => false,
];                                                                      // Массив с флагами для корректной работы цикла

if (strcmp('.php', $extensionFile) === 0) {

    while (($firstChar = fgetc($openedFile)) !== false) {
        if ($registers['html_com_flag'] === true) {

            if (strcmp('-', $firstChar) === 0) {
                $secondChar = fgetc($openedFile);
                if ($secondChar === false) {
                    fwrite($madeFileCom, $firstChar);
                    break;
                }
                $firstChar .= $secondChar;

                if (strcmp('--', $firstChar) === 0) {
                    $secondChar = fgetc($openedFile);
                    if ($secondChar === false) {
                        fwrite($madeFileCom, $firstChar);
                        break;
                    }
                    $firstChar .= $secondChar;

                    if (strcmp('-->', $firstChar) === 0) {
                        $registers['html_com_flag'] = false;
                        fwrite($madeFileCom, $firstChar. PHP_EOL);

                    } else fwrite($madeFileCom, $firstChar);
                } else fwrite($madeFileCom, $firstChar);
            } else fwrite($madeFileCom, $firstChar);

        } elseif ($registers['php_flags']['code'] === true) {

            if ($registers['php_flags']['comm'] === true) {

                if (strcmp('*', $firstChar) === 0) {
                    $secondChar = fgetc($openedFile);
                    if ($secondChar === false) {
                        fwrite($madeFileCom, $firstChar);
                        break;
                    }
                    $firstChar .= $secondChar;

                    if (strcmp('*/', $firstChar) === 0) {
                        $registers['php_flags']['comm'] = false;
                        fwrite($madeFileCom, $firstChar . PHP_EOL);

                    } else fwrite($madeFileCom, $firstChar);
                } else fwrite($madeFileCom, $firstChar);

            } elseif ($registers['php_flags']['strings']['apostrophe'] === true) {

                if (strcmp("'", $firstChar) === 0) {
                    $registers['php_flags']['strings']['apostrophe'] = false;
                    fwrite($madeFile, $firstChar);

                } else fwrite($madeFile, $firstChar);

            } elseif ($registers['php_flags']['strings']['quotes'] === true) {

                if (strcmp('"', $firstChar) === 0) {
                    $registers['php_flags']['strings']['quotes'] = false;
                    fwrite($madeFile, $firstChar);

                } else fwrite($madeFile, $firstChar);

            } else {

                if (strcmp('/', $firstChar) === 0) {
                    $secondChar = fgetc($openedFile);
                    if ($secondChar === false) {
                        fwrite($madeFile, $firstChar);
                        break;
                    }
                    $firstChar .= $secondChar;

                    if(strcmp('/*', $firstChar) === 0) {
                        $registers['php_flags']['comm'] = true;
                        fwrite($madeFileCom, $firstChar);

                    } elseif (strcmp('//', $firstChar) === 0) {
                        $secondChar = fgets($openedFile);
                        if ($secondChar === false) {
                            fwrite($madeFileCom, $firstChar);
                            break;
                        }
                        fwrite($madeFileCom, $firstChar . $secondChar);
                        fwrite($madeFile, PHP_EOL);

                    } else fwrite($madeFile, $firstChar);

                } elseif (strcmp("'", $firstChar) === 0) {
                    $registers['php_flags']['strings']['apostrophe'] = true;
                    fwrite($madeFile, $firstChar);

                } elseif (strcmp('"', $firstChar) === 0) {
                    $registers['php_flags']['strings']['quotes'] = true;
                    fwrite($madeFile, $firstChar);

                } else fwrite($madeFile, $firstChar);
            }

        } else {

            if (strcmp('<', $firstChar) === 0) {
                $secondChar = fgetc($openedFile);
                if ($secondChar === false) {
                    fwrite($madeFile, $firstChar);
                    break;
                }
                $firstChar .= $secondChar;

                if (strcmp('<?', $firstChar) === 0) {
                    $registers['php_flags']['code'] = true;
                    fwrite($madeFile, $firstChar);

                } elseif (strcmp('<!', $firstChar) === 0) {
                    $secondChar = fgetc($openedFile);
                    if ($secondChar === false) {
                        fwrite($madeFile, $firstChar);
                        break;
                    }
                    $firstChar .= $secondChar;

                    if (strcmp('<!-', $firstChar) === 0) {
                        $secondChar = fgetc($openedFile);
                        if ($secondChar === false) {
                            fwrite($madeFile, $firstChar);
                            break;
                        }
                        $firstChar .= $secondChar;

                        if (strcmp('<!--', $firstChar) === 0) {
                            $registers['html_com_flag'] = true;
                            fwrite($madeFileCom, $firstChar);

                        } else fwrite($madeFile, $firstChar);
                    } else fwrite($madeFile, $firstChar);
                } else fwrite($madeFile, $firstChar);
            } else fwrite($madeFile, $firstChar);
        }
    }
} //else

/*
 * Закрытие ранее открытых файлов
 */
fclose($openedFile);
fclose($madeFile);
fclose($madeFileCom);
