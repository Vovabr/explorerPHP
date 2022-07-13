<?php

function uploader() {

    if (empty($_FILES['files']['name'][0])) exit;

    // echo '<pre>';
    // print_r($_FILES);
    // echo '</pre>';

    $destPath = './uploads';
    if (!file_exists($destPath)) {
        mkdir('./uploads'); /*создать папку*/
    }

    $allFiles = scandir($destPath); /*Получает список файлов и каталогов, расположенных по указанному пути*/
    foreach ($_FILES['files']['tmp_name'] as $index => $path) {
        if (file_exists($path)) {
            $fileInfo = pathinfo($_FILES['files']['name'][$index]); /*Возвращает информацию о пути к файлу*/
            $findFiles = preg_grep("/^" . $fileInfo ['filename'] . "(.+)?\." . $fileInfo ['extension'] . "$/", $allFiles); /*Возвращает массив вхождений, которые соответствуют шаблону*/
            $filename = $fileInfo ['filename'] . (count($findFiles) > 0 ? '_' . (count($findFiles)+1) : '') . '.' . $fileInfo ['extension'];
            move_uploaded_file($path, $destPath . '/' . $filename);/*Перемещает загруженный файл в новое место*/
        }
    }
    header('Refresh: 0; url = ./index.php');
    exit; 
}

uploader();

