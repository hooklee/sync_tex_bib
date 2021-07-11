# sync_tex_bib
A PHP script for tidying a .bib file and synchronise it with the .tex file using the .bib file.

Deceloper
=======

2019-2021 Shujun Li @ www.hooklee.com

Usage
====

    php sync_tex_bib.php [options]
    Options:
    -t file         Path of the input .tex file (required)
    -b file         Path of the input .bib file (default: the same name as the .tex file)
    -o file         Path of the output .bib file (default: [input_bib_filename]_output.bib)
    -r [...]        Specify a comma-separated list of unwanted BibTeX fields for removal
                    Default value: abstract,address,isbn,issn,keywords,file,location,timestamp,biburl,bibsource,month
                    To set the list to empty (i.e., keep all fields), simply use -r without any list.
     -s              Sort the BibTeX items by their keys (default: do not sort = keep the original order)
    -cp [...]       Specify a comma-separated list of citation command prefixes (default: cite)
    -n              Use original field names and item types in all BibTeX items (default: converting to lowercase)
    -rlb            Remove the leading text before the first BibTeX item (default: keep it)
    -ric            Remove all inline comments between BibTeX items (default: keep them)
    -kfb            Keep the final text after the last BibTeX item (default: remove it)
    -%              Do not exclude inline comments in the .tex file (default: exclude)
    -,              Add a comma at the end of a field line if it is missing (default: do nothing)

Development History
=======

v2.1 (11 July 2021)
----

* Added a new feature for excluding inline comments in the .tex file
* Fixed some bugs

v2.0 (3 January 2021)
----

* Revised the way how BibTeX items are extracted to be more robust
* New feature: processing inline comments between BibTeX items
* New feature: processing the final text after the last BibTeX item
* New feature: changing BibTeX filed names and item type to lower case
* Changed the option for removing unwanted fields and the default value
* Made processing of input arguments more robust
* Improved error and warning messages

v1.2 (11 October 2020)
----

* Added a new option -amc for adding a missing comma to a field line
* Corrected a minor typo.

v1.1 (24 February 2019)
----

* Fixed some bugs and enhanced fault tolerance
* Added a feature to process BibTeX fields across multipline lines
* Changed the manual configuration to command line arguments
* Added key-based sorting

Predecessor
----

sync_tex_bib was derived from [Martin Rebane's bib-matcher v1.0](https://github.com/martinrebane/bib-matcher), but has been substantially rewritten and extended to support a range of new features and future extensions.
