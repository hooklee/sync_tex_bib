# sync_tex_bib
A PHP script for tidying a .bib file and synchronise it with the .tex file using the .bib file.

Deceloper
=======

2019-2021 Shujun Li @ www.hooklee.com

Development History
=======

v2.1 11 July 2021
----

*Added a new feature for excluding inline comments in the .tex file
*Fixed some bugs

v2.0 3 January 2021
----

*Revised the way how BibTeX items are extracted to be more robust
*New feature: processing inline comments between BibTeX items
*New feature: processing the final text after the last BibTeX item
*New feature: changing BibTeX filed names and item type to lower case
*Changed the option for removing unwanted fields and the default value
*Made processing of input arguments more robust
*Improved error and warning messages

v1.2 11 October 2020
----

*Added a new option -amc for adding a missing comma to a field line
*Corrected a minor typo.

v1.1 24 February 2019
----

*Fixed some bugs and enhanced fault tolerance
*Added a feature to process BibTeX fields across multipline lines
*Changed the manual configuration to command line arguments
*Added key-based sorting

Predecessor
----

sync_tex_bib was derived from [Martin Rebane's bib-matcher v1.0](https://github.com/martinrebane/bib-matcher), but has been substantially rewritten and extended to support a range of new features and future extensions.
