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
// diff.php
//
// Show the differences between 2 revisions of a file.
//

require_once 'include/setup.php';
require_once 'include/svnlook.php';
require_once 'include/utils.php';
require_once 'include/template.php';

require_once 'include/diff_inc.php';

$vars['action'] = $lang['DIFF'];
$all = (@$_REQUEST['all'] == 1);
$ignoreWhitespace = (@$_REQUEST['ignorews'] == 1);

// Make sure that we have a repository
if ($rep) {
	$svnrep = new SVNRepository($rep);

	// If there's no revision info, go to the lastest revision for this path
	$history = $svnrep->getLog($path, '', '', true, 2, $peg);
	$youngest = ($history) ? $history->entries[0]->rev : 0;

	if (empty($rev)) {
		$rev = $youngest;
	}

	$history = $svnrep->getLog($path, $rev, '', false, 2, $peg);

	if ($path{0} != '/') {
		$ppath = '/'.$path;
	} else {
		$ppath = $path;
	}

	$prevrev = @$history->entries[1]->rev;

	$vars['path'] = htmlentities($ppath, ENT_QUOTES, 'UTF-8');
	$vars['rev1'] = $rev;
	$vars['rev2'] = $prevrev;
	$vars['prevrev'] = $prevrev;

	if ($history) {
		$vars['log'] = xml_entities($history->entries[0]->msg);
		$vars['date'] = $history->entries[0]->date;
		$vars['author'] = $history->entries[0]->author;
		$vars['rev'] = $vars['rev1'] = $history->entries[0]->rev;
		$vars['peg'] = $peg;
	}

	createPathLinks($rep, $ppath, $passrev, $peg);
	$passRevString = createRevAndPegString($passrev, $peg);

	if ($rev != $youngest) {
		$vars['goyoungesturl'] = $config->getURL($rep, $path, 'diff').($peg ? 'peg='.$peg : '');
		$vars['goyoungestlink'] = '<a href="'.$vars['goyoungesturl'].'">'.$lang['GOYOUNGEST'].'</a>';
	}

	$vars['revurl'] = $config->getURL($rep, $path, 'revision').$passRevString;
	$vars['revlink'] = '<a href="'.$vars['revurl'].'">'.$lang['LASTMOD'].'</a>';

	$vars['logurl'] = $config->getURL($rep, $path, 'log').$passRevString;
	$vars['loglink'] = '<a href="'.$vars['logurl'].'">'.$lang['VIEWLOG'].'</a>';

	$vars['filedetailurl'] = $config->getURL($rep, $path, 'file').$passRevString;
	$vars['filedetaillink'] = '<a href="'.$vars['filedetailurl'].'">'.$lang['FILEDETAIL'].'</a>';

	$vars['blameurl'] = $config->getURL($rep, $path, 'blame').$passRevString;
	$vars['blamelink'] = '<a href="'.$vars['blameurl'].'">'.$lang['BLAME'].'</a>';

	if ($rep->isRssEnabled()) {
		$vars['rssurl'] = $config->getURL($rep, $path, 'rss').($peg ? 'peg='.$peg : '');
		$vars['rsslink'] = '<a href="'.$vars['rssurl'].'">'.$lang['RSSFEED'].'</a>';
	}

	// Check for binary file type before diffing.
	$svnMimeType = $svnrep->getProperty($path, 'svn:mime-type', $rev);

	// If no previous revision exists, bail out before diffing
	if (!$rep->getIgnoreSvnMimeTypes() && preg_match('~application/*~', $svnMimeType)) {
		$vars['warning'] = 'Cannot display diff of binary file. (svn:mime-type = '.$svnMimeType.')';

	} else if (!$prevrev) {
		$vars['noprev'] = 1;

	} else {
		$diff = $config->getURL($rep, $path, 'diff').$passRevString;

		$passIgnoreWhitespace = ($ignoreWhitespace ? '&amp;ignorews=1' : '');
		if ($all) {
			$vars['showcompactlink'] = '<a href="'.$diff.$passIgnoreWhitespace.'">'.$lang['SHOWCOMPACT'].'</a>';
		} else {
			$vars['showalllink'] = '<a href="'.$diff.$passIgnoreWhitespace.'&amp;all=1'.'">'.$lang['SHOWENTIREFILE'].'</a>';
		}
		$passShowAll = ($all ? '&amp;all=1' : '');
		if ($ignoreWhitespace) {
			$vars['regardwhitespacelink'] = '<a href="'.$diff.$passShowAll.'">'.$lang['REGARDWHITESPACE'].'</a>';
		} else {
			$vars['ignorewhitespacelink'] = '<a href="'.$diff.$passShowAll.'&amp;ignorews=1">'.$lang['IGNOREWHITESPACE'].'</a>';
		}

		// Get the contents of the two files
		$newerFile = tempnam($config->getTempDir(), '');
		$highlightedNew = $svnrep->getFileContents($history->entries[0]->path, $newerFile, $history->entries[0]->rev, $peg, '', true);

		$olderFile = tempnam($config->getTempDir(), '');
		$highlightedOld = $svnrep->getFileContents($history->entries[1]->path, $olderFile, $history->entries[1]->rev, $peg, '', true);
		// TODO: Figured out why diffs across a move/rename are currently broken.

		$ent = (!$highlightedNew && !$highlightedOld);
		$listing = do_diff($all, $ignoreWhitespace, $ent, $newerFile, $olderFile);

		// Remove our temporary files
		@unlink($newerFile);
		@unlink($olderFile);
	}

	if (!$rep->hasReadAccess($path, false)) {
		$vars['error'] = $lang['NOACCESS'];
	}
}

$vars['template'] = 'diff';
$template = ($rep) ? $rep->getTemplatePath() : $config->getTemplatePath();
parseTemplate($template.'header.tmpl', $vars, $listing);
parseTemplate($template.'diff.tmpl', $vars, $listing);
parseTemplate($template.'footer.tmpl', $vars, $listing);
