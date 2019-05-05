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
 * Version details
 *
 * @package    theme_adaptable
 * @copyright  2015-2016 Jeremy Hopkins (Coventry University)
 * @copyright  2015-2017 Fernando Acedo (3-bits.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

// Include header.
require_once(dirname(__FILE__) . '/includes/header.php');

// Set layout.
$left = $PAGE->theme->settings->blockside;
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$regions = theme_adaptable_grid($left, $hassidepost);

$hasfootnote = (!empty($PAGE->theme->settings->footnote));
$dashblocksposition = $PAGE->theme->settings->dashblocksposition;

if ( (!empty($PAGE->theme->settings->dashblocksenabled)) && ($dashblocksposition == 'abovecontent') ) { ?>
    <div id="frontblockregion">
        <div class="row-fluid">
            <?php echo $OUTPUT->get_block_regions('dashblocklayoutlayoutrow'); ?>
        </div>
    </div>
<?php
}
?>


<?php

$sidebarclasses = '';
// Hide sidebar on mobile.
if (!empty($PAGE->theme->settings->smallscreenhidesidebar)) {
    $sidebarclasses = ' d-none d-md-block ';
} ?>

<div class="container outercont">
    <div id="page-content" class="row-fluid">
        <?php
        if (!empty($PAGE->theme->settings->tabbedlayoutdashboard)) {
            $taborder = explode ('-', $PAGE->theme->settings->tabbedlayoutdashboard);
            $count = 0;
            echo '<section id="region-main" class="' . $regions['content']  . '">';

            echo '<main id="dashboardtabcontainer" class="tabcontentcontainer">';

            foreach ($taborder as $tabnumber) {
                if ($tabnumber == 0) {
                    $tabname = 'dashboard-tab-content';
                    $tablabel = get_string('tabbedlayouttablabeldashboard', 'theme_adaptable');
                } else {
                    $tabname = 'dashboard-tab' . $tabnumber;
                    $tablabel = get_string('tabbedlayouttablabeldashboard' . $tabnumber, 'theme_adaptable');
                }

                echo '<input id="' . $tabname . '" type="radio" name="tabs" class="dashboardtab" ' .
                    ($count == 0 ? ' checked ' : '') . '>' .
                    '<label for="' . $tabname . '" class="dashboardtab">' . $tablabel .'</label>';
                    $count++;
            }


            // Basic array used by appropriately named blocks below (e.g. course-tab-one).  All this is due to the re-use of
            // existing functionality and non-use of numbers in block region names.
            $wordtonumber = array (1 => 'one', 2 => 'two');
            foreach ($taborder as $tabnumber) {
                if ($tabnumber == 0) {
                    echo '<section id="adaptable-dashboard-tab-content" class="adaptable-tab-section tab-panel">';

                    echo $OUTPUT->course_content_header();
                    echo $OUTPUT->main_content();
                    echo $OUTPUT->course_content_footer();
                    echo '</section>';
                } else {
                    echo '<section id="adaptable-dashboard-tab-' . $tabnumber . '" class="adaptable-tab-section tab-panel">';
                    echo $OUTPUT->get_block_regions('customrowsetting', 'my-tab-' . $wordtonumber[$tabnumber] . '-', '12-0-0-0');
                    echo '</section>';
                }
            }

            echo '</main>';
            echo '</section>';
            echo $OUTPUT->blocks('side-post', $regions['blocks'] .  $sidebarclasses);
        } else { ?>
        <section id="region-main" class="<?php echo $regions['content'];?>">
            <?php
                echo $OUTPUT->course_content_header();
                echo $OUTPUT->main_content();
                echo $OUTPUT->course_content_footer();
            ?>
        </section>

        <?php
            echo $OUTPUT->blocks('side-post', $regions['blocks']  . $sidebarclasses);
        } ?>

</div>

<?php if ( (!empty($PAGE->theme->settings->dashblocksenabled)) && ($dashblocksposition == 'belowcontent') ) { ?>
    <div id="frontblockregion">
        <div class="row-fluid">
            <?php echo $OUTPUT->get_block_regions('dashblocklayoutlayoutrow'); ?>
        </div>
    </div>
<?php
}
?>

<?php
if (is_siteadmin()) {
?>
    <div class="hidden-blocks">
        <div class="row-fluid">
            <h3><?php echo get_string('frnt-footer', 'theme_adaptable') ?></h3>
            <?php
            echo $OUTPUT->blocks('frnt-footer', 'col-10');
            ?>
        </div>
    </div>
    <?php
}
?>
</div>

<?php
// Include footer.
require_once(dirname(__FILE__) . '/includes/footer.php');
