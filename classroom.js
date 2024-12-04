// $('button').on('click', function(){
//     $('button').removeClass('selected');
//     $(this).addClass('selected');
// });

// JavaScript function to toggle marks visibility
function toggleMarks(studentId) {

    const marksRow = document.getElementById(`marks-row-${studentId}`);
    const toggleIcon = document.getElementById(`toggle-icon-${studentId}`);
    marksRow.classList.toggle('hidden');

    // Update the icon based on visibility
    if (marksRow.classList.contains('hidden')) {
        toggleIcon.textContent = '[+]';
    } else {
        toggleIcon.textContent = '[-]';
    }
}

function toggleClass(className) {
    console.log(`class-${className}`);
    const studentsRow = document.getElementById(`class-${className}`);
    const toggleIcon = document.getElementById(`toggle-icon-${className}`);
    studentsRow.classList.toggle('hidden');
    toggleIcon.innerText = studentsRow.classList.contains('hidden') ? '[+]' : '[-]';
}

function toggleStudentMarks(studentId) {
    const marksRow = document.getElementById(`marks-row-${studentId}`);
    const toggleIcon = document.getElementById(`toggle-icon-${studentId}`);
    marksRow.classList.toggle('hidden');
    toggleIcon.innerText = marksRow.classList.contains('hidden') ? '[+]' : '[-]';
}