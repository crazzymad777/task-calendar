<?php

spl_autoload_register(function ($class) {
    include 'classes/' . str_replace('\\', '/', $class) . '.php';
});

require_once 'config.php';
require_once 'views/helpers.php';

if (isset($_REQUEST['function']) && !empty($_REQUEST['function'])) {
    switch($_REQUEST['function']) {
        case 'getCalendar':
            getCalendar($_POST['month'], $_POST['year']);
            break;
        case 'getEvents':
            $output = "html";
            if (isset($_REQUEST['output'])) $output = $_REQUEST['output'];
            $result = getTasks($_POST['date'], $output);
            if ($output != "html") {
                returnJSON($result);
            }
            break;
        case 'createTask':
            returnJSON(createTask($_REQUEST['date'], $_REQUEST['title'], $_REQUEST['comment'], $_REQUEST['color']));
            break;
        case 'moveTask':
            moveTask($_REQUEST['id'], $_REQUEST['date']);
            break;
        case 'renameTask':
            renameTask($_POST['id'], $_POST['title']);
            break;
        case 'setStatusTask':
            setStatusTask($_POST['id'], $_POST['status']);
            break;
        case 'changeColorTask':
            changeColorTask($_POST['id'], $_POST['color']);
            break;
        case 'editCommentTask':
            editCommentTask($_POST['id'], $_POST['comment']);
            break;
        case 'setStatusDay':
            setStatusDay($_POST['date'], $_POST['status']);
            break;
        default:
            returnJSON(["status" => "error"]);
    }
} else if ($_SERVER["SCRIPT_NAME"] == "/functions.php"){
    returnJSON(["status" => "error"]);
}

function returnJSON($data) {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data);
}

function createTask($date, $title, $comment, $color){
    $task = new Task(["date" => $date,
                      "title" => $title,
                      "comment" => $comment,
                      "color" => $color]);
    $task->create();
    return ["status" => "ok", "response" => $task];
}

function moveTask($id, $date) {
    $task = Task::get($id);
    if ($task !== null) {
        $task["date"] = $date;
        $task->save();
    }
}

function renameTask($id, $title) {
    $task = Task::get($id);
    if ($task !== null) {
        $task["title"] = $title;
        $task->save();
    }
}

function setStatusTask($id, $status) {
    if ($status == 0 || $status == 1) {
        $task = Task::get($id);
        if ($task !== null) {
            $task["status"] = $status;
            $task->save();
        }
    }
}

function editCommentTask($id, $comment) {
    $task = Task::get($id);
    if ($task !== null) {
        $task["comment"] = $comment;
        $task->save();
    }
}

function changeColorTask($id, $color) {
    $task = Task::get($id);
    if ($task !== null) {
        $task["color"] = $color;
        $task->save();
    }
}

function setStatusDay($date, $status) {
    if ($status == 0 || $status == 1) {
        $day = Day::get($date);
        if ($day !== null) {
            $day["status"] = $status;
            $day->save();
        } else {
            $day = new Day(["date" => $date, "status" => $status]);
            $day->create();
        }
    }
}

function getTasks($date, $output = "html") {
    $tasks = Task::whereDateEq($date);
    if ($output == "html") {
        $view = new Views\Tasks($tasks);
        $view->render();
    } else {
        return ["status" => "ok", "response" => $tasks];
    }
    return null;
}

function getCalendar($month = '', $year = '') {
	$grid = new Views\DaysGrid($month, $year);
	$grid->render();
}