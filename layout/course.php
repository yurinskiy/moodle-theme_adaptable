<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *  Course-specific layout page
 *
 *  Includes course block region checking and formatting.
 *
 * @package    theme_adaptable
 * @copyright  2017 Manoj Solanki (Coventry University)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

// Include header.
require_once(dirname(__FILE__) . '/includes/header.php');

$left = $PAGE->theme->settings->blockside;

// If page is Grader report, override blockside setting to align left.
if (($PAGE->pagetype == "grade-report-grader-index") ||
    ($PAGE->bodyid == "page-grade-report-grader-index")) {
    $left = true;
}

$movesidebartofooter = !empty(($PAGE->theme->settings->coursepagesidebarinfooterenabled)) ? true : false;
$hassidepost = false;

// Definition of block regions for top and bottom.  These are used in potentially retrieving
// any missing block regions (due to layout changes that may hide blocks).
$blocksarray = array (
        array('settingsname'        => 'coursepageblocklayoutlayouttoprow',
                'classnamebeginswith' => 'course-top-'),
        array('settingsname'        => 'coursepageblocklayoutlayoutbottomrow',
                'classnamebeginswith' => 'course-bottom-')
);

if (!$movesidebartofooter) {
        $hassidepost = true;
}

$regions = theme_adaptable_grid($left, $hassidepost);
?>

<div class="container outercont">
    <div id="page-content" class="row-fluid">
        <?php
        echo $OUTPUT->page_navbar(false);

        // If course page, display course top block region.
        if (!empty($PAGE->theme->settings->coursepageblocksenabled)): ?>
            <div id="frontblockregion">
            <div class="row-fluid">
            <?php echo $OUTPUT->get_block_regions('coursepageblocklayoutlayouttoprow', 'course-top-'); ?>
            </div>
            </div>
        <?php
        endif;
        ?>

        <section id="region-main" class="<?php echo $regions['content'];?>">

		<?php if (!empty($PAGE->theme->settings->tabbedlayoutcoursepage)): ?>
            <?php

            // Use Adaptable tabbed layout.
            $currentpage = theme_adaptable_get_current_page();

            $taborder = explode ('-', $PAGE->theme->settings->tabbedlayoutcoursepage);
            $count = 0;

            echo '<main id="coursetabcontainer" class="tabcontentcontainer">';

            foreach ($taborder as $tabnumber) {
                if ($tabnumber == 0) {
                    $tabname = 'tab-content';
                    $tablabel = get_string('tabbedlayouttablabelcourse', 'theme_adaptable');
                } else {
                    $tabname = 'tab' . $tabnumber;
                    $tablabel = get_string('tabbedlayouttablabelcourse' . $tabnumber, 'theme_adaptable');
                }

                $checkedstatus = '';
                if ( ($count == 0 && $currentpage == 'coursepage') ||
                    ($currentpage != 'coursepage' && $tabnumber == 0) ) {
                    $checkedstatus = 'checked';
                }
                $extrastyles = '';
                if ($currentpage == 'coursepage') {
                   $extrastyles = ' style="display: none"';
                }
                echo  '<input id="' . $tabname . '" type="radio" name="tabs" class="coursetab" ' .
                    $checkedstatus . ' >' .
                    '<label for="' . $tabname . '" class="coursetab" ' . $extrastyles . '>' . $tablabel .'</label>';
                $checkedstatus = '';
                $count++;
            }

            // Basic array used by appropriately named blocks below (e.g. course-tab-one).  All this is to re-use existing
            // functionality and the non-use of numbers in block region names.
            $wordtonumber = array (1 => 'one', 2 => 'two');

            foreach ($taborder as $tabnumber) {
                if ($tabnumber == 0) {
                    echo '<section id="adaptable-course-tab-content" class="adaptable-tab-section tab-panel">';

                    echo $OUTPUT->get_course_alerts();
                    if (!empty($PAGE->theme->settings->coursepageblocksliderenabled) ) {
                        echo $OUTPUT->get_block_regions('customrowsetting', 'news-slider-', '12-0-0-0');
                    }
                    echo $OUTPUT->course_content_header();
                    echo $OUTPUT->main_content();
                    echo $OUTPUT->course_content_footer();

                    echo '</section>';
                } else {
                    echo '<section id="adaptable-course-tab-' . $tabnumber . '" class="adaptable-tab-section tab-panel">';

                    echo $OUTPUT->get_block_regions('customrowsetting', 'course-tab-' . $wordtonumber[$tabnumber] . '-',
                         '12-0-0-0');
                    echo '</section>';
                }
            }
            echo '</main>';
            ?>

        <?php else: ?>

            <?php
            echo $OUTPUT->get_course_alerts();
            if (!empty($PAGE->theme->settings->coursepageblocksliderenabled) ) {
                echo $OUTPUT->get_block_regions('customrowsetting', 'news-slider-', '12-0-0-0');
            }
            echo $OUTPUT->course_content_header();
            echo $OUTPUT->main_content();
            echo $OUTPUT->course_content_footer(); ?>

		<?php endif; ?>

        <?php // Check here if sidebar is configured to be in footer as we want to include
              // the sidebar information in the main content. ?>
        <?php if ($movesidebartofooter == false): ?>
        </section>
        <?php endif; ?>

        <?php
        // Check if the block regions are disabled in settings.  If it is and there were any blocks
        // assigned to those regions, they would obviously not display.  This will allow to override
        // the call to get_missing_block_regions to just display them all.

        $displayall = false;

        if (empty($PAGE->theme->settings->coursepageblocksenabled)) {
            $displayall = true;
        }

        $classes = '';
        // Hide sidebar on mobile.
        if (!empty($PAGE->theme->settings->smallscreenhidesidebar)) {
            $classes = ' d-none d-md-block ';
        }

        if ($movesidebartofooter == false) {
            echo $OUTPUT->blocks('side-post', $regions['blocks'] . $classes);

            // Get any missing blocks from changing layout settings.  E.g. From 4-4-4-4 to 6-6-0-0, to recover
            // what was in the last 2 spans that are now 0.
            echo $OUTPUT->get_missing_block_regions($blocksarray, $regions['blocks'], $displayall);
        }
        ?>

        <?php // If course page, display course bottom block region. ?>
        <?php if (!empty($PAGE->theme->settings->coursepageblocksenabled)) : ?>
            <div id="frontblockregion" class="container">
            <div class="row-fluid">
            <?php echo $OUTPUT->get_block_regions('coursepageblocklayoutlayoutbottomrow', 'course-bottom-'); ?>
            </div>
            </div>
        <?php endif; ?>

        <?php
        if ($movesidebartofooter) {
            echo $OUTPUT->blocks('side-post', ' col-12 ' . $classes);

            // Get any missing blocks from changing layout settings.  E.g. From 4-4-4-4 to 6-6-0-0, to recover
            // what was in the last 2 spans that are now 0.
            echo $OUTPUT->get_missing_block_regions($blocksarray, array(), $displayall);
        } ?>

        <?php if ($movesidebartofooter == true): ?>
        </section>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer.
require_once(dirname(__FILE__) . '/includes/footer.php');

if (!empty($PAGE->theme->settings->tabbedlayoutcoursepagetabpersistencetime)) {
    $tabbedlayoutcoursepagetabpersistencetime = $PAGE->theme->settings->tabbedlayoutcoursepagetabpersistencetime;
} else {
    $tabbedlayoutcoursepagetabpersistencetime = 30;
}
if (!empty($PAGE->theme->settings->tabbedlayoutcoursepage)) {
    $PAGE->requires->js_call_amd('theme_adaptable/utils', 'init', array('currentpage' => $currentpage, 'tabpersistencetime' => $tabbedlayoutcoursepagetabpersistencetime));
    echo '<noscript><style>label.coursetab { display: block !important; }</style><noscript>';
}
