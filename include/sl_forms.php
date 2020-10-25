<?php
// $Id: sl_forms.php,v 1.2 2005/05/12 18:31:53 rowd Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //
//  Module        : sitelang (File: sl_forms.php)                            //
//  Creation date : 03-April-2005                                            //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
/**
 * A function to display a form containing global site language settings
 *
 * @param string $mode 'e' or 'c' (representing edit or confirm)
 */
function languagesForm($mode = 'e')
{
    $myts = MyTextSanitizer::getInstance();

    // get all languages that are already in the database

    $sitelangHandler = xoops_getModuleHandler('sitelang', 'sitelang');

    $langcriteria = new CriteriaCompo();

    $langcriteria->setSort('langid');

    $languages = $sitelangHandler->getObjects($langcriteria, false, true); // $criteria, $id_as_key, $as_objects

    $languagescount = count($languages);

    // get default language

    $configHandler = xoops_getHandler('config');

    $criteria = new CriteriaCompo(new Criteria('conf_name', 'language'));

    $metatag = [];

    $config = &$configHandler->getConfigs($criteria, true);

    foreach (array_keys($config) as $i) {
        $metatag[$config[$i]->getVar('conf_name')] = $config[$i]->getVar('conf_value');
    }

    // needed for second form, installing a new language

    $listed_langs = [];

    $count = 0;

    if ('c' == $mode) {
        echo '<div>' . _AM_LANG_PLEASECONFIRM . '<br><br></div>';
    } else {
        echo "<div><a href='" . XOOPS_URL . "/modules/system/admin.php?fct=preferences&op=show&confcat_id=1'>[ " . _AM_LANG_LANGDEFAULT . ' ]</a> <br></div>';

        echo '<div>&nbsp;&nbsp;&nbsp;' . _AM_LANG_DEFAULTLANG_DESC . '<br><br></div>';
    }

    echo "
        	<form action='index.php' method='post' name='languagesform' id='languagesform'>
        	<table class='outer' cellpadding='4' cellspacing='1' align='center' width='80%'>
        	";

    // headings

    echo "<tr align='left'><th>" . _AM_LANG_LANGCODE . ' </th><th>' . _AM_LANG_LANGDIRNAME . '</th><th>' . _AM_LANG_ACTIVE . '</th>';

    if ('e' == $mode) {
        echo '<th>' . _AM_LANG_ACTION . '</th>';
    }

    echo '</tr>';

    $count = 0;

    $listed_langs = [];

    $numofchangedrows = 0;

    foreach ($languages as $lang) {
        if (0 == $count % 2) {
            $class = 'even';
        } else {
            $class = 'odd';
        }

        $count++;

        $lid = $lang->getVar('langid');

        $langcss = $lang->getVar('langcss');

        switch ($mode) {
            case 'e':
                echo "<tr class='$class'>";
                // language code
                echo '<td>' . $lang->getVar('langcode') . '</td>';

                // language directory name
                echo '<td>' . $lang->getVar('langdirname') . '</td>';

                // Active
                if (1 == $lang->getVar('langisactive') && $lang->getVar('langdirname') != $metatag['language']) {
                    echo "<td><input type='checkbox' name='newstatus[" . $lid . "]' checked>
					          <input type='hidden' name='oldstatus[" . $lid . "]' value='1'></td>";
                } elseif ($lang->getVar('langdirname') == $metatag['language']) {
                    echo "<td><input type='checkbox' name='disabledstatus[" . $lid . "]' checked disabled>
					          <input type='hidden' name='oldstatus[" . $lid . "]' value='1'>
							  <input type='hidden' name='newstatus[" . $lid . "]' value='1'></td>";
                } else {
                    echo "<td><input type='checkbox' name='newstatus[" . $lid . "]'>
					          <input type='hidden' name='oldstatus[" . $lid . "]' value='0'></td>";
                }

                // Action
                $extra = '';
                if (('english' != $lang->getVar('langdirname')) && ($lang->getVar('langdirname') != $metatag['language'])) {
                    $extra = '<a href="' . XOOPS_URL . '/modules/sitelang/admin/index.php?op=delete&amp;alangid=' . $lang->getVar('langid') . '">' . _AM_LANG_UNINSTALL . '</a>';

                    $extra .= '&nbsp;|&nbsp;';
                } else {
                    $extra = $lang->getVar('langdirname') != $metatag['language'] ? '&nbsp;&nbsp;&nbsp;' : _AM_LANG_DEFAULT . '&nbsp;|&nbsp;';
                }
                if (!empty($langcss)) {
                    $extra .= '<a href="' . XOOPS_URL . '/modules/sitelang/admin/index.php?op=addcss&amp;alangid=' . $lang->getVar('langid') . '">' . _AM_LANG_CHANGE . '&nbsp;CSS</a>';
                } else {
                    $extra .= '<a href="' . XOOPS_URL . '/modules/sitelang/admin/index.php?op=addcss&amp;alangid=' . $lang->getVar('langid') . '">' . _ADD . '&nbsp;CSS</a>';
                }
                echo "<td align='center'>" . $extra . "</td>\n	</tr>";
                $listed_langs[$lang->getVar('langdirname')] = $lang->getVar('langdirname');
                echo '</tr>';
                break;
            case 'c':
                $oldstatus[$lid] = isset($_POST['oldstatus'][$lid]) ? (int)$_POST['oldstatus'][$lid] : 0;
                $newstatus[$lid] = isset($_POST['newstatus'][$lid]) ? 1 : 0;

                if ($oldstatus[$lid] != $newstatus[$lid]) {
                    $numofchangedrows++;

                    echo "<tr class='$class'>
			                <input type='hidden' name='langcode[" . $lid . "]' value='" . $lang->getVar('langcode') . "'>
			                <input type='hidden' name='dirname[" . $lid . "]' value='" . $lang->getVar('langdirname') . "'>
    			            <input type='hidden' name='newstatus[" . $lid . "]' value='" . $newstatus[$lid] . "'>
			                <input type='hidden' name='sitename[" . $lid . "]' value='" . $lang->getVar('sitename') . "'>
			                <input type='hidden' name='slogan[" . $lid . "]' value='" . $lang->getVar('slogan') . "'>
                            <input type='hidden' name='footer[" . $lid . "]' value='" . $lang->getVar('footer') . "'>
                            <input type='hidden' name='charset[" . $lid . "]' value='" . $lang->getVar('charset') . "'>
                            <input type='hidden' name='langcss[" . $lid . "]' value='" . $lang->getVar('langcss') . "'>
    				        <input type='hidden' name='langids[]' value='" . $lid . "'>";

                    // language code

                    echo '  <td>' . $lang->getVar('langcode') . '</td>';

                    // directory name

                    echo '  <td>' . $lang->getVar('langdirname') . '</td>';

                    // active

                    echo '<td align="center">';

                    if (1 == $newstatus[$lid]) {
                        if (0 == $oldstatus[$lid]) {
                            echo "<span style='color:#ff0000;font-weight:bold;'>" . _AM_LANG_ACTIVATE . '</span>';
                        } else {
                            echo _YES . ' - ' . _AM_LANG_NOCHANGE;
                        }
                    } else {
                        if (1 == $oldstatus[$lid]) {
                            echo "<span style='color:#ff0000;font-weight:bold;'>" . _AM_LANG_DEACTIVATE . '</span>';
                        } else {
                            echo _NO . ' - ' . _AM_LANG_NOCHANGE;
                        }
                    }

                    echo '  </td>';

                    echo '</tr>';

                    break;
                }
        }
    }

    if ('e' == $mode) {
        echo "<tr class='foot'><td colspan='4' align='center'>
                <input type='hidden' name='op' value='confirmedit'>
              	<input type='submit' name='submit' value='" . _AM_LANG_SUBMIT . "'>
            </td></tr>";
    } elseif ('c' == $mode) {
        if ($numofchangedrows > 0) {
            echo "<tr class='foot' align='center'><td colspan='4'>
     				<input type='hidden' name='op' value='submit'>
    				<input type='submit' value='" . _AM_LANG_SUBMIT . "'>&nbsp;<input type='button' value='" . _AM_LANG_CANCEL . "' onclick='location=\"index.php\"'>
				    </td></tr>";
        } else {
            echo "<tr><td colspan='4'>&nbsp;</td></tr>";

            echo "<tr align='center'>
        				<td colspan='4'>" . _AM_LANG_NOCHANGES . '
        				</td></tr>';

            echo "<tr><td colspan='4'>&nbsp;</td></tr>";

            echo "<tr class='foot' align='center'>
    			        <td colspan='4'>
    				    <input type='hidden' name='op' value='edit'>
				        <input type='button' value='" . _AM_LANG_RETURN . "' onclick='location=\"index.php?op=list\"'>
				      </td></tr>";
        }
    }

    echo '</table></form><br>';

    // languages which have a folder in XOOPS_ROOT_URL/language/ but are not yet added to the database (lang table)

    if ('e' == $mode) {
        echo "<table border='0' class='outer' cellpadding='4' cellspacing='1' align='left' width='50%'>";

        echo "<tr align='center'><th>" . _AM_LANG_LANGDIRNAME . '</th><th>' . _AM_LANG_ACTION . '</th></tr>';

        $languages_dir = [];

        $languages_dir = XoopsLists::getLangList();

        $uninstalled_langs = array_diff($languages_dir, $listed_langs);

        $langcount = count($uninstalled_langs);

        if ($langcount > 0) {
            foreach ($uninstalled_langs as $k => $v) {
                if (0 == $count % 2) {
                    $class = 'even';
                } else {
                    $class = 'odd';
                }

                echo '<tr class="' . $class . '" align="center" valign="middle">
 			            <td align="center" valign="bottom">' . $v . '</td>
       					<td><a href="' . XOOPS_URL . '/modules/sitelang/admin/index.php?op=add&amp;langdirname=' . $v . '">' . _AM_LANG_ADD . '</a></td>
				        </tr>';
            }
        } else {
            echo "<tr align='center'><td colspan='2' align='center'>" . _AM_LANG_NOLANGUAGES . '</td></tr>';
        }

        echo '</table>';

        echo '<br> <br> <br> <br>';
    }
}

/**
 * A function to display a form containing site text language variables,
 * such as site name, site slogan and site footer.
 *
 * @param string $mode 'e' or 'c' (representing edit or confirm)
 */
function sitetextForm($mode = 'e')
{
    $myts = MyTextSanitizer::getInstance();

    // get all languages that are already in the database

    $sitelangHandler = xoops_getModuleHandler('sitelang', 'sitelang');

    $langcriteria = new CriteriaCompo();

    $langcriteria->setSort('langid');

    $languages = $sitelangHandler->getObjects($langcriteria, false, true); // $criteria, $id_as_key, $as_objects

    $languagescount = count($languages);

    $charsetinvalid = 0;

    $namelangHandler = xoops_getModuleHandler('namelang', 'sitelang');

    if ('c' == $mode) {
        echo '<div>' . _AM_LANG_PLEASECONFIRM . '<br><br></div>';
    } else {
        echo "<div><a href='" . XOOPS_URL . "/modules/system/admin.php?fct=preferences&op=show&confcat_id=1'>[ " . _AM_LANG_SITETEXTDEFAULT . ' ]</a> <br></div>';

        echo '<div>&nbsp;&nbsp;&nbsp;' . _AM_LANG_DEFAULTSITETEXT_DESC . '<br><br></div>';
    }

    echo "
        	<form action='sitetextmgnt.php' method='post' name='sitetextform' id='sitetextform'>
        	<table class='outer' cellpadding='4' cellspacing='1' align='center' width='70%'>
        	";

    // headings

    echo "<tr align='left'><th>" . _AM_LANG_LANGNAME . ' </th><th>' . _AM_LANG_SITENAME . '</th><th>' . _AM_LANG_SLOGAN . '</th></tr>';

    $count = 0;

    $numofchangedrows = 0;

    foreach ($languages as $lang) {
        if (0 == $count % 2) {
            $class = 'even';
        } else {
            $class = 'odd';
        }

        $count++;

        $lid = $lang->getVar('langid');

        $namelang = $namelangHandler->get(_LANGCODE, $lang->getVar('langcode')); // langcode of the page, langcode for each installed language

        if ('e' == $mode) {
            echo "<tr class='$class'>";

            echo '<td>' . $namelang->getVar('langname') . '</td>';

            // Site name

            if (_CHARSET == $lang->getVar('charset')) {
                echo "<td><input type='text' name='newsitename[" . $lid . "]' value='" . $lang->getVar('sitename', 'e') . "' maxlength='150' size='30'>";
            } else {
                $charsetinvalid++;

                echo '<td>' . _AM_LANG_INPUTDISABLED . "*
			     <input type='hidden' name='newsitename[" . $lid . "]' value='" . $lang->getVar('sitename') . "'>";
            }

            echo " <input type='hidden' name='oldsitename[" . $lid . "]' value='" . $lang->getVar('sitename') . "'>
				  </td>";

            // Slogan

            if (_CHARSET == $lang->getVar('charset')) {
                echo "   <td><input type='text' name='newslogan[" . $lid . "]' value='" . $lang->getVar('slogan', 'e') . "' maxlength='150' size='50'>";
            } else {
                $charsetinvalid++;

                echo '<td>' . _AM_LANG_INPUTDISABLED . "*
			     <input type='hidden' name='newslogan[" . $lid . "]' value='" . $lang->getVar('slogan') . "'>";
            }

            echo "   <input type='hidden' name='oldslogan[" . $lid . "]' value='" . $lang->getVar('slogan') . "'>
				  </td>
    		 </tr>";
        } elseif ('c' == $mode) {
            if (isset($_POST['oldsitename'])) {
                $oldsitename[$lid] = trim($myts->stripSlashesGPC($_POST['oldsitename'][$lid]));
            }

            if (isset($_POST['newsitename'])) {
                $newsitename[$lid] = trim($myts->stripSlashesGPC($_POST['newsitename'][$lid]));
            }

            if (isset($_POST['oldslogan'])) {
                $oldslogan[$lid] = trim($myts->stripSlashesGPC($_POST['oldslogan'][$lid]));
            }

            if (isset($_POST['newslogan'])) {
                $newslogan[$lid] = trim($myts->stripSlashesGPC($_POST['newslogan'][$lid]));
            }

            if (($oldsitename[$lid] != $newsitename[$lid])
                || ($oldslogan[$lid] != $newslogan[$lid])) {
                $numofchangedrows++;

                echo "<tr class='$class'>
			            <input type='hidden' name='langcode[" . $lid . "]' value='" . $lang->getVar('langcode') . "'>
			            <input type='hidden' name='langdirname[" . $lid . "]' value='" . $lang->getVar('langdirname') . "'>
			            <input type='hidden' name='langstatus[" . $lid . "]' value='" . $lang->getVar('langisactive') . "'>
			            <input type='hidden' name='newsitename[" . $lid . "]' value='" . htmlspecialchars($newsitename[$lid], ENT_QUOTES) . "'>
			            <input type='hidden' name='newslogan[" . $lid . "]' value='" . htmlspecialchars($newslogan[$lid], ENT_QUOTES) . "'>
			            <input type='hidden' name='footer[" . $lid . "]' value='" . $lang->getVar('footer') . "'>
			            <input type='hidden' name='charset[" . $lid . "]' value='" . $lang->getVar('charset') . "'>
                        <input type='hidden' name='langcss[" . $lid . "]' value='" . $lang->getVar('langcss') . "'>
    				    <input type='hidden' name='langids[]' value='" . $lid . "'>
	                    <td>" . $lang->getVar('langdirname') . '</td>';

                echo "  <td align='center'>" . $oldsitename[$lid];

                if ($oldsitename[$lid] != $newsitename[$lid]) {
                    echo "  <br>&nbsp;&raquo;&raquo;&nbsp;<span style='color:#ff0000;font-weight:bold;'>" . $newsitename[$lid] . '</span>';
                }

                echo '  </td>';

                echo "  <td align='center'>" . $oldslogan[$lid];

                if ($oldslogan[$lid] != $newslogan[$lid]) {
                    echo "  <br>&nbsp;&raquo;&raquo;&nbsp;<span style='color:#ff0000;font-weight:bold;'>" . $newslogan[$lid] . '</span>';
                }

                echo '  </td>
	                  </tr>';
            }
        }
    }

    if ('e' == $mode) {
        echo "<tr class='foot'><td colspan='3' align='center'>
           	<input type='hidden' name='op' value='confirmedit'>
           	<input type='submit' name='submit' value='" . _AM_LANG_SUBMIT . "'>
       	    </td></tr>";
    } elseif ('c' == $mode) {
        if ($numofchangedrows > 0) {
            echo "<tr class='foot' align='center'><td colspan='3'>
				<input type='hidden' name='op' value='submit'>
				<input type='submit' value='" . _AM_LANG_SUBMIT . "'>&nbsp;<input type='button' value='" . _AM_LANG_CANCEL . "' onclick='location=\"sitetextmgnt.php\"'>
				</td></tr>";
        } else {
            echo "<tr><td colspan='3'>&nbsp;</td></tr>";

            echo "<tr align='center'>
				<td colspan='3'>" . _AM_LANG_NOCHANGES . '
				</td></tr>';

            echo "<tr><td colspan='3'>&nbsp;</td></tr>";

            echo "<tr class='foot' align='center'>
				<td colspan='3'>
				  <input type='hidden' name='op' value='edit'>
				  <input type='button' value='" . _AM_LANG_RETURN . "' onclick='location=\"sitetextmgnt.php?op=list\"'>
				</td></tr>";
        }
    }

    echo '</table></form><br>';

    if ($charsetinvalid > 0) {
        echo '<div>* ' . _AM_LANG_INPUTDISABLED_DESC . '<br><br></div>';
    }
}

/**
 * A function to display a form containing language names
 *
 * @param string $mode 'e' or 'c' (representing edit or confirm)
 */
function languageNamesForm($mode = 'e')
{
    $myts = MyTextSanitizer::getInstance();

    // Object handlers

    $namelangHandler = xoops_getModuleHandler('namelang', 'sitelang');

    $langHandler = xoops_getModuleHandler('sitelang', 'sitelang');

    // retrieve all installed languages

    $languages = $langHandler->getObjects(null, false, true);

    if ('c' == $mode) {
        echo '<div>' . _AM_LANG_PLEASECONFIRM . '<br><br></div>';
    } else {
        echo '<div>&nbsp;&nbsp;&nbsp;' . _AM_LANG_LANGNAME_DESC . '<br><br></div>';
    }

    echo "<form action='langnamemgnt.php' method='post' name='langnameform' id='langnameform'>";

    echo "<table class='outer' cellpadding='4' cellspacing='1' align='center' width='70%'>";

    // headings

    echo "<tr align='left'><th>" . _AM_LANG_LANGCODE . '</th><th>' . _AM_LANG_LANGDIRNAME . ' </th><th>' . _AM_LANG_TAB2 . '</th></tr>';

    $count = 0;

    $numofchangedrows = 0;

    foreach ($languages as $lang) {
        if (0 == $count % 2) {
            $class = 'even';
        } else {
            $class = 'odd';
        }

        $count++;

        $lid = $lang->getVar('langid');

        $langcode = $lang->getVar('langcode');

        unset($namelang);

        $namelang = $namelangHandler->get(_LANGCODE, $langcode); // langcode of the page, langcode for each installed language

        if ('e' == $mode) {
            echo "<tr class='$class'>
	              <td>" . $lang->getVar('langcode') . '</td>
	              <td>' . $lang->getVar('langdirname') . '</td>';
        }

        // language names

        if ('e' == $mode) {
            echo "<td><input type='text' name='newname[" . $lid . "]' value='" . $namelang->getVar('langname', 'e') . "' maxlength='150' size='50'>";

            echo "<input type='hidden' name='oldname[" . $lid . "]' value='" . $namelang->getVar('langname') . "'></td>";
        } elseif ('c' == $mode) {
            if (isset($_POST['oldname'])) {
                $oldname[$lid] = trim($myts->stripSlashesGPC($_POST['oldname'][$lid]));
            }

            if (isset($_POST['newname'])) {
                $newname[$lid] = trim($myts->stripSlashesGPC($_POST['newname'][$lid]));
            }

            if ($oldname[$lid] != $newname[$lid]) {
                $numofchangedrows++;

                echo "<tr class='$class'>
   			          <input type='hidden' name='langcode[" . $lid . "]' value='" . $namelang->getVar('langcode') . "'>
 	    		      <input type='hidden' name='guilangcode[" . $lid . "]' value='" . $namelang->getVar('guilangcode') . "'>
			          <input type='hidden' name='newname[" . $lid . "]' value='" . htmlspecialchars($newname[$lid], ENT_QUOTES) . "'>
				      <input type='hidden' name='langids[]' value='" . $lid . "'>";

                echo '<td>' . $namelang->getVar('langcode') . '</td>';

                echo '<td>' . $lang->getVar('langdirname') . '</td>';

                echo ' <td align="center">' . $oldname[$lid];

                echo '<br>&nbsp;&raquo;&raquo;&nbsp;<span style="color:#ff0000;font-weight:bold;">' . $newname[$lid] . '</span>';

                echo '</td>';
            }
        } else {
            echo '<td>' . $namelang->getVar('langname') . '</td>';
        }

        echo ' </tr>';
    }

    if ('e' == $mode) {
        echo "<tr class='foot'><td colspan='3' align='center'>
           	<input type='hidden' name='op' value='confirmedit'>
           	<input type='submit' name='submit' value='" . _AM_LANG_SUBMIT . "'>
       	    </td></tr>";
    } elseif ('c' == $mode) {
        if ($numofchangedrows > 0) {
            echo "<tr class='foot' align='center'><td colspan='3'>
				<input type='hidden' name='op' value='submit'>
				<input type='submit' value='" . _AM_LANG_SUBMIT . "'>&nbsp;<input type='button' value='" . _AM_LANG_CANCEL . "' onclick='location=\"langnamemgnt.php\"'>
				</td></tr>";
        } else {
            echo "<tr><td colspan='3'>&nbsp;</td></tr>";

            echo "<tr align='center'><td colspan='2'>" . _AM_LANG_NOCHANGES . '</td></tr>';

            echo "<tr><td colspan='3'>&nbsp;</td></tr>";

            echo "<tr class='foot' align='center'>
				<td colspan='3'><input type='hidden' name='op' value='edit'><input type='button' value='" . _AM_LANG_RETURN . "' onclick='location=\"langnamemgnt.php?op=list\"'>
				</td></tr>";
        }
    }

    echo '</table></form><br>';
}

/**
 * A function to display a form containing module_lang variables.
 *
 * @param int    $moduleid the id of the module
 * @param string $mode     'e' or 'c' (representing edit or confirm)
 */
function moduleForm($moduleid, $mode = 'e')
{
    $myts = MyTextSanitizer::getInstance();

    $charsetinvalid = 0;

    if (isset($_POST['modid'])) {
        $moduleid = $_POST['modid'];
    }

    // default module name for given module id

    $moduleHandler = xoops_getHandler('module');

    $defaultmod = $moduleHandler->get($moduleid);

    // modlang values for given module id.

    $modlangHandler = xoops_getModuleHandler('modlang', 'sitelang');

    $criteria = new CriteriaCompo();

    $criteria->add(new Criteria('mid', (int)$moduleid));

    $modlangs = $modlangHandler->getObjects($criteria, false, true); // $criteria, $id_as_key, $as_objects

    // retrieve all installed languages

    $langHandler = xoops_getModuleHandler('sitelang', 'sitelang');

    $languages = $langHandler->getObjects(null, false, true);

    $namelangHandler = xoops_getModuleHandler('namelang', 'sitelang');

    if ('c' == $mode) {
        echo '<div>' . _AM_LANG_PLEASECONFIRM . $defaultmod->getVar('name') . '<br><br></div>';
    } else {
        echo "<div><a href='" . XOOPS_URL . "/modules/system/admin.php?fct=modulesadmin'>[ " . _AM_LANG_MODDEFAULT . ' ]</a> <br></div>';

        echo '<div>&nbsp;&nbsp;&nbsp;' . _AM_LANG_DEFAULTMODNAME_DESC . '<br><br></div>';
    }

    echo "
        	<form action='modulesmgnt.php' method='post' name='modnameform' id='modnameform'>
			<input type='hidden' name='modid' value=" . $moduleid . '>';

    echo "    	<table class='outer' cellpadding='4' cellspacing='1' align='center' width='70%'>
        	";

    // headings

    echo "<tr align='left'><th>" . _AM_LANG_LANGNAME . ' </th><th>' . _AM_LANG_TAB4 . '</th></tr>';

    $count = 0;

    $numofchangedrows = 0;

    foreach ($modlangs as $mid) {
        if (0 == $count % 2) {
            $class = 'even';
        } else {
            $class = 'odd';
        }

        $count++;

        $lid = $mid->getVar('langid');

        $lang = $langHandler->get($lid);

        $namelang = $namelangHandler->get(_LANGCODE, $lang->getVar('langcode')); // langcode of the page, langcode for each installed language

        if ('e' == $mode) {
            echo "<tr class='$class'>";

            echo '<td>' . $namelang->getVar('langname') . '</td>';

            if (_CHARSET == $lang->getVar('charset')) {
                echo "<td><input type='text' name='newname[" . $lid . "]' value='" . $mid->getVar('modname', 'e') . "' maxlength='150' size='50'>";
            } else {
                $charsetinvalid++;

                echo '  <td>' . _AM_LANG_INPUTDISABLED . "*
				        <input type='hidden' name='newname[" . $lid . "]' value='" . $mid->getVar('modname') . "'>";
            }

            echo "<input type='hidden' name='oldname[" . $lid . "]' value='" . $mid->getVar('modname') . "'></td>
    		 </tr>";
        } elseif ('c' == $mode) {
            if (isset($_POST['oldname'])) {
                $oldname[$lid] = trim($myts->stripSlashesGPC($_POST['oldname'][$lid]));
            }

            if (isset($_POST['newname'])) {
                $newname[$lid] = trim($myts->stripSlashesGPC($_POST['newname'][$lid]));
            }

            if ($oldname[$lid] != $newname[$lid]) {
                $numofchangedrows++;

                echo "<tr class='$class'>
			      <input type='hidden' name='newname[" . $lid . "]' value='" . htmlspecialchars($newname[$lid], ENT_QUOTES) . "'>
				  <input type='hidden' name='langids[]' value='" . $lid . "'>";

                echo '<td>' . $namelang->getVar('langname') . '</td>';

                echo ' <td align="center">' . $oldname[$lid];

                echo '<br>&nbsp;&raquo;&raquo;&nbsp;<span style="color:#ff0000;font-weight:bold;">' . $newname[$lid] . '</span>';

                echo '</td></tr>';
            }
        } else {
            echo '    <td>' . $mid->getVar('modname') . '</td>
    		 </tr>';
        }
    }

    if ('e' == $mode) {
        echo "<tr class='foot'><td colspan='2' align='center'>
           	<input type='hidden' name='op' value='confirmedit'>
           	<input type='submit' name='submit' value='" . _AM_LANG_SUBMIT . "'>
       	    </td></tr>";
    } elseif ('c' == $mode) {
        if ($numofchangedrows > 0) {
            echo "<tr class='foot' align='center'><td colspan='2'>
				<input type='hidden' name='op' value='submit'>
				<input type='submit' value='" . _AM_LANG_SUBMIT . "'>&nbsp;<input type='button' value='" . _AM_LANG_CANCEL . "' onclick='location=\"modulesmgnt.php\"'>
				</td></tr>";
        } else {
            echo "<tr><td colspan='2'>&nbsp;</td></tr>";

            echo "<tr align='center'><td colspan='2'>" . _AM_LANG_NOCHANGES . '</td></tr>';

            echo "<tr><td colspan='2'>&nbsp;</td></tr>";

            echo "<tr class='foot' align='center'>
				<td colspan='2'><input type='hidden' name='op' value='edit'><input type='button' value='" . _AM_LANG_RETURN . "' onclick='location=\"modulesmgnt.php?op=list\"'>
				</td></tr>";
        }
    }

    echo '</table></form><br>';

    if ($charsetinvalid > 0) {
        echo '<div>* ' . _AM_LANG_INPUTDISABLED_DESC . '<br><br></div>';
    }
}

/**
 * A function to display a form containing block_lang variables.
 *
 * @param int    $blockid the id of the block type (custom, system, [modulename])
 * @param string $mode    'e' or 'c' (representing edit or confirm)
 * @param mixed $moduleid
 * @param mixed $selectedlangid
 */
function blockForm($moduleid = 1, $blockid = -1, $selectedlangid = 0, $mode = 'e')
{
    $myts = MyTextSanitizer::getInstance();

    $charsetinvalid = 0;

    if (isset($_POST['bid'])) {
        $blockid = $_POST['bid'];
    }

    // all blocks for the current module type

    $block_arr = XoopsBlock::getByModule($moduleid);

    // blocklang values for given module id.

    $blocklangHandler = xoops_getModuleHandler('blocklang', 'sitelang');

    $criteria = new CriteriaCompo();

    $criteria->add(new Criteria('mid', (int)$moduleid));

    $blocklangs = $blocklangHandler->getObjects($criteria, false, true); // $criteria, $id_as_key, $as_objects

    $allblocks = -1;

    $block_keys = [];

    foreach ($blocklangs as $b) {
        $block_keys[$b->getVar('bid')] = $b->getVar('bid');
    }

    if (in_array($blockid, $block_keys, true)) {
        $allblocks = $blockid;
    }

    // retrieve all installed languages

    $langHandler = xoops_getModuleHandler('sitelang', 'sitelang');

    $languages = $langHandler->getObjects(null, false, true);

    $namelangHandler = xoops_getModuleHandler('namelang', 'sitelang');

    if ('c' == $mode) {
        echo '<div>' . _AM_LANG_PLEASECONFIRM . '<br><br></div>';
    }

    echo "
        	<form action='blocksmgnt.php' method='post' name='blocknameform' id='blocknameform'>
			<input type='hidden' name='mid' value=" . $moduleid . '>';

    echo "    	<table class='outer' cellpadding='4' cellspacing='1' align='center' width='70%'>
        	";

    // headings

    echo "<tr align='left'><th>" . _AM_LANG_LANGNAME . ' </th><th>' . _AM_LANG_TAB5 . '</th></tr>';

    $count = 0;

    $numofchangedrows = 0;

    foreach ($blocklangs as $bid) {
        if ((($allblocks < 0) || ($allblocks > 0 && $bid->getVar('bid') == $allblocks))
            && (($selectedlangid <= 0) || ($selectedlangid > 0 && (int)$bid->getVar('langid') == $selectedlangid))) {
            if (0 == $count % 2) {
                $class = 'even';
            } else {
                $class = 'odd';
            }

            $count++;

            $lid = $bid->getVar('langid');

            $lcode = $bid->getVar('langcode');

            $blckid = $bid->getVar('bid');

            $lang = $langHandler->get($lid);

            $namelang = $namelangHandler->get(_LANGCODE, $lang->getVar('langcode')); // langcode of the page, langcode for each installed language

            if ('e' == $mode) {
                echo "<tr class='$class'>";

                echo '<td>' . $namelang->getVar('langname') . '</td>';

                if (_CHARSET == $lang->getVar('charset')) {
                    echo "<td><input type='text' name='newname[" . $blckid . '][' . $lid . "]' value='" . $bid->getVar('blockname', 'e') . "' maxlength='150' size='50'>";
                } else {
                    $charsetinvalid++;

                    echo '  <td>' . _AM_LANG_INPUTDISABLED . "*
				            <input type='hidden' name='newname[" . $blckid . '][' . $lid . "]' value='" . $bid->getVar('blockname') . "'>";
                }

                echo "<input type='hidden' name='oldname[" . $blckid . '][' . $lid . "]' value='" . $bid->getVar('blockname') . "'></td>
    		     </tr>";
            } elseif ('c' == $mode) {
                $oldname[$blckid][$lid] = '';

                $newname[$blckid][$lid] = '';

                if (isset($_POST['oldname'][$blckid][$lid])) {
                    $oldname[$blckid][$lid] = trim($myts->stripSlashesGPC($_POST['oldname'][$blckid][$lid]));
                }

                if (isset($_POST['newname'][$blckid][$lid])) {
                    $newname[$blckid][$lid] = trim($myts->stripSlashesGPC($_POST['newname'][$blckid][$lid]));
                }

                if ($oldname[$blckid][$lid] != $newname[$blckid][$lid]) {
                    $numofchangedrows++;

                    echo "<tr class='$class'>
			          <input type='hidden' name='newname[" . $blckid . '][' . $lid . "]' value='" . htmlspecialchars($newname[$blckid][$lid], ENT_QUOTES) . "'>
				      <input type='hidden' name='blckids[" . $blckid . '][' . $lid . "]' value='" . $lcode . "'>
					  ";

                    echo '<td>' . $namelang->getVar('langname') . '</td>';

                    echo ' <td align="center">' . $oldname[$blckid][$lid];

                    echo '<br>&nbsp;&raquo;&raquo;&nbsp;<span style="color:#ff0000;font-weight:bold;">' . $newname[$blckid][$lid] . '</span>';

                    echo '</td></tr>';
                }
            } else {
                echo '    <td>' . $bid->getVar('modname') . '</td>
            		 </tr>';
            }
        }
    }

    if ('e' == $mode) {
        echo "<tr class='foot'><td colspan='2' align='center'>
           	<input type='hidden' name='op' value='confirmedit'>
           	<input type='submit' name='submit' value='" . _AM_LANG_SUBMIT . "'>
       	    </td></tr>";
    } elseif ('c' == $mode) {
        if ($numofchangedrows > 0) {
            echo "<tr class='foot' align='center'><td colspan='2'>
				<input type='hidden' name='op' value='submit'>
				<input type='submit' value='" . _AM_LANG_SUBMIT . "'>&nbsp;<input type='button' value='" . _AM_LANG_CANCEL . "' onclick='location=\"blocksmgnt.php\"'>
				</td></tr>";
        } else {
            echo "<tr><td colspan='2'>&nbsp;</td></tr>";

            echo "<tr align='center'><td colspan='2'>" . _AM_LANG_NOCHANGES . '</td></tr>';

            echo "<tr><td colspan='2'>&nbsp;</td></tr>";

            echo "<tr class='foot' align='center'>
				<td colspan='2'><input type='hidden' name='op' value='edit'><input type='button' value='" . _AM_LANG_RETURN . "' onclick='location=\"blocksmgnt.php?op=list\"'>
				</td></tr>";
        }
    }

    echo '</table></form><br>';

    if ($charsetinvalid > 0) {
        echo '<div>* ' . _AM_LANG_INPUTDISABLED_DESC . '<br><br></div>';
    }
}
