<?php
/**
 * @author Kovács László
 *
 * Array usage practice
 */
session_start();
//if (isset($_SESSION['schoolbook'])) {
//    unset($_SESSION['schoolbook']);
//}

require_once "classroom-helper.php";
require_once "classroom-html.php";

htmlHead();
$data = getData();
displayNav($data['classes']);
$selectedClass = '*';
if (isset($_POST['class-selector'])) {
    $selectedClass = $_POST['class-selector'];
}
displayClassSelector($data['classes'], $selectedClass);
displayExport('*');
if (empty($_SESSION['schoolbook'])) {
    $_SESSION['schoolbook'] = generateSchoolBook($data);
}
echo "Hello world";
handleRequest($data);

