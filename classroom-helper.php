<?php
/**
 * @author Kovács László
 *
 * Array usage practice
 */

require_once "classroom-data.php";

/**
 * @return array
 */
function getData(): array
{
    return DATA;
}

function getClasses(): array
{
    return DATA["classes"];
}

function getSchoolBook()
{
    if (isset($_SESSION['schoolbook'])) {
        return $_SESSION['schoolbook'];
    }
    $schoolBook = generateSchoolBook(DATA);
    $_SESSION['schoolbook'] = $schoolBook;

    return $schoolBook;
}

/**
 * @return void
 */
function handleRequest($data)
{
    $request = $_SERVER['REQUEST_METHOD'];
    switch ($request) {
        case 'POST': handlePostRequests($data);
        break;

        case 'GET': handleGetRequests();
        break;
    }
}

/**
 * @return void
 */
function handlePostRequests($data)
{
    if (isset($_POST['submit-class-btn'])) {
        $selectedClass = '*';
        if (isset($_POST['class-selector'])) {
            $selectedClass = $_POST['class-selector'];
        }
        else if ($_POST['submit-class-btn']) {
            $selectedClass = $_POST['submit-class-btn'];
        }

        $schoolBook = getSchoolBook();
        showSelectedClass($schoolBook, $selectedClass);
    }
    if (isset($_POST['submit-queries-btn'])) {
        displayQueriesSubmenu();
    }
}

/**
 * @return void
 */
function handleGetRequests()
{
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        if ($action == 'export') {
            if (empty($_GET['class'])) {
                throw new Exception("No class selected");
            }
            if ($_GET['class'] == '*') {
                $classes = getClasses();
                foreach ($classes as $class) {
                    exportClassData($class);
                    echo "<div class='success'>$class osztály adatai sikeresen elmentve.</div>";
                }

                return;
            }
            $class = $_GET['class'];
            exportClassData($class);
            echo "<div class='success'>$class osztály adatai sikeresen elmentve.</div>";
        }
    }
    if (isset($_GET['query'])) {
        $query = $_GET['query'];
        switch ($query) {
            case 'subjects-avg':
                displaySubjectsAvg(getSubjectsAvg());
            break;
            case 'students-order':
                displayStudentsOrderBySchool(getStudentsOrderBySchool());
            break;
            case 'students-order-by-class':
                $class = $_GET['class'];
                if ($class == '*') {
                    displayStudentsOrderByClasses(getStudentsOrderByClasses());
                    break;
                }
                displayStudentsOrderByClass(getStudentsOrderByClass($class));
                break;
            case 'classes-order':
                displayClassesOrder(getClassesOrder());
            break;
        }
    }
}

function getStudentsOrderByClass(mixed $class)
{
    $students = getStudentsOrderBySchool();
    $result[$class] = [];
    foreach ($students as $name => $details) {
        if ($details['class'] == $class) {
            $result[$class][$name] = ['class' => $details['class'], 'avg' => $details['avg']];
        }
    }

    return $result;
}

function getStudentsOrderByClasses()
{
        $result = [];
        foreach (DATA["classes"] as $class) {
            $result[$class] = getStudentsOrderByClass($class);
        }

        return $result;
}

/**
 * @param array $data
 * @param string $class
 * @return array
 */
function generateStudents(array $data, string $class): array
{
    $students = [];
    $count = rand(10, 15);
    for ($i = 0; $i < $count; $i++) {
        $lastname = $data['lastnames'][rand(0, count($data['lastnames'])-1)];
        $gender = rand(1, 2);
        if ($gender == 1) {
            $firstname = $data['firstnames']['men'][rand(0, count($data['firstnames']['men'])-1)];
        }
        else {
            $firstname = $data['firstnames']['women'][rand(0, count($data['firstnames']['women'])-1)];
        }
        $subjects = $data['subjects'];
        sort($subjects);
        $students[] = [
            'id' => "$class-$i",
            'name' => "$lastname $firstname",
            'firstname' => $firstname,
            'lastname' => $lastname,
            'gender' => $gender,
//            'class' => $class,
            'marks' => generateMarks($subjects),
        ];
    }
    usort($students, function ($a, $b) {
        // Compare lastnames first
        $lastnameComparison = strcmp($a['lastname'], $b['lastname']);
        if ($lastnameComparison !== 0) {
            return $lastnameComparison;
        }
        // If lastnames are equal, compare firstnames
        return strcmp($a['firstname'], $b['firstname']);
    });

    return $students;
}

/**
 * @param array $data
 * @return array
 */
function generateSchoolBook(array $data)
{
    $schoolBook = [];
    foreach ($data['classes'] as $class) {
        $schoolBook[$class] = generateStudents($data, $class);
    }

    return $schoolBook;
}

/**
 * @param array $subjects
 * @return array
 */
function generateMarks(array $subjects): array
{
    $result = [];
    foreach ($subjects as $subject) {
        $count = rand(0, 5);
        $marks = [];
        for ($i = 0; $i < $count; $i++) {
            $marks[] = rand(1, MARKS_COUNT);
        }
        $result[$subject] = $marks;
    }

    return $result;
}

/**
 * @param array $schoolBook
 * @param string $selectedClass
 * @return array
 */
function getClassStudents(array $schoolBook, string $selectedClass): array
{
    foreach ($schoolBook as $class => $students) {
        if ($class == $selectedClass) {
            return $students;
        }
    }

    return [];
}

function exportClassData(string $class)
{

    $exportDir = 'export';
    if ( !file_exists( $exportDir ) || !is_dir( $exportDir ) ) {
        mkdir( $exportDir );
    }
    $file = fopen("$exportDir/$class.csv", "w");
    // Define headers for the structure
    $header = ['ID', 'Name', 'Firstname', 'Lastname', 'Gender', 'Subject', 'Marks'];
    fputcsv($file, $header, ';'); // Using ';' as delimiter

    // Loop through each student
    $students = $_SESSION['schoolbook'][$class];
    foreach ($students as $student) {
        $id = $student['id'];
        $name = $student['name'];
        $firstname = $student['firstname'];
        $lastname = $student['lastname'];
        $gender = $student['gender'];

        // Loop through each subject and its marks
        foreach ($student['marks'] as $subject => $marks) {
            $marksString = implode(',', $marks); // Combine marks with commas
            $row = [$id, $name, $firstname, $lastname, $gender, $subject, $marksString];

            // Write the row to the CSV file
            fputcsv($file, $row, ';'); // Use ';' as delimiter
        }
    }
    // Close the file
    fclose($file);
}

function getAvg(array $marks)
{
    if (empty($marks)) {
        return 0;
    }
    $sum = 0;
    foreach ($marks as $mark) {
        $sum += $mark;
    }

    return round($sum / count($marks), 2);
}

function getStudentsAvg()
{
    $result = [];
    $schoolbook = getSchoolBook();
    foreach ($schoolbook as $class => $students) {
        foreach ($students as $student) {
            $result[$class][$student['name']]['id'] = $student['id'];
            $result[$class][$student['name']]['avg'] = getStudentAvg($student);
            foreach ($student['marks'] as $subject => $marks) {
                $result[$class][$student['name']][$subject] = getAvg($marks);
            }
        }
    }

    return $result;
}

function getStudentAvg(array $student)
{
    $marks = $student['marks'];
    if (empty($marks)) {
        return 0;
    }

    $avg = 0;
    foreach ($marks as $subject => $items) {
        $avg += getAvg($items);
    }

    return round($avg / count($marks), 2);
}

function getStudentsOrderBySchool()
{
    $schoolbook = getSchoolBook();
    $result = [];
    foreach ($schoolbook as $class => $students) {
        foreach ($students as $student) {
            $result[$student['name']]['avg'] = getStudentAvg($student);
            $result[$student['name']]['class'] = $class;
        }
    }

    uasort($result, function ($a, $b) {
        // descending order
        return $b['avg'] <=> $a['avg'];
    });

    return $result;
}

function getSubjectsAvg($classFilter = "*")
{
    $schoolBook = getSchoolBook();
    $helper = [];
    foreach ($schoolBook as $class => $students) {
//        if ($classFilter == $class || $classFilter == "*") {
            foreach ($students as $student) {
                foreach ($student['marks'] as $subject => $marks) {
                    foreach ($marks as $mark) {
                        $helper[$class][$subject][] = $mark;
                    }
                }
            }
//        }
    }
    $result = [];
    foreach ($helper as $class => $subjects) {
        foreach ($subjects as $subject => $marks) {
            $result[$class][$subject] = getAvg($marks);
        }
    }

    if (isset($result[$classFilter])) {
        return $result[$classFilter];
    }

    return $result;
}

function getClassesOrder()
{

}