<?php

function showExplorer() {
    // $path = '.';
    
    $path = $_SERVER['DOCUMENT_ROOT'];

    if (!empty($_GET['url'])) $path = $_GET['url'];

    if (is_dir($path)) {
        $path .= '/';
        $dir_list = scandir($path);
    } else {
        $content = file_get_contents($path);

        $path_arr = array_filter(explode('/', $path));
        array_pop($path_arr);

        $path_back = implode('/', $path_arr);

        $html = '
        <form action="/admin/?url=' . $path_back . '" method="post">
            <input type="hidden" name="path" value="' . $path . '" />
            <a href="/admin/?url=' . $path_back . '">Back</a>
            <textarea id="editor" name="content">' . $content . '</textarea>
            <button name="action" value="edit">Edit</button>
        </form>
        ';

        echo $html;
        return;
    }

    $html = '<form action="" method="post">
        <input type="hidden" name="path" value="' . $path . '" />
        <ul>';

    foreach($dir_list as $item) {
        if ($path == './' && $item == '.') continue;

        $item_url = $path . $item;
        
        if ($item == '.') $item_url = '/';

        if ($item == '..') {
            if ($path != $_SERVER['DOCUMENT_ROOT'] . '/') {
                $path_arr = array_filter(explode('/', $path));

                array_pop($path_arr);

                if (count($path_arr) == 0) continue;

                $path_back = implode('/', $path_arr);

                $item_url = $path_back;
            } else {
                $item_url = $_SERVER['DOCUMENT_ROOT'];
            }
        }

        $href = ($item != '.' ? '?url=' . $item_url : '/');

        if ($item == '..' && $item_url == '.') $href = '/';

        $html .= '<li>';

        if ($item != '.' && $item != '..') $html .= '
            <input type="checkbox" name="url[]" value="' . $item_url . '" />
        ';

        if (is_dir($item_url)) {
            $item = '<img src = "/images/1.png" class = "icon">' . $item;
        } else {

        $item = '<img src = "/images/2.png" class = "icon">' . $item;
        }
        
        $html .= '<a href="' . $href .'">' . $item . '</a></li>';
    }

    $html .= '</ul>';

    $html .= '
        <div class="explorer__options">            
            <button name="action" value="remove">Remove</button>

            <input type="text" name="name" />
            <button name="action" value="save">Save</button>
            <button name="action" value="rename">Rename</button>
        </div>
    </form>';

    echo $html;
}

function create($path, $name) {
    if (!is_dir($path)) return false;

    $info = pathinfo($name);

    if (!empty($info['extension'])) {
        if (!file_exists($path . $name)) {
            $fd = fopen($path . $name, 'x');
            fclose($fd);
        }

        header('Refresh: 0');
        exit;
    } else {
        if (!file_exists($path . $name)) {
            mkdir($path . $name);
        }

        header('Refresh: 0');
        exit;
    }
}

function edit($path, $content) {
    file_put_contents($path, $content);
    
    header('Refresh: 0');
    exit;
}

function remove_dir($dir)
{
    if ($objs = glob($dir . DIRECTORY_SEPARATOR . '*')) 
    {
        foreach($objs as $obj) 
        {
            is_dir($obj) ? remove_dir($obj) : unlink($obj);
        }
    }
    rmdir($dir);
}

function remove($files, $parent = '') {
    static $files_tmp = [];

    if (empty($files_tmp)) $files_tmp = $files;

    foreach($files as $file) {
        if (!is_dir($file)) {
            unlink($file);
        } else {
            $dir_list = scandir($file);
            $dir_list = array_diff($dir_list, ['.', '..']);

            if (count($dir_list) == 0) {
                @rmdir($file);
            } else {
                $arr = [];

                foreach($dir_list as $item) {
                    array_push($arr, $file . '/' . $item);
                }

                remove($arr, $file);
            }
        }

        if (!empty($parent)) {
            $parent_dir_list = scandir($parent);
            $parent_dir_list = array_diff($parent_dir_list, ['.', '..']);

            if (count($parent_dir_list) == 0) {
                rmdir($parent);
            }
        }
    }

    $error = false;
    foreach($files_tmp as $file) {
        if (file_exists($file)) $error = true;
    }

    if (!$error) {
        header('Refresh: 0');
        exit;
    }
}

function ren($files, $newname) {
    if (empty($files) || count($files) > 1 || strlen($newname) == 0) return;
    
    $file = $files[0];

    $fileinfo = pathinfo($file);
    $newfileinfo = pathinfo($newname);

    $newfile = $fileinfo['dirname'] . '/' . $newfileinfo['filename'];
    
    if (is_file($file)) {
        $newfile .= (!empty($newfileinfo['extension']) ? '.' . $newfileinfo['extension'] : '.' . $fileinfo['extension']);
    }

    rename($file, $newfile);

    header('Refresh: 0');
    exit;
}

showExplorer();

// echo '<pre>';
// var_dump($_POST);
// echo '</pre>';

if (!empty($_POST['action'])) {
    switch ($_POST['action']) {
        case 'save':
            if (empty($_POST['name'])) die();

            if (empty($_POST['url'])) {
                create($_POST['path'], $_POST['name']);
            }
        break;
        case 'edit':
            edit($_POST['path'], $_POST['content']);
        break;
        case 'remove':

            remove($_POST['url']);
        break;
        case 'rename':
            ren($_POST['url'], $_POST['name']);
        break;
    }
}

$html = '
    <form action="uploader.php" method="POST" enctype="multipart/form-data">
        <div class="explorer__options">
            <!--<input type="text" name="name" placeholder="Имя файла" value="files"/>-->
            <input type="file" multiple name="files[]"/>
            <button>Send</button>
        </div>    
    </form>
';

echo $html;