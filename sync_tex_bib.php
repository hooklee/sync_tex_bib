<?php

	/*********************** DEVELOPMENT HISTORY ******************************/
	// Deceloper: Shujun Li @ www.hooklee.com
	// v2.1 11 July 2021
	// -- Fixed some bugs
	// -- Added a new feature for excluding inline comments in the .tex file
	// v2.0 3 January 2021
	// -- Revised the way how BibTeX items are extracted to be more robust
	// -- New feature: processing inline comments between BibTeX items
	// -- New feature: processing the final text after the last BibTeX item
	// -- New feature: changing BibTeX filed names and item type to lower case
	// -- Changed the option for removing unwanted fields and the default value
	// -- Made processing of input arguments more robust
	// -- Improved error and warning messages
	// v1.2 11 October 2020
	// -- Added a new option -amc for adding a missing comma to a field line
	// -- Corrected a minor typo.
	// v1.1 24 February 2019
	// -- Fixed some bugs and enhanced fault tolerance
	// -- Added a feature to process BibTeX fields across multipline lines
	// -- Changed the manual configuration to command line arguments
	// -- Added key-based sorting
	/********************** Predecessor ***************************************/
	// sync_tex_bib was derived from Martin Rebane's bib-matcher.
	// v1.0 2018 Martin Rebane @ https://github.com/martinrebane
	// https://github.com/martinrebane/bib-matcher
	/********************** END OF DEVELOPMENT HISTORY ************************/

	/***************************** MAIN SCRIPT ********************************/

	// Initialise the variable for storing the input .tex file's name/path.
	$tex_file = '';
	// Initialise the variable for storing the input .bib file's name/path.
	$bib_file = '';
	// Initialise the variable for storing the output .bib file's name/path.
	$output_bib_file = '';

	// The prefixes of all LaTeX commands used to cite BibTeX items in the .tex file,
	// e.g., "accoding to Smith \cite{smith_2009} this..."
	// Use prefixes because there are citation commands with the same prefix, e.g., \citep, \citet, \citeauthor, \citeyear.
	$cite_command_prefixes = 'cite';
	
	// Initialise the flag for excluding inline comments in the .tex file.
	$exclude_tex_comments = true;

	// Comma separated fields of bibliography fields that you would like to remove
	// EXAMPLE: $ignore_tags_str = 'url,urldate'; will remove "url" and "urldate" fields
	// By default, remove the following fields that are more likely unnecessary for most bibliographic styles.
	$ignore_tags_str_default = 'abstract,address,isbn,issn,keywords,file,location,timestamp,biburl,bibsource,month';
	$ignore_tags_str = $ignore_tags_str_default;
	
	// Initialise the flag of sorting the BibTeX items (by key).
	$sort_by_keys = false;
	// Initialise the flag of adding a missing comma to field lines.
	$add_missing_comma = false;
	// Initialise the flag of changing using lower case for all field names and BibTeX item types.
	$use_lowercase_names = true;
	// Initialise the string storing the final text after the last BibTeX item.
	$bib_leading_text = '';
	// Initialise the flag on how to process the final text after the last BibTeX item.
	$keep_leading_text = true;
	// Initialise the flag on how to process the inline comments between BibTeX items.
	$keep_inline_comments = true;
	// Initialise the string storing the final text after the last BibTeX item.
	$bib_final_text = '';
	// Initialise the flag on how to process the final text after the last BibTeX item.
	$keep_final_text = false;

	// Parse the command line arguments list.
	if (count($argv) <= 2) {
		usage($argv[0]);
	}
	$i = 1;
	while($i<count($argv)) {
		switch (strtolower($argv[$i])) {
			case '-t':
				if (++$i < count($argv))
					$tex_file = $argv[$i];
				break;
			case '-b':
				if (++$i < count($argv))
					$bib_file = $argv[$i];
				break;
			case '-o':
				if (++$i < count($argv))
					$output_bib_file = $argv[$i];
				break;
			case '-r':
				if ($i+1 < count($argv)) {
					if ($argv[$i+1][0] != '-') {
						// Make all tags lower case to allow case-insensitive search.
						// Only increase $i if the next argument is the -r list.
						$ignore_tags_str = strtolower($argv[++$i]);
					}
					else {
						$ignore_tags_str = ''; // If -r does not follow any list, reset $ignore_tags_str.
					}
				}
				else {
					$ignore_tags_str = ''; // If -r is the last option, reset $ignore_tags_str.
				}
				break;
			case '-s':
				$sort_by_keys = true;
				break;
			case '-cp':
				if (++$i < count($argv))
					$cite_command_prefixes = $argv[$i];
				break;
			case '-n':
				$use_lowercase_names = false;
				break;
			case '-rlt':
				$keep_leading_text = false;
				break;
			case '-ric':
				$keep_inline_comments = false;
				break;
			case '-kft':
				$keep_final_text = true;
				break;
			case '-%':
				$exclude_tex_comments = false;
				break;
			case '-,':
				$add_missing_comma = true;
				break;
		}
		$i++;
	}
	if (empty($tex_file)) {
		echo "Error: The .tex file is requred!\n";
		usage($argv[0]);
	}
	if (strcasecmp(substr($tex_file,-4,4), '.tex') != 0) {
		$tex_file = "$tex_file.tex";
		echo "Warning: The input .tex file name does not have the .tex extension! Appended automatically!\n";
	}
	if (empty($bib_file)) {
		$bib_file = str_replace('.tex', '.bib', $tex_file);
		echo "Warning: The input .bib file is not defined so the default value is used.\n";
	}
	if (strcasecmp(substr($bib_file,-4,4), '.bib') != 0) {
		$bib_file = "$bib_file.bib";
		echo "Warning: The input .bib file name does not have the .bib extension! Appended automatically!\n";
	}
	if (empty($output_bib_file)) {
		$output_bib_file = str_replace('.bib', '_output.bib', $bib_file);
		echo "Warning: The output .bib file is not defined so the default value is used.\n";
	}
	elseif (strcasecmp(substr($output_bib_file,-4,4), '.bib') != 0) {
		$output_bib_file = "$output_bib_file.bib";
		echo "Warning: The output .bib file name does not have the .bib extension! Appended automatically!\n";
	}
	echo "Input .tex file: $tex_file\n";
	echo "Input .bib file: $bib_file\n";
	echo "Output .bib file: $output_bib_file\n";
	if (empty($ignore_tags_str)) {
		echo "BibTeX fields to be removed: none\n";
	}
	else {
		echo "BibTeX fields to be removed: $ignore_tags_str\n";
	}
	if ($sort_by_keys) {
		echo "Sort all BibTeX items: true\n";
	}
	else {
		echo "Sort all BibTeX items: false\n";
	}
	if ($use_lowercase_names) {
		echo "Change all field names to lowercase: true\n";
	}
	else {
		echo "Change all field names to lowercase: false\n";
	}
	if ($add_missing_comma) {
		echo "Append a comma at the end of a field if not already present: true\n";
	}
	else {
		echo "Append a comma at the end of a field if not already present: false\n";
	}
	if (strcasecmp($bib_file, $output_bib_file) == 0) {
		exit("Error: The output .bib file is not allowed to have the same path as the input .bib file for safety reason (you may accidentally overwrite your original .bib file)!\n");
	}
	// exit();

	// Load input .tex file and find all cited BibTeX items (keys).
	$tex = file_get_contents($tex_file);
	if ($tex == false) {
		exit("The input .tex file \"$tex_file\" cannot be found or read!");
	}
	$tex_cited_keys = find_tex_cited_keys($tex);

	// Load the .bib file and search for all BibTeX items cited.
	$bib = file_get_contents($bib_file);
	if ($bib == false) {
		exit("The input .bib file \"$bib_file\" cannot be found or read!");
	}
	$used_bib_items = get_used_bib_items($bib, $tex_cited_keys);

	// Remove unwanted fields from each BibTeX item.
	$ignore_tags = explode(",", $ignore_tags_str);
	// array_filter and array_map should be used together to bring the results back.
	$ignore_tags = array_filter(array_map('trim', $ignore_tags));
	if (count($ignore_tags) > 0) {
		$used_bib_items = remove_tags($used_bib_items, $ignore_tags);
	}

	// Sort all BibTeX items according to their keys.
	if ($sort_by_keys) {
		ksort($used_bib_items);
	}

	// Write to the output .bib file.
	build_new_bib_file($output_bib_file, $used_bib_items);
	exit(0);
	
	/************************** END OF MAIN SCRIPT ****************************/
	
	/*********************** INTERNAL FUNCTIONS *******************************/
	
	// Function for printing usage of the tool.
	function usage($name) {
		global $ignore_tags_str_default;
		
		// Names of the tool
		$tool_name = 'sync_tex_bib';
		$tool_name_old = 'bib-matcher';
		
		echo "$tool_name: A tool for tidying up BibTeX files\n";
		echo "Copyright (C) 2019-2021 Shujun Li @ www.hooklee.com\n";
		echo "v2.1 11 July, 2021\n";
		echo "$tool_name was derived from Martin Rebane's $tool_name_old:\n";
		echo "Martin Rebane @ https://github.com/martinrebane\n";
		echo "$tool_name_old version May 11, 2018\n";
		echo "GitHub project: https://github.com/martinrebane/bib-matcher\n";
		echo "Usage:\n";
		echo "php $name [options]\n";
		echo "Options:\n";
		echo "-t file\t\tPath of the input .tex file (required)\n";
		echo "-b file\t\tPath of the input .bib file (default: the same name as the .tex file)\n";
		echo "-o file\t\tPath of the output .bib file (default: [input_bib_filename]_output.bib)\n";
		echo "-r [...]\tSpecify a comma-separated list of unwanted BibTeX fields for removal\n";
		echo "\t\tDefault value: $ignore_tags_str_default\n";
		echo "\t\tTo set the list to empty (i.e., keep all fields), simply use -r without any list.\n";
		echo "-s\t\tSort the BibTeX items by their keys (default: do not sort = keep the original order)\n";
		echo "-cp [...]\tSpecify a comma-separated list of citation command prefixes (default: cite)\n";
		echo "-n\t\tUse original field names and item types in all BibTeX items (default: converting to lowercase)\n";
		echo "-rlb\t\tRemove the leading text before the first BibTeX item (default: keep it)\n";
		echo "-ric\t\tRemove all inline comments between BibTeX items (default: keep them)\n";
		echo "-kfb\t\tKeep the final text after the last BibTeX item (default: remove it)\n";
		echo "-%\t\tDo not exclude inline comments in the .tex file (default: exclude)\n";
		echo "-,\t\tAdd a comma at the end of a field line if it is missing (default: do nothing)\n";
		exit();
	}

	// Function for creating the output .bib file.
	function build_new_bib_file($output_bib_file, $used_bib_items) {
		global $keep_inline_comments;
		global $keep_final_text;
		global $bib_leading_text;
		global $keep_leading_text;
		
		$file_conents = "";
		if ($keep_leading_text) {
			$file_conents .= $bib_leading_text;
		}
		foreach($used_bib_items as $used_bib_item) {
			if ($keep_inline_comments && !empty($used_bib_item['prefix'])) {
				// The last newline characters are not captured in prefix.
				$file_conents .= $used_bib_item['prefix'] . PHP_EOL;
			}
			$file_conents .= $used_bib_item['value'] . PHP_EOL; // Use the current platform's EOL string after each BibTeX item.
		}
		if ($keep_final_text) {
			$file_conents .= $bib_final_text;
		}
		if (file_put_contents($output_bib_file, $file_conents)) {
			echo "Generated the output .bib file: $output_bib_file\n";
		}
		else {
			echo "Error: Failed to generate the new .bib file: $output_bib_file!\n";
		}
	}

	// Function for removing unwanted fields from all BibTeX items.
	function remove_tags($used_bib_items, $ignore_tags) {
		global $add_missing_comma;
		global $use_lowercase_names;
		
		$filtered_bib_items = array();
		// This is used to store the previously found tag to properly handle fields that go over one line.
		// The initial value is set to TRUE so that the first line of the first BibTeX item will also be kept.
		$first_eq_sign_pos_prev = true;

		// remove fields starting with the ignorable tag
		foreach ($used_bib_items as $used_bib_item) {
			$value = $used_bib_item['value'];
			$used_bib_item['value'] = "";
			// Using explode and str_replace is much faster than using reg_split.
			// See Reed's answer here: https://stackoverflow.com/questions/1483497/how-can-i-put-strings-in-an-array-split-by-new-line
			// "\n\r" is removed as this is not known used by any OS.
			$item_lines = explode("\n", str_replace(["\r\n","\r"], "\n", $value));
			foreach ($item_lines as $line) {
				// Remove unnecessary white space at the beginning and the end of each line.
				$line = trim($line);
				$first_eq_sign_pos = strpos($line, '=');
				if ($first_eq_sign_pos !== false) {
					$field_name = trim(substr($line, 0, $first_eq_sign_pos));
					$field_name_lower = strtolower($field_name);
					// Always trim white spaces to tidy the bib file.
					$line_after_equal_sign = trim(substr($line,$first_eq_sign_pos+1));
					if ($use_lowercase_names) {
						$line =  "$field_name_lower = $line_after_equal_sign";
					}
					else {
						$line = "$field_name = $line_after_equal_sign";
					}
					// Add a comma at the end if it does not present.
					if ($add_missing_comma && substr($line,-1)!==',') {
						$line .= ',';
					}
					if (!in_array($field_name_lower, $ignore_tags)) {
						$used_bib_item['value'] .= $line . "\n";
					}
					$first_eq_sign_pos_prev = $first_eq_sign_pos;
				}
				// Copy the current line only if the previous tag is not ignored.
				// Empty lines will be skipped.
				elseif ($first_eq_sign_pos_prev!==false && !empty($line)) {
					$used_bib_item['value'] .= $line . "\n";
				}
			}
			array_push($filtered_bib_items, $used_bib_item);
		}
		return $filtered_bib_items;
	}

	// Function for constructing an array of BibTeX items cited in the .tex file.
	function get_used_bib_items($bib, $tex_cited_keys) {
		global $bib_leading_text;
		global $bib_final_text;
		global $use_lowercase_names;
		
		//The reusable recursive regular expression is inspired by:
		// https://www.php.net/manual/en/regexp.reference.recursive.php#111935
		$bracket_system = '(\{(?:(?>[^{}])+|(?-1))*\})';
		preg_match_all('/\s*@\w+\s*'.$bracket_system.'/u', $bib, $bib_items, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		if (empty($bib_items)) {
			exit("Warning: The input .bib file does not have any BibTeX items!\n");
		}
		// Keep the whole patterns matched only (which correspond to the complete BibTeX items).
		$bib_items = $bib_items[0];
		if (empty($bib_items)) {
			exit("Warning: The input .bib file does not have any BibTeX items!\n");
		}
		// For faulty tolerance.
		if (count($bib_items[0]) <2) {
			exit("Error (for debugging purposes): Unexpected error for preg_match_all() function!\n");
		}

		// Get the leading text before the first BibTeX item.
		$bib_leading_text = substr($bib, 0, $bib_items[0][1]);
		$bib_items[0]['prefix'] = '';
		// Find possible inline comments before BibTeX items and attacth them as the prefix from the second BibTeX item.
		$pos_pre = $bib_items[0][1] + strlen($bib_items[0][0]);
		for($i=1; $i<count($bib_items); $i++) {
			$bib_item = $bib_items[$i];
			if ($bib_item[1]>$pos_pre) {
				$bib_items[$i]['prefix'] = trim(substr($bib, $pos_pre, $bib_item[1]-$pos_pre));
				// echo "Found inline comment: '" . $bib_items[$i]['prefix'] . "'\n";
			}
			else {
				$bib_items[$i]['prefix'] = '';
			}
			$pos_pre = $bib_item[1] + strlen($bib_item[0]);
		}
		$bib_final_text = substr($bib, $pos_pre);
		
		$used_bib_items = array();
		$found_keys = array();
		
		foreach ($bib_items as $bib_item) {
			$value = $bib_item[0];
			$start_pos = strpos($value, '{') + 1;
			// Get the item type between @ and {...}.
			$item_type = substr($value, 0, $start_pos);
			// Change the item type to lower case if required.
			if ($use_lowercase_names) {
				$value = strtolower($item_type) . substr($value, $start_pos);
			}
			// Get the item key in the BibTeX item immediately after @...{ and before the first comma.
			$end_pos = strpos($value, ',');
			$key = substr($value, $start_pos, $end_pos - $start_pos);
			// Add both the BibTeX item and the inline comments before it.
			if (in_array($key, $tex_cited_keys)) {
				$used_bib_items[$key]['value'] = $value;
				$used_bib_items[$key]['prefix'] = $bib_item['prefix'];
				// Add the key into a list of found BibTeX item keys, for sanity check and displaying a warning message later.
				array_push($found_keys, $key);
			}
		}

		// Terminate the script if no any BibTeX keys cited in the input .tex file are found in the input .bib file.
		if (count($used_bib_items) == 0) {
			exit("Error: The input .bib file does not contain no any BibTeX keys cited in the input .tex file!\n");
		}

		// Display all BibTeX keys cited in the input .tex file that were found in the input .bib file.
		asort($found_keys);
		echo count($used_bib_items) . ' BibTeX keys cited in the input .tex file (out of '. count($tex_cited_keys) . " ones) are found in the .bib file:\n";
		echo implode(", ", $found_keys) . "\n";
		// If the cited BibTeX keys do not all appear in the input .bib file, display some warning messages.
		$mismatch = array_diff($tex_cited_keys, $found_keys);
		if (count($mismatch) > 0) {
			echo "Warning: The following BibTeX keys cited in the input .tex file cannot be found in the input .bib file:\n" . implode(', ', $mismatch) . "\n";
		}
		
		return $used_bib_items;
	}

	// Function for constructing an array of all BibTeX keys cited in the .tex file.
	function find_tex_cited_keys($tex) {
		global $cite_command_prefixes;
		global $exclude_tex_comments;

		// Produce a list of all prefixes.
		$cite_command_prefixes = explode(',', $cite_command_prefixes);
		// Remove unwanted white spaces around each prefix.
		$cite_command_prefixes = array_filter(array_map('trim', $cite_command_prefixes));
		// Convert the list into |-separated for the regular expression matches below.
		$cite_command_prefixes = implode('|', $cite_command_prefixes);
		
		$lists_keys = array();
		
		//Remove commented contents
		if ($exclude_tex_comments) {
			$tex = preg_replace('/^([^\\\\%]*(?:\\\\\\\\)*[^\\\\%]*)%.*$/m', '${1}', $tex);
		}

		// Remove optional arguments from citation commands.
		$tex = preg_replace('/\\\\((?:'.$cite_command_prefixes.')\w*)\[.*?\]{/u', '\\$1{', $tex);

		// Find all citation commands.
		$cite_command_count = preg_match_all('/\\\\(?:(?:'.$cite_command_prefixes.')\w*){(.*?)}/u', $tex, $lists_keys, PREG_PATTERN_ORDER);
		if (count($lists_keys)<2) {
			exit("Error: The input .tex file contains no any citation commands!\n");
		}
		// Remove duplicate key lists and keep the contents of the remaining citation commands (lists of BibTeX item keys) only.
		$lists_keys = array_unique($lists_keys[1]);

		$cited_keys = array();
		foreach ($lists_keys as $list_keys) {
			// Produce a list of all keys.
			$keys = explode(',', $list_keys);
			// Trim unwanted white spaces around each key.
			$keys = array_filter(array_map('trim', $keys));
			// Add newly found keys.
			$cited_keys = array_merge($cited_keys, $keys);
		}
		// Remove duplicate keys.
		$cited_keys = array_unique($cited_keys);

		// Terminate the script if no any cited keys are found.
		if (count($cited_keys) == 0) {
			exit("Error! The .tex file contains no any citations! Return without processing the .bib file!\n");
		}
		echo "Found $cite_command_count citation commands and " . count($cited_keys) . " unique BibTeX items cited in the .tex file:\n";
		asort($cited_keys);
		echo implode(', ', $cited_keys) . "\n";

		return $cited_keys;
	}

	/******************** END OF INTERNAL FUNCTIONS ***************************/
