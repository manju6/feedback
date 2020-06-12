<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Core\Exception\NotFoundException;
use app\src\Core\Exception\Exception;
use PDOException as ORMException;
use Cascade\Cascade;

/**
 * Index Router
 *  
 * @license GPLv3
 * 
 * @since       5.0.0
 * @package     eduTrac SIS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$hasher = new \app\src\PasswordHash(8, FALSE);

/**
 * Before route check.
 */
$app->before('GET|POST', '/', function() {
    if (_h(get_option('enable_myetsis_portal')) == 0 && !hasPermission('edit_myetsis_css')) {
        etsis_redirect(get_base_url() . 'offline' . '/');
    }
});

$app->get('/', function () use($app) {
    $app->view->display('index/index');
});

$app->before('GET|POST', '/spam/', function() use($app) {
    if (_h(get_option('enable_myetsis_portal')) == 0 && !hasPermission('edit_myetsis_css')) {
        etsis_redirect(get_base_url() . 'offline' . '/');
    }

    if (empty($app->req->server['HTTP_REFERER'])) {
        etsis_redirect(get_base_url());
    }
});

$app->get('/spam/', function () use($app) {
    $app->view->display('index/spam');
});

$app->get('/offline/', function () use($app) {
    $app->view->display('index/offline');
});

$app->before('GET|POST', '/online-app/', function() {
    if (_h(get_option('enable_myetsis_portal')) == 0 && !hasPermission('edit_myetsis_css')) {
        etsis_redirect(get_base_url() . 'offline' . '/');
    }
});

/**
 * Before route check.
 */

$app->before('GET|POST', '/login/', function() use($app) {
    if (is_user_logged_in()) {
        $redirect_to = ($app->req->get['redirect_to'] != null ? $app->req->get['redirect_to'] : get_base_url());
        etsis_redirect($redirect_to);
    }
});

$app->match('GET|POST', '/login/', function () use($app) {

    if ($app->req->isPost()) {        
         etsis_authenticate_person($app->req->post['uname'], $app->req->post['password'], $app->req->post['rememberme']);
    }

    $app->view->display('index/login', [
        'title' => 'Login'
        ] );
});


// SSO Changes Starts

$app->before('GET|POST', '/sso-login/', function() use($app) {

    if (is_user_logged_in()) {
        $redirect_to = ($app->req->get['redirect_to'] != null ? $app->req->get['redirect_to'] : get_base_url());
        etsis_redirect($redirect_to);
    }
});

$app->match('GET|POST','/sso-login/', function () use($app) 
{  
    if ($app->req->isGet()) 
    {
        $opts = [
            "http" => [
                    "method" => "GET"
                    ]
                ];
        
        $context = stream_context_create($opts);
        $user_token = $app->req->get['user'];
        $master_url = 'http://23.99.141.44:3000/getUserDetails?user=';
        $user_response = file_get_contents($master_url.$user_token, false, $context);
        #$user_response = '{"user": {"institutionEmail": "rtui23423@20minutemail.it","prefix": "Mr", "firstName": "Alfi", "lastName": "Solomon", "institutionName": "AHEA", "roles": ["ROLE_USER"]}}';
        
        $user_response = json_decode($user_response);
        $user = $user_response->user;
        $unmae = $user->institutionEmail;

        if(etsis_isRegistedUser($unmae))
        {
            etsis_logger_activity_log_write('SSO-Authentication', 'User Validation',  $app->req->get['user'], "User already present");
            etsis_update_person_sso($user);            
            etsis_authenticate_person_sso($unmae,'yes');          
        }     
        else
        {
            etsis_logger_activity_log_write('SSO-Authentication', 'User Validation',  $unmae, "New User");
            etsis_insert_new_person_sso($user); 
        }

    }

    if ($app->req->isPost()) 
    {
            if(etsis_isRegistedUser($app->req->post['uname']))
            {
                etsis_authenticate_person_sso($app->req->post['uname'],$app->req->post['rememberme']);          
            }     
            else
            {           
                etsis_insert_new_person_sso($app); 
            }
   }
               
   $app->view->display('index/login', [
    'title' => 'Login'
    ]);

});

// SSO Changes Ends

$app->post('/reset-password/', function () use($app) {

    if ($app->req->isPost()) {
        try {
            $addr = $app->req->post['email'];
            $name = $app->req->post['name'];
            $body = sprintf(_t('<p><strong>Name:</strong> %s</p>'), $app->req->post['name']);
            $body .= sprintf(_t('<p><strong>Username:</strong> %s</p>'), $app->req->post['uname']);
            $body .= sprintf(_t('<p><strong>Student/Staff ID:</strong> %s</p>'), $app->req->post['sid']);
            $body .= sprintf(_t('<p><strong>Email Address:</strong> %s</p>'), $app->req->post['email']);
            $body .= $app->req->post['message'];
            $message = process_email_html($body, _t("Reset Password Request"));
            $headers[] = sprintf("From: %s <%s>", $name, $addr);
            _etsis_email()->etsisMail(_h(get_option('system_email')), _t("Reset Password Request"), $message, $headers);
            _etsis_flash()->success(_t('Your request has been sent.'), $app->req->server['HTTP_REFERER']);
        } catch (phpmailerException $e) {
            _etsis_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (Exception $e) {
            _etsis_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        }
    }
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/profile/', function() {
    if (!is_user_logged_in()) {
        _etsis_flash()->error(_t('401 - Error: Unauthorized.'), get_base_url() . 'login' . '/');
        exit();
    }
});

$app->get('/profile/', function () use($app) {

    try {
        $profile = $app->db->query("SELECT 
                                personID,prefix,uname,fname,lname,mname,email,ssn,ethnicity,
                                dob,emergency_contact,emergency_contact_phone,
                            CASE veteran 
                            WHEN '1' THEN 'Yes' 
                            ELSE 'No' END AS 'Veteran',
                            CASE gender 
                            WHEN 'M' THEN 'Male'
                            ELSE 'Female' END AS 'Gender'
                            FROM person 
                            WHERE personID = ?", [get_persondata('personID')]
        );
        $q1 = $profile->find(function($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        $addr = $app->db->address()
            ->setTableAlias('a')
            ->_join('address', 'a.personID = b.personID', 'b')
            ->where('a.personID = ?', get_persondata('personID'))->_and_()
            ->where('b.addressType = "P"')->_and_()
            ->where('b.addressStatus = "C"')
            ->where('b.endDate IS NULL')->_or_()
            ->whereLte('b.endDate', '0000-00-00');
        $q2 = $addr->find(function($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
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

    $app->view->display('index/profile', [
        'title' => 'My Profile',
        'profile' => $q1,
        'addr' => $q2
        ]
    );
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/password/', function() {
    if (!is_user_logged_in()) {
        _etsis_flash()->error(_t('401 - Error: Unauthorized.'), get_base_url() . 'login' . '/');
        exit();
    }
});

$app->match('GET|POST', '/password/', function () use($app) {
    if ($app->req->isPost()) {
        try {
            $pass = $app->db->person()->select('personID,password')
                ->where('personID = ?', get_persondata('personID'));
            $q = $pass->find(function($data) {
                $array = [];
                foreach ($data as $d) {
                    $array[] = $d;
                }
                return $array;
            });
            $a = [];
            foreach ($q as $r) {
                $a[] = $r;
            }
            if (etsis_check_password($app->req->post['currPass'], $r['password'], $r['personID'])) {
                $sql = $app->db->person();
                $sql->password = etsis_hash_password($app->req->post['newPass']);
                $sql->where('personID = ?', get_persondata('personID'));
                $sql->update();

                /**
                 * @since 6.1.07
                 */
                $pass = [];
                $pass['pass'] = $app->req->post['newPass'];
                $pass['personID'] = get_persondata('personID');
                $pass['uname'] = get_persondata('uname');
                $pass['fname'] = get_persondata('fname');
                $pass['lname'] = get_persondata('lname');
                $pass['email'] = get_persondata('email');
                /**
                 * Fires after password was updated successfully.
                 * 
                 * @since 6.1.07
                 * @param string $pass Plaintext password submitted by logged in user.
                 */
                $app->hook->do_action('post_change_password', $pass);

                _etsis_flash()->success(_etsis_flash()->notice(200), $app->req->server['HTTP_REFERER']);
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

    $app->view->display('index/password', [
        'title' => 'Change Password'
        ]
    );
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/permission.*', function() {
    if (!hasPermission('access_permission_screen')) {
        _etsis_flash()->error(_t('403 - Error: Forbidden.'), get_base_url() . 'dashboard' . '/');
        exit();
    }
});

$app->match('GET|POST', '/permission/', function () use($app) {

    etsis_register_style('form');
    etsis_register_style('table');
    etsis_register_script('select');
    etsis_register_script('select2');
    etsis_register_script('datatables');

    $app->view->display('permission/index', [
        'title' => 'Manage Permissions',
        ]
    );
});

$app->match('GET|POST', '/permission/(\d+)/', function ($id) use($app) {
    if ($app->req->isPost()) {
        try {
            $perm = $app->db->permission();
            foreach (_filter_input_array(INPUT_POST) as $k => $v) {
                $perm->$k = $v;
            }
            $perm->where('id = ?', $id);
            $perm->update();

            etsis_logger_activity_log_write('Update Record', 'Permission', _filter_input_string(INPUT_POST, 'permName'), get_persondata('uname'));
            _etsis_flash()->success(_etsis_flash()->notice(200), $app->req->server['HTTP_REFERER']);
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

    try {
        $perm = $app->db->permission()->where('id = ?', $id)->findOne();
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
    if ($perm == false) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If the query is legit, but there
     * is no data in the table, then 404
     * will be shown.
     */ elseif (empty($perm) == true) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If data is zero, 404 not found.
     */ elseif (_h($perm->id) <= 0) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If we get to this point, the all is well
     * and it is ok to process the query and print
     * the results in a html format.
     */ else {

        etsis_register_style('form');
        etsis_register_script('select');
        etsis_register_script('select2');

        $app->view->display('permission/view', [
            'title' => 'Edit Permission',
            'perm' => $perm
            ]
        );
    }
});

$app->match('GET|POST', '/permission/add/', function () use($app) {

    if ($app->req->isPost()) {
        try {
            $perm = $app->db->permission();
            foreach (_filter_input_array(INPUT_POST) as $k => $v) {
                $perm->$k = $v;
            }
            $perm->save();

            etsis_logger_activity_log_write('New Record', 'Permission', _filter_input_string(INPUT_POST, 'permName'), get_persondata('uname'));
            _etsis_flash()->success(_etsis_flash()->notice(200), get_base_url() . 'permission' . '/');
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
    etsis_register_script('select');
    etsis_register_script('select2');

    $app->view->display('permission/add', [
        'title' => 'Add New Permission'
        ]
    );
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/role.*', function() {
    if (!hasPermission('access_role_screen')) {
        _etsis_flash()->error(_t('403 - Error: Forbidden.'), get_base_url() . 'dashboard' . '/');
        exit();
    }
});

$app->match('GET|POST', '/role/', function () use($app) {

    etsis_register_style('form');
    etsis_register_style('table');
    etsis_register_script('select');
    etsis_register_script('select2');
    etsis_register_script('datatables');

    $app->view->display('role/index', [
        'title' => 'Manage Roles'
        ]
    );
});

$app->match('GET|POST', '/role/(\d+)/', function ($id) use($app) {
    try {
        $role = $app->db->role()->where('id = ?', $id)->findOne();
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
    if ($role == false) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If the query is legit, but there
     * is no data in the table, then 404
     * will be shown.
     */ elseif (empty($role) == true) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If data is zero, 404 not found.
     */ elseif (count($role->id) <= 0) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If we get to this point, the all is well
     * and it is ok to process the query and print
     * the results in a html format.
     */ else {

        etsis_register_style('form');
        etsis_register_style('table');
        etsis_register_script('select');
        etsis_register_script('select2');

        $app->view->display('role/view', [
            'title' => 'Edit Role',
            'role' => $role
            ]
        );
    }
});

$app->match('GET|POST', '/role/add/', function () use($app) {

    if ($app->req->isPost()) {
        try {
            $roleID = $app->req->post['roleID'];
            $roleName = $app->req->post['roleName'];
            $rolePerm = maybe_serialize($app->req->post['permission']);

            $strSQL = $app->db->query(sprintf("REPLACE INTO `role` SET `id` = %u, `roleName` = '%s', `permission` = '%s'", $roleID, $roleName, $rolePerm));
            if ($strSQL) {
                $_id = $strSQL->lastInsertId();
                _etsis_flash()->success(_etsis_flash()->notice(200), get_base_url() . 'role' . '/' . $_id . '/');
            } else {
                _etsis_flash()->error(_etsis_flash()->notice(409));
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

    etsis_register_style('form');
    etsis_register_script('select');
    etsis_register_script('select2');

    $app->view->display('role/add', [
        'title' => 'Add Role'
        ]
    );
});

$app->post('/role/editRole/', function () use($app) {
    try {
        $roleID = $app->req->post['id'];
        $roleName = $app->req->post['roleName'];
        $rolePerm = maybe_serialize($app->req->post['permission']);

        $strSQL = $app->db->query(sprintf("REPLACE INTO `role` SET `id` = %u, `roleName` = '%s', `permission` = '%s'", $roleID, $roleName, $rolePerm));
        if ($strSQL) {
            _etsis_flash()->success(_etsis_flash()->notice(200), $app->req->server['HTTP_REFERER']);
        } else {
            _etsis_flash()->error(_etsis_flash()->notice(409), $app->req->server['HTTP_REFERER']);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
    } catch (Exception $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _etsis_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
    }
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/message/', function() {
    if (!is_user_logged_in()) {
        _etsis_flash()->error(_t('401 - Error: Unauthorized.'), get_base_url() . 'login' . '/');
        exit();
    }
});

$app->post('/message/', function () use($app) {
    $options = ['myetsis_welcome_message'];

    foreach ($options as $option_name) {
        if (!isset($app->req->post[$option_name]))
            continue;
        $value = $app->req->post[$option_name];
        update_option($option_name, $value);
    }
    /**
     * Fired when updating options for options_meta table.
     * 
     * @return mixed
     */
    $app->hook->do_action('myetsis_welcome_message_option');
    /* Write to logs */
    etsis_logger_activity_log_write('Update', 'myetSIS', 'Welcome Message', get_persondata('uname'));

    etsis_redirect($app->req->server['HTTP_REFERER']);
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/switchUserTo/(\d+)/', function() {
    if (!hasPermission('login_as_user')) {
        _etsis_flash()->error(_t('403 - Error: Forbidden.'), get_base_url() . 'dashboard' . '/');
        exit();
    }
});

$app->get('/switchUserTo/(\d+)/', function ($id) use($app) {

    if (isset($app->req->cookie['ETSIS_COOKIENAME'])) {
        $switch_cookie = [
            'key' => 'SWITCH_USERBACK',
            'personID' => get_persondata('personID'),
            'uname' => get_persondata('uname'),
            'remember' => (_h(get_option('cookieexpire')) - time() > 86400 ? _t('yes') : _t('no')),
            'exp' => _h(get_option('cookieexpire')) + time()
        ];
        $app->cookies->setSecureCookie($switch_cookie);
    }

    $vars = [];
    parse_str($app->cookies->get('ETSIS_COOKIENAME'), $vars);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file = $app->config('cookies.savepath') . 'cookies.' . $vars['data'];
    try {
        if (etsis_file_exists($file)) {
            unlink($file);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
    }

    /**
     * Delete the old cookie.
     */
    $app->cookies->remove("ETSIS_COOKIENAME");

    $auth_cookie = [
        'key' => 'ETSIS_COOKIENAME',
        'personID' => $id,
        'uname' => get_user_value($id, 'uname'),
        'remember' => (_h(get_option('cookieexpire')) - time() > 86400 ? _t('yes') : _t('no')),
        'exp' => _h(get_option('cookieexpire')) + time()
    ];

    $app->cookies->setSecureCookie($auth_cookie);

    _etsis_flash()->success(_t('Switching user was successful.'), get_base_url() . 'dashboard' . '/');
});

$app->get('/switchUserBack/(\d+)/', function ($id) use($app) {
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

    $app->cookies->remove("ETSIS_COOKIENAME");

    $vars2 = [];
    parse_str($app->cookies->get('SWITCH_USERBACK'), $vars2);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file2 = $app->config('cookies.savepath') . 'cookies.' . $vars2['data'];
    try {
        if (etsis_file_exists($file2)) {
            unlink($file2);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
    }

    $app->cookies->remove("SWITCH_USERBACK");

    /**
     * After the login as user cookies have been
     * removed from the server and the browser,
     * we need to set fresh cookies for the
     * original logged in user.
     */
    $switch_cookie = [
        'key' => 'ETSIS_COOKIENAME',
        'personID' => $id,
        'uname' => get_user_value($id, 'uname'),
        'remember' => (_h(get_option('cookieexpire')) - time() > 86400 ? _t('yes') : _t('no')),
        'exp' => _h(get_option('cookieexpire')) + time()
    ];
    $app->cookies->setSecureCookie($switch_cookie);
    _etsis_flash()->success(_t('Switching back to original user was successful.'), get_base_url() . 'dashboard' . '/');
});

$app->get('/logout/', function () {

    etsis_logger_activity_log_write('Authentication', 'Logout', get_name(get_persondata('personID')), get_persondata('uname'));
    /**
     * This function is documented in app/functions/auth-function.php.
     * 
     * @since 6.2.0
     */
    etsis_clear_auth_cookie();

    etsis_redirect(get_base_url() . 'login' . '/');
});

$app->setError(function() use($app) {

    $app->view->display('error/404', [
        'title' => '404 Error'
        ]
    );
});
