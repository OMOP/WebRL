<?php
// WebSVN - Subversion repository viewing via the web using PHP
// Copyright (C) 2004-2006 Tim Armes
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
//
// --
//
// blame.php
//
// Show the blame information of a file.
//

require_once 'include/setup.php';
require_once 'include/svnlook.php';
require_once 'include/utils.php';
require_once 'include/template.php';

$vars['action'] = $lang['BLAME'];

// Make sure that we have a repository
if ($rep) {
	$svnrep = new SVNRepository($rep);

	// If there's no revision info, go to the lastest revision for this path
	$history = $svnrep->getLog($path, '', '', false, 2, $peg);
	$youngest = ($history) ? $history->entries[0]->rev : 0;

	if (empty($rev)) {
		$rev = $youngest;
	} else {
		$history = $svnrep->getLog($path, $rev, '', false, 2, $peg);
	}

	if ($path{0} != '/') {
		$ppath = '/'.$path;
	} else {
		$ppath = $path;
	}

	// Find the parent path (or the whole path if it's already a directory)
	$pos = strrpos($ppath, '/');
	$parent = substr($ppath, 0, $pos + 1);

	$vars['rev'] = $rev;
	$vars['peg'] = $peg;
	$vars['path'] = htmlentities($ppath, ENT_QUOTES, 'UTF-8');

	if ($history) {
		$vars['log'] = xml_entities($history->entries[0]->msg);
		$vars['date'] = $history->entries[0]->date;
		$vars['author'] = $history->entries[0]->author;
	}

	createPathLinks($rep, $ppath, $passrev, $peg);
	$passRevString = createRevAndPegString($passrev, $peg);

	if ($rev != $youngest) {
		$vars['goyoungesturl'] = $config->getURL($rep, $path, 'blame').($peg ? 'peg='.$peg : '');
		$vars['goyoungestlink'] = '<a href="'.$vars['goyoungesturl'].'">'.$lang['GOYOUNGEST'].'</a>';
	}

	$vars['revurl'] = $config->getURL($rep, $path, 'revision').$passRevString;
	$vars['revlink'] = '<a href="'.$vars['revurl'].'">'.$lang['LASTMOD'].'</a>';

	$vars['logurl'] = $config->getURL($rep, $path, 'log').$passRevString;
	$vars['loglink'] = '<a href="'.$vars['logurl'].'">'.$lang['VIEWLOG'].'</a>';

	$vars['filedetailurl'] = $config->getURL($rep, $path, 'file').$passRevString;
	$vars['filedetaillink'] = '<a href="'.$vars['filedetailurl'].'">'.$lang['FILEDETAIL'].'</a>';

	if ($history == null || count($history->entries) > 1) {
		$vars['diffurl'] = $config->getURL($rep, $path, 'diff').$passRevString;
		$vars['difflink'] = '<a href="'.$vars['diffurl'].'">'.$lang['DIFFPREV'].'</a>';
	}

	if ($rep->isRssEnabled()) {
		$vars['rssurl'] = $config->getURL($rep, $path, 'rss').($peg ? 'peg='.$peg : '');
		$vars['rsslink'] = '<a href="'.$vars['rssurl'].'">'.$lang['RSSFEED'].'</a>';
	}

	// Check for binary file type before grabbing blame information.
	$svnMimeType = $svnrep->getProperty($path, 'svn:mime-type', $rev, $peg);

	if (!$rep->getIgnoreSvnMimeTypes() && preg_match('~application/*~', $svnMimeType)) {
		$vars['warning'] = 'Cannot display blame info for binary file. (svn:mime-type = '.$svnMimeType.')';
		$vars['javascript'] = '';
	} else {
		// Get the contents of the file
		$tfname = tempnam($config->getTempDir(), '');
		$highlighted = $svnrep->getFileContents($path, $tfname, $rev, $peg, '', true);

		if ($file = fopen($tfname, 'r')) {
			// Get the blame info
			$tbname = tempnam($config->getTempDir(), '');

			$svnrep->getBlameDetails($path, $tbname, $rev, $peg);

			if ($blame = fopen($tbname, 'r')) {
				// Create an array of version/author/line

				$index = 0;
				$seen_rev = array();
				$last_rev = '';
				$row_class = '';

				while (!feof($blame) && !feof($file)) {
					$blameline = fgets($blame);

					if ($blameline != '') {
						list($revision, $author, $remainder) = sscanf($blameline, '%d %s %s');
						$empty = !$remainder;

						$listing[$index]['lineno'] = $index + 1;

						if ($last_rev != $revision) {
							$url = $config->getURL($rep, $parent, 'revision');
							$listing[$index]['revision'] = '<a id="l'.$index.'-rev" class="blame-revision" href="'.$url.'rev='.$revision.'&amp;peg='.$rev.'">'.$revision.'</a>';
							$seen_rev[$revision] = 1;
							$row_class = ($row_class == 'light') ? 'dark' : 'light';
							$listing[$index]['author'] = $author;
						} else {
							$listing[$index]['revision'] = '';
							$listing[$index]['author'] = '';
						}

						$listing[$index]['row_class'] = $row_class;
						$last_rev = $revision;

						$line = rtrim(fgets($file));
						if (!$highlighted)
							$line = replaceEntities($line);
						$listing[$index]['line'] = ($empty) ? '&nbsp;' : wrapInCodeTagIfNecessary($line);
						$index++;
					}
				}
				fclose($blame);
			}
			fclose($file);
			@unlink($tbname);
		}
		@unlink($tfname);

		// Build the necessary JavaScript as an array of lines, then join them with \n
		$javascript = array();
		$javascript[] = '<script type="text/javascript" src="'.$locwebsvnhttp.'/javascript/blame-popup.js"></script>';
		$javascript[] = '<script type="text/javascript">';
		$javascript[] = '/* <![CDATA[ */';
		$javascript[] = 'var rev = new Array();';

		ksort($seen_rev); // Sort revisions in descending order by key
		if (empty($peg))
			$peg = $rev;
		if (!isset($vars['warning'])) {
			foreach ($seen_rev as $key => $val) {
				$history = $svnrep->getLog($path, $key, $key, false, 1, $peg);
				if ($history) {
					$javascript[] = 'rev['.$key.'] = \'<div class="date">'.$history->curEntry->date.'</div><div class="msg">'.addslashes(preg_replace('/\n/', ' ', $history->curEntry->msg)).'</div>\';';
				}
			}
		}
		$javascript[] = '/* ]]> */';
		$javascript[] = '</script>';
		$vars['javascript'] = implode("\n", $javascript);
	}

	if (!$rep->hasReadAccess($path, false)) {
		$vars['error'] = $lang['NOACCESS'];
	}
}

$vars['template'] = 'blame';
$template = ($rep) ? $rep->getTemplatePath() : $config->getTemplatePath();
parseTemplate($template.'header.tmpl', $vars, $listing);
parseTemplate($template.'blame.tmpl', $vars, $listing);
parseTemplate($template.'footer.tmpl', $vars, $listing);
