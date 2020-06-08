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


// Manju - Login Validation Starts

function etsis_isRegistedUser($login)
{
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


function etsis_authenticate_person_sso($login,$rememberme)
{
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


function etsis_authenticate_sso($login,$rememberme)
{
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


function etsis_update_person_sso($user_data) {

        $reg_person = $app->db->person()
            ->select('person.personID,person.uname')
            ->where('(person.uname = ?)',  $user_data->institutionEmail)
            ->findOne();

        $person = $app->db->person();
            $person->prefix = if_null($user_data->prefix);
            $person->fname = if_null($user_data->firstName);
            $person->lname = if_null($user_data->lastName);
            $person->mname = if_null($user_data->middleName);
            $person->ssn =  if_null($user_data->ssn);
            $person->veteran = if_null($user_data->veteran);
            $person->ethnicity = if_null($user_data->ethnicity);
            $person->dob = if_null($user_data->dob);
            $person->gender = if_null($user_data->gender);
            $person->emergency_contact = if_null($user_data->emergency_contact);
            $person->emergency_contact_phone = if_null($user_data->emergency_contact_phone);
            $person->status = if_null($user_data->status);
            $person->tags = if_null($user_data->tags);
            $person->where('personID = ?', $reg_person->personID);                    
            $person->update();

            $sso_addr = $app->db->address();
            $sso_addr->address1 = $user_data->address1;
            $sso_addr->address2 = $user_data->address2;
            $sso_addr->city = $user_data->city;
            $sso_addr->phone1 = $user_data->phone;
            $sso_addr->email1 = $user_data->email;
            $sso_addr->state = $user_data->state;
            $sso_addr->zip = $user_data->zip;
            $sso_addr->country = $user_data->country;
            $sso_addr->where('personID = ?', $reg_person->personID); 
            $sso_addr->update();
    
    $person_type = $user_data->roles[0];

    if(strtolower($person_type)=='staff')
    {
        //etsis_insert_new_staff_sso($app, $_id, $approverId);
    }
    else if(strtolower($person_type)=='student')
    {
        //etsis_insert_new_student_sso($app, $_id, $approverId);
    }

            
}



function etsis_insert_new_person_sso($app)
{    

    $AppoverPerson = $app->db->person()
            ->select('person.personID,person.uname')
            ->where('(person.uname = ?)', ['SSO'])
            ->findOne();

    $approverId = $AppoverPerson->personID;

    $passSuffix = 'etSIS*';
    if ($app->req->isPost()) {
        try {
            
            $dob = str_replace(['-', '_', '/', '.'], '', $app->req->post['dob']); //yyyy-mm-dd
            $ssn = str_replace(['-', '_', '.'], '', $app->req->post['ssn']);

            if ($app->req->post['ssn'] > 0) {
                $password = $ssn . $passSuffix;
            } elseif (!empty($app->req->post['dob'])) {
                $password = $dob . $passSuffix;
            } else {
                $password = 'myaccount' . $passSuffix;
            }

           // Insert New Person Record
            $person = $app->db->person();
            $person->uname = $app->req->post['uname'];
            $person->altID = if_null($app->req->post['altID']);
            $person->personType = $app->req->post['personType'];
            $person->prefix = $app->req->post['prefix'];
            $person->fname = $app->req->post['fname'];
            $person->lname = $app->req->post['lname'];
            $person->mname = $app->req->post['mname'];
            $person->email = $app->req->post['email'];
            $person->ssn = $app->req->post['ssn'];
            $person->veteran = $app->req->post['veteran'];
            $person->ethnicity = $app->req->post['ethnicity'];
            $person->dob = if_null($app->req->post['dob']);
            $person->gender = $app->req->post['gender'];
            $person->emergency_contact = $app->req->post['emergency_contact'];
            $person->emergency_contact_phone = $app->req->post['emergency_contact_phone'];
            $person->status = $app->req->post['status'];
            $person->tags = if_null($app->req->post['tags']);
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
            
            // Insert Role of the Person 
            $role = $app->db->person_roles();
            $role->insert([
                'personID' => (int) $_id,
                'roleID' => $app->req->post['roleID'],
                'addDate' => \Jenssegers\Date\Date::now()
            ]);
            
            // Insert Address of the Person    
            $sso_addr = $app->db->address();
            $sso_addr->personID = (int) $_id;
            $sso_addr->address1 = $app->req->post['address1'];
            $sso_addr->address2 = $app->req->post['address2'];
            $sso_addr->city = $app->req->post['city'];
            $sso_addr->addDate = \Jenssegers\Date\Date::now();
            $sso_addr->addedBy = $approverId;
            $sso_addr->addressType = "P";  
            $sso_addr->addressStatus = "C";     
            $sso_addr->startDate = \Jenssegers\Date\Date::now();

            $sso_addr->phone1 = $app->req->post['phone'];
            $sso_addr->email1 = $app->req->post['email'];
            $sso_addr->state = $app->req->post['state'];
            $sso_addr->zip = $app->req->post['zip'];
            $sso_addr->country = $app->req->post['country'];
            $sso_addr->endDate = \Jenssegers\Date\Date::now();
            $sso_addr->save();
            etsis_logger_activity_log_write('SSO-Authentication', 'Insert-New-Person'.$approverId.$password, $_id ,$app->req->post['uname']);

            // if (isset($app->req->post['sendemail']) && $app->req->post['sendemail'] == 'send') {

            //     try {
            //         Node::dispense('login_details');
            //         $node = Node::table('login_details');
            //         $node->uname = (string) $app->req->post['uname'];
            //         $node->email = (string) $app->req->post['email'];
            //         $node->personid = (int) $_id;
            //         $node->fname = (string) $app->req->post['fname'];
            //         $node->lname = (string) $app->req->post['lname'];
            //         $node->password = (string) $password;
            //         $node->altid = (string) $app->req->post['altID'];
            //         $node->sent = (int) 0;
            //         $node->save();
            //     } catch (NodeQException $e) {
            //         Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
            //     } catch (Exception $e) {
            //         Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
            //     }
            // }

         

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
            $personType =  $app->req->post['personType'];
            if(strtolower($personType)=='staff')
            {
                etsis_insert_new_staff_sso($app, $_id, $approverId);
            }
            else if(strtolower($personType)=='student')
            {
                etsis_insert_new_student_sso($app, $_id, $approverId);
            }
            etsis_authenticate_person_sso($app->req->post['uname'],$app->req->post['rememberme']);
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

    etsis_register_style('form');
    etsis_register_style('selectize');
    etsis_register_style('gridforms');
    etsis_register_script('datepicker');
    etsis_register_script('select2');
    etsis_register_script('select');
    etsis_register_script('gridforms');

    // $app->view->display('person/add', [
    //     'title' => 'Name and Address'
    // ]);

}

function etsis_insert_new_student_sso($app, $id,$approverId)
{

        try {
            $person = get_person_by('personID', $id);
            if (_escape($person->ssn) > 0) {
                $pass = str_replace('-', '', _escape($person->ssn));
            } elseif (_escape($person->dob) != '0000-00-00') {
                $pass = str_replace('-', '', _escape($person->dob));
            } else {
                $pass = 'myaccount';
            }
            // $degree = $app->db->acad_program()->where('acadProgCode = ?', _trim($app->req->post['acadProgCode']))->findOne();
            // $appl = $app->db->application()->where('personID = ?', $id)->findOne();

            $student = $app->db->student();
            $student->stuID = $id;  
            $student->status = $app->req->post['status'];
            $student->tags = $app->req->post['tags'];
            $student->addDate = \Jenssegers\Date\Date::now();
            $student->approvedBy = $approverId;
            $student->save();

            // $sacp1 = $app->db->sacp();
            // $sacp1->stuID = $id;
            // $sacp1->acadProgCode = $app->req->post['acadProgCode'];
            // $sacp1->currStatus = 'A';
            // $sacp1->statusDate = \Jenssegers\Date\Date::now();
            // $sacp1->startDate = $app->req->post['startDate'];
            // $sacp1->approvedBy = 1;
            // $sacp1->antGradDate = $app->req->post['antGradDate'];
            // $sacp1->advisorID = $app->req->post['advisorID'];
            // $sacp1->catYearCode = $app->req->post['catYearCode'];
            // $sacp1->save();



            $al = $app->db->stal();
            $al->stuID = $id;
            $al->acadProgCode = _trim($app->req->post['acadProgCode']);
            $al->acadLevelCode = _trim($app->req->post['acadLevelCode']);
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

            etsis_logger_activity_log_write('SSO-Authentication', 'Insert-New-Student', $id ,$app->req->post['uname']);      
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
    

    // try {
    //     $stu = $app->db->acad_program()
    //             ->setTableAlias('a')
    //             ->select('a.id,a.acadProgCode,a.acadProgTitle')
    //             ->select('a.acadLevelCode,b.majorName,c.locationName')
    //             ->select('d.schoolName,e.personID,e.startTerm,aclv.comp_months')
    //             ->_join('major', 'a.majorCode = b.majorCode', 'b')
    //             ->_join('location', 'a.locationCode = c.locationCode', 'c')
    //             ->_join('school', 'a.schoolCode = d.schoolCode', 'd')
    //             ->_join('application', 'a.acadProgCode = e.acadProgCode', 'e')
    //             ->_join('student', 'e.personID = f.stuID', 'f')
    //             ->_join('aclv', 'a.acadLevelCode = aclv.code')
    //             ->where('e.personID = ?', $id)->_and_()
    //             ->whereNull('f.stuID');

    //     $q = $stu->find(function($data) {
    //         $array = [];
    //         foreach ($data as $d) {
    //             $array[] = $d;
    //         }
    //         return $array;
    //     });
    // } catch (NotFoundException $e) {
    //     Cascade::getLogger('error')->error($e->getMessage());
    //     _etsis_flash()->error(_etsis_flash()->notice(409));
    // } catch (ORMException $e) {
    //     Cascade::getLogger('error')->error($e->getMessage());
    //     _etsis_flash()->error(_etsis_flash()->notice(409));
    // } catch (Exception $e) {
    //     Cascade::getLogger('error')->error($e->getMessage());
    //     _etsis_flash()->error(_etsis_flash()->notice(409));
    // }

    /**
     * If the database table doesn't exist, then it
     * is false and a 404 should be sent.
     */
    // if ($q === false) {

    //     $app->view->display('error/404', ['title' => '404 Error']);
    // }
    /**
     * If data is zero, redirect to the current
     * student record.
      */
      //elseif (count($q) <= 0) {

    //     etsis_redirect(get_base_url() . 'stu' . '/' . $id . '/');
    // }
    /**
     * If we get to this point, the all is well
     * and it is ok to process the query and print
     * the results in a html format.
     */ 
    // else {

    //     etsis_register_style('form');
    //     etsis_register_style('selectize');
    //     etsis_register_script('select');
    //     etsis_register_script('select2');
    //     etsis_register_script('datepicker');

    //     $app->view->display('student/add', [
    //         'title' => 'Create Student Record',
    //         'student' => $q
    //             ]
    //     );
    // }
}

function etsis_insert_new_staff_sso($app, $id,$approverId)
{
        $get_person = get_person( $id );
        $get_staff = get_staff( $id );
 
            try {
                $add_staff = $app->db->staff();
                $add_staff->staffID = $id;

                $add_staff->schoolCode = $app->req->post['schoolCode'];
                $add_staff->buildingCode = $app->req->post['buildingCode'];
                $add_staff->officeCode = $app->req->post['officeCode'];
                $add_staff->office_phone = $app->req->post['office_phone'];
                $add_staff->deptCode = $app->req->post['deptCode'];

                $add_staff->status = $app->req->post['status'];
                $add_staff->tags = $app->req->post['tags'] != '' ? $app->req->post['tags'] : NULL;
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
                $staff_meta->jobStatusCode = $app->req->post['jobStatusCode'];
                $staff_meta->jobID = $app->req->post['jobID'];
                $staff_meta->staffID = $id;
                $staff_meta->supervisorID = $app->req->post['supervisorID'];
                $staff_meta->staffType = $app->req->post['staffType'];
                $staff_meta->hireDate = $app->req->post['hireDate'];
                $staff_meta->startDate = $app->req->post['startDate'];
                $staff_meta->endDate = ($app->req->post['endDate'] != '' ? $app->req->post['endDate'] : NULL);
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

                etsis_logger_activity_log_write('SSO-Authentication', 'Insert-New-Staff', $id ,$app->req->post['uname']);               
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
        

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($get_person == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ 
        elseif (empty($get_person) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ 
        elseif (_escape($get_person->personID) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If staffID already exists, then redirect
         * the user to the staff record.
         */ 
        elseif (_escape($get_staff->staffID) > 0) {

            etsis_redirect(get_base_url() . 'staff' . '/' . _escape($get_staff->staffID) . '/');
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ 
        else {

            etsis_register_style('form');
            etsis_register_style('selectize');
            etsis_register_script('select');
            etsis_register_script('select2');
            etsis_register_script('datepicker');

            // $app->view->display('staff/add', [
            //     'title' => get_name(_escape($get_person->personID)),
            //     'person' => (array) $get_person
            //     ]
            // );
        }
}



// Manju - Login Validation Ends

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
