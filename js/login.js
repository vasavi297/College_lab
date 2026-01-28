document.addEventListener('DOMContentLoaded', () => {
    const teacherAdminForm = document.getElementById('teacherAdminForm');
    const studentForm = document.getElementById('studentForm');

    // Simple validation and redirect for Teacher/Admin form
    teacherAdminForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const teacherUsername = document.getElementById('teacherUsername').value.trim();
        const teacherPassword = document.getElementById('teacherPassword').value.trim();

        if (teacherUsername === '' || teacherPassword === '') {
            alert('Please fill out both username and password.');
        } else {
            // For now, just a dummy redirect.
            window.location.href = '../html/dashboard.html';
        }
    });

    // Simple validation and redirect for Student form
    studentForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const studentRollNumber = document.getElementById('username').value.trim();
        const studentPassword = document.getElementById('password').value.trim();

        if (studentRollNumber === '' || studentPassword === '') {
            alert('Please fill out both roll number and password.');
        } else {
            
            window.location.href = 'login.php';
        }
    });
});