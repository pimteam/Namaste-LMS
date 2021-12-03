=== Namaste! LMS ===
Contributors: prasunsen, wakeop
Tags: LMS, learning, courses, lessons, ILE, wpmu, multisite, buddypress
Requires at least: 4.2
Tested up to: 5.8
Stable tag: 2.5.5
License: GPL2

Namaste! LMS is a learning management system for WordPress. Supports unlimited number of courses, lessons, assignments, students etc. You can create various rules for course and lesson access and completeness based on assignment completion, test results, or manual admin approval.

/*** License

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
    ***/

== Description ==

[PRO Modules](http://namaste-lms.org/pro.php "Go PRO") | [Premium Theme](http://namaste-lms.org/theme.php "Get the theme")

Namaste! LMS is a learning management system for WordPress. Supports unlimited number of courses, lessons, assignments, students etc. You can create various rules for course and lesson access and completeness based on assignment completion, test results, or manual admin approval.

Namaste! LMS lets you assign different user roles to work with the LMS and other roles who will manage it.

Students can earn certificates upon completing courses. 

Grade and Point systems let you mark the students performance.

For quick tour and more detailed help go to <a href="http://namaste-lms.org" target="_blank">namaste-lms.org</a>.

Full documentation and demo is available on <a href="http://demo.namaste-lms.org" target="_blank">demo.namaste-lms.org</a>.

= Community Translations =

Thanks to @Peter Baumgartner we have a German translation available [here](http://peter.baumgartner.name/namaste-deutsche-uebersetzung/ "German translation") 

Thanks to [Kaliningrad State Technical University](http://klgtu.ru/) for providing a Russian translation.

= Integrations =

The plugin is integrated with:

[Watu](https://wordpress.org/plugins/watu/ "Watu") / [WatuPRO](https://calendarscripts.info/watupro "WatuPRO"), and [Chained Quiz](https://wordpress.org/plugins/chained-quiz/) for handling quizzes, tests and exams.

[MoolaMojo](https://moolamojo.com "MoolaMojo") for transferring points from the points system as virtual credits and selling courses for virtual credits.

[WooCommerce](https://woocommerce.com/ "WooCommerce") for selling access for paid courses via [this bridge](http://blog.calendarscripts.info/namaste-lms-bridge-for-woocommerce/ "Namaste! LMS bridge for WooCommerce")

[myCRED](https://mycred.me/ "MyCRED") for transferring points and badges via [this bridge](http://blog.calendarscripts.info/namaste-lms-bridge-to-mycred/ "Namaste! LMS Bridge to myCRED")

[GamiPress](https://wordpress.org/plugins/gamipress/ "GamiPress") for adding new activity events via [this bridge](https://wordpress.org/plugins/namaste-lms-bridge-for-gamipress/ "Namaste! LMS Bridge to GamiPress")

[WP Experience API](https://wordpress.org/plugins/wp-experience-api/ "WP xAPI") to send Tin Can statements to an LRS

= BuddyPress Integration =

The plugin has basic BuddyPress integration:

- When user enrolls or get approved to enroll a course they can automatically become a member of a BP group and/or be removed from a BP group.
- When student completes a course they can automatically become a member of a selected BP group and/or be removed from a BP group.

This way you can easily move members within groups as they start and complete courses.

If you have the [Namaste! Connect](https://namaste-lms.org/connect.php "Namaste! Connect") module you can also tie the activity in Namaste! LMS into the associated enroll group activity in BuddyPress. 


== Installation ==

1. Unzip the contents and upload the entire `namaste-lms` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Namaste LMS" in your menu and manage the plugin

== Frequently Asked Questions ==

= Do you have any documentation? =

Yes, check the Help page under the Namaste! LMS menu (once the plugin is installed). You will find links to the online documentation, list of all shortcodes, and more.

= Can I rename the URL slugs from "namaste-course" and "namaste-lesson" to something else? =

Yes, you can set your custom URL slugs in the Namaste Settings page

= Does Namaste! LMS support SCORM or Tin Can API? =

Namaste! LMS supports Tin Can API through the [WP Experience API](https://wordpress.org/plugins/wp-experience-api/ "WP xAPI") plugin

= How to remove the links to next / previous lessons and courses from lessons / course pages? =

By default most WordPress themes add such links under every post. So these links come from your theme and we can't control them from the plugin. You can create custom post templates in your theme for the Namaste! LMS course and lesson pages. Copy single.php into single-namaste_lesson.php and remove the next / previous links in it. Then copy the file also as single-namaste_course.php if you want to use the same design for the course pages. More information about creating custom post type templates is available <a href="https://codex.wordpress.org/Post_Type_Templates">here</a>.

If you want to display links precisely to next / previous lesson in a course you should use the shortcodes [namaste-next-lesson] and [namaste-prev-lesson] or their Pro alternatives. Have a look at the Help page under your Namaste! LMS menu for more information on these shortcodes.

= How to add a hierarchical breadcrumb navigation? =

Use the shorthocde [namaste-breadcrumb]. The shortcode allows passing a custom separator like this: [namaste-breadcrumb separator="/"].
If you want to add the breadcrumb navigation to the theme see how to do it [here](https://blog.calendarscripts.info/hierarchical-breadcrumb-navigation-in-namaste-lms/ "How to add breadcrumb navigation").

== Screenshots ==

1. Create/edit course. Courses are custom post type and support rich formatting, can be categorized etc. The course page itself is just a presentation page about what's in the course.
2. Create/edit lesson. Lessons are custom post type and support rich formatting and all kind of categorization. There are various rules about lesson access and lesson completion.
3. Manage assignments/homeworks
4. Student enrollments in a course
5. Progress of a given student in the course
6. Assignments for a lesson
7. Submitting a solution for assignment

== Changelog ==

= Version 2.5.5 =
- Adjusted access control to module pages: their contents should not be visible if you are not enrolled in the course or if there are unsatisfied module pre-requisites.
- Added shortcode [namaste-breadcrumb] to display a breadcrumb style hierarchical navigation on lesson and module pages.
- Added order by Namaste! points in your main Users page (when using points system is enabled).
- Homework assignments now allow being "self approving". For these assignments you will not accept and review solutions from students. Instead, they will mark the homework completed themselves.
- Added option to accept reviews from students on completed course. The reviews with ratings can be shown on the front-end.

= Version 2.5 =
- Added filters by lesson status for each lesson in Manage Students page.
- Added "order" attribute to [namaste-assignments] shortcode to allow you to sort them by latest on top.
- Passing "lesson_title" as a first parameter to [namaste-next-lesson] and [namaste-prev-lesson] shortcode will make them dynamically use the lesson title as hyperlinked text.
- Added parameters enrolled_text, pending_text, rejected_text, and completed_text to the [namaste-enroll] shortcode. They can be used to override the default text which is shown instead of enroll button in case the student has been rejected, is pending, has completed or is already enrolled in the course. 
- Integrated the Chained Quiz plugin for tests/exams.
- The WooCommerce integration is now built-in. [Learn more](https://blog.calendarscripts.info/namaste-lms-bridge-for-woocommerce/)
- Improvement to the WooCommerce integration: now you can automatically link a course to a product from the course page.
- Removed sessions usage to avoid potential loopback issues.
- Improvement to lesson listings (shortcode, My Courses -> Lessons page, Student's lesson page): it will now show them grouped by module if modules are used.
- Added shortcode [namaste-course-status] to show the status of the course accordingly to the user who looks at it.

= Version 2.4 =
- New option lets you activate ajax file upload with a progress bar for assignment solutions.
- Added options to limit file size uploads for homework solutions: total size of files for a solution and per-file size limit.
- Started BuddyPress integration: when user enrolls a course join a selected BP group.
- BuddyPress integration: when a student completes a course join a selected BP group.
- BuddyPress integration: when a student completes or joins a course remove them from a selected BP group.
- Added navigation to lesson pages from assigmnets pages (both in backend dashboard and shortcodes).
- Added shortcode [namaste-module-link] which can be used in lessons to link back to the module page. 
- Added shortcode [namaste-num-modules] to show the number of modules under a course.
- The shortcode [namaste-num-lessons] can now receive attribute "module_id" to show the number of lessons published in a module.
- Added configuration for links target (same window/tab or new window/tab) for "My Courses" and related pages of students.
- Added attribute "column_widths" for [namaste-mycourses] shortcode to allow you specify widths of the columns.

= Version 2.3 =
- Added custom post type namaste_homework - such a post will be automatically created when you add assignments to a lesson to ensure easier handling of "no dashboard access" LMS.
- Fixed bug: when module is completed the associated course ID was not stored in history 
- When a non-admin has manager's access to the LMS but no access to the Namaste! Settings page, they will have "To-Do" as their default page for the Namaste! LMS menu.
- Attribute "class" added to the shortcodes [namaste-first-lesson], [namaste-next-lesson], and [namaste-prev-lesson] to allow you passing a desired CSS class to the generated links.
- Added option to not use the rich text editor on certificates. The purpose is to prevent it from messing custom HTML code and allow inserting external or embedded CSS.
- Improved Konnichiwa! integration: if there is no access to the course from Konnichiwa, don't display the enroll buttons in My Courses page
- Added option to restrict enrolling courses by user role
- Added sorting and "number per page" selection on Manage Students and Mass Enroll pages.
- Added option to tag student to course enrollments. This allows you to tag students for example by year of enrollment, source, campaign, etc - anything that could help you search these students.
- Replaced CURL with WP HTTP API
- Enabled Gutenberg on courses, modules, and lessons

= Version 2.2 =
- Added shortcode for the My Gradebook page.
- Added Plugins & Help page to the role-based access control configuraiton.
- Added option to restrict access to Watu / WatuPRO exam when it has been assigned to a lesson until that lesson is started.  
- On the Lessons page added link to exam result from Watu / WatuPRO when exam is required for the lesson.  
- Added shortcode [namaste-lesson-status] to display the status of a lesson.
- Added parameter "compact" to the namaste-gradebook shortcode to allow more compact display when the horizontal table is too wide and won't fit well.
- Added options to set expiration date or expiration period to certificates
- Improvements to [namaste-course-lessons], [namaste-module-lessons], and [namaste-course-modules] shortcodes. When used inside a lesson or module, they will show the corresponding lessons or modules for the parent course / module.
- Added personal data erasing functions and hooked the into the WP personal data eraser (GDPR compliance)
- Shortcodes for next / previous lesson will now work in module pages as links to next / previous module.

= Version 2.1 =
- Added "show_grade" argument to the [namaste-course-lessons] shortcode to display the assigned lesson grade.
- Added "public_view" argument to the [namaste-gradebook] shortcode. When the parameter is set to "true" or entirely missing, the gradebook is visible for everyone. Set the parameter to "false" to apply user-access permissions so teachers / managers see only students in their groups (if any), students see only their own gradebook, and non-logged in users see no gradebook at all.
- Added user email in students list and export.
- Added option to automatically enroll students in course when they register on the site.
- Solutions to assignments now can accept multiple file uploads.
- You can now define a set of accepted file extensions for solution file uploads.
- Added enrollment or completion date on the Manage Students page. 
- Added class 'namaste-list' to all ordered/unordered lists generated by shortcodes.
- Now you can choose to store solution files in Uploads directory instead of the database. This is not secure, do it if the content is not sensitive.
- Added option to automatically create protected custom folder for homework solution uploads.
- Added option to show only enrolled, pending enrollment and completed courses on My Courses page
- Added configuration for "Mass Enroll" user role access.

= Version 2.0 =
- Added a To-Do page for managers to show pending course enrollments, lessons approvals, and homework approvals.
- Added "view-only" shortcode for the whole gradebook on a selected course
- Added filter by user login and user email on the Manage Students page
- Updated to the latest Stripe library
- Added tag taxonomy to courses and lessons
- Added option a homework grade to automatically become lesson grade
- Added option to delete a homework note
- Added "mass approve" & "mass reject" buttons on Manage Students page
- Added MoolaMojo integration of the Points system so you can award Namaste! points as MoolaMojo virtual credits.
- Added MoolaMojo integration so you can charge virtual credits for enrolling paid course
- Implemented Tin Can / Experience API through the <a href="https://wordpress.org/plugins/wp-experience-api/">WP Experience API plugin</a>

= Version 1.9 =
- Added a new lesson action and filters that will allow you to add custom requirements for lesson completion. The action is "namaste-lesson-requirements" and the filters - "namaste-lesson-other-requirements" and "namaste-lesson-other-todo". Check the developer's docs at http://namaste-lms.org/developers.php for more information.
- Added configurations for the default "You need to be logged in" messages so you can provide login link or link to page with subscription plans etc.
- Added two new hooks that allow other plugin to add filters to the student search form in the Manage Students page
- You can now export student's table from the Manage Students page
- Added text attributes to the [namaste-enroll] shortcode that let you specify custom buttons text. See the internal Help page for details.
- Added shortcode to display grade for a lesson (get the shortcode from your Gradebook)
- Added option to automatically grade a course based on its lesson grades
- Added Course filter on the Manage Lessons page. The Manage Courses page now has a link to manage the lessons in the course, which leads to filtered Manage Lessons page for that course. 
- Introducing Modules as an intermediate level between courses and lessons. These can be enabled optionally from the Namaste! LMS Settings page.
- Added shortcode generator for the [namaste-course-lessons] and [namaste-module-lessons] shortcodes.

= Version 1.8 =
- Added [namaste-search] shortcode which generates search form and searches in courses and lessons (accordingly to user's enrollment). Non-logged in users can only search the course contents.
- Added option to automatically link the course from each lesson page. You can configure this from the Namaste! LMS Settings page.
- Added shortcode namaste-condition which allows you to display different content on the course page depending on whether  the user is enrolled or not. See the "Shortcodes" section in the Add / Edit Course page for more information.
- Added Buddypress support in lessons.
- Added option to set date interval when solutions to assignments will be accepted.
- New option on the "Fine-tune role settings" page lets you hide "My Courses" link for a given teacher's role.
- Added new shortcodes: [namaste-num-courses], [namaste-num-students], [namaste-num-lessons], [namaste-num-assignments]. Check the plugin Help page for more details.
- Added the optional attribute "enrolled=1" to [namaste-mycourses] shortcode. You can use it to limit this page to displaying courses the user has enrolled to. Note that it will display even courses with pending or rejected enrollment, frozen access, and completed courses.
- Added link to preview a certificate. Note that the preview will show the variables as variables. Only certificates that are really earned (no previews) will have the variables replaced with real data.
- Added shortcode [namaste-userinfo] to display user profile data on chosen locations accross your site. See the internal Help page for shortcode details.
- Added option to mass cleanup students to course relations

= Version 1.7 =
- Added option to unenroll from a course. This is configurable per course level from the Edit Course page.
- Added compatibility with the new free Instamojo integration plugin (see the Namaste Settings -> Other payment options section for a link)
- Made some improvements to notes / feedback on assignments and their navigation
- Added pagination on the Manage Students page
- Added option to automatically transfer Watu or WatuPRO quiz grade into a lesson grade (in case you use a grading system)
- Grade for course and lessons will now be shown in the Manage Students page if grading system is used
- Added column "points" for student to course, lesson, and homework relations. It will be used to store all points collected in the given homework/lesson/course. Will be shown at a later version when some data is accumulated.
- Use the lesson Excerpt box to show content to users who don't have access to the lesson. This will be shown in addition to or in some cases instead of the default text which appears in these cases. You can also show excerpts in the namaste-course-lessons shortcode.
- The namaste-todo shortcode now accepts post_type and post_id arguments. This allows you to expose the to-do items of a course or lesson outside of the course / lesson page itself.
- Added option to mass enroll students in a course

= Version 1.6 =
- Added [namaste-earned-certificates] shortcode to display certificates earned by the user on a given course. Can be used on course page or elsewhere by passing course_id.
- Added Sandbox mode for testing paypal payments
- Added filter to make sure unenrolled user does not see comments on a lesson
- Added page that shows all users who earned a certificate along with a link to see / print their certificate
- Added option to show lessons in blog and archive pages
- Added information about the "mark completed" button in the lesson to-do popup box
- Added shortcode [namaste-course-link] that creates a link to the course a lesson belongs to
- Switched the lesson order to ID (order of creating) rather than title based in couple of places
- Added custom column with LMS points in the "Users" page of your administration. The column appears if you choose to use points system. There is also a link to cleanup the points earned of every user.
- Made start / end dates of course and lesson use the timezone settings. Added also start / end times.
- You can now set desired paper size and orientation when creating certificates with the PDF bridge. Requires PDF Bridge version 0.7 or newer.

= Version 1.5 =
- Added elaborated access options for the roles that can manage the LMS
- Certificates will now be earned each time when the course is completed even if the same certificate was earned previously
- Added option to change the course and lesson URL slugs from "namaste-course" and "namaste-lesson" to your custom slugs
- Added "custom currency" option in the payment settings page
- Submitting solutions to assignments on a lesson is not allowed if the lesson access requirements are not satisfied.
- Added option to un-enroll student from a course
- Now you can generate PDF certificates using the free PDF bridge plugin
- Added variables for course description and certificate ID in the certificates. Improved the certificate displaying.
- Added option to redirect to specific URL after Paypal payment
- Added option to include "enroll" shortcodes outside of course page
- Improved the redirect URL after course enrollment (now defaults to the course page)
- Added [namaste-mycertificates] shortcode and made the certificate display accessible outside of wp-admin

= Version 1.4 =
- Advanced users only: You can now create your own versions of all Namaste views for high-level of customization
- Added shortcode to display functional "lesson assignments" page 
- Added shhortcode to display student grade on given course (see Gradebook)
- Links are generated to exams/tests in [namaste-todo] shortcode
- Allow multiple grades to be used in the "required quiz" for completing a lesson
- Fixed problem with listing pages on your homepage when Namaste is activated
- Course access / prerequisites: you can require other courses to be completed before student can enroll in a course
- Fixed problem with [namaste-enroll] shortcode and the new course pre-requisites
- Added setting to show / hide courses in blog and home pages (default is off)
- Added filers for course access to allow other plugins to add conditions
- Added shortcode to publish a button that lets student mark lesson as completed themselves
- Added shortcode to display the first lesson in a course
- The [lesson-todo] shortcode will now automatically recognize if you have published the [namaste-assignments] shortcode for this lesson and will display the front-end link to submit a solution for the to-do assignments. This way you can entirely hide the back-end if you wish.

= Version 1.3 =
- The URL slugs "namaste-lesson" and "namaste-course" are now translate-able so you can use your own URL rewrites
- Links to assignments are now generated in [namaste-todo] shortcode
- "Accept file upload as solution" is finally implemented
- fixed problems with PDFs and other large files when uploading solution to assignment
- Added arguments that let you control the order of lessons in [namaste-course-lessons] shortcode
- Fixed possible bug with passing course ID through Paypal
- Improved the Paypal error logging and added a "View errorlog" link on the main payment settings page (link visible only if there are errors)
- Added [namaste-next-lesson] shortcode to display a link to the next lesson in a course (please see the Help page inside Namaste menu) 
- Similarly, added [namaste-prev-lesson] to show the previous lesson link
- Improved course cleanup - now homework solutions are deleted on cleanup. If you use watu / watupro exams you can select to cleanup them as well (from the Namaste Settings page)
- User can be enrolled in course using username as well
- Fixed small issue with role restriction on enrolling

= Version 1.2 =
- Avoids duplicate completion on lessons and courses
- Allow using [namaste-course-lessons] inside a lesson page of the course
- Added new shortcodes to allow exporting part of the functionality outside of the user's dashboard. Shorcodes added: [namaste-mycourses] and [namaste-course-lessons]. See the internal Help page for more details.
- Added Help page and information about Namaste! Reports in the Plugins/API page 
- Force activation hook on update because activation hook doesn't run sometimes
- Completed the points system (see Namaste Settings page)
- added shortcodes for user points and simple points-based leaderboard
- Created DB log for all important user actions
- Fixed issues with clearing the DB history log
- Fixed important issue regarding who can change user roles that administre the LMS

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