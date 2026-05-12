<?php
if (isset($_POST) && !empty($_POST)){
    session_start();
    $_SESSION['isCollapsed'] = $_POST['isCollapsed'];
    return true;
}