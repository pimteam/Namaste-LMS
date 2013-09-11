=== Namaste! LMS ===
Contributors: prasunsen
Tags: LMS, learning, courses, lessons, ILE
Requires at least: 3.0
Tested up to: 3.6
Stable tag: trunk
License: GPL2

Learning management system for Wordpress. Support unlimited number of courses, lessons, assignments, students etc. 

== License ==

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

== Description ==

Namaste! LMS is a learning management system for Wordpress. Supports unlimited number of courses, lessons, assignments, students etc. You can create various rules for course and lesson access and completeness based on assignment completion, test results, or manual admin approval.

Namaste! lets you assign different user roles to work with the LMS and other roles who will manage it.

Students can earn certificates upon completing courses. 

For quick tour and more detailed help go to <a href="http://namaste-lms.org" target="_blank">namaste-lms.org</a>.

== Installation ==

1. Unzip the contents and upload the entire `namaste` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Namaste LMS" in your menu and manage the plugin

== Frequently Asked Questions ==

None yet, please ask in the forum

== Screenshots ==

1. Create/edit course. Courses are custom post type and support rich formatting, can be categorized etc. The course page itself is just a presentation page about what's in the course.
2. Create/edit lesson. Lessons are custom post type and support rich formatting and all kind of categorization. There are various rules about lesson access and lesson completion.
3. Manage assignments/homeworks
4. Student enrollments in a course
5. Progress of a given student in the course
6. Assignments for a lesson
7. Submitting a solution for assignment

== Changelog ==

= Version 1.1 =
- Gradebook - you can now grade user performance in assignments, lessons and courses
- The grading system is configurable by you
- User sees My Gradebook in their dashboard when grading system is enabled
- Catching watu/watupro submit actions so lesson status can be immediatelly updated when exam is submitted
- Added Course columng in Manage Lessons page so you can see which course each lesson belongs to
- Added shortcode [namaste-enroll] to display enrollment button or information right on the course page
- Added action to allow other plugins add their submenu under Namaste! LMS
- Added basic visit stats for courses and lessons

= Version 1.0 = 
- Paypal payment button can now be generated automatically
- Paypal IPN will be handled and enrollment will be automatically inserted after payment (pending or active, depends on your settings)
- Added information about Namaste! Connect and the Developers API
- Stripe integration implemented, you can now accept Stripe payments
- Fixed issue with backslashes in assignments
- Fixed issues with thickbox
- Fixed bug with marking lessons as visited
- Fixed bug with {{name}} bariable in certificates
- Fixed bug with lesson completeness when admin approval is not required
- Fixed bugs with premature marking lesson as accessed
- Fixed bug when cleaning up student/course record
- Fixed several strick mode issues
- Fixed problem with adding the custom post type to homepage
- Added missing thickbox include 

= Version 0.9 = 
- Important bug fixes on required homeworks
- "In progress" popup showing what does a student has to do to complete a course
- [namaste-todo] shortcode for lessons and courses to show what you need to do to complete them
- Let admin/manager access any lesson (no need to be enrolled)
- Started the developers API. More info on http://namaste-lms.org/developers.php (this is still the very beginning!)
- You can require payment for a course (for now payment processing is manual)
- Fixed bug with certificates
- Filter students by enrollment status
- Cleanup completed or rejected student from a course

= Version 0.8 =
- Admin can create/edit personalized certificates
- Users get certificates assigned to them upon successfully completing courses
- bug fixes and code improvements

= Version 0.7 =
- admin can see everyone's solution to a homework
- admin/manager can also be a student and has My Courses section
- other small bug fixes and code improvements