<?php
//this file defines bit masks used to get data from bit maps

/*bit masks for permissions. see documents/permissions for details*/
//MSB: 32768 (16 bit field). if set, this overrides all other access bits
$bm_isowner = 32768;	//this bit is set if the owner is viewing their own profile
$bm_isindexed = 16384;	//set if profile is available to public search engines
$bm_issearchable = 8192;	//set if profile is available for resme search
$bm_allaccess = 63; //allows users all access to a profile


$bm_profileaccess = 1;	//defines if the profile page is visible
$bm_commentaccess = 2;	//defines if the comment page is visible
$bm_pageaccess = 3;	//defines if the profile and comment pages are visible (combo of above)
$bm_showtags = 4;	//if set, tags are displayed
$bm_writable = 8;	//if set, comments are open
$bm_downloadable = 16;	//if set, resume is downloadable
$bm_stackable = 32;	//if set, resume is stackable


$shift_resme = 6;	//used to shift the resme access bits into the right most 6 bits.

/* end permission masks */

/*
*bit masks for submission category types (used in the submission table)
*the datatype stored in the category column of the submission table is TINYINT
*this gives us 8 bits to play with.
*/
$bm_jobs = 1;
$bm_business = 2;
$bm_economy = 4;
$bm_advice= 8;

?>
