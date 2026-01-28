 let data = {
            students: [],
            teachers: [],
            courses: [],
            timetable: [],
            announcements: []
        };

        let editingItem = null;
        let editingType = null;

        // Navigation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                const section = this.dataset.section;
                
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(section).classList.add('active');
                
                const titles = {
                    dashboard: 'Dashboard Overview',
                    students: 'Student Management',
                    teachers: 'Teacher Management',
                    courses: 'Course Management',
                    timetable: 'Timetable Management',
                    reports: 'Reports',
                    announcements: 'Announcements'
                };
                
                document.getElementById('pageTitle').textContent = titles[section];
                
                if (window.innerWidth <= 768) {
                    document.getElementById('sidebar').classList.remove('mobile-visible');
                }

                if (section === 'timetable') {
                    renderTimetableGrid();
                }
            });
        });

        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('mobile-visible');
        });

        // Student Management
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const student = {
                id: Date.now(),
                name: document.getElementById('studentName').value,
                studentId: document.getElementById('studentId').value,
                email: document.getElementById('studentEmail').value,
                department: document.getElementById('studentDept').value,
                section: document.getElementById('studentSection').value
            };
            
            data.students.push(student);
            renderStudents();
            updateStats();
            this.reset();
        });

        function renderStudents(filter = '') {
            const tbody = document.getElementById('studentTableBody');
            const filtered = data.students.filter(s => 
                s.name.toLowerCase().includes(filter.toLowerCase()) ||
                s.studentId.toLowerCase().includes(filter.toLowerCase()) ||
                s.email.toLowerCase().includes(filter.toLowerCase()) ||
                s.department.toLowerCase().includes(filter.toLowerCase()) ||
                s.section.toLowerCase().includes(filter.toLowerCase())
            );
            
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No students found</td></tr>';
                return;
            }
            
            tbody.innerHTML = filtered.map(student => `
                <tr>
                    <td>${student.name}</td>
                    <td>${student.studentId}</td>
                    <td>${student.email}</td>
                    <td>${student.department}</td>
                    <td>${student.section}</td>
                    <td class="action-buttons">
                        <button class="btn btn-warning" onclick="editStudent(${student.id})">Edit</button>
                        <button class="btn btn-danger" onclick="deleteStudent(${student.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        function editStudent(id) {
            const student = data.students.find(s => s.id === id);
            editingItem = student;
            editingType = 'student';
            
            document.getElementById('modalTitle').textContent = 'Edit Student';
            document.getElementById('editFormFields').innerHTML = `
                <div class="form-group">
                    <label>Student Name *</label>
                    <input type="text" id="editName" value="${student.name}" required>
                </div>
                <div class="form-group">
                    <label>Student ID *</label>
                    <input type="text" id="editStudentId" value="${student.studentId}" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" id="editEmail" value="${student.email}" required>
                </div>
                <div class="form-group">
                    <label>Department *</label>
                    <select id="editDept" required>
                        <option value="CSE" ${student.department === 'CSE' ? 'selected' : ''}>Computer Science Engineering</option>
                        <option value="ECE" ${student.department === 'ECE' ? 'selected' : ''}>Electronics & Communication</option>
                        <option value="EEE" ${student.department === 'EEE' ? 'selected' : ''}>Electrical & Electronics</option>
                        <option value="MECH" ${student.department === 'MECH' ? 'selected' : ''}>Mechanical Engineering</option>
                        <option value="CIVIL" ${student.department === 'CIVIL' ? 'selected' : ''}>Civil Engineering</option>
                        <option value="IT" ${student.department === 'IT' ? 'selected' : ''}>Information Technology</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Section *</label>
                    <select id="editSection" required>
                        <option value="A" ${student.section === 'A' ? 'selected' : ''}>Section A</option>
                        <option value="B" ${student.section === 'B' ? 'selected' : ''}>Section B</option>
                        <option value="C" ${student.section === 'C' ? 'selected' : ''}>Section C</option>
                        <option value="D" ${student.section === 'D' ? 'selected' : ''}>Section D</option>
                    </select>
                </div>
            `;
            
            document.getElementById('editModal').classList.add('active');
        }

        function deleteStudent(id) {
            if (confirm('Are you sure you want to delete this student?')) {
                data.students = data.students.filter(s => s.id !== id);
                renderStudents();
                updateStats();
            }
        }

        document.getElementById('studentSearch').addEventListener('input', function(e) {
            renderStudents(e.target.value);
        });

        // Teacher Management
        document.getElementById('teacherForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const teacher = {
                id: Date.now(),
                name: document.getElementById('teacherName').value,
                employeeId: document.getElementById('teacherId').value,
                email: document.getElementById('teacherEmail').value,
                department: document.getElementById('teacherDept').value
            };
            
            data.teachers.push(teacher);
            renderTeachers();
            updateStats();
            this.reset();
        });

        function renderTeachers(filter = '') {
            const tbody = document.getElementById('teacherTableBody');
            const filtered = data.teachers.filter(t => 
                t.name.toLowerCase().includes(filter.toLowerCase()) ||
                t.employeeId.toLowerCase().includes(filter.toLowerCase()) ||
                t.email.toLowerCase().includes(filter.toLowerCase()) ||
                t.department.toLowerCase().includes(filter.toLowerCase())
            );
            
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No teachers found</td></tr>';
                return;
            }
            
            tbody.innerHTML = filtered.map(teacher => `
                <tr>
                    <td>${teacher.name}</td>
                    <td>${teacher.employeeId}</td>
                    <td>${teacher.email}</td>
                    <td>${teacher.department}</td>
                    <td class="action-buttons">
                        <button class="btn btn-warning" onclick="editTeacher(${teacher.id})">Edit</button>
                        <button class="btn btn-danger" onclick="deleteTeacher(${teacher.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        function editTeacher(id) {
            const teacher = data.teachers.find(t => t.id === id);
            editingItem = teacher;
            editingType = 'teacher';
            
            document.getElementById('modalTitle').textContent = 'Edit Teacher';
            document.getElementById('editFormFields').innerHTML = `
                <div class="form-group">
                    <label>Teacher Name *</label>
                    <input type="text" id="editName" value="${teacher.name}" required>
                </div>
                <div class="form-group">
                    <label>Employee ID *</label>
                    <input type="text" id="editEmployeeId" value="${teacher.employeeId}" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" id="editEmail" value="${teacher.email}" required>
                </div>
                <div class="form-group">
                    <label>Department *</label>
                    <select id="editDept" required>
                        <option value="CSE" ${teacher.department === 'CSE' ? 'selected' : ''}>Computer Science Engineering</option>
                        <option value="ECE" ${teacher.department === 'ECE' ? 'selected' : ''}>Electronics & Communication</option>
                        <option value="EEE" ${teacher.department === 'EEE' ? 'selected' : ''}>Electrical & Electronics</option>
                        <option value="MECH" ${teacher.department === 'MECH' ? 'selected' : ''}>Mechanical Engineering</option>
                        <option value="CIVIL" ${teacher.department === 'CIVIL' ? 'selected' : ''}>Civil Engineering</option>
                        <option value="IT" ${teacher.department === 'IT' ? 'selected' : ''}>Information Technology</option>
                    </select>
                </div>
            `;
            
            document.getElementById('editModal').classList.add('active');
        }

        function deleteTeacher(id) {
            if (confirm('Are you sure you want to delete this teacher?')) {
                data.teachers = data.teachers.filter(t => t.id !== id);
                renderTeachers();
                updateStats();
            }
        }

        document.getElementById('teacherSearch').addEventListener('input', function(e) {
            renderTeachers(e.target.value);
        });

        // Course Management
        document.getElementById('courseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const course = {
                id: Date.now(),
                name: document.getElementById('courseName').value,
                courseId: document.getElementById('courseId').value,
                department: document.getElementById('courseDept').value,
                credits: document.getElementById('courseCredits').value
            };
            
            data.courses.push(course);
            renderCourses();
            updateStats();
            this.reset();
        });

        function renderCourses(filter = '') {
            const tbody = document.getElementById('courseTableBody');
            const filtered = data.courses.filter(c => 
                c.name.toLowerCase().includes(filter.toLowerCase()) ||
                c.courseId.toLowerCase().includes(filter.toLowerCase()) ||
                c.department.toLowerCase().includes(filter.toLowerCase())
            );
            
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No courses found</td></tr>';
                return;
            }
            
            tbody.innerHTML = filtered.map(course => `
                <tr>
                    <td>${course.name}</td>
                    <td>${course.courseId}</td>
                    <td>${course.department}</td>
                    <td>${course.credits}</td>
                    <td class="action-buttons">
                        <button class="btn btn-warning" onclick="editCourse(${course.id})">Edit</button>
                        <button class="btn btn-danger" onclick="deleteCourse(${course.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        function editCourse(id) {
            const course = data.courses.find(c => c.id === id);
            editingItem = course;
            editingType = 'course';
            
            document.getElementById('modalTitle').textContent = 'Edit Course';
            document.getElementById('editFormFields').innerHTML = `
                <div class="form-group">
                    <label>Course Name *</label>
                    <input type="text" id="editName" value="${course.name}" required>
                </div>
                <div class="form-group">
                    <label>Course ID *</label>
                    <input type="text" id="editCourseId" value="${course.courseId}" required>
                </div>
                <div class="form-group">
                    <label>Department *</label>
                    <select id="editDept" required>
                        <option value="CSE" ${course.department === 'CSE' ? 'selected' : ''}>Computer Science Engineering</option>
                        <option value="ECE" ${course.department === 'ECE' ? 'selected' : ''}>Electronics & Communication</option>
                        <option value="EEE" ${course.department === 'EEE' ? 'selected' : ''}>Electrical & Electronics</option>
                        <option value="MECH" ${course.department === 'MECH' ? 'selected' : ''}>Mechanical Engineering</option>
                        <option value="CIVIL" ${course.department === 'CIVIL' ? 'selected' : ''}>Civil Engineering</option>
                        <option value="IT" ${course.department === 'IT' ? 'selected' : ''}>Information Technology</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Credits *</label>
                    <input type="number" id="editCredits" value="${course.credits}" min="1" max="10" required>
                </div>
            `;
            
            document.getElementById('editModal').classList.add('active');
        }

        function deleteCourse(id) {
            if (confirm('Are you sure you want to delete this course?')) {
                data.courses = data.courses.filter(c => c.id !== id);
                renderCourses();
                updateStats();
            }
        }

        document.getElementById('courseSearch').addEventListener('input', function(e) {
            renderCourses(e.target.value);
        });

        // Timetable Management
        document.getElementById('timetableForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const entry = {
                id: Date.now(),
                day: document.getElementById('timetableDay').value,
                time: document.getElementById('timetableTime').value,
                course: document.getElementById('timetableCourse').value,
                teacher: document.getElementById('timetableTeacher').value
            };
            
            data.timetable.push(entry);
            renderTimetableList();
            renderTimetableGrid();
            this.reset();
        });

        function renderTimetableGrid() {
            const grid = document.getElementById('timetableGrid');
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const times = ['09:30-10:30', '10:30-11:20', '11:20-12:10', '01:00-02:00', '02:00-02:50', '03:40-04:30'];
            
            let html = '<div class="timetable-cell header">Time/Day</div>';
            
            days.forEach(day => {
                html += `<div class="timetable-cell header">${day}</div>`;
            });
            
            times.forEach(time => {
                html += `<div class="timetable-cell time-header">${time}</div>`;
                
                // Check if this is lunch break time
                if (time === '01:00-02:00') {
                    days.forEach(() => {
                        html += `
                            <div class="timetable-cell" style="background: linear-gradient(135deg, #494846ff 0%, #e67e22 100%); text-align: center; align-items: center; justify-content: center; display: flex;">
                                <div style="color: white; font-weight: 600; font-size: 14px;">LUNCH BREAK</div>
                            </div>
                        `;
                    });
                } else {
                    days.forEach(day => {
                        const entry = data.timetable.find(e => e.day === day && e.time === time);
                        if (entry) {
                            html += `
                                <div class="timetable-cell">
                                    <div class="timetable-entry">
                                        <div class="timetable-course">${entry.course}</div>
                                        <div class="timetable-teacher">${entry.teacher}</div>
                                    </div>
                                </div>
                            `;
                        } else {
                            html += '<div class="timetable-cell"></div>';
                        }
                    });
                }
            });
            
            grid.innerHTML = html;
        }

        function renderTimetableList() {
            const tbody = document.getElementById('timetableTableBody');
            
            if (data.timetable.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No timetable entries yet</td></tr>';
                return;
            }
            
            const sorted = [...data.timetable].sort((a, b) => {
                const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                return days.indexOf(a.day) - days.indexOf(b.day) || a.time.localeCompare(b.time);
            });
            
            tbody.innerHTML = sorted.map(entry => `
                <tr>
                    <td>${entry.day}</td>
                    <td>${entry.time}</td>
                    <td>${entry.course}</td>
                    <td>${entry.teacher}</td>
                    <td class="action-buttons">
                        <button class="btn btn-warning" onclick="editTimetable(${entry.id})">Edit</button>
                        <button class="btn btn-danger" onclick="deleteTimetable(${entry.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        function editTimetable(id) {
            const entry = data.timetable.find(t => t.id === id);
            editingItem = entry;
            editingType = 'timetable';
            
            document.getElementById('modalTitle').textContent = 'Edit Timetable Entry';
            document.getElementById('editFormFields').innerHTML = `
                <div class="form-group">
                    <label>Day *</label>
                    <select id="editDay" required>
                        <option value="Monday" ${entry.day === 'Monday' ? 'selected' : ''}>Monday</option>
                        <option value="Tuesday" ${entry.day === 'Tuesday' ? 'selected' : ''}>Tuesday</option>
                        <option value="Wednesday" ${entry.day === 'Wednesday' ? 'selected' : ''}>Wednesday</option>
                        <option value="Thursday" ${entry.day === 'Thursday' ? 'selected' : ''}>Thursday</option>
                        <option value="Friday" ${entry.day === 'Friday' ? 'selected' : ''}>Friday</option>
                        <option value="Saturday" ${entry.day === 'Saturday' ? 'selected' : ''}>Saturday</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Time *</label>
                    <select id="editTime" required>
                        <option value="09:30-10:30" ${entry.time === '09:30-10:30' ? 'selected' : ''}>09:30-10:30</option>
                        <option value="10:30-11:20" ${entry.time === '10:30-11:20' ? 'selected' : ''}>10:30-11:20</option>
                        <option value="11:20-12:10" ${entry.time === '11:20-12:10' ? 'selected' : ''}>11:20-12:10</option>
                        <option value="02:00-02:50" ${entry.time === '02:00-02:50' ? 'selected' : ''}>02:00-02:50</option>
                        <option value="03:40-04:30" ${entry.time === '03:40-04:30' ? 'selected' : ''}>03:40-04:30</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Course *</label>
                    <input type="text" id="editCourse" value="${entry.course}" required>
                </div>
                <div class="form-group">
                    <label>Teacher *</label>
                    <input type="text" id="editTeacher" value="${entry.teacher}" required>
                </div>
            `;
            
            document.getElementById('editModal').classList.add('active');
        }

        function deleteTimetable(id) {
            if (confirm('Are you sure you want to delete this timetable entry?')) {
                data.timetable = data.timetable.filter(t => t.id !== id);
                renderTimetableList();
                renderTimetableGrid();
            }
        }

        // Announcements
        document.getElementById('announcementForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const announcement = {
                id: Date.now(),
                title: document.getElementById('announcementTitle').value,
                description: document.getElementById('announcementDesc').value,
                date: new Date().toLocaleDateString()
            };
            
            data.announcements.unshift(announcement);
            renderAnnouncements();
            updateStats();
            this.reset();
        });

        function renderAnnouncements() {
            const container = document.getElementById('announcementList');
            
            if (data.announcements.length === 0) {
                container.innerHTML = '<div class="empty-state">No announcements yet</div>';
                return;
            }
            
            container.innerHTML = data.announcements.map(ann => `
                <div class="announcement-item">
                    <div class="announcement-header">
                        <div>
                            <div class="announcement-title">${ann.title}</div>
                            <div class="announcement-date">${ann.date}</div>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-warning" onclick="editAnnouncement(${ann.id})">Edit</button>
                            <button class="btn btn-danger" onclick="deleteAnnouncement(${ann.id})">Delete</button>
                        </div>
                    </div>
                    <div class="announcement-description">${ann.description}</div>
                </div>
            `).join('');
        }

        function editAnnouncement(id) {
            const ann = data.announcements.find(a => a.id === id);
            editingItem = ann;
            editingType = 'announcement';
            
            document.getElementById('modalTitle').textContent = 'Edit Announcement';
            document.getElementById('editFormFields').innerHTML = `
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" id="editTitle" value="${ann.title}" required>
                </div>
                <div class="form-group">
                    <label>Description *</label>
                    <textarea id="editDesc" required>${ann.description}</textarea>
                </div>
            `;
            
            document.getElementById('editModal').classList.add('active');
        }

        function deleteAnnouncement(id) {
            if (confirm('Are you sure you want to delete this announcement?')) {
                data.announcements = data.announcements.filter(a => a.id !== id);
                renderAnnouncements();
                updateStats();
            }
        }

        // Modal Functions
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (editingType === 'student') {
                editingItem.name = document.getElementById('editName').value;
                editingItem.studentId = document.getElementById('editStudentId').value;
                editingItem.email = document.getElementById('editEmail').value;
                editingItem.department = document.getElementById('editDept').value;
                editingItem.section = document.getElementById('editSection').value;
                renderStudents();
            } else if (editingType === 'teacher') {
                editingItem.name = document.getElementById('editName').value;
                editingItem.employeeId = document.getElementById('editEmployeeId').value;
                editingItem.email = document.getElementById('editEmail').value;
                editingItem.department = document.getElementById('editDept').value;
                renderTeachers();
            } else if (editingType === 'course') {
                editingItem.name = document.getElementById('editName').value;
                editingItem.courseId = document.getElementById('editCourseId').value;
                editingItem.department = document.getElementById('editDept').value;
                editingItem.credits = document.getElementById('editCredits').value;
                renderCourses();
            } else if (editingType === 'timetable') {
                editingItem.day = document.getElementById('editDay').value;
                editingItem.time = document.getElementById('editTime').value;
                editingItem.course = document.getElementById('editCourse').value;
                editingItem.teacher = document.getElementById('editTeacher').value;
                renderTimetableList();
                renderTimetableGrid();
            } else if (editingType === 'announcement') {
                editingItem.title = document.getElementById('editTitle').value;
                editingItem.description = document.getElementById('editDesc').value;
                renderAnnouncements();
            }
            
            closeModal();
        });

        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
            editingItem = null;
            editingType = null;
        }

        // Reports
        function updateReportFilters() {
            const reportType = document.getElementById('reportType').value;
            const deptFilter = document.getElementById('departmentFilter');
            const sectFilter = document.getElementById('sectionFilter');
            
            document.getElementById('reportContainer').style.display = 'none';
            
            if (reportType === 'students' || reportType === 'marks' || reportType === 'attendance') {
                deptFilter.style.display = 'block';
                sectFilter.style.display = 'block';
            } else if (reportType === 'teachers') {
                deptFilter.style.display = 'block';
                sectFilter.style.display = 'none';
            } else {
                deptFilter.style.display = 'none';
                sectFilter.style.display = 'none';
            }
        }

        function generateReport() {
            const reportType = document.getElementById('reportType').value;
            const department = document.getElementById('filterDepartment').value;
            const section = document.getElementById('filterSection').value;
            const container = document.getElementById('reportContainer');
            const content = document.getElementById('reportContent');
            
            if (!reportType) {
                alert('Please select a report type');
                return;
            }
            
            container.style.display = 'block';
            
            if (reportType === 'students') {
                if (!department || !section) {
                    alert('Please select both department and section');
                    return;
                }
                
                const filtered = data.students.filter(s => 
                    s.department === department && s.section === section
                );
                
                document.getElementById('reportTitle').textContent = 
                    `Student List Report - ${department} Section ${section}`;
                
                content.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Student ID</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Section</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${filtered.length > 0 ? filtered.map(s => `
                                <tr>
                                    <td>${s.name}</td>
                                    <td>${s.studentId}</td>
                                    <td>${s.email}</td>
                                    <td>${s.department}</td>
                                    <td>${s.section}</td>
                                </tr>
                            `).join('') : '<tr><td colspan="5" class="empty-state">No students found for the selected filters</td></tr>'}
                        </tbody>
                    </table>
                `;
            } else if (reportType === 'teachers') {
                if (!department) {
                    alert('Please select a department');
                    return;
                }
                
                const filtered = data.teachers.filter(t => t.department === department);
                
                document.getElementById('reportTitle').textContent = 
                    `Teacher List Report - ${department} Department`;
                
                content.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Employee ID</th>
                                <th>Email</th>
                                <th>Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${filtered.length > 0 ? filtered.map(t => `
                                <tr>
                                    <td>${t.name}</td>
                                    <td>${t.employeeId}</td>
                                    <td>${t.email}</td>
                                    <td>${t.department}</td>
                                </tr>
                            `).join('') : '<tr><td colspan="4" class="empty-state">No teachers found for the selected department</td></tr>'}
                        </tbody>
                    </table>
                `;
            } else if (reportType === 'courses') {
                document.getElementById('reportTitle').textContent = 'Course List Report';
                
                content.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Course ID</th>
                                <th>Department</th>
                                <th>Credits</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.courses.length > 0 ? data.courses.map(c => `
                                <tr>
                                    <td>${c.name}</td>
                                    <td>${c.courseId}</td>
                                    <td>${c.department}</td>
                                    <td>${c.credits}</td>
                                </tr>
                            `).join('') : '<tr><td colspan="4" class="empty-state">No courses available</td></tr>'}
                        </tbody>
                    </table>
                `;
            } else if (reportType === 'marks') {
                if (!department || !section) {
                    alert('Please select both department and section');
                    return;
                }
                
                const filtered = data.students.filter(s => 
                    s.department === department && s.section === section
                );
                
                document.getElementById('reportTitle').textContent = 
                    `Marks Report - ${department} Section ${section}`;
                
                content.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Subject 1</th>
                                <th>Subject 2</th>
                                <th>Subject 3</th>
                                <th>Total</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${filtered.length > 0 ? filtered.map(s => {
                                const marks1 = Math.floor(Math.random() * 40) + 60;
                                const marks2 = Math.floor(Math.random() * 40) + 60;
                                const marks3 = Math.floor(Math.random() * 40) + 60;
                                const total = marks1 + marks2 + marks3;
                                const percentage = (total / 3).toFixed(2);
                                return `
                                    <tr>
                                        <td>${s.name}</td>
                                        <td>${s.studentId}</td>
                                        <td>${marks1}</td>
                                        <td>${marks2}</td>
                                        <td>${marks3}</td>
                                        <td>${total}</td>
                                        <td>${percentage}%</td>
                                    </tr>
                                `;
                            }).join('') : '<tr><td colspan="7" class="empty-state">No students found for the selected filters</td></tr>'}
                        </tbody>
                    </table>
                    <p style="margin-top: 15px; color: #7f8c8d; font-size: 14px;"><em>Note: Sample marks data shown for demonstration</em></p>
                `;
            } else if (reportType === 'attendance') {
                if (!department || !section) {
                    alert('Please select both department and section');
                    return;
                }
                
                const filtered = data.students.filter(s => 
                    s.department === department && s.section === section
                );
                
                document.getElementById('reportTitle').textContent = 
                    `Attendance Report - ${department} Section ${section}`;
                
                content.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Total Classes</th>
                                <th>Classes Attended</th>
                                <th>Attendance %</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${filtered.length > 0 ? filtered.map(s => {
                                const totalClasses = 100;
                                const attended = Math.floor(Math.random() * 30) + 70;
                                const percentage = attended;
                                const status = percentage >= 75 ? 'Good' : 'Low';
                                return `
                                    <tr>
                                        <td>${s.name}</td>
                                        <td>${s.studentId}</td>
                                        <td>${totalClasses}</td>
                                        <td>${attended}</td>
                                        <td>${percentage}%</td>
                                        <td style="color: ${status === 'Good' ? '#2ecc71' : '#e74c3c'}; font-weight: 600;">${status}</td>
                                    </tr>
                                `;
                            }).join('') : '<tr><td colspan="6" class="empty-state">No students found for the selected filters</td></tr>'}
                        </tbody>
                    </table>
                    <p style="margin-top: 15px; color: #7f8c8d; font-size: 14px;"><em>Note: Sample attendance data shown for demonstration</em></p>
                `;
            }
        }

        function exportReport() {
            const reportType = document.getElementById('reportType').value;
            const department = document.getElementById('filterDepartment').value;
            const section = document.getElementById('filterSection').value;
            let csvContent = '';
            
            if (reportType === 'students') {
                const filtered = data.students.filter(s => 
                    s.department === department && s.section === section
                );
                csvContent = 'Name,Student ID,Email,Department,Section\n';
                csvContent += filtered.map(s => 
                    `"${s.name}","${s.studentId}","${s.email}","${s.department}","${s.section}"`
                ).join('\n');
            } else if (reportType === 'teachers') {
                const filtered = data.teachers.filter(t => t.department === department);
                csvContent = 'Name,Employee ID,Email,Department\n';
                csvContent += filtered.map(t => 
                    `"${t.name}","${t.employeeId}","${t.email}","${t.department}"`
                ).join('\n');
            } else if (reportType === 'courses') {
                csvContent = 'Course Name,Course ID,Department,Credits\n';
                csvContent += data.courses.map(c => 
                    `"${c.name}","${c.courseId}","${c.department}","${c.credits}"`
                ).join('\n');
            } else if (reportType === 'marks') {
                const filtered = data.students.filter(s => 
                    s.department === department && s.section === section
                );
                csvContent = 'Student Name,Student ID,Subject 1,Subject 2,Subject 3,Total,Percentage\n';
                csvContent += filtered.map(s => {
                    const marks1 = Math.floor(Math.random() * 40) + 60;
                    const marks2 = Math.floor(Math.random() * 40) + 60;
                    const marks3 = Math.floor(Math.random() * 40) + 60;
                    const total = marks1 + marks2 + marks3;
                    const percentage = (total / 3).toFixed(2);
                    return `"${s.name}","${s.studentId}","${marks1}","${marks2}","${marks3}","${total}","${percentage}%"`;
                }).join('\n');
            } else if (reportType === 'attendance') {
                const filtered = data.students.filter(s => 
                    s.department === department && s.section === section
                );
                csvContent = 'Student Name,Student ID,Total Classes,Classes Attended,Attendance %,Status\n';
                csvContent += filtered.map(s => {
                    const totalClasses = 100;
                    const attended = Math.floor(Math.random() * 30) + 70;
                    const percentage = attended;
                    const status = percentage >= 75 ? 'Good' : 'Low';
                    return `"${s.name}","${s.studentId}","${totalClasses}","${attended}","${percentage}%","${status}"`;
                }).join('\n');
            }
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${reportType}_report_${Date.now()}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Table Sorting
        function sortTable(type, column) {
            if (type === 'students') {
                const keys = ['name', 'studentId', 'email', 'department', 'section'];
                data.students.sort((a, b) => {
                    const aVal = a[keys[column]] || '';
                    const bVal = b[keys[column]] || '';
                    return aVal.toString().localeCompare(bVal.toString());
                });
                renderStudents();
            } else if (type === 'teachers') {
                const keys = ['name', 'employeeId', 'email', 'department'];
                data.teachers.sort((a, b) => {
                    const aVal = a[keys[column]] || '';
                    const bVal = b[keys[column]] || '';
                    return aVal.toString().localeCompare(bVal.toString());
                });
                renderTeachers();
            } else if (type === 'courses') {
                const keys = ['name', 'courseId', 'department', 'credits'];
                data.courses.sort((a, b) => {
                    const aVal = a[keys[column]] || '';
                    const bVal = b[keys[column]] || '';
                    return aVal.toString().localeCompare(bVal.toString());
                });
                renderCourses();
            }
        }

        // Update Statistics
        function updateStats() {
            document.getElementById('totalStudents').textContent = data.students.length;
            document.getElementById('totalTeachers').textContent = data.teachers.length;
            document.getElementById('totalCourses').textContent = data.courses.length;
            document.getElementById('totalAnnouncements').textContent = data.announcements.length;
        }

        // Initialize
        updateStats();
        renderTimetableGrid();