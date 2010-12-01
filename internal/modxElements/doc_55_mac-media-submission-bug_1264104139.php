<?php
$ASE_timestamp = '1264104139';
$ASE_time = 'January 21, 2010, 3:02 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_doc_raw = <<<'NOWDOC'
a:37:{s:2:"id";s:2:"55";s:4:"type";s:8:"document";s:11:"contentType";s:9:"text/html";s:9:"pagetitle";s:24:"Mac Media Submission Bug";s:9:"longtitle";s:0:"";s:11:"description";s:0:"";s:5:"alias";s:24:"mac-media-submission-bug";s:15:"link_attributes";s:0:"";s:9:"published";s:1:"1";s:8:"pub_date";s:1:"0";s:10:"unpub_date";s:1:"0";s:6:"parent";s:2:"12";s:8:"isfolder";s:1:"0";s:9:"introtext";s:0:"";s:7:"content";s:2958:"<markdown>
<div style="padding: 10px; background-color: yellow; font-weight: bold;">
This article refers to a bug affecting an older version of Obojobo (1.3.1).  The bug detailed in this article has been resolved as of Obojobo version 1.3.2.
</div>

About
=====

Obojobo is affected by a bug in version 10.0.32.18 of the Mac Flash Player (To see which version of the Flash Player you have, visit [http://playerversion.com/](http://playerversion.com/)).  This bug causes media questions in the assessment sections in the Viewer to sometimes not send a score to Obojobo.  The bug affects all Mac browsers.  Windows users are not affected.

The Obojobo development team is currently looking into this issue.  In the meantime, read below for more information on this bug as well as how to workaround it.

Detail
======

This bug affects media questions in the Obojobo assessment.  Media questions contain interactive components and graphics that differ from standard multiple choice and fill-in-the-blank questions.  In addition, media questions contain a "Reload Media" button.  You can see an example of a media question below:

<img class="mediacenter" src="/assets/images/help/general/blue-bar-1.png" alt="Figure 1: Reload Media Button" />

When you complete or submit the media question, a blue bar which says "Progress Saved" will pop-up underneath the media piece.  The bug will sometimes cause this bar to not show up.  If you **do not** see this blue bar after submitting or completing the media question, then your score for this question **has not** been recorded into Obojobo.

<img class="mediacenter" src="/assets/images/help/general/blue-bar-2.png" alt="Figure 2: Blue Bar Example" />

When you finish your assessment attempt, if you receive an "Incomplete Question Warning" dialog then one or more questions do not have recorded responses.

<img class="mediacenter" src="/assets/images/help/general/incomplete-question-warning.png" alt="Figure 3: Incomplete Question Warning" />

If you believe you have answered all of the questions and you receive this dialog, go back to each question (especially media questions) and make sure that you see the blue bar described above for each question.

Workaround
==========

There are two available workarounds to resolve this issue:

1. Completely close all browsers and restart your preferred browser.  In our testing this always resolves the issue.  Note that if you are in the middle of an assessment quiz, Obojobo can resume your quiz from where you left off.
2. Use Obojobo under Windows (either through a Windows computer, a Windows Boot Camp install on your Mac, or through virtualization software such as Parallels or VMWare Fusion).

In addition, down-grading your Flash Player to an older version may resolve this issue, however this is not recommended as it imposes a security risk.

When this issue is resolved this article will be updated.
</markdown>";s:8:"richtext";s:1:"1";s:8:"template";s:1:"4";s:9:"menuindex";s:1:"9";s:10:"searchable";s:1:"1";s:9:"cacheable";s:1:"1";s:9:"createdby";s:1:"8";s:9:"createdon";s:10:"1251752770";s:8:"editedby";s:1:"8";s:8:"editedon";s:10:"1264104139";s:7:"deleted";s:1:"0";s:9:"deletedon";s:1:"0";s:9:"deletedby";s:1:"0";s:11:"publishedon";s:10:"1251757365";s:11:"publishedby";s:1:"8";s:9:"menutitle";s:0:"";s:7:"donthit";s:1:"0";s:11:"haskeywords";s:1:"0";s:11:"hasmetatags";s:1:"0";s:10:"privateweb";s:1:"0";s:10:"privatemgr";s:1:"0";s:13:"content_dispo";s:1:"0";s:8:"hidemenu";s:1:"0";}';
$ASE_doc_map_to_tv_value_raw = <<<'NOWDOC'
a:0:{}'
NOWDOC;
$ASE_doc_map_to_group_raw = <<<'NOWDOC'
a:0:{}'
NOWDOC;
?>