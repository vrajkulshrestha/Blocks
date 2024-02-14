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
 * @package    block_moodletest
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');

class block_moodletest extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_moodletest');
    }

    function get_content() {
        global $CFG,$DB,$USER;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $course = $this->page->course;

        $courseid = $course->id;
        #Get all the course modules in a courses:
        $sql = "SELECT cm.id,cm.course,cm.instance,cm.added ,cm.visible,md.name as moduletype FROM {course_modules} as cm JOIN {modules} as md on md.id = cm.module WHERE `course` = $courseid";
        $moddata = $DB->get_records_sql($sql);
        
        foreach ($moddata as $mod) {            
            $modtype = $mod->moduletype;
            $table   = $mod->moduletype;
            $instance  = $mod->instance;
            $cmid    = $mod->id;
            $creation_date = userdate($mod->added,'%d-%b-%Y');
            #Get the module info:
            $moddata = $DB->get_record($table,['id'=>$instance]);
            $modfullname = $moddata->name;         
            
            #Get the status of the module's:
            $userid = $USER->id;
            $course_module = $DB->get_record('course_modules_completion',['coursemoduleid'=>$cmid,'userid'=>$userid]);
            $completion_status = '';
            if($course_module){
                $completion_status = ' - '.get_string('complete','block_moodletest');
            }
            #Create the link url:
            $href = $CFG->wwwroot.'/mod/'.$modtype.'/view.php?id='.$cmid;
            #Display content:
            $this->content->items[] = html_writer::nonempty_tag('a',$cmid.' - '.$modfullname.' - '.$creation_date.$completion_status,array('href'=>$href));
        }

        return $this->content;
    }

    /**
     * Returns the role that best describes this blocks contents.
     *
     * This returns 'navigation' as the blocks contents is a list of links to activities and resources.
     *
     * @return string 'navigation'
     */
    public function get_aria_role() {
        return 'navigation';
    }

    function applicable_formats() {
        return array('all' => true, 'mod' => false, 'my' => false, 'admin' => false,
                     'tag' => false);
    }
}


