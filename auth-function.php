<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Core\Exception\NotFoundException;
use app\src\Core\Exception\Exception;
use app\src\Core\Exception\UnauthorizedException;
use PDOException as ORMException;
use Cascade\Cascade;

/**
 * eduTrac Auth Helper
 *
 * @since 3.0.0
 * @package eduTrac SIS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
function hasPermission($perm)
{
    $acl = new \app\src\ACL(get_persondata('personID'));

    if ($acl->hasPermission($perm) )//&& is_user_logged_in()) 
    {
        return true;
    } else {
        return false;
    }
}

/**
 * Retrieve info of the logged in user.
 * 
 * @param mixed $field The field you want returned.
 * @return mixed
 */
function get_persondata($field)
{
    try {
        $app = \Liten\Liten::getInstance();
        $person = get_secure_cookie_data('ETSIS_COOKIENAME');
        $value = $app->db->person()
            ->select('person.*,address.*,staff.*,student.*')
            ->_join('address', 'person.personID = address.personID')
            ->_join('staff', 'person.personID = staff.staffID')
            ->_join('student', 'person.personID = student.stuID')
            ->where('person.personID = ?', _h($person->personID))->_and_()
            ->where('person.uname = ?', _h($person->uname));
        $q = $value->find(function ($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        foreach ($q as $r) {
            return _h($r[$field]);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    }
}

/**
 * Checks if a visitor is logged in or not.
 * 
 * @since 6.2.10
 * @return boolean
 */
function is_user_logged_in()
{
    $app = \Liten\Liten::getInstance();

    $person = get_person_by('personID', get_persondata('personID'));

    if ('' != _h($person->personID) && $app->cookies->verifySecureCookie('ETSIS_COOKIENAME')) {
        return true;
    }

    return false;
}

/**
 * Wrapper for the hasPermission() function since
 * this is not really a permission but a restriction.
 * It should give a user/developer more clarity when
 * understanding what this is actually allowing or
 * not allowing a person to do or see.
 *
 * @since 4.3
 * @param $perm string(required)            
 * @return bool
 */
function hasRestriction($perm)
{
    if (hasPermission($perm)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Hide element function.
 *
 * This is an alternative to the hl() function which may become
 * deprecated in a later release.
 *
 * @since 6.2.0
 * @param string $permission
 *            Permission to check for.
 * @return bool
 */
function _he($permission)
{
    if (hasPermission($permission)) {
        return true;
    }

    return false;
}

/**
 * Module function.
 *
 * This is an alternative to the ml() function which may become
 * depreated in a later release.
 *
 * @since 6.2.0
 * @param string $function_name
 *            Function to check for.
 * @return bool
 */
function _mf($function_name)
{
    if (function_exists($function_name)) {
        return true;
    }

    return false;
}

function ae($perm)
{
    if (!hasPermission($perm)) {
        return ' style="display:none !important;"';
    }
}

function rep($perm)
{
    if (hasRestriction($perm)) {
        return ' readonly="readonly"';
    }
}

/**
 * General Inquiry only on Forms.
 */
function gio()
{
    if (hasRestriction('general_inquiry_only')) {
        return ' readonly="readonly"';
    }
}

/**
 * General inquiry disable submit buttons.
 */
function gids()
{
    if (hasRestriction('general_inquiry_only')) {
        return ' style="display:none !important;"';
    }
}

/**
 * Course Inquiry only.
 */
function cio()
{
    if (hasRestriction('course_inquiry_only')) {
        return ' readonly="readonly"';
    }
}

/**
 * Course inquiry disable submit buttons.
 */
function cids()
{
    if (hasRestriction('course_inquiry_only')) {
        return ' style="display:none !important;"';
    }
}

/**
 * Course Sec Inquiry only.
 */
function csio()
{
    if (hasRestriction('course_sec_inquiry_only')) {
        return ' readonly="readonly"';
    }
}

/**
 * Course Sec disable submit buttons.
 */
function csids()
{
    if (hasRestriction('course_sec_inquiry_only')) {
        return ' style="display:none !important;"';
    }
}

/**
 * Course Sec disable select dropdowns.
 */
function csid()
{
    if (hasRestriction('course_sec_inquiry_only')) {
        return ' disabled';
    }
}

/**
 * Academic Program Inquiry only.
 */
function apio()
{
    if (hasRestriction('acad_prog_inquiry_only')) {
        return ' readonly="readonly"';
    }
}

/**
 * Academic Program disable submit buttons.
 */
function apids()
{
    if (hasRestriction('acad_prog_inquiry_only')) {
        return ' style="display:none !important;"';
    }
}

/**
 * Academic Program disable select dropdowns.
 */
function apid()
{
    if (hasRestriction('acad_prog_inquiry_only')) {
        return ' disabled';
    }
}

/**
 * Address Inquiry only.
 */
function aio()
{
    if (hasRestriction('address_inquiry_only')) {
        return ' readonly="readonly"';
    }
}

/**
 * Address disable submit buttons.
 */
function aids()
{
    if (hasRestriction('address_inquiry_only')) {
        return ' style="display:none !important;"';
    }
}

/**
 * Faculty Inquiry only.
 */
function fio()
{
    if (hasRestriction('faculty_inquiry_only')) {
        return ' readonly="readonly"';
    }
}

/**
 * Faculty disable submit buttons.
 */
function fids()
{
    if (hasRestriction('faculty_inquiry_only')) {
        return ' style="display:none !important;"';
    }
}

/**
 * Student Inquiry only.
 */
function sio()
{
    if (hasRestriction('student_inquiry_only')) {
        return ' readonly="readonly"';
    }
}

/**
 * Student disable submit buttons.
 */
function sids()
{
    if (hasRestriction('student_inquiry_only')) {
        return ' style="display:none !important;"';
    }
}

/**
 * Student Account Inquiry only.
 */
function saio()
{
    if (hasRestriction('student_account_inquiry_only')) {
        return ' readonly="readonly"';
    }
}

/**
 * Student Account disable submit buttons.
 */
function saids()
{
    if (hasRestriction('student_account_inquiry_only')) {
        return ' style="display:none !important;"';
    }
}

/**
 * Staff Inquiry only.
 */
function staio()
{
    if (hasRestriction('student_inquiry_only')) {
        return ' readonly="readonly"';
    }
}

/**
 * Staff disable submit buttons.
 */
function staids()
{
    if (hasRestriction('student_inquiry_only')) {
        return ' style="display:none !important;"';
    }
}

/**
 * Person Inquiry only.
 */
function pio()
{
    if (hasRestriction('person_inquiry_only')) {
        return ' readonly="readonly"';
    }
}

/**
 * Person disable submit buttons.
 */
function pids()
{
    if (hasRestriction('person_inquiry_only')) {
        return ' style="display:none !important;"';
    }
}

/**
 * Parent Inquiry only.
 */
function paio()
{
    if (hasRestriction('parent_inquiry_only')) {
        return ' readonly="readonly"';
    }
}

/**
 * Parent disable submit buttons.
 */
function paids()
{
    if (hasRestriction('parent_inquiry_only')) {
        return ' style="display:none !important;"';
    }
}

/**
 * Disable option
 */
function dopt($perm)
{
    if (!hasPermission($perm)) {
        return ' disabled';
    }
}

/**
 * Retrieve person info by a given field from the person's table.
 *
 * @since 6.2.0
 * @param string $field The field to retrieve the user with.
 * @param int|string $value A value for $field (personID, altID, uname or email).
 */
function get_person_by($field, $value)
{
    $app = \Liten\Liten::getInstance();
    try {
        $person = $app->db->person()
            ->select('person.*, address.*, staff.*, student.*')
            ->_join('address', 'person.personID = address.personID')
            ->_join('staff', 'person.personID = staff.staffID')
            ->_join('student', 'person.personID = student.stuID')
            ->where("person.$field = ?", $value)
            ->findOne();

        return $person;
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    }
}

/**
 * Logs a person in after the login information has checked out.
 *
 * @since 6.2.0
 * @param string $login Person's username or email address.
 * @param string $password Person's password.
 * @param string $rememberme Whether to remember the person.
 */
function etsis_authenticate($login, $password, $rememberme)
{
    $app = \Liten\Liten::getInstance();
    try {
        $person = $app->db->person()
            ->select('person.personID,person.uname,person.password')
            ->_join('staff', 'person.personID = staff.staffID')
            ->_join('student', 'person.personID = student.stuID')
            ->where('(person.uname = ? OR person.email = ?)', [$login, $login])->_and_()
            ->where('(staff.status = "A" OR student.status = "A")')
            ->findOne();

        if (false == $person) {
            _etsis_flash()->error(sprintf(_t('Your account is not active. <a href="%s">More info.</a>'), 'https://www.edutracsis.com/manual/troubleshooting/#Your_Account_is_Deactivated'), $app->req->server['HTTP_REFERER']);
            return;
        } 

        $ll = $app->db->last_login();
        $ll->insert([
            'personID' => _h($person->personID),
            'loginTimeStamp' => \Jenssegers\Date\Date::now()
        ]);
        /**
         * Filters the authentication cookie.
         * 
         * @since 6.2.0
         * @param object $person Person data object.
         * @param string $rememberme Whether to remember the person.
         * @throws Exception If $person is not a database object.
         */
        try {
            $app->hook->apply_filter('etsis_auth_cookie', $person, $rememberme);
        } catch (UnauthorizedException $e) {
            Cascade::getLogger('error')->error(sprintf('AUTHSTATE[%s]: Unauthorized: %s', $e->getCode(), $e->getMessage()));
        }

        etsis_logger_activity_log_write('Authentication', 'Login', get_name(_h($person->personID)), _h($person->uname));
        $redirect_to = ($app->req->post['redirect_to'] != null ? $app->req->post['redirect_to'] : get_base_url());
        etsis_redirect($redirect_to);

    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409), $app->req->server['HTTP_REFERER']);
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409), $app->req->server['HTTP_REFERER']);
    } catch (Exception $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409), $app->req->server['HTTP_REFERER']);
    }
}

/**
 * Checks a person's login information.
 *
 * @since 6.2.0
 * @param string $login Person's username or email address.
 * @param string $password Person's password.
 * @param string $rememberme Whether to remember the person.
 */
function etsis_authenticate_person($login, $password, $rememberme)
{
    $app = \Liten\Liten::getInstance();

    if (empty($login) || empty($password)) {

        if (empty($login)) {
            _etsis_flash()->error(_t('<strong>ERROR</strong>: The username/email field is empty.'), $app->req->server['HTTP_REFERER']);
        }

        if (empty($password)) {
            _etsis_flash()->error(_t('<strong>ERROR</strong>: The password field is empty.'), $app->req->server['HTTP_REFERER']);
        }
        return;
    }

    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $person = get_person_by('email', $login);

        if (false == _h($person->email)) {
            _etsis_flash()->error(_t('<strong>ERROR</strong>: Invalid email address.'), $app->req->server['HTTP_REFERER']);
            return;
        }
    } else {
        $person = get_person_by('uname', $login);
        //$person = get_person_by('personID', $uniqueId);

        if (false == _h($person->uname)) {
            _etsis_flash()->error(_t('<strong>ERROR</strong>: Invalid username.'), $app->req->server['HTTP_REFERER']);
            return;
        }
    }

    if (!etsis_check_password($password, $person->password, _h($person->personID))) {
        _etsis_flash()->error(_t('<strong>ERROR</strong>: The password you entered is incorrect.'), $app->req->server['HTTP_REFERER']);
        return;
    }

    /**
     * Filters log in details.
     * 
     * @since 6.2.0
     * @param string $login Person's username or email address.
     * @param string $password Person's password.
     * @param string $rememberme Whether to remember the person.
     */
    $person = $app->hook->apply_filter('etsis_authenticate_person', $login, $password, $rememberme);
    return $person;
}


// SSO Changes Starts
/**
 * Checks if the user is already present 
 * 
 * @param string $login Person's username or email address.
*/
function etsis_isRegistedUser($login){
    $app = \Liten\Liten::getInstance();
    $regiteredUser = false;

    if (filter_var($login, FILTER_VALIDATE_EMAIL)) 
    {
        $person = get_person_by('email', $login);
        if (true == _h($person->email)) 
        {
            $regiteredUser = true;
        }
    } 
    else {
        $person = get_person_by('uname', $login);
        if (true == _h($person->uname)) 
        {
            $regiteredUser = true;
        }
    }
    return $regiteredUser;
}

/**
 * Authenticate user.
 *
 * @param string $login Person's username or email address.
 * @param string $rememberme Whether to remember the person.
*/
function etsis_authenticate_person_sso($login,$rememberme){
        $app = \Liten\Liten::getInstance();

        if (empty($login)) {
            _etsis_flash()->error(_t('<strong>ERROR</strong>: The username/email field is empty.'), $app->req->server['HTTP_REFERER']);
        }
    
        if(filter_var($login, FILTER_VALIDATE_EMAIL)) 
        {
            $person = get_person_by('email', $login);
            if (false == _h($person->email)) {
                _etsis_flash()->error(_t('<strong>ERROR</strong>: Invalid email address.'), $app->req->server['HTTP_REFERER']);
                return;
            }
        } 
        else 
        {
            $person = get_person_by('uname', $login);
            if (false == _h($person->uname)) {
                _etsis_flash()->error(_t('<strong>ERROR</strong>: Invalid username.'), $app->req->server['HTTP_REFERER']);
                return;
            }
        }
    $person = $app->hook->apply_filter('etsis_authenticate_person_sso', $login,$rememberme);
    return $person;
}

/**
 * Authenticate user.
 * 
 * @param string $login Person's username or email address.
 * @param string $rememberme Whether to remember the person.
 */
function etsis_authenticate_sso($login,$rememberme){
    $app = \Liten\Liten::getInstance();
    try {
        $person = $app->db->person()
            ->select('person.personID,person.uname,person.password,person.fname')
            ->_join('staff', 'person.personID = staff.staffID')
            ->_join('student', 'person.personID = student.stuID')
            ->where('(person.uname = ? OR person.email = ?)', [$login, $login])->_and_()
            ->where('(staff.status = "A" OR student.status = "A")')
            ->findOne();

        if (false == $person) {
            _etsis_flash()->error(sprintf(_t('Your account is not active. <a href="%s">More info.</a>'), 'https://www.edutracsis.com/manual/troubleshooting/#Your_Account_is_Deactivated'), $app->req->server['HTTP_REFERER']);
            return;
        } 
        
        $ll = $app->db->last_login();
        $ll->insert([
            'personID' => _h($person->personID),
            'loginTimeStamp' => \Jenssegers\Date\Date::now()
        ]);

        try {
            $app->hook->apply_filter('etsis_auth_cookie', $person, $rememberme);
        } catch (UnauthorizedException $e) {
            Cascade::getLogger('error')->error(sprintf('AUTHSTATE[%s]: Unauthorized: %s', $e->getCode(), $e->getMessage()));
        }

        etsis_logger_activity_log_write('SSO-Authentication', 'SSO-Login', $person->personID, _h($person->uname));
        $redirect_to = ($app->req->post['redirect_to'] != null ? $app->req->post['redirect_to'] : get_base_url());
        etsis_redirect($redirect_to);

    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409), $app->req->server['HTTP_REFERER']);
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409), $app->req->server['HTTP_REFERER']);
    } catch (Exception $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409), $app->req->server['HTTP_REFERER']);
    }
}

/** Updates person table during login if user already present
 *
 * @param json $user_data user details json
 * 
 */
function etsis_update_person_sso($user_data) {
    $app = \Liten\Liten::getInstance();
    $reg_person = $app->db->person()
        ->select('person.personID,person.uname')
        ->where('(person.uname = ?)',  $user_data->institutionEmail)
        ->findOne();

    //Update Person table
    $person = $app->db->person();
        $person->prefix = if_null($user_data->prefix);
        $person->fname = if_null($user_data->firstName);
        $person->lname = if_null($user_data->lastName);
        $person->mname = if_null($user_data->middleName);
        $person->veteran = 0;
        $person->gender = 'M';
        $person->status = 'A';
        $person->where('personID = ?', $reg_person->personID);                    
        $person->update();

    //Update Address Table
    $sso_addr = $app->db->address();
        $sso_addr->address1 = 'NA';
        $sso_addr->address2 = 'NA';
        $sso_addr->city = 'NA';
        $sso_addr->email1 = if_null($user_data->institutionEmail);
        $sso_addr->where('personID = ?', $reg_person->personID); 
        $sso_addr->update();            
}

/** Insert new data into person table during login and perform login
 *
 * @param json $user_data user details json
 */
function etsis_insert_new_person_sso($user_data){
    
    // $AppoverPerson = $app->db->person()
    //     ->select('person.personID,person.uname')
    //     ->where('(person.uname = ?)', ['SSO'])
    //     ->findOne();

    // $approverId = $AppoverPerson->personID;
    $app = \Liten\Liten::getInstance();
    $approverId = 1;

    $user_role = $user_data->roles[0];
    if($user_role == 'ROLE_USER'){
        $personType = 'student';
    } else{
        $personType = 'staff';
    }

    etsis_logger_activity_log_write('SSO-Authentication', 'etsis_insert_new_person_sso',  $user_data->institutionEmail, "Adding New User");

    $passSuffix = 'etSIS*';
    try {
          
        $password = 'myaccount' . $passSuffix;

        // Insert New Person Record
        $person = $app->db->person();
        $person->uname = $user_data->institutionEmail;
        $person->personType = $personType;
        $person->prefix = $user_data->prefix;
        $person->fname = $user_data->firstName;
        $person->lname = $user_data->lastName;
        $person->email = $user_data->institutionEmail;
        $person->veteran = 0;
        $person->gender = 'M';
        $person->status = 'A';//$user_data->status;
        $person->tags = if_null($user_data->tags);
        $person->approvedBy = $approverId;
        $person->approvedDate = \Jenssegers\Date\Date::now();
        $person->password = etsis_hash_password($password);

        /**
         * Fires before person record is created.
         *
         * @since 6.1.07
         */
        $app->hook->do_action('pre_save_person');

        /**
         * Fires during the saving/creating of an person record.
         * @since 6.1.10
         * @param array $person
         * Person data object.
         */
        $app->hook->do_action('save_person_db_table', $person);

        $person->save();
            
        $_id = $person->lastInsertId();
        //$personType =  'staff';

        // Insert Role of the Person 
        $role = $app->db->person_roles();

        if(strtolower($personType)=='staff')
        {
            $roleID = 11;
            $role->insert([
                'personID' => (int) $_id,
                'roleID' => $roleID,
                'addDate' => \Jenssegers\Date\Date::now()
            ]);
        }
        else if(strtolower($personType)=='student')
        {
            $roleID = 12;
            $role->insert([
                'personID' => (int) $_id,
                'roleID' => $roleID,
                'addDate' => \Jenssegers\Date\Date::now()
            ]);
        }    
            
        // Insert Address of the Person    
        $sso_addr = $app->db->address();
        $sso_addr->personID = (int) $_id;
        $sso_addr->address1 = "NA";
        $sso_addr->address2 = "NA";
        $sso_addr->city = "NA";
        $sso_addr->addDate = \Jenssegers\Date\Date::now();
        $sso_addr->addedBy = $approverId;
        $sso_addr->addressType = "P";  
        $sso_addr->addressStatus = "C";     
        $sso_addr->startDate = \Jenssegers\Date\Date::now();

        $sso_addr->phone1 = "NA";
        $sso_addr->email1 = $user_data->institutionEmail;
        $sso_addr->state = "NA";
        $sso_addr->zip = "NA";
        $sso_addr->country = "NA";
        $sso_addr->endDate = \Jenssegers\Date\Date::now();
        $sso_addr->save();
        etsis_logger_activity_log_write('SSO-Authentication', 'Insert-New-Person', $_id ,$user_data->institutionEmail);

        /**
         * Fires after person record has been created.
         *
         * @since 6.1.07
         * @param string $pass
         *            Plaintext password.
         * @param array $nae
         *            Person data object.
         */
        $app->hook->do_action_array('post_save_person', [
            $password,
            $person
        ]);

            
        //Add Staff / Student Record
        if(strtolower($personType)=='staff')
        {
            etsis_insert_new_staff_sso($user_data, $_id, $approverId, $app);
        }
        else if(strtolower($personType)=='student')
        {
            etsis_insert_new_student_sso($user_data, $_id, $approverId, $app);
        }
        etsis_authenticate_person_sso($user_data->institutionEmail,'yes');
        // _etsis_flash()->success(_t('200 - Success: Ok. If checked `Send username & password to the user`, email has been sent to the queue.'), get_base_url() . 'person' . '/' . (int) $_id . '/');
            
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    }

}

/** Insert new data into student table during login
 *
 * @param json $user_data user details json
 * @param int $id person id generated while inserting into person table
 * @param int $approverId Super Admin ID 1 by default
 * @param Object $app Liten Instance
 */
function etsis_insert_new_student_sso($user_data, $id,$approverId, $app){

        try {

            $student = $app->db->student();
            $student->stuID = $id;  
            $student->status = 'A';
            $student->addDate = \Jenssegers\Date\Date::now();
            $student->approvedBy = $approverId;
            $student->save();

            $sacp1 = $app->db->sacp();
            $sacp1->stuID = $id;
            $sacp1->acadProgCode = _trim('GMS.9000');
            $sacp1->currStatus = 'A';
            $sacp1->statusDate = \Jenssegers\Date\Date::now();
            $sacp1->startDate = \Jenssegers\Date\Date::now();
            $sacp1->approvedBy = $approverId;
            $sacp1->save();

            $al = $app->db->stal();
            $al->stuID = $id;
            $al->acadProgCode = _trim('GMS.9000');
            $al->save();
            

            /**
             * Fires before new student record is created.
             *
             * @since 6.1.07
             * @param int $id Student's ID.
             */
            $app->hook->do_action('pre_save_stu', $id);

            /**
             * @since 6.1.07
             */
            $spro = $app->db->student()
                    ->setTableAlias('spro')
                    ->select('spro.*, person.*, addr.*')
                    ->_join('person', 'spro.stuID = person.personID', 'person')
                    ->_join('address', 'spro.stuID = addr.personID', 'addr')
                    ->where('spro.stuID = ?', $id)->_and_()
                    ->where('addr.addressType = "P"')->_and_()
                    ->where('addr.addressStatus = "C"')
                    ->findOne();
            /**
             * Fires after new student record has been created.
             * 
             * @since 6.1.07
             * @param array $spro Student data object.
             */
            $app->hook->do_action('post_save_stu', $spro);

            etsis_logger_activity_log_write('SSO-Authentication', 'Insert-New-Student', $id ,$user_data->institutionEmail);      
            //_etsis_flash()->success(_etsis_flash()->notice(200), get_base_url() . 'stu/' . $id . '/');
        } catch (NotFoundException $e) {
            Cascade::getLogger('error')->error($e->getMessage());
            _etsis_flash()->error(_etsis_flash()->notice(409));
        } catch (ORMException $e) {
            Cascade::getLogger('error')->error($e->getMessage());
            _etsis_flash()->error(_etsis_flash()->notice(409));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error($e->getMessage());
            _etsis_flash()->error(_etsis_flash()->notice(409));
        }
    
}

/** Insert new data into staff table during login
 *
 * @param json $user_data user details json
 * @param int $id person id generated while inserting into person table
 * @param int $approverId Super Admin ID 1 by default
 * @param Object $app Liten Instance
 */
function etsis_insert_new_staff_sso($user_data, $id, $approverId, $app){
    $get_person = get_person( $id );
    $get_staff = get_staff( $id );
 
    try {
        $add_staff = $app->db->staff();
        $add_staff->staffID = $id;
        $add_staff->status = 'A';
        $add_staff->addDate = Jenssegers\Date\Date::now();
        $add_staff->approvedBy = $approverId;

        /**
         * Fires during the saving/creating of a staff record.
         *
         * @since 6.1.12
         * @param array $staff Staff object.
         */
        $app->hook->do_action('save_staff_db_table', $add_staff);
        $add_staff->save();
               
        $staff_meta = $app->db->staff_meta();
        $staff_meta->staffID = $id;
        $staff_meta->staffType = 'STA';
        $staff_meta->hireDate = Jenssegers\Date\Date::now();
        $staff_meta->startDate = Jenssegers\Date\Date::now();
        $staff_meta->endDate = Jenssegers\Date\Date::now();
        $staff_meta->addDate = Jenssegers\Date\Date::now();
        $staff_meta->approvedBy = $approverId;
        
        /**
         * Fires during the saving/creating of staff
         * meta data.
         *
         * @since 6.1.12
         * @param array $meta Staff meta object.
         */
        $app->hook->do_action('save_staff_meta_db_table', $staff_meta);
        $staff_meta->save();
        
        /**
         * Is triggered after staff record has been created.
         * 
         * @since 6.1.12
         * @param mixed $staff Staff data object.
         */
        $app->hook->do_action('post_save_staff', $add_staff);

        /**
         * Is triggered after staff meta data is saved.
         * 
         * @since 6.1.12
         * @param mixed $staff Staff meta data object.
         */
        $app->hook->do_action('post_save_staff_meta', $staff_meta);

        etsis_logger_activity_log_write('SSO-Authentication', 'Insert-New-Staff', $id ,$user_data->institutionEmail);               
        // _etsis_flash()->success(_etsis_flash()->notice(200), get_base_url() . 'staff' . '/' . $id . '/');

    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error(_etsis_flash()->notice(409));
    }
        
}

// SSO Changes Ends

function etsis_set_auth_cookie($person, $rememberme = '')
{

    $app = \Liten\Liten::getInstance();

    if (!is_object($person)) {
        throw new UnauthorizedException(_t('"$person" should be a database object.'), 4011);
    }

    if (isset($rememberme)) {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         * 
         * @since 6.2.0
         */
        $expire = $app->hook->apply_filter('auth_cookie_expiration', (_h(get_option('cookieexpire')) !== '') ? _h(get_option('cookieexpire')) : $app->config('cookies.lifetime'));
    } else {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         *
         * @since 6.2.0
         */
        $expire = $app->hook->apply_filter('auth_cookie_expiration', ($app->config('cookies.lifetime') !== '') ? $app->config('cookies.lifetime') : 86400);
    }

    $auth_cookie = [
        'key' => 'ETSIS_COOKIENAME',
        'personID' => _h($person->personID),
        'uname' => _h($person->uname),
        'remember' => (isset($rememberme) ? $rememberme : _t('no')),
        'exp' => $expire + time()
    ];

    /**
     * Fires immediately before the secure authentication cookie is set.
     *
     * @since 6.2.0
     * @param string $auth_cookie Authentication cookie.
     * @param int    $expire  Duration in seconds the authentication cookie should be valid.
     */
    $app->hook->do_action('set_auth_cookie', $auth_cookie, $expire);

    $app->cookies->setSecureCookie($auth_cookie);
}

/**
 * Removes all cookies associated with authentication.
 * 
 * @since 6.2.0
 */
function etsis_clear_auth_cookie()
{

    $app = \Liten\Liten::getInstance();

    /**
     * Fires just before the authentication cookies are cleared.
     *
     * @since 6.2.0
     */
    $app->hook->do_action('clear_auth_cookie');

    $vars1 = [];
    parse_str($app->cookies->get('ETSIS_COOKIENAME'), $vars1);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file1 = $app->config('cookies.savepath') . 'cookies.' . $vars1['data'];
    try {
        if (etsis_file_exists($file1)) {
            unlink($file1);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
    }

    $vars2 = [];
    parse_str($app->cookies->get('SWITCH_USERBACK'), $vars2);
    /**
     * Checks to see if the cookie exists on the server.
     * It it exists, we need to delete it.
     */
    $file2 = $app->config('cookies.savepath') . 'cookies.' . $vars2['data'];
    if (etsis_file_exists($file2, false)) {
        @unlink($file2);
    }

    /**
     * After the cookie is removed from the server,
     * we know need to remove it from the browser and
     * redirect the user to the login page.
     */
    $app->cookies->remove('ETSIS_COOKIENAME');
    $app->cookies->remove('SWITCH_USERBACK');
}

/**
 * Shows error messages on login form.
 * 
 * @since 6.2.5
 */
function etsis_login_form_show_message()
{
    $app = \Liten\Liten::getInstance();
    echo $app->hook->apply_filter('login_form_show_message', _etsis_flash()->showMessage());
}

/**
 * Retrieves data from a secure cookie.
 * 
 * @since 6.3.0
 * @param string $key COOKIE key.
 * @return mixed
 */
function get_secure_cookie_data($key)
{
    $app = \Liten\Liten::getInstance();
    $data = $app->cookies->getSecureCookie($key);
    return $data;
}
