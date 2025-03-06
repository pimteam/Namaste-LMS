# Namaste! LMS

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Namaste! LMS is a Learning Management System for WordPress. It supports an unlimited number of courses, lessons, assignments, students, and more.

## Key Features

- Unlimited courses, lessons, assignments, and students.
- Create various rules for course and lesson access based on assignment completion, test results, or manual admin approval.
- Different user roles to work with the LMS and manage it.
- Students can earn certificates upon completing courses.
- Integration with PayPal and Stripe for payments.

For a quick tour and more detailed help, visit [namaste-lms.org](http://namaste-lms.org).

## Installation

1. Unzip the contents and upload the entire `namaste` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to "Namaste LMS" in your menu and manage the plugin.

## Frequently Asked Questions

None yet, please ask in the forum.

## Screenshots

## Screenshots

### **Create/Edit Course**
![Create/Edit Course](https://namaste-lms.org/img/scr/screenshot-1.png)
Courses are custom post types and support rich formatting, categorization, etc. The course page is a presentation page about the course content.

---

### **Create/Edit Lesson**
![Create/Edit Lesson](https://namaste-lms.org/img/scr/screenshot-2.png)
Lessons are custom post types and support rich formatting and all kinds of categorization. There are various rules for lesson access and completion.

---

### **Manage Assignments/Homework**
![Manage Assignments/Homework](https://namaste-lms.org/img/scr/screenshot-3.png)
Manage assignments and homework for students.

---

### **Student Enrollments**
![Student Enrollments](https://namaste-lms.org/img/scr/screenshot-4.png)
View student enrollments in a course.

---

### **Student Progress**
![Student Progress](https://namaste-lms.org/img/scr/screenshot-5.png)
Track the progress of a given student in the course.

---

### **Assignments for a Lesson**
![Assignments for a Lesson](https://namaste-lms.org/img/scr/screenshot-6.png)
View assignments associated with a specific lesson.

---

### **Submit Assignment**
![Submit Assignment](https://namaste-lms.org/img/scr/screenshot-7.png)
Students can submit solutions for assignments.

## Changelog

### Version 1.1
- **Gradebook**: Grade user performance in assignments, lessons, and courses.
- Configurable grading system.
- Users see "My Gradebook" in their dashboard when the grading system is enabled.
- Catching WATU/WatuPro submit actions to update lesson status immediately after exam submission.
- Added a "Course" column in the Manage Lessons page to show which course each lesson belongs to.
- Added `[namaste-enroll]` shortcode to display an enrollment button or information on the course page.
- Added action to allow other plugins to add their submenu under Namaste! LMS.
- Added basic visit stats for courses and lessons.

### Version 1.0
- Automatically generate PayPal payment buttons.
- Handle PayPal IPN to automatically insert enrollment after payment (pending or active, depending on settings).
- Added information about Namaste! Connect and the Developers API.
- Implemented Stripe integration for accepting Stripe payments.
- Fixed issues with backslashes in assignments.
- Fixed issues with Thickbox.
- Fixed bugs related to marking lessons as visited.
- Fixed bugs with `{{name}}` variable in certificates.
- Fixed bugs with lesson completeness when admin approval is not required.
- Fixed bugs with premature marking of lesson access.
- Fixed bugs when cleaning up student/course records.
- Fixed several strict mode issues.
- Fixed problems with adding custom post types to the homepage.
- Added missing Thickbox include.

### Version 0.9
- Important bug fixes for required homeworks.
- "In Progress" popup showing what a student needs to do to complete a course.
- `[namaste-todo]` shortcode for lessons and courses to show what needs to be done to complete them.
- Allow admin/manager to access any lesson (no need to be enrolled).
- Started the Developers API. More info at [Namaste! Developers API](http://namaste-lms.org/developers.php) (this is still in the early stages).
- Require payment for a course (manual payment processing for now).
- Fixed bugs with certificates.
- Filter students by enrollment status.
- Cleanup completed or rejected students from a course.

### Version 0.8
- Admin can create/edit personalized certificates.
- Users get certificates assigned to them upon successfully completing courses.
- Bug fixes and code improvements.

### Version 0.7
- Admin can view everyone's solution to a homework.
- Admin/Manager can also be a student and has a "My Courses" section.
- Other small bug fixes and code improvements.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
