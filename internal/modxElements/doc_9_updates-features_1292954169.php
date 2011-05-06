<?php
$ASE_timestamp = '1292954169';
$ASE_time = 'December 21, 2010, 12:56 pm';
$ASE_savedby = 'obojobo.ucf.edu,,,132.170.240.85';
$ASE_doc_raw = <<<'NOWDOC'
a:37:{s:2:"id";s:1:"9";s:4:"type";s:8:"document";s:11:"contentType";s:9:"text/html";s:9:"pagetitle";s:18:"Updates & Features";s:9:"longtitle";s:0:"";s:11:"description";s:0:"";s:5:"alias";s:18:"updatesandfeatures";s:15:"link_attributes";s:0:"";s:9:"published";s:1:"1";s:8:"pub_date";s:1:"0";s:10:"unpub_date";s:1:"0";s:6:"parent";s:1:"3";s:8:"isfolder";s:1:"0";s:9:"introtext";s:0:"";s:7:"content";s:15379:"<markdown>
Updates & New Features
======================

Version 1.7 -- Monday, December 20th 2010
===============================

Here's what we've been cooking up in this new Obojobo release:

* **Webcourses@UCF Gradebook Integration**: Obojobo will now allow you to send your student's scores to Webcourses.  Setup is easy: When you create an instance, simply select your course from a drop down and select the option to sync scores.  We will create a grade book column and send student assessment scores directly into that column.
* **Date & Time in Assessment Scores:** We've added time to the assessment scores display to help you determine more accurately when students submit their assessments.
* **Student Completion E-Mail Notices:** Students will now receive an e-mail from Obojobo with their assessment score & attempt history each time they submit an attempt.
* **Captivate 5 Quiz Support:** Content creators can now develop quizzes in Captivate 5.  Obojobo will automatically integrate, using the scores reported from your Captivate 5 quiz as the score for that question.  Captivate 2 quizzes are still supported.
* **Database Optimizations:** We spent a lot of effort tweaking our database structure to speed up and simplify our code. Retrieving learning objects from the database is approximately twice as fast now.
* **Creator Optimizations:** The creator has had various updates to reduce load times and resource usage.

As well as various bug fixes.  We hope you enjoy the new release!

Monday, August 16 2010
===============================

Obojobo is now at version 1.6.  Read about some of the key new features and changes below:

* Links to objects have been simplified, and now look like http://obojobo.ucf.edu/view/10.  Older style links will still work fine.
* Obojobo has added integration with Kogneato.  Content creators can now use a Kogneato widget for content, practice or assessment.
* Content creators, you can now create your own page templates!  This feature allows you to break out of the content page templates and construct pages much like a Power Point slide.  Select the new 'Custom Layout' page template when building pages to give this feature a try.
* Many bug fixes and under the hood optimizations.

Tuesday, March 30 2010
======================

Obojobo is now at version 1.5.  Read about some of the key new features and changes below:

* Logins into Obojobo are no longer grouped between 'UCF User' and 'Guest Login'.  Both users will now simply use the same username/password login.

* New - The Viewer now provides the maximum amount of space to questions. Previously both questions and answers took up 50% of the available area, regardless of content.

* New - The Repository now provides an 'Updated' column in the 'Assessment Scores' section.  This column provides the date of the most recent assessment attempt submission.

* New - The Editor now provides some new text content editing tools: Page links, external links and tooltips.  These tools are available in Content pages, text questions and multiple choice and short answer question feedback.  Page links look like a typical HTML link, but instead navigate the user to a specific content page in an instance.  External links will link outside of Obojobo to a webpage.  Tooltips provide additional information to a portion of text through a small in-line popup.

* New - In addition to the text content editing tools, content pages in the Editor now have an 'Advanced' mode which will allow you to input HTML directly.

* Bug Fix - Editor: Fixed an issue where the window to edit a practice or assessment question would no longer display.

* Various system improvements and fixes.

Friday, January 15 2010
=======================

Obojobo is now at version 1.4.  Read about some of the key new features and changes below:

* New - The Obojobo system now provides the option for students to import previous scores for learning objects they have taken previously.  All instances published before this update have this option turned on by default.  You can disable this option by selecting an instance, selecting the 'Edit Details' button, and uncheck the 'Allow past scores to be imported' option.  If a student accesses an object they have already taken (and this feature is enabled), they will receive a dialog giving them the option to either re-take the assessment or to import the highest attempt score from a previous attempt.  If they choose to import the previous score, that score will be the resulting 'Final Score' for that instance and they will not be able to take the assessment again.  Read [Previous Instance Score Importing]([~58~]) for more information.

* New - Repository: Instances can now be shared among colleagues with Instance Managers.  This allows multiple users to manage details and view scoring data for an instance.  Select an instance and click on 'Managers' to add and remove Instance Managers.

* New - Repository: Data for attempts that were not submitted by the student can now be viewed in the Repository.  This is helpful when a student forgets to submit an attempt.

* New - Repository: The new 'View Scores By Question' feature gives you a powerful interface to see, on a question-by-question level, how students performed & responded on each question.  You can access this feature by selecting an instance from the 'Published Instances' section, then clicking 'View Scores By Question' from the 'Assessment Scores' tab.

* New - Help inside the Repository has been improved.  

* New - Both the Repository and the Viewer now utilize the Menu button (shown in the upper left hand corner of the screen).  To log out of the Repository, click on the Menu button and select 'Logout'.  The Menu button also provides a 'Zoom View' feature which allows you to scale the Repository and the Viewer up or down.

* Bug Fix - Viewer: The score results screen no longer shows an incorrect date for the attempt submission time.

* Bug Fix - Viewer: Fixed an issue where logging out of the viewer resulted in an error message.

* Various bug fixes, optimizations & improvements.

Tuesday, September 29 2009
==========================

A small update provided a few fixes and added some features left-over from the 1.3.2 update:

* New - An update to the underpinnings of Obojobo were completed allowing Obojobo to run faster.
* Fix - A bug that caused sporadic or errant behavior when defining permissions of a learning object to other users has been resolved.
* Fix - The front-page login form was not working correctly.  This has been resolved.

Thursday, September 24 2009 (v1.3.2)
====================================

Obojobo is now at version 1.3.2.  Read about some of the key new features and changes below:

* NOTE - Any internal Obojobo users (all users that have a username that begins with a tilda (~) character) who previously used the 'Others' link to login will now login through the 'Guest' option.
* Fix - The issue affecting our Mac users with saving media scores (as described [here]([~55~])) has been resolved. 
* New - Advanced score reporting: Repository users can now view more detailed score reporting (including user responses).  To access this feature, select an instance from the 'Published Instances' section, then click on the 'Assessment Scores' tab.  Finally, click on the small green 'A+' button.  Click [here]([~57~]) for more information.
* New - Media uses dialog: Repository users can now see where a media asset is being used.  Select a media asset from the 'Media Assets' section, then click on 'View Uses' to view this information.
* Fix - Zooming controls: The viewer now has more robust zooming controls.  Click on the 'Open Menu' button in the upper left-hand corner of the Viewer to access these controls.  Zooming now scales the entire page instead of scaling text.
* Fix - Windows in the Repository and Editor are now keyboard friendly.  'Esc' should cancel out of a window.
* Fix - Fixed an issue in the Viewer where starting another attempt would not clear out the responses from the last attempt.
* New - Viewer score recorded notifications: The viewer has been tweaked to make it more apparent which answers have been recorded and which answers have not.  Documentation for this feature will be coming shortly.
* New - All media content now contains a loading bar.
* Fix - The 'Search' fields for the Repository (previously labeled as 'Filter') should now correctly filter content.
* Fix - Significant work has been done to the internals of Obojobo to improve the system. 


Thursday, July 16 2009 (v1.3.1)
===============================

Obojobo is now at version 1.3.1.  Read about some of the key new features and changes below;

* Obojobo is now requiring Flash Player 10.  You can upgrade Flash Player in a matter of minutes by visiting the [Flash Player download page](http://get.adobe.com/flashplayer/).
* We are always working towards improving and expanding our help documentation.  We've started adding a few tutorial videos, with plans to add many more.  Read the next two points for more information on changes to our documentation:
* New - Documentation: We now have a tutorial with video of how faculty & staff can import scores from Obojobo into a Webcourses grade book.  Note that you must be logged in before you can access this page.  View the tutorial [here]([~52~]).
* New - Documentation: We have a video demonstrating how to reset your UCF password.  You can view this video [here]([~43~]).
* New - Editor: The editor now supports undo and redo functionality.  When using the editor, you can find undo and redo buttons at the bottom center of the page.
* Bug Fix - Fixed a bug in the Repository which caused very long Object or Instance names to stretch panels and windows.
* Bug Fix - The last version incorrectly removed the white background for media elements in the Viewer interface.  Media elements in the Viewer will now have the correct white background.
* Bug Fix - Fixed various small bugs and typos.

Thursday, May 7 2009 (v1.3)
===========================

Obojobo is now at version 1.3.  Read about some of the key new features and changes below;

* New - Obojobo is now more integrated into UCF, joining other applications such as Webcourses which now uses the same UCF NID Password.
* New - Score reporting has been made more robust.  You can now specify how Obojobo calculates the final score for instances with more than one attempt.  You may choose for Obojobo to use the highest attempt score, the most recent attempt score or to take an average of all attempt scores.
* New - Editor: Short Answer questions have been revamped.  Now you may specify a list of possible correct answer choices instead of a single correct answer.
* New - Editor: We have added media capability for Multiple Choice and Short Answer questions.
* New - Editor: We have added question bank features in the form of Question Alternates.  This allows you to create one question that contains multiple alternate forms.  To enable this feature, click on the 'Options' button in the Assessment section of the Editor.
* New - Editor: The 'Description' field (in the Metadata section) has been transformed into 'Notes'.  We felt that the description field was too redundant with the learning objective.  This more open-ended notes field is presented to you in the Repository but is not presented to students.  Therefore this field is now optional.
* New - Editor: You can now export and import questions using Respondus formatting.
* New - Repository: We have created the 'Visit Visualizer', a tool that allows you to see a visualization of how a user progressed through an instance.
* New - Repository: Reorganized drafts and masters to be listed in one list instead of separating them.  This prevents having to switch between a drafts list and masters list.
* New - Repository: Lists of users and scores now sort by last name instead of first name.  In addition, filter controls have been re-written so that searches are more intelligent in finding what you are searching for.
* New - Viewer: The display of the overview page has been streamlined.  In addition, the course information is displayed to help the student better identify what the instance is used for.
* Bug Fix - Fixed a bug that would drop most permissions when a draft object was turned into a master.  Now permissions are preserved when creating a master from a draft.
* Bug Fix - The Viewer was using the title on the learning object instead of the title of the instance.  Now instances correctly use the instance title.
* Bug Fix - Fixed an issue where left/right content pages flipped the content in the Viewer.
* Bug Fix - Fixed an issue in the Viewer practice/assessment quizzes where the radio button of a possible multiple choice answer didn't align vertically with the answer choice text.

Thursday, November 20 2008
==========================

* New - Editor:  The entire editor interface has been re-built using Flex.  The interface should be easier to use and better integrated into the Repository.  The old editor will still be available till the next update (to use the old editor, In the Repository, click the "My Account" button, select the "Preferences" tab, then check "Use the old Editor Interface" check box).
* New - Editor: WYSIWYG text input fields allow you to set text formatting options using a clean interface.
* New - Editor: Spell checking is now enabled on nearly every text input with exception of the objective builder and keywords input.
* New - Video: Obojobo now supports upload and playback of flash video files (.FLV) and externally linked YouTube videos.  (note that YouTube videos are hosted on an external network, and may not be available for various uncontrollable reasons.)
* New - Documentation: A whole new section for creating learning objects is available in the help (note, you must be logged in and have creator privileges)
* Bug Fix - Repository: When viewing the "Instance Details" tab in the Published Instances section, the Download Scores button does not function properly.
* Bug Fix - Repository: Download Scores - Downloading scores may fail on instances with many scores.
* Bug Fix - Editor: Mouse wheel support is partially supported.
* Bug Fix - Editor: When opening the editor, you may receive a message similar to the following: "This object, 'undefined', is currently being edited by undefined undefined and therefore has been locked."
* Bug Fix - Editor: When adding or editing multiple choice questions, on occasion, the multiple choice interface may glitch.


Thursday, October 24 2008
==========================

* Bug Fix - Viewer: Students can now open multiple learning objects at once.  Previously this caused some errors in the tracking data.
* Change - The "Save Answer" button has been eliminated for multiple choice questions. Answers are automatically saved so there is no need to hit any extra buttons.
* Bug Fix - Obojobo now supports Adobe Flash Player 9 and 10.

Thursday, June 26 2008
======================

* Bug Fix - Repository: CSV files now report the user id as 'User ID', which is the expected format for webcourses.  Previously this was labeled as 'UserID'.
</markdown>";s:8:"richtext";s:1:"1";s:8:"template";s:1:"4";s:9:"menuindex";s:1:"3";s:10:"searchable";s:1:"1";s:9:"cacheable";s:1:"1";s:9:"createdby";s:1:"1";s:9:"createdon";s:10:"1224880080";s:8:"editedby";s:1:"8";s:8:"editedon";s:10:"1292954169";s:7:"deleted";s:1:"0";s:9:"deletedon";s:1:"0";s:9:"deletedby";s:1:"0";s:11:"publishedon";s:10:"1224883109";s:11:"publishedby";s:1:"1";s:9:"menutitle";s:0:"";s:7:"donthit";s:1:"0";s:11:"haskeywords";s:1:"0";s:11:"hasmetatags";s:1:"0";s:10:"privateweb";s:1:"0";s:10:"privatemgr";s:1:"0";s:13:"content_dispo";s:1:"0";s:8:"hidemenu";s:1:"0";}';
$ASE_doc_map_to_tv_value_raw = <<<'NOWDOC'
a:0:{}'
NOWDOC;
$ASE_doc_map_to_group_raw = <<<'NOWDOC'
a:0:{}'
NOWDOC;
?>