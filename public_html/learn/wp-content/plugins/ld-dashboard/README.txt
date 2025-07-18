=== LearnDash Dashboard ===
Contributors: wbcomdesigns
Donate link: https://wbcomdesigns.com/contact/
Tags: learndash, instructor, dashboard
Requires at least: 3.0.1
Tested up to: 6.2.2
Stable tag: 6.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin creates a dashboard panel for Learndash instructors and students. The instructors can manage the courses, view their courses progress and student logs.

== Description ==

This plugin basically acts as an addon to LearnDash LMS plugin. This plugin creates a dashboard panel for the instructor, and students on the site. They can manage their created courses from the '/my-dashboard' page. View the course of each course and overall too.
The student log gets also created for the instructors to follow up things.

This plugin only requires the LearnDash plugin.

== Installation ==

1. Upload `learndash-dashboard` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What other plugin does it require? =

This plugin only requires the core learndash plugin.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif).

== Changelog ==

= 6.4.1 =
* Fix: (#198) Error message on designs settings
* Fix: Uniform user name at all places on the course report
* Fix: Avg completion time
* Fix: Export file name in the student course report
* Added: "Course Short Description" field for BuddyBoss theme
* Added: BuddyBoss and reign theme course cover image option
* Added: average competition column in the course report
* Managed: (#826) course cover uploading image UI and button
* Update: class-ld-dashboard-time-tracking.php

= 6.4.0 =

* Update: class-ld-dashboard-time-tracking.php
* Fix: (#823)Invitation Link in the mail
* Fix: (#824)Remove the certificate link in the quiz
* Fix: Fatal error DivisionByZeroError
* Fix: Changed Label
* Added: WP Editor for inviting student messages on the admin side
* Added: HTML Email Format
* Fix: (#820) PHP warnings and notices on submit invitation form
* Fix: (#761) Added stripe as withdrawal option for instructors
* Fix: (#818) - Invite functionality fixes
* Fix: Managed invite student table message and spacing UI
* Fix: Text domain
* Fix: Syntax error

= 6.3.1 =
* Fix: Manage time tracking popup UI
* Fix: Fixed unnecessary nonce verification in course report tab
* Fix: Updated file names for report downloads

= 6.3.0 =
* Improvement: Added #743 - added nonce to check verify request
* Improvement: Added ACF free version bundle
* Fix: #813 - Top course cart and course completon chart
* Fix: #804 - Show loader on time spent section
* Fix: #809 - Group Leader dashboard issue
* Fix: #807 - Total Student Count Issue
* Improvement: (#743) Course report UI fixes
* Improvement: Added #743 - Added User wise course report for Lessons, Topics and Quiz
* Improvement: Single student course report
* Improvement: #743 - Added Reset Time Tracking in admin side on edit user page
* Improvement: (#743) Managed time tracking popup UI
* Improvement: #743 - Added idle message option and display on frontend
* Improvement: Fixed course report pagination
* Improvement: (#743) Managed course report table UI in mobile view
* Fix: (#799) Fixed top course char hide when course completion chart change
* Fix: (#787) Fixed active course tab showing wrong data with correct pagination
* Fix: (#794) Fixed assignment approve button showing console error
* Fix: (#796) Fixed warning on student course activity tab

= 6.2.0 =
* Improvement: Course completion chart UI improvment
* Fix: (#778) Fixed essay max point is not visible
* Fix: (#759) Fixed student could not join meeting by browser
* Fix: (#764) Fixed unpublished courses should not be calculated
* Fix: (#777) Fixed console error on assignment report tab
* Fix: (#775) Fixed unnecessary pop up on reloading student dashboard
* Fix: (#765) Fixed essay reportand assingment report is not display
* Fix: (#742) Fixed course completion chart course drop down show open courses
* Fix: (#763) Fixed total earnings issue in float value
* Fix: (#762) Fixed course completion chart is showing blank if no data is found
* Fix: (#766) Fixed reference column getting blank in instructor commission 
* Fix: (#768) Fixed course completion progress bar color not set
* Fix: (#745) Changed student details chart typo and count
* Improvement: (#736) Added add new question button
* Improvement: (#748) Added question overview option on quiz
* Fix: (#745, #744) students and course details chart improvement UI
* Fix: (#758)Added unfiltered_html capability to instrcutors
* Fix: (#744) Course Details chart improvment
* Fix: (#745) Student details chart improvment
* Fix: Fixed #756 - unable to create courses via video playlist
* Fix: (#753) Fixed certificate page preview link redirect to access denied
* Fix: (#752) Fixed announcement count missing
* Fix: (#755) Fixed monetization tabs not visible for instructor
* Fix: Fixed #754 - pagination not working
* Fix: (#751) Fixed instructor can publish courses even if the option is disabled

= 6.1.0 =
* Fix: Fixed Youtube Playlist render
* Improvement: Managed upcoming meeting shortcode widget area
* Improvement: Managed zoom meeting shortcode UI and added browser join button
* New Feature: Added join from browser link in meeting shortcode
* Fix: (#734) Fixed unable to grade assingment
* New Feature: (#726) Migrate Zoom Api Authentication from JWT to S2S
* Fix: (#727) Fixed view CPT notice after publish and update
* Improvement: Added zoom credentials for instructor
* Improvement: Managed UI edit and remove link with course builder
* New Feature:  (#714) Embed Zoom Meeting shortcode UI

= 6.0.4 =
* Fix: Added View Tutorial button link in admin section
* Fix: Managed UI edit and remove link with course builder
* Fix: Added edit link on course builder
* Fix: Fixed Youtube Rendar Process data issue
* Fix: (#728) Fixed iframe removed from content
* Fix: (#727) Fixed view CPT notice after publish and update

= 6.0.3 =
* Fix: Manage reset setting button and update string
* Fix: Added reset option button for each option page
* Fix: Fixed html structure and added extra class for each option

= 6.0.2 =
* Fix: Fixed dashboard menus not displaying after the plugin update
* Fix: Managed general setting button
* Fix: Fixed menu set to enable student dashboard menus
* Fix: Added Monetization option dependency
* Fix: Managed dashboard menu according to extra space
* Fix: Remove unused tabs for student
* Fix: Move Instructor Earning Logs to the components set
* Fix: Updated general setting options
* Fix: Fixed functionality glitch in becoming an instructor

= 6.0.1 =
* Fix: (#718)Fixed php warnings in single meeting sortcode
* Fix: Fixed hide dashboard title issue
* Fix: (#719) Fixed php warnings on instructor commission report
* Fix: (#717) Added pagination in submitted essays
* Fix: (#722) Added email notification for instructor if approve or rejected
* Fix: (#715) Added pagination om enrolled course tab
* Fix: (#718) managed upcoming meetings UI
* Fix: (#720) Admin Email Logs UI and search activity button color
* Fix: (#716) Fixed chart js console error
* Fix: Removed old migration scripts
* Fix: Fixed tiles not visible on plugin update

= 6.0.0 =
* New Feature: New Reports for Essay, Quiz, Assignment
* New Feature: Menu Improvement
* Improvement: Disable Course Learner specific menus for Admin, Instructor and Group Leader

= 5.9.8 =
* Fix: (#684) Fixed dashboard modules are not visible
* Fix: (#685) Fixed topic timer option would be time picker
* Fix: (#686) Fixed php warnings on Instructor Commission Report
* Fix: (#688) Added points option to lesson and topics
* Fix: (#689) Added tiles access for subscriber role

= 5.9.7 =
* Fix: (#677) Fixed submitted essay not seen on dashboard
* Fix: Fixed selling through WooCommerce setting is not visible
* Fix: Fixed admin general option dropdown UI

= 5.9.6 =
* Fix: #672 - Create course from the playlist is not working

= 5.9.5 =
* Fix: (#665) Fixed conflict with WC Vendors Pro

= 5.9.4 =
* Fix: (#618) Fixed variable mismatch issue
* Fix: (#655) Fixed translate javascript strings

= 5.9.3 =
* Fix: (#648) Fixed managed learndash shortcode UI in frontend
* Fix: (#649) Fixed added condition for revert back to wordpress editor
* Fix: (#651) Fixed export students detach course content
* Fix: (#652) Fixed show user name instead of %s
* Fix: (#654) Fixed php warnings on student dashboard
* Fix: (#656) Enhancment added chart color option

= 5.9.2 =
* Fix: (#624) Fixed javascript strings translation issues
* Fix: (#645) Fixed cant see the created lesson on my dashboard page
* Fix: (#641) Fixed avatar issue
* Fix: (#642) Update icon set material icons to ld dashboard custom icon set
* Fix: (#639) Fixed profile override setting is not working
* Fix: (#630) Fixed dashboard assignment pie chart data calculation
* Fix: (#625) Fixed translation not working for some strings
* Fix: Fixed #624 - Arabic translation issue
* Fix: (#624) Fixed chart legent string is not translate

= 5.9.1 =
* Fix: (#606) Added submited essays grade/approve at frond end dashboard
* Fix: (#613) Fixed display full list of enrolled users
* Fix: (#614) Fixed media library empty on plugin activete
* Fix: (#615) Added edit with elementor link on course, lesson, topics and quizzes


= 5.9.0 =
* Fix: (#607) Fixed redirect to BP profile from LD user profile with BP and BB
* Fix: #608 - Show group dropdown for instructor user role
* Fix: (#608) Students detail by group filter UI
* New Feature: (#603) Added update and delete functionality for announcement
* Fix: #608 - Students detail by group filter
* New Feature: Managed zoom account Co-hosts admin UI
* Fix: (#610) Added single meeting view url
* Fix: (#605) Managed filter form options fields UI
* New Feature: #605 - filter on course activity feed
* Fix: #600 - unable to edit questions
* Fix: (#599) - Fixed certificate background image issue in visual mode
* Fix: Admin monetization settings UI
* Fix: Paid unpaid earning commission report UI
* Fix: (#597) Fixed php notice on commission report setting
* Fix: Fixed commission tab issue

= 5.8.1 =
* Fix: Added phone number field in profile settings
* Fix: Managed backend settings UI
* Fix: (#535) Announcements notification count managed
* Fix: (#595) custom link svg issue
* Fix: Fixed console error in backend
* Fix: Added sticky submit buttom for field restrictoins tab
* Fix: Admin wrapper UI updated
* Fix: (#591) - Added wysiwyg editor for group content

= 5.8.0 =
* Fix: Fixed a variable mismatch has been detected issue
* Fix: (#589) managed Group list button UI
* Fix: (#585) Create video playlist UI
* Fix: (#590) - Fixed login page warnings
* Fix: (#579) - Fixed group add/edit button issue in frontend
* New Feature: (#578) - Added course playlist functionality from frontend
* New Feature: (#579) - Added group creation functionality in frontend
* New Feature: (#579) - Added instructor group creation functionality ( if they are group leader )
* New Feature: Managed color option with dashboard
* New Feature: (#491) - Set default preset colors on activation
* Fix: (#583) - Fixed translation issue
* Fix: (#584) - Fixed plugin activation issue
* Fix: (#586) - Fixed undefined variable issue
* Fix: Fixed earning mode issue
* Fix: Fixed #581 - client site private message issue
* Fix: remove svg icons and added google material icons
* Fix: (#580) Fixed all students list even instructor has not any student enrolled
* Fix: (#575) - Fixed form fields enable disable issue
* Fix: (#577) - Fixed admin in private message
* Fix: (#576) - Fixed instructor group capability
* Fix: (#572) - Fixed unable to edit topic issue
* Fix: Fixed translation issue in my profile tab

= 5.7.2 =
* Fix: (#571) - Fixed students detail issue for admin

= 5.7.1 =
* Fix: (#565) icons issue and create meeting loader
* Fix: Added co-author option description
* Fix: (#556) Fixed php warnings on create meeting
* Fix: managed single zoom meeting
* Fix: (#553) - Fixed single instructors content
* Fix: (#555) - Added zoom meeting co-hosts functionality
* Fix: (#557) - Removed edit button for past meetings
* Fix: (#557) - Fixed date issue in meeting form
* Fix: (#556) - Fixed instructor meetings not showing on the admin
* Fix: (#554) - Fixed start url button for meeting created with admin
* Fix: (#554) - Removed start meeting button for students in single meeting
* Fix: Fixed instructor profile courses grid

= 5.7.0 =
* New Feature: Zoom Meeting for Instructors
* New Feature: Instructor Grid
* New Feature: Single Instructor Layout
* New Feature: Options for Single Instructor Course Grid
* New Feature: Individual Zoom Meeting Layout and Shortcode
* Fix: (#547) Fixed most popular courses enrolled users count issue
* New Feature: Option for Dashboard Menus based on role to show and hide them.

= 5.6.1 =
* Fix: (#527) - Fixed certificate dropdown issue in course form
* Fix: (#527) - Fixed courses dropdown issue in quiz form
* Fix: Fixed translation issue
* Fix: (#523) - Fixed acf group field confilct

= 5.6.0 =
* Fix: Fixed multiple select input box
* Fix: Fixed course builder issue
* Fix: (#520) - Fixed quiz setting not working
* Fix: Fixed course activity empty text issue for Quizes
* Fix: (#506) - Fixed groups leader courses listing
* Fix: (#505) - Fixed group leader plus instructor user functionality
* Fix: (#514) - Fixed course builder issue with topics & quizes
* Fix: (#518) Earning tiles UI managed
* Fix: Fixed profile fields setting
* Added: New Earnings UI
* Added: hook to update course access modes
* Added: view toggle for popluar course setting
* Added: Add section option for course builder
* Added: Added new DB tables for earning and commission for PayPal Stripe and WooCommerce
* Added: Added support for BB and BuddyPress Avtars

= 5.5.1 =
* Fix: Fixed create lesson issue in course builder
* Fix: Managed profile update button
* Added: Managed Mobile menu tabs
* Fix: Managed profile tab and remove social media url
* Added: (#481) - Added hookable position to add/remove profile fields in frontend
* Fix: the course builder lesson and topic issues

= 5.5.0 =
* Fix: (#477) - Fixed course builder lesson remove error
* Fix: (#468) - Fixed error-notice-on-creating-new-quiz-from-course-builder
* Fix: (#475) - Fixed error-warning-on-create-course-page-frontend
* Fix: (#477) - Course Builder submit button not visible on adding lesson
* Fix: (#435) - Fixed open-course-enrollment
* Fix: (#435) - Fixed Courses not found message
* Fix: (#473) - Fixed Fata error when create new lesson
* Fix: (#473) - Course Builder : Topic and Quizzes can be moved
* Fix: (#473) Update ld dashboard is sortable height
* Fix: (#465) Added RTL support
* Fix: Added Missing Text Domain
* Fix: (#473) Managed ld dashboard is sortable UI when edit course
* Fix: Update #473 - Course Builder : Topic and Quizzes
* Fix: Managed Lesson section
* Fix: Builder button UI improved
* Fix: #466 - Quiz Create issue when create from course
* Fix: Space managed topics
* Fix: Managed Builder components UI
* Added: #466 - Add New Topic and Quiz to course Builder
* Added: #466 - Add New sesson to course Builder
* Fix: Managed dashboard wrapper

= 5.4.0 =
* Fix: Style improvement for KLEO, BuddyBoss Theme
* Fix: Added options of most popular course for group leader and student
* Fix: fixed jquery ui dialog error
* Fix: managed admin email logs tab
* Fix: Improved RTL Support
* Fix: (#464) Fixed ld dashboard scripts are not loading
* Fix: Fixed ACF Fronted compatibility
* Fix: Managed elementor page wrapper
* Fix: Added string if no currency enable
* Fix: Replace static dashboard url with mapped dashboard url
* Fix: Fixed console error on course builder setting
* Fix: Course completion and top course chart not working and code optimize
* Fix: Managed backend course field group
* Fix: Managed acf popup box UI for tags
* Fix: Set default options of monetization
* Fix: Added WooCommerce product description and short description on course creation

= 5.3.0 =
* New Feature: Added restriction for withdrawal and earning contents on dashboard
* New Feature: Assign Product Type as Course when create product and option
* New Feature: Added (#439) - WooCommerce Course link up with product creation
* New Feature: Added Total earning wallet balance update functionality
* New Feature: Added instructor earning tile, earning widget and backend setting
* New Feature: Managed Instructor Earning stats UI chart
* New Feature: Added Instructor Email Logs tab to show email logs History
* New Feature: Added fee deduction on course purchase (extra from commission )
* Fix: (#461)Fixed added dependency of learndash woocommerce plugin
* Fix: (405) Fixed dashboard charts is not visible for instructor
* Fix: (#432) Hide withdraw tab if revenue sharing is disable
* Fix: (#432) Fixed withdraw content is not showing
* Fix: (#448) Set default color for earning tiles
* Fix: (#444) UI issue in withdraw settings tab managed
* Fix: (#458) Fixed Enable/Disable fields not working
* Fix: (#457) Fixed php notice
* Fix: Disable monetization options if option is disable
* Fix: Instructor registration and registered UI
* Fix: (#447)Fixed php notice on course form update
* Fix: (#449) - Fixed course price not showing
* Fix: Fixed form redirection, learndash currency symbol issue
* Fix: Fixed acf form redirection issue
* Fix: Fixed top courses chart content
* Fix: Fixed Top courses chart labels issue
* Fix: (#405) - Added Course Completiond chart, Top courses chart for instructor
* Fix: (#442) - Added message on popup when any payment is already pending

= 5.2.0 =
* Fix: Fixed grunt text domain not found
* Fix: Added Learndash Shortcode tinyMCE button to frontend editors
* Fix: (#428) - Fixed conflict with all in one seo plugin
* Fix: Fixed post save message popup issue
* Fix: Submit course msg UI
* Fix: Added publishes/saved message with view link for frontend
* Fix: managed courses Wysiwyg and Shortcode icon learndash
* Fix: Fixed form fields label issue
* Fix: Added wsyiwyg editor for course material field
* Fix: Added Announcements
* Fix: (#423) - Fixed instructor button should not be appear for admin issue
* Fix: Fixed certificates by author issue
* Fix: (#404) - Added student announcment feature
* Fix: Added approve/pending filter for instructor listing in backend
* Fix: #413 Add new instructor form UI
* Fix: (#402) - Fixed instructor registration flow

= 5.1.0 =
* Fix: Added become an instructor button for student and setting in backend
* Fix: managed billing-cycle-field group
* Fix: #336 all checkboxes with toggle checkbox
* Fix: (#338) - Added automatic quiz submission feature in frontend
* Fix: (#403)Fixed enrolled student names not getting on send email
* Fix: Fix tiles restriction for admin
* Fix: Fixed delete avatar issue
* Fix: (#358) - Fixed live course activity issue
* Fix: (#361) - Added WYSIWYG editor
* Fix: change ld-dashboard-progress-filled color
* Fix: Billing Cycle and my quizzes time limit UI fixed
* Fix: (#410) - Fixed user should not access all media issue
* Fix: (#401) - Fixed learndash-course-access-mode
* Fix: (#409) - Fixed course activity & my course activity active tab issue
* Fix: (#411) - Fixed quiz from automatic submit issue in frontend

= 5.0.0 =
* New Feature: Frontend Course Builder for the instructors.
* New Feature: Instructor can create and manage Courses, Lessons, Topics, Assignments, Quizzes, questions, using frontend dashboard
* New Feature: Instructors can view the created Lessons, Topics, Quizzes, and questions list and filter out them according to the courses
* New Feature: Instructor can create the course tags from frontend
* New Feature: Instructors can view and approve the assignments from the front end
* New Feature: Instructor can view student wise quiz attempts with their results
* New Feature: Site admin can display the most popular courses on the instructor dashboard based on the course tags.
* New Feature: Site admin can Control the below course related fields. IF these fields are restricted by the admin, Won’t display on the front end dashboard.
* New Feature: Course Fields Control for frontend
* New Feature: Lesson Fields Control for frontend
* New Feature: Topics Fields Control for frontend
* New Feature:  Quiz Fields Control for frontend
* New Feature: Question Fields Control for frontend
* New Feature: Students can view their quiz attempts on the student dashboard
* New Feature: Students can view their enrolled course on the student dashboard
* New Feature: All the students can manage their profile easily from the frontend dashboard.
* New Feature: Shared Courses from frontend course builder

= 4.7.0 =
* Fix: Fixed #303 - Instructor earning issue when commission set 0
* Fix: Fixed #284 - Unable to translate string
* Fix: (#302) Updated UI with twentytwentyone
* Fix: (#302) Updated UI with twentytwenty
* Fix:Fixed #301 - Export csv issue
* Fix: Removed duplicate line
* Fix: (#300) Updated LearnDash Dashboard settings(backend) fields UI
* Fix: Fixed # php warning Index undefined
* Fix: Fixed #273 - if course access setting is set to be opened, enrolled sudents are not showing under course details & student details section
* Fix: Fixed Action priority
* Fix: Update POT file #284 - Unable to translate string
* Fix: Fixed #295 - Warning issue generated while disable live course activity
* Fix: Fixed #299 - All disable option issue in General option
* Fix: Fixed #289 - Plugin activation issue
* Fix: Fixed #290 - php errors in ajax response in admin dashboard
* Fix: (#296, #297) Update my dashboard page UI
* Fix: Added WPML Language support

= 4.6.0 =
* Fix: instructor pending user role assign and remove other user
* Fix: #281 - User roles are not getting loaded in multiselect dropdown
* Fix: #280 - Warnings on plugin activation
* Fix: Dashboard page loading on instructor login.
* Fix: #274 - Client site issue

= 4.5.0 =
* Fix: Fixed Administrator issue when course price type is open for all user
* Fix: Update plugin backend UI
* Fix: #270 - Quizzes are showing incompleted in the graph
* Fix: #272 - Managed select all dropdown and email course loader UI
* Fix: Added #272 - We should have select all box in student dropdown
* Fix: Duplicate User id in Student details dropdown
* Fix: #271 - Issue in loading student and course details

= 4.4.1 =
* Fixed : #269 - view assignment/essays redirecting to the home page
* Fixed : Added filter for essay permission redirect filter to see essay

= 4.4.0 =
* Fixed: #265 - mention instructor's email in 'From Field'
* Fixed: #264 - Notices and warnings
* Fixed: #268 - LearnDash Dashboard Profile Menu not showing on front end

= 4.3.0 =
* Fixed: Updated language strings

= 4.2.0 =
* Fixed: #262 - notice on Admin My Dashboard page
* Fixed: #261 - Typo mistakes in support Question
* Fixed: Added FAQ in Support Tab
* Fixed: Rearranged plugin Options
* Fixed: Added Do action to add additional section for customize
* Enhancement: Create Three new shortcode [ld_course_details], [ld_student_details] and [ld_instructor_earnings]

= 4.1.0 =
* Fixed #250 PHP warnings
* Fixed #247 - Assignment related issue
* Fixed #248 - BP messaging related Issue
* Fixed Date time formate translate

= 4.0.0 =
* Enhancement: Added Group-wise filter to send emails for administrator and Group leader.
* Enhancement: Added feature to show/hide live feeds(role wise).
* Enhancement: Added feature to show/hide student details.
* Fixed: Fixed customizer glitch.

= 3.2.2 =
* Fixed: Assignment filter setting for the instructor.

= 3.2.1 =
* Fixed: WP 5.5 version compatibility

= 3.2.0 =
* Fixed: Instructor Earning will show only for Instructor login
* Fixed: Add Loader when student fetch from course
* Fixed: #208 - Instructor Earning : graph
* Fixed: #76 - dashboard page (courses tab)
* Fixed: (#209) Send mail Loader
* Fixed: Fixed Currency Symbol issue in instructor earning graph
* Fixed: #207 -course enrollment activity
* Fixed: #210 - On changing a subscriber's role to group leader he is can view other group leader's live feed

= 3.1.0 =
* Fixed: Apply Become as instructor issue and coauthor
* Fixed: Course counting issue on the instructor dashboard
* Fixed: #202 - Data Statistics Issue
* Fixed: #201 - By default all 'General' and 'Integration' options should be enabled on a fresh installation
* Fixed: #203 - Registration page related warning
* Fixed: Apply instructor button on the logged out user
* Fixed: #200 - If logged in user, not as an instructor then display apply
* Fixed: Newly assign instructor user display all course listing
* Fixed: #198 - Notification is not getting listed on co author's dashboard
* Fixed: #176 - wrong data is showing on Co author's dashboard
* Fixed: #197 - Divison By 0 warning
* Fixed: Update My Enrolled Course in My Dashboard
* Fixed: Managed my courses tab UI
* Fixed: #195 - pagination(next and prev links)
* Fixed: (#194) Managed export CSV button UI
* Enhancement: Import Instructor commission report CSV file
* Enhancement: Add Simple CSV file
* Enhancement: Fixed Minor changes
* Enhancement: Remove Instructor Total Sales Option
* Enhancement: (#191) Managed Export CSV Button Style
* Fixed #185 - Course progress chart is showing disabled for co-author
* Fixed #182 - My dashboard page as a new student
* Fixed #183 - Undefined index notice.
* Enhancement: Add Export CSV format for Student Course Progress Report and Course Wise Student Progress Report
* Enhancement: Set Couse wise Student Information pagination and Student wise Course wise information pagination
* Fixed Student enrolled course display on Instructor dashboard
* Fixed - Instructor can see assignments of other instructors course student

= 3.0.0 =
* Enhancement: Added Option for Instructor Registration
* Enhancement: Added Option for Co Instructor
* Enhancement: Improved BuddyPress TODO Integration
* Enhancement: Improved Chart UI and Color combination
* Enhancement: Updated Plugin options with page mapping
* Fixed: Add Edit Other Courses cap for instructor user role
* Fixed: Notice on Live feed section

= 2.6.0 =
* Fix: #133 - Assignment is visible to another instrutors
* Fix: #136 - Student graph issues in student dashboard
* Fix: Fixed instructor course progress issue

= 2.5.0 =
* Fix: fixed conflict with propanel

= 2.4.0 =
* Fix: #122 course count error for the group leader
* Fix: Student course progress report style manage
* Fix: Removed inline css
* Fix: Display Course Dropdown and display student course progress report
* Fix: Fixed Group Leader Dashboard issues

= 2.3.0 =
* Design upgarde of options
* Todo List fixes for saving options

= 2.2.1 =
* Fix: Language Fixes

= 2.2.0 =
* Enhancement: BuddyPress Todo List Support
* Enhancement: Improving Options to display settings
* Enhancement: Instructor WooCommerce Graph Integration
* Enhancement: Learndash Notes Support

= 2.1.0 =
* Fix : Instructor Course Permission Fix

= 2.0.0 =
* Plugin Rewrite

= 1.0.1 =
*  Initial Release
*  Minor fixes for admin dashboard

= 1.0.0 =
*  Initial Release
