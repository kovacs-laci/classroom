<?php
function htmlHead() {
    echo '
    <!doctype html>
    <html lang="hu-hu">
    <head>
        <meta charset="utf-8">
        <title>Osztálynapló</title>
        <!-- Scripts -->
        <script src="classroom.js" type="text/javascript"></script>
        <!-- Styles -->
        <link href="classroom.css" rel="stylesheet" type="text/css">
        <!-- Icons -->
        <!-- 
        <link rel="icon" type="image/x-icon" href="favicon.ico">
        -->
    </head>';
}

/**
 * @param array $classes
 * @return void
 *
 * Showing navigation buttons with name provided in $classes
 */
function displayNav(array $classes, $activeBtn = '')
{
    echo "<nav>";
    showClassroomBtn('*', false);
    foreach ($classes as $class) {
        showClassroomBtn($class, $class == $activeBtn);
    }
    displayQueriesBtn('*');
    echo "</nav>";
}

function displayQueriesSubmenu()
{
    /*
     * tantárgyi átlagok iskola és osztály szinten
tanulók rangsorolása iskolai és osztály szinten, tantárgyanként és összesítve, kiemelve a 3 legjobb és a 3 leggyengébb tanulót
tantárgyi átlagok tanuló szinten (ezt lehet a tanuló osztályzatai mellett is megjeleníteni
a legjobb és a leggyengébb osztály összesen és tantárgyanként
     */
    echo "<nav><ul>";
        echo "<li>Tantárgyi átlagok";
            echo "<ul>";
                echo "<li><a href='classroom.php?query=subjects-avg'>Iskolai</a></li>";
                echo "<li><a href='classroom.php?query=subjects-avg&class=*'>Osztályok szerint</a></li>";
            echo "</ul>";
        echo "</li>";
        echo "<li>Tanulók rangsora
                <ul>";
                echo "<li><a href='classroom.php?query=students-order'>Iskolai</a></li>";
                echo "<li><a href='classroom.php?query=students-order-by-class&class=*'>Osztályok szerint</a></li>";
                foreach (DATA['classes'] as $class) {
                    echo "<li><a href='classroom.php?query=students-order-by-class&class=$class'>$class</a></li>";
                }
            echo
                "</ul>
            </li>";
        echo "<li>Osztályok rangsora
                <ul>";
                echo "<li><a href='classroom.php?query=classes-order'>Iskolai</a></li>";
                echo "<li><a href='classroom.php?query=classes-order-by-subjects'>Tantárgyak szerint</a></li>";
        echo "        </ul>
            </li>";
    echo "</ul></nav>";
}
/**
 * @param string $class
 * @param bool $isActive
 * @return void
 *
 * Showing a button with name $class in a submit form.
 * If $isActive true then the button will have active style.
 */
function showClassroomBtn(string $class)
{
    echo "
        <form name='nav' method='post' action='classroom.php'>
            <button type='submit' name='submit-class-btn' value='$class'>$class</button>
        </form>
    ";
}
function displayQueriesBtn($className)
{
    echo "
        <form name='nav' method='post' action='classroom.php'>
            <button type='submit' name='submit-queries-btn' value='$className'>Lekérdezések</button>
        </form>
    ";
}

function showDropDown($options, $name, $selectedOption = '*', $title = '')
{
    echo "
        <select name='$name' id='$name' title='$title'>
            <option value='*'>-- Válassz --</option>";
        foreach ($options as $option) {
            $selected = '';
            if ($option == $selectedOption) {
                $selected = 'selected';
            }
            echo "<option value='$option' $selected>$option</option>";
        }
        echo "</select>";
}

function displayClassSelector($classes, $selectedClass = '*')
{
    echo "<form name='form-classes' id='form-classes' method='post' action='classroom.php'>";
    showDropDown($classes, 'class-selector', $selectedClass, 'Osztályok');
    echo "<button type='submit' name='submit-class-btn'>OK</button>";
    echo "</form>";
}

function displayExport($className)
{
    echo "<a href='classroom.php?action=export&class=$className'>Export</a>";
}
function displayStudents(array $students)
{
    echo
    "<td colspan='9'>
        <table>";
            foreach ($students as $student) {
                displayStudent($student);
            }
        echo
        "</table>
    </td>";
}

function displayStudentMarks(array $student)
{
        $marks = $student['marks'];
        echo "<table>";
        echo "<thead>
                <tr class='upperline subjects-header'>
                    <th>Tantárgyak</th><th colspan='5'>Osztályzatok</th><th>Átlag</th>
                </tr>
            </thead>
            <tbody>";
        $row = 0;
        $total = 0;
        $totalCount = 0;
        foreach ($marks as $subject => $items) {
            $row++;
            $class = 'odd';
            if ($row % 2 == 0) {
                $class = 'even';
            }
            echo "<tr class='student-mark $class'>";
                echo "<td>{$subject}</td>";
                for ($i = 0; $i < max(count($items), MARKS_COUNT); $i++) {
                    $mark = '&nbsp;';
                    if (isset($items[$i])) {
                        $mark = $items[$i];
                    }
                    echo "<td class='mark'>{$mark}</td>";

                }
                $avg = getAvg($items);
                if (empty($avg)) {
                    $avg = '&nbsp;';
                }
                echo "<td class='subject-avg'>{$avg}</td>";
            echo "</tr>";

        }
        $studentAvg = getStudentAvg($student);
        if (empty($studentAvg)) {
            $studentAvg = '&nbsp;';
        }
        echo "<tr class='student-avg'>
                    <td colspan='6'>Átlag</td>
                    <td>$studentAvg</td>
                </tr>";
        echo "</tbody></table>";
}

function showSelectedClass(array $schoolBook, string $selectedClass)
{
    if ($selectedClass == '*') {
        displaySchoolbook($schoolBook);
    }
    else {
        $students = getClassStudents($schoolBook, $selectedClass);
        displayClassRow($selectedClass, true);
        displayStudents($students);
    }
}

function displayClassRow(string $className, $isOpen = false)
{
    $toggleIcon = $isOpen ? '[-]' : '[+]';
    echo "
        <tr class='class-row'>
            <td class='toggle-icon' id='toggle-icon-$className' onclick='toggleClass(\"$className\")'>$toggleIcon</td>
            <td colspan='6'>$className</td>
            <td>";
            displayExport($className);
        echo "</td>
            <td>";

        echo "</td>
        </tr>";
}

function displayStudent(array $student)
{
    $studentId = $student['id'];
    $gender = $student['gender'] == 1 ? 'F' : 'L';
    echo "
        <tr class='student-row' onclick='toggleStudentMarks(\"$studentId\")'>
            <td colspan='2' class='toggle-icon' id='toggle-icon-$studentId'>[+]</td>
            <td colspan='6'>{$student['lastname']} {$student['firstname']}</td>
            <td>$gender</td>
        </tr>";
    echo
        "<tr id='marks-row-$studentId' class='marks-row hidden'>
            <td colspan='2'></td>
                <td colspan='7'>";
                    displayStudentMarks($student);
                    echo
                "</td>
            </td>
        </tr>";
}

function displaySchoolbookHeader()
{
    echo "<thead>
            <tr class='schoolbook-header'>
                <th colspan='2'>Osztály</th><th colspan='6'>Név</th><th>Nem</th>   
            </tr>
        </thead>";
}
function displaySchoolbook(array $classes)
{
    echo "<table id='schoolbook-table'>";
    displaySchoolbookHeader();
    echo"
        <tbody>";
        foreach ($classes as $className => $students) {
            displayClassRow($className);
            // Container for students, initially hidden
            echo "
            <tr id='class-$className' class='student-rows hidden'>";
                displayStudents($students);
            echo
            "</tr>";
        }
    echo "</tbody></table>";
}

function displayStudentsOrderBySchoolHeader()
{
    echo "<thead>";
        echo "<tr>";
        echo "<th colspan='4'>Diákok iskolai rangsora</th>";
        echo "</tr>";
        echo "<tr>";
            echo "<th>#</th><th>Név</th><th>Osztály</th><th>Átlag</th>";
        echo "</tr>";
    echo "</thead>";
}
function displayStudentsOrderBySchool(array $data)
{
    echo "<table id='students-order-table'>";
    displayStudentsOrderBySchoolHeader();
    echo "<tbody>";
        $i = 0;
        foreach ($data as $student => $details) {
            $i++;
            echo "<tr>";
                echo "<td>$i.</td><td>{$student}</td><td>{$details['class']}</td><td>{$details['avg']}</td>";
            echo "</tr>";
        }
    echo "</tbody></table>";
}

function displayStudentsOrderByClass(array $data)
{
    echo "<table id='students-order-table'>";

    echo "<tbody>";
    $i = 0;
    foreach ($data as $class => $students) {
        displayStudentsOrderByClassHeader($class);
        foreach ($students as $student => $details) {
            $i++;
            echo "<tr>";
            echo "<td>$i.</td><td>{$student}</td><td>{$details['avg']}</td>";
            echo "</tr>";
        }
    }
    echo "</tbody></table>";
}

function displayStudentsOrderByClasses(array $data)
{
    echo "<table id='students-order-table'>";
    echo "<tbody>";
    foreach ($data as $classes) {
        foreach ($classes as $class => $students) {
            displayStudentsOrderByClassHeader($class);
            $i = 0;
            foreach ($students as $student => $details) {
                $i++;
                echo "<tr>";
                echo "<td>$i.</td><td>{$student}</td><td>{$details['avg']}</td>";
                echo "</tr>";
            }
        }
    }
    echo "</tbody></table>";
}


function displayStudentsOrderByClassHeader($class)
{
    echo "<thead>";
    echo "<tr>";
    echo "<th colspan='3'>$class osztály rangsora</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<th>#</th><th>Név</th><th>Átlag</th>";
    echo "</tr>";
    echo "</thead>";
}

function displaySubjectsAvg($subjectsAvg)
{
    echo "<table id='subjects-avg-table'>";
    echo "<thead><tr><th>Tantárgy</th><th>Átlag</th></tr></thead>";
    echo "<tbody>";
    $sum = 0;
    foreach ($subjectsAvg as $subject => $avg) {
        echo "<tr>";
        echo "<td>{$subject}</td><td>{$avg}</td>";
        echo "</tr>";
        $sum += $avg;
    }
    $totalAvg = 0;
    if (count($subjectsAvg) > 0) {
        $totalAvg = $sum / count($subjectsAvg);
    }
    echo "<tr><td>Átlag</td><td>$totalAvg</td></tr>";
    echo "</tbody></table>";
}

function displayClassesOrder(array $data)
{

}
