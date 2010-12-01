<?php
$ASE_timestamp = '1264001114';
$ASE_time = 'January 20, 2010, 10:25 am';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_doc_raw = <<<'NOWDOC'
a:37:{s:2:"id";s:2:"58";s:4:"type";s:8:"document";s:11:"contentType";s:9:"text/html";s:9:"pagetitle";s:33:"Previous Instance Score Importing";s:9:"longtitle";s:0:"";s:11:"description";s:0:"";s:5:"alias";s:33:"previous-instance-score-importing";s:15:"link_attributes";s:0:"";s:9:"published";s:1:"1";s:8:"pub_date";s:1:"0";s:10:"unpub_date";s:1:"0";s:6:"parent";s:2:"11";s:8:"isfolder";s:1:"0";s:9:"introtext";s:0:"";s:7:"content";s:4340:"<markdown>
About
=====

Obojobo (as of version 1.4) allows students to import a previous score for an instance they have already taken previously.  This document explains how to enable or disable this feature, covers how it works, and outlines the student user experience when presented with the option to import a previous instance score.

Setting the Option
==================

When you publish an instance, a checkbox at the bottom of the create instance window (labeled 'Allow past scores to be imported') provides you with the option of enabling or disabling this feature.  You may turn this option on and off after the fact by selecting the instance from the Published Instances section and clicking on 'Edit Details'.

<img class="mediacenter" src="/assets/images/help/create/instance-import-options.png" alt="The 'Allow past scores to be imported' option" />

How it Works
============

This feature allows students to forfeit all of their assessment attempts and instead import their highest previous attempt score in place of their final score.  This option is presented to students only if they have already taken an instance of the same learning object and you have enabled the 'Allow past scores to be imported' option).  

Example:
Assume that a student has previously taken an instance from the 'Avoiding Plagiarism 1.0' learning object.  They had three attempts, in which they scored a 60, 80, and 90.  If they later took an instance you published from the same 'Avoiding Plagiarism 1.0' learning object, then they would be allowed the option to import their '90' as their final score.

NOTE: Version numbers count.  Avoiding Plagiarism 1.0 and Avoiding Plagiarism 2.0 are considered two different objects.  If a student takes version 1.0, then later is assigned version 2.0, they will not be allowed the option to import their version 1.0 highest score.

Student User Experience
=======================

The following images assume that a student is visiting an instance which they have already taken (and the instance has the 'Allow past scores to be imported' option enabled).

After logging in, this student will be presented immediately with this dialog, informing them of the choice to import a score from a previous attempt.  Most relevant dialogs display the score that they will be given the choice to import.

<img class="mediacenter" src="/assets/images/help/create/instance-prev-attempt-found.png" alt="The 'Previous Attempts Found' window" />

Students may choose 'Review Content' to dismiss the pop-up, or 'Jump To Assessment' to navigate to the Assessment section.

At the Assessment page, a new blue box is presented with two options.  Students cannot begin the assessment until they select one of the options.

<img class="mediacenter" src="/assets/images/help/create/instance-import-landing-page.png" alt="The assessment page with the blue option box" />

If a student chooses to not import, they are presented with this confirmation dialog:

<img class="mediacenter" src="/assets/images/help/create/instance-confirm-no-import.png" alt="The confirm not to import window" />

If they confirm that they don't want to import, the blue box will disappear and the Attempts section will return to normal.  They will not be forced to begin the assessment as soon as they confirm not to import.

If a student chooses to import, they are presented with this confirmation dialog:

<img class="mediacenter" src="/assets/images/help/create/instance-confirm-import.png" alt="The confirm import window" />

If they confirm the import dialog, they will be taken to the score page, which will show their one and only score.

<img class="mediacenter" src="/assets/images/help/create/instance-score-screen.png" alt="The score screen with the imported score displayed" />

Students are required before taking the assessment to decide rather to import their previous highest attempt score or to forgo the import option and take the assessment normally.  This choice cannot be undone.  If they choose to forgo the import option, they will not be allowed to import their score later.  Conversely, a student who imports their score can not choose to take the assessment later.

NOTE: You cannot view score details for students who have imported a previous score.
</markdown>";s:8:"richtext";s:1:"1";s:8:"template";s:1:"4";s:9:"menuindex";s:2:"20";s:10:"searchable";s:1:"1";s:9:"cacheable";s:1:"1";s:9:"createdby";s:1:"8";s:9:"createdon";s:10:"1263928953";s:8:"editedby";s:1:"8";s:8:"editedon";s:10:"1264001114";s:7:"deleted";s:1:"0";s:9:"deletedon";s:1:"0";s:9:"deletedby";s:1:"0";s:11:"publishedon";s:10:"1264001114";s:11:"publishedby";s:1:"8";s:9:"menutitle";s:0:"";s:7:"donthit";s:1:"0";s:11:"haskeywords";s:1:"0";s:11:"hasmetatags";s:1:"0";s:10:"privateweb";s:1:"0";s:10:"privatemgr";s:1:"0";s:13:"content_dispo";s:1:"0";s:8:"hidemenu";s:1:"0";}';
$ASE_doc_map_to_tv_value_raw = <<<'NOWDOC'
a:0:{}'
NOWDOC;
$ASE_doc_map_to_group_raw = <<<'NOWDOC'
a:0:{}'
NOWDOC;
?>