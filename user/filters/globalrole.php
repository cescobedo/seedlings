<?php

require_once($CFG->dirroot.'/user/filters/lib.php');

/**
 * User filter based on global roles.
 */
class user_filter_globalrole extends user_filter_type {

    /**
     * Constructor
     * @param string $name the name of the filter instance
     * @param string $label the label of the filter instance
     * @param boolean $advanced advanced form element flag
     */
    function user_filter_globalrole($name, $label, $advanced) {
        parent::user_filter_type($name, $label, $advanced);
    }

    /**
     * Returns an array of available roles
     * @return array of availble roles
     */
    function get_roles() {
        $context = context_system::instance();
        $roles = array(0=> get_string('anyrole','filters')) + get_assignable_roles($context);
        return $roles;
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    function setupForm(&$mform) {
        $obj =& $mform->addElement('select', $this->_name, format_string($this->_label), $this->get_roles());
        $mform->setDefault($this->_name, 0);
        if ($this->_advanced) {
            $mform->setAdvanced($this->_name);
        }
    }

    /**
     * Retrieves data from the form data
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    function check_data($formdata) {
        $field = $this->_name;

        if (array_key_exists($field, $formdata) and !empty($formdata->$field)) {
            return array('value' => (int)$formdata->$field);
        }
        return false;
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return array sql string and $params
     */
    function get_sql_filter($data) {
        global $CFG;
        $value = (int)$data['value'];
        $timenow = round(time(), 100);
        $params = array();
        $namecontext = user_filter_type::filter_unique_param('ex_globalrole');
        $namerole = user_filter_type::filter_unique_param('ex_globalrole');
        $sql = "id IN (SELECT userid
                         FROM {role_assignments} a
                        WHERE a.contextid = :{$namecontext} AND a.roleid = :{$namerole})";
        $params[$namecontext] = SYSCONTEXTID;
        $params[$namerole] = $value;
        return array($sql, $params);
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        global $DB;

        $role = $DB->get_record('role', array('id'=>$data['value']));


        $a = new stdClass();
        $a->label = $this->_label;
        $a->value = '"'.role_get_name($role).'"';

        return get_string('globalrolelabel', 'filters', $a);
    }
}
