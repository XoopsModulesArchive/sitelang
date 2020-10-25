<?php
// $Id: functions.php,v 1.3 2005/05/13 23:47:15 rowd Exp $
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System  				                    //
// Copyright (c) 2000 XOOPS.org                         					//
// <https://www.xoops.org>                        						    //
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
// it under the terms of the GNU General Public License as published by     //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
// 																			//
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
// 																			//
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
// 																			//
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
//  Module        : sitelang (File: functions.php)                          //
//  Creation date : 02-November-2004                                        //
//  Author        : Rowd ( http://keybased.net/dev/ )                       //
//  ----------------------------------------------------------------------- //

function adminLangMenu($currentoption = 0, $breadcrumb = '')
{
    /* Nice buttons styles */

    echo "
    	<style type='text/css'>
    	#buttontop { float:left; width:100%; background: #e7e7e7; font-size:93%; line-height:normal; border-top: 1px solid black; border-left: 1px solid black; border-right: 1px solid black; margin: 0; }
    	#buttonbar { float:left; width:100%; background: #e7e7e7 url('" . XOOPS_URL . "/modules/sitelang/admin/images/bg.gif') repeat-x left bottom; font-size:93%; line-height:normal; border-left: 1px solid black; border-right: 1px solid black; margin-bottom: 12px; }
    	#buttonbar ul { margin:0; margin-top: 15px; padding:10px 10px 0; list-style:none; }
		#buttonbar li { display:inline; margin:0; padding:0; }
		#buttonbar a { float:left; background:url('" . XOOPS_URL . "/modules/sitelang/admin/images/left_both.gif') no-repeat left top; margin:0; padding:0 0 0 9px; border-bottom:1px solid #000; text-decoration:none; }
		#buttonbar a span { float:left; display:block; background:url('" . XOOPS_URL . "/modules/sitelang/admin/images/right_both.gif') no-repeat right top; padding:5px 15px 4px 6px; font-weight:bold; color:#765; }
		/* Commented Backslash Hack hides rule from IE5-Mac \*/
		#buttonbar a span {float:none;}
		/* End IE5-Mac hack */
		#buttonbar a:hover span { color:#333; }
		#buttonbar #current a { background-position:0 -150px; border-width:0; }
		#buttonbar #current a span { background-position:100% -150px; padding-bottom:5px; color:#333; }
		#buttonbar a:hover { background-position:0% -150px; }
		#buttonbar a:hover span { background-position:100% -150px; }
		.extcaladmin { float: left; margin-bottom: 20px; }
		</style>
    ";

    global $xoopsModule;

    $selbox = languagesSelectBox(false, 'langname');

    $selform = "<form name='languages_form' id='languages_form' action='" . basename($_SERVER['REQUEST_URI']) . "' method='post' onchange='submit()'>\n";

    if (isset($_POST['current_mod']) && !empty($_POST['current_mod'])) {
        $selform .= "<input type='hidden' name='current_mod' value='" . (int)$_POST['current_mod'] . "'>";
    }

    $selform .= _AM_LANG_CURRENTLANG . ':&nbsp;' . $selbox . '&nbsp;&nbsp;|&nbsp;' . _AM_LANG_CHARACTERSET . ': ' . _CHARSET;

    $selform .= '</form>';

    $tblColors = [];

    $tblColors[0] = $tblColors[1] = $tblColors[2] = $tblColors[3] = $tblColors[4] = '';

    $tblColors[$currentoption] = 'current';

    echo "<div id='buttontop'>\n";

    echo "<table style=\"width: 100%; padding: 0; \" cellspacing=\"0\">\n<tr>\n";

    echo '<td style="font-size: 10px; text-align: left; color: #333333; padding: 0 6px; line-height: 18px;">' . $selform . "</td>\n";

    echo "<td style=\"font-size: 10px; text-align: right; color: #2F5376; padding: 0 6px; line-height: 18px;\">\n<b>" . $xoopsModule->name() . '</b> : ' . $breadcrumb . "\n</td>\n";

    echo "</tr>\n</table>\n";

    echo "</div>\n";

    echo "<div id='buttonbar'>\n";

    echo "<ul>\n";

    echo "<li id='" . $tblColors[0] . "'><a href=\"" . XOOPS_URL . '/modules/sitelang/admin/index.php"><span>' . _AM_LANG_TAB1 . "</span></a></li>\n";

    echo "<li id='" . $tblColors[1] . "'><a href=\"" . XOOPS_URL . '/modules/sitelang/admin/langnamemgnt.php"><span>' . _AM_LANG_TAB2 . "</span></a></li>\n";

    echo "<li id='" . $tblColors[2] . "'><a href=\"" . XOOPS_URL . '/modules/sitelang/admin/sitetextmgnt.php"><span>' . _AM_LANG_TAB3 . "</span></a></li>\n";

    echo "<li id='" . $tblColors[3] . "'><a href=\"" . XOOPS_URL . '/modules/sitelang/admin/modulesmgnt.php"><span>' . _AM_LANG_TAB4 . "</span></a></li>\n";

    echo "<li id='" . $tblColors[4] . "'><a href=\"" . XOOPS_URL . '/modules/sitelang/admin/blocksmgnt.php"><span>' . _AM_LANG_TAB5 . "</span></a></li>\n";

    echo "</ul>\n</div>\n";
}

// only get active languages, otherwise get them all
// use the langname field as text, or langdirname
// selected language id => -1=not to be considered, 0=all languages, >0 = the selected language
function languagesSelectBox($activelangs = true, $displayname = 'langname', $selectname = 'selected_lg', $selectedid = -1)
{
    global $xoopsConfig;

    $sitelangHandler = xoops_getModuleHandler('sitelang', 'sitelang');

    $sitelangs = [];

    $langcount = 0;

    $lang_options = '';

    if ($selectedid >= 0) {
        $lang_options = '<option value="0">' . _ALL . '</option>';
    }

    if ($activelangs) {
        // only get active languages

        $sitelangs = $sitelangHandler->getActiveLangs(_LANGCODE);

        $langcount = count($sitelangs);
    } else {
        // get all languages

        $sitelangs = $sitelangHandler->getAllLangs(_LANGCODE);

        $langcount = count($sitelangs);
    }

    if ($langcount > 0) {
        foreach ($sitelangs as $lang) {
            $lang_options .= '<option value="' . $lang['langid'] . '"';

            if ($selectedid >= 0) {
                if ($lang['langid'] == $selectedid) {
                    $lang_options .= ' selected="selected"';
                }
            } else {
                if ($xoopsConfig['language'] == $lang['langdirname']) {
                    $lang_options .= ' selected="selected"';
                }
            }

            if ('langname' == $displayname) {
                $lang_options .= '>' . $lang['langname'] . '</option>';
            } else {
                $lang_options .= '>' . $lang['langdirname'] . '</option>';
            }
        }
    } else {
        $lang_options .= '<option value="1"';

        $lang_options .= ' selected="selected"';

        $lang_options .= '>' . $xoopsConfig['language'] . '</option>';
    }

    $selectbox = '<select name="' . $selectname . '" onchange="submit();" size="1">' . $lang_options . '</select>';

    return $selectbox;
}

/**
 * Function to get all css file names in the sitelang/templates directory and display in a select box.
 *
 * @param mixed $selectedcss
 * @return string charset or boolean false if unsuccessful
 */
function langCssSelectBox($selectedcss = '')
{
    global $xoopsConfig;

    $dir = XOOPS_ROOT_PATH . '/modules/sitelang/css/';

    $files = [];

    $matches = [];

    $file_options = '';

    if ($dh = opendir($dir)) {
        while (false !== ($filename = readdir($dh))) {
            if (preg_match("/([\d\w\.-]+)\.(css|CSS)$/", $filename, $matches)) {
                $files[] = $matches[0];
            }
        }

        closedir($dh);
    }

    $filecount = count($files);

    if ($filecount > 0) {
        foreach ($files as $cssfile) {
            $file_options .= '<option value="' . $cssfile . '"';

            if ($selectedcss == $cssfile) {
                $file_options .= ' selected="selected"';
            }

            $file_options .= '>' . $cssfile . '</option>';
        }
    }

    $file_options .= '<option value="none"';

    if (empty($selectedcss) || ('none' == $selectedcss) || !($filecount > 0)) {
        $file_options .= ' selected="selected"';
    }

    $file_options .= '>' . _NONE . '</option>';

    $selectbox = '<select name="cssfile_select" size="1">' . $file_options . '</select>';

    return $selectbox;
}

// get all blocks for the system module is the default.
// moduleid = get all lang_blocks for this module
// blockid = selected block
// name uses the default block names
function moduleSelectBox($moduleid = 1, $selectname = 'selected_mod')
{
    // get all installed modules

    $moduleHandler = xoops_getHandler('module');

    $blocktypes = [0 => _AM_LANG_CUSTOMBLOCKS];

    $modulelist = $moduleHandler->getList(null);

    $blocktypes += $modulelist;

    $blocktypes_count = count($blocktypes);

    $mod_options = '';

    if ($blocktypes > 1) {
        foreach ($blocktypes as $mod_id => $mod_name) {
            $mod_options .= "<option value='" . $mod_id . "'";

            if ($mod_id == (int)$moduleid) {
                $mod_options .= " selected='selected'";
            }

            $mod_options .= '>' . $mod_name . '</option>';
        }
    } else {
        $mod_options .= '<option value="1"';

        $mod_options .= ' selected="selected"';

        $mod_options .= '>' . $moduleid . '</option>';
    }

    $selectbox = '<select name="' . $selectname . '" onchange="submit();" size="1">' . $mod_options . '</select>';

    return $selectbox;
}

// get all blocks for the system module is the default.
// moduleid = get all lang_blocks for this module
// blockid = selected block
// name uses the default block names
function blockSelectBox($moduleid = 1, $blockid = -1, $selectname = 'selected_blck')
{
    $db = XoopsDatabaseFactory::getDatabaseConnection();

    $sql = $sql = 'SELECT * FROM ' . $db->prefix('newblocks') . ' WHERE mid=' . $moduleid . '';

    $result = $db->query($sql);

    $newblocks = [];

    $name = '';

    while (false !== ($myrow = $db->fetchArray($result))) {
        $name = ('C' != $myrow['block_type']) ? $myrow['name'] : $myrow['title'];

        $newblocks[$myrow['bid']] = $name;
    }

    $newblockscount = count($newblocks);

    $block_options = "<option value='-1'>" . _ALL . '</option>';

    foreach ($newblocks as $blck_id => $blck_name) {
        $block_options .= "<option value='" . $blck_id . "'";

        if ($blck_id == (int)$blockid) {
            $block_options .= " selected='selected'";
        }

        $block_options .= '>' . $blck_name . '</option>';
    }

    $selectbox = '<select name="' . $selectname . '" onchange="submit();" size="1">' . $block_options . '</select>';

    return $selectbox;
}

/**
 * Function to set language values for the global XoopsConfig array
 * @param mixed $language
 * @param mixed $sitename
 * @param mixed $slogan
 */
function SetXoopsConfigLang($language, $sitename, $slogan)
{
    global $xoopsConfig;

    $myts = MyTextSanitizer::getInstance();

    $xoopsConfig['language'] = $language;

    $xoopsConfig['sitename'] = $myts->undoHtmlSpecialChars($sitename);

    $xoopsConfig['slogan'] = $myts->undoHtmlSpecialChars($slogan);
}

/**
 * Function to get a language constant from a given language file.
 * @param string or Array $filepath filepath(s) to the language file(s).
 * @param string $constantname name of the constant
 * @return string or Array constant value(s)
 * @author Rowd
 */
function getLangConstantFromFile($filepath, $constantname)
{
    if (is_array($filepath)) {
        $ret = [];

        foreach ($filepath as $file) {
            if (file_exists($file)) {
                $matches = [];

                $lines = file($file);

                $pattern = "/(define\()([\"'])" . $constantname . "\\2,\s*\\2([\d\w-]+)\\2/";

                foreach ($lines as $line_num => $line) {
                    if (preg_match($pattern, $line, $matches)) {
                        $ret[] = $matches[3];
                    }
                }
            }
        }
    } else {
        $ret = '';

        if (file_exists($filepath)) {
            $matches = [];

            $lines = file($filepath);

            $pattern = "/(define\()([\"'])" . $constantname . "\\2,\s*\\2([\d\w-]+)\\2/";

            foreach ($lines as $line_num => $line) {
                if (preg_match($pattern, $line, $matches)) {
                    $ret = $matches[3];
                }
            }
        }
    }

    return $ret;
}

/**
 * Function to synchronise the lang_* tables with newly installed/deleted modules and blocks.
 * It also sets a language to "inactive" if the core language directory has been deleted.
 */
function syncLang()
{
    require_once XOOPS_ROOT_PATH . '/modules/sitelang/class/xoopsblocklang.php';

    require_once XOOPS_ROOT_PATH . '/modules/sitelang/class/modulelang.php';

    $xoopsblocklang = new XoopsBlockLang();

    // object handlers

    $moduleHandler = xoops_getHandler('module');

    $modlangHandler = xoops_getModuleHandler('modlang', 'sitelang');

    $sitelangHandler = xoops_getModuleHandler('sitelang', 'sitelang');

    $blocklangHandler = xoops_getModuleHandler('blocklang', 'sitelang');

    // get all installed modules which are able to be used in the main menu

    $criteria = new CriteriaCompo();

    $criteria->add(new Criteria('hasmain', '1'));

    $installed_mods = $moduleHandler->getList($criteria);

    // get all lang_mod records
    $modlang = $modlangHandler->getObjects(null, true, true); // $criteria, $id_as_key, $as_objects

    // get all installed languages
    $sitelang = $sitelangHandler->getObjects(null, false, true); // $criteria, $id_as_key, $as_objects

    // get all lang_block records, with bid as key
    $blocklang = $blocklangHandler->getObjects(null, true, true); // $criteria, $id_as_key, $as_objects

    // get all blocks
    $blocks = $xoopsblocklang->getObjects(null, true); // $criteria, $id_as_key

    // if the language directory has been deleted then set the language to inactive.

    foreach ($sitelang as $lang) {
        $langdir = $lang->getVar('langdirname');

        if (!file_exists(XOOPS_ROOT_PATH . '/language/' . $langdir . '/global.php')) {
            if (0 != $lang->getVar('langisactive')) {
                $lang->setVar('langisactive', 0);

                $sitelangHandler->insert($lang, true);
            }
        }
    }

    //*************** this section synchronises lang_modules with the core modules table ****************************

    // Add lang_modules records for newly installed modules

    foreach ($installed_mods as $key => $value) {
        if (!array_key_exists($key, $modlang)) {
            foreach ($sitelang as $l) {
                // add new lang_modules record for this module/language combination

                $newmodlang = $modlangHandler->create(true);

                $newmodlang->setVars(
                    [
                        'mid' => $key,
                        'langid' => $l->getVar('langid'),
                        'modname' => $value,
                    ]
                );

                $modlangHandler->insert($newmodlang, true);

                unset($newmodlang);
            }
        }
    }

    // Check for deleted modules, need to delete lang_modules link

    unset($modlang);

    $modlang = $modlangHandler->getObjects(null, true, true); // $criteria, $id_as_key, $as_objects

    foreach ($modlang as $m) {
        if (!array_key_exists($m->getVar('mid'), $installed_mods)) {
            // handle deleting lang_modules records for this module

            $criteria = new CriteriaCompo();

            $criteria->add(new Criteria('mid', $m->getVar('mid')));

            $delmods = $modlangHandler->getObjects($criteria, false, true); // $criteria, $id_as_key, $as_objects

            foreach ($delmods as $del) {
                $modlangHandler->delete($del, true);
            }
        }
    }

    //*************** this section synchronises lang_blocks with the core newblocks table ****************************

    // Add lang_block records for newly installed blocks

    foreach ($blocks as $block) {
        if (!array_key_exists($block->getVar('bid'), $blocklang)) {
            foreach ($sitelang as $l) {
                // add new lang_blocks record for this block/language combination

                $newblocklang = $blocklangHandler->create(true);

                $blockname = ('C' != $block->getVar('block_type')) ? $block->getVar('name') : $block->getVar('title');

                $newblocklang->setVars(
                    [
                        'bid' => $block->getVar('bid'),
                        'mid' => $block->getVar('mid'),
                        'langid' => $l->getVar('langid'),
                        'langcode' => $l->getVar('langcode'),
                        'blockname' => $blockname,
                    ]
                );

                $blocklangHandler->insert($newblocklang, true);

                unset($newblocklang);
            }
        }
    }

    // Check for deleted blocks, need to delete lang_blocks

    unset($blocklang);

    $blocklang = $blocklangHandler->getObjects(null, true, true); // $criteria, $id_as_key, $as_objects

    foreach ($blocklang as $bl) {
        if (!array_key_exists($bl->getVar('bid'), $blocks)) {
            // handle deleting block_lang records for this module

            $criteria = new CriteriaCompo();

            $criteria->add(new Criteria('bid', $bl->getVar('bid')));

            $delblocks = $blocklangHandler->getObjects($criteria, false, true); // $criteria, $id_as_key, $as_objects

            foreach ($delblocks as $del) {
                $blocklangHandler->delete($del, true);
            }
        }
    }
}

/**
 * Functions for checking existence of a MySQL table
 *
 * @author Hervé
 * @param mixed $tablename
 * @return bool
 */
function SitelangTableExists($tablename)
{
    global $xoopsDB;

    $result = $xoopsDB->queryF("SHOW TABLES LIKE '$tablename'");

    return ($xoopsDB->getRowsNum($result) > 0);
}

function SitelangFieldExists($fieldname, $table)
{
    global $xoopsDB;

    $result = $xoopsDB->queryF("SHOW COLUMNS FROM	$table LIKE '$fieldname'");

    return ($xoopsDB->getRowsNum($result) > 0);
}

function SitelangAddField($field, $table)
{
    global $xoopsDB;

    $result = $xoopsDB->queryF('ALTER TABLE ' . $table . " ADD $field;");

    return $result;
}

function SitelangChangeField($field, $table)
{
    global $xoopsDB;

    $result = $xoopsDB->queryF('ALTER TABLE ' . $table . " CHANGE $field;");

    return $result;
}
