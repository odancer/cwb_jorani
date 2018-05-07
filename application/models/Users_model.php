<?php
/**
 * This model contains the business logic and manages the persistence of users (employees)
 * @copyright  Copyright (c) 2014-2017 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.1.0
 */

if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * This model contains the business logic and manages the persistence of users (employees)
 * It is also used by the session controller for the authentication.
 */
class Users_model extends CI_Model {

    /**
     * Default constructor
     */
    public function __construct() {

    }

    /**
     * Get the list of users or one user
     * @param int $id optional id of one user
     * @return array record of users
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function getUsers($id=0) {
        $this->db->select('users.*');
        if ($id === 0 ) {
            $query = $this->db->get('users');
            return $query->result_array();
        }
        $query = $this->db->get_where('users', array('users.id' => $id));
        return $query->row_array();
    }


    public function getUsers2($role=0,$grp=0) {
        $this->db->select('users.*');
        if ( ($role === 0 && $grp === 0)  ) {
            $query = $this->db->get('users');
            return $query->result_array();
        } 
        if($role !=0) $this->db->where('role',$role);
        if($grp !=0) $this->db->where('organization',$grp);
        $query = $this->db->get('users');
        return $query->result_array();
    }
    
    /**
     * Get the list of users and their roles
     * @return array record of users
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function getUsersAndRoles() {
        $this->db->select('users.id, active, firstname, lastname, login, email');
        $this->db->select("GROUP_CONCAT(roles.name SEPARATOR ',') as roles_list", FALSE);
        $this->db->join('roles', 'roles.id = (users.role & roles.id)');
        $this->db->group_by('users.id, active, firstname, lastname, login, email');
        $query = $this->db->get('users');
        return $query->result_array();
    }

    public function getUsersAndRoles2($grp_info) {
        $this->db->select('users.id, active, firstname, lastname, login, email');
        $this->db->select("GROUP_CONCAT(roles.name SEPARATOR ',') as roles_list", FALSE);
        $this->db->join('roles', 'roles.id = (users.role & roles.id)');
        if($grp_info !=0 ) $this->db->where('organization',$grp_info);
        $this->db->group_by('users.id, active, firstname, lastname, login, email');
        $query = $this->db->get('users');
        return $query->result_array();
    }
    
    /**
     * Get the list of employees
     * @return array record of users
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function getAllEmployees() {
        $this->db->select('id, firstname, lastname, email');
        $query = $this->db->get('users');
        return $query->result_array();
    }

    public function getAllEmployees2($grp_info) {
        $this->db->select('id, firstname, lastname, email');
        $this->db->where('organization',$grp_info);
        $query = $this->db->get('users');
        return $query->result_array();
    }

    /**
     * Get the list of employees and the name of their entities
     * @return array record of users
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function getAllEmployeesAndTheirEntities() {
        $this->db->select('users.id, firstname, lastname');
        $this->db->select('organization.name as department_name');
        $this->db->from('users');
        $this->db->join('organization', 'users.organization = organization.id');
        $this->db->order_by("lastname", "asc");
        $this->db->order_by("firstname", "asc");
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /**
     * Get the name of a given user
     * @param int $id Identifier of employee
     * @return string firstname and lastname of the employee
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function getName($id) {
        $record = $this->getUsers($id);
        if (count($record) > 0) {
            return $record['firstname'] . ' ' . $record['lastname'];
        }
    }

    public function getRole($id) {
        $record = $this->getUsers($id);
        if (count($record) > 0) {
            return $record['role'] ;
        }
    }
 

    public function getGroup($id) {
        $record = $this->getUsers($id);
        if (count($record) > 0) {
            return $record['organization'] ;
        }
    }

    public function getGroupBySupervisor2($id) {
        $this->db->select('id');
        $this->db->from('organization');
        $this->db->where('supervisor2',$id);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function getLoginid($id) {
        $record = $this->getUsers($id);
        if (count($record) > 0) {
            return $record['login'] ;
        }
    }


    public function getID($login) {
       $this->db->select('id');
       $this->db->from('users');
       $this->db->where('login',$login);
       $query=$this->db->get();
       return $query->row_array();
    }
    
    /**
     * Get the list of employees that are the collaborators of the given user
     * @param int $id identifier of the manager
     * @return array record of users
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function getCollaboratorsOfManager($id = 0) {
        $grp_list=array();
        $user_info=$this->getUsers($id);
        if($user_info['role'] ==32) {
            $group_list=$this->getGroupBySupervisor2($id);
            for($i=0;$i<count($group_list);$i++) {
                array_push($grp_list,$group_list[$i]['id']);
            }
        }
        //error_log( print_r($grp_list,TRUE) );
        $this->db->select('users.*');
        $this->db->select('organization.name as department_name, positions.name as position_name, contracts.name as contract_name');
        $this->db->from('users');
        $this->db->join('organization', 'users.organization = organization.id');
        $this->db->join('positions', 'positions.id  = users.position', 'left');
        $this->db->join('contracts', 'contracts.id  = users.contract', 'left');
        $this->db->order_by("lastname", "asc");
        $this->db->order_by("firstname", "asc");
        if(($user_info['role'] !=8) && ($user_info['role'] !=32)) $this->db->where('manager', $id);
        if(($user_info['role'] ==8) && ($user_info['id'] !=1)) $this->db->where('organization', $user_info['organization']);
        if($user_info['role'] ==32) {
            if (empty($grp_list)) {
                    $this->db->where_in('organization', $user_info['organization']);
                }else{
                    $this->db->where_in('organization', $grp_list);   
                }
        }
        $query = $this->db->get();
        return $query->result_array();
    }


    /**
     * Check if an employee is the collaborator of the given user
     * @param int $employee identifier of the collaborator
     * @param int $manager identifier of the manager
     * @return bool TRUE if the employee is a collaborator, FALSE otherwise
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function isCollaboratorOfManager($employee, $manager) {
        $this->db->from('users');
        $this->db->where('id', $employee);
        $this->db->where('manager', $manager);
        $result = $this->db->get()->result_array();
        return (count($result) > 0);
    }
    
    /**
     * Check if a login can be used before creating the user
     * @param string $login login identifier
     * @return bool TRUE if available, FALSE otherwise
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function isLoginAvailable($login) {
        $this->db->from('users');
        $this->db->where('login', $login);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * Delete a user from the database
     * @param int $id identifier of the user
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function deleteUser($id) {
        $this->db->delete('users', array('id' => $id));
        $this->load->model('entitleddays_model');
        $this->load->model('leaves_model');
        $this->load->model('overtime_model');
        $this->entitleddays_model->deleteEntitledDaysCascadeUser($id);
        $this->leaves_model->deleteLeavesCascadeUser($id);
        $this->overtime_model->deleteExtrasCascadeUser($id);
        //Cascade delete line manager role
        $data = array(
            'manager' => NULL
        );
        $this->db->where('manager', $id);
        $this->db->update('users', $data);
        $this->deleteUsersHistory($id);
    }

    /**
     * Insert a new user into the database. Inserted data are coming from an HTML form
     * @return string deciphered password (so as to send it by e-mail in clear)
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function setUsers() {
        //Decipher the password value (RSA encoded -> base64 -> decode -> decrypt)
        $password = '';
        if (function_exists('openssl_pkey_get_private')) {
            $privateKey = openssl_pkey_get_private(file_get_contents('./assets/keys/private.pem', TRUE));
            openssl_private_decrypt(base64_decode($this->input->post('CipheredValue')), $password, $privateKey);
        } else {
            require_once FCPATH . "vendor/autoload.php";
            $rsa = new phpseclib\Crypt\RSA();
            $private_key = file_get_contents('./assets/keys/private.pem', TRUE);
            $rsa->setEncryptionMode(phpseclib\Crypt\RSA::ENCRYPTION_PKCS1);
            $rsa->loadKey($private_key, phpseclib\Crypt\RSA::PRIVATE_FORMAT_PKCS1);
            $password = $rsa->decrypt(base64_decode($this->input->post('CipheredValue')));
        }
        //Hash the clear password using bcrypt (8 iterations)
        $salt = '$2a$08$' . substr(strtr(base64_encode($this->getRandomBytes(16)), '+', '.'), 0, 22) . '$';
        $hash = crypt($password, $salt);
        
        //Role field is a binary mask
        $role = 0;
        foreach($this->input->post("role") as $role_bit){
            $role = $role | $role_bit;
        }
        
        $data = array(
            'firstname' => $this->input->post('firstname'),
            'lastname' => $this->input->post('lastname'),
            'login' => $this->input->post('login'),
            'email' => $this->input->post('email'),
            'password' => $hash,
            'role' => $role,
            'manager' => $this->input->post('manager'),
            'contract' => $this->input->post('contract'),
            'identifier' => $this->input->post('identifier'),
            'jobcategory' => $this->input->post('jobcategory'),
            'salarypoint' => $this->input->post('salarypoint'),
            'salary' => $this->input->post('salary'),
            'language' => $this->input->post('language'),
            'grade' =>0,
            'rating'=>"未評定",
            'stationedorg' => $this->input->post('stationedorg'),
            'stationedunit' => $this->input->post('stationedunit'),
            'bidname' => $this->input->post('bidname'),
            'timezone' => $this->input->post('timezone')
        );
        
        if ($this->input->post('entity') != NULL && $this->input->post('entity') != '') {
            $data['organization'] = $this->input->post('entity');
        }
        if ($this->input->post('position') != NULL && $this->input->post('position') != '') {
            $data['position'] = $this->input->post('position');
        }
        if ($this->input->post('datehired') != NULL && $this->input->post('datehired') != '') {
            $data['datehired'] = $this->input->post('datehired');
        }
        
        if ($this->config->item('ldap_basedn_db')!==FALSE) {
            $data['ldap_path'] = $this->input->post('ldap_path');
        }
        $this->db->insert('users', $data);

        
        //Deal with user having no line manager
        if ($this->input->post('manager') == -1) {
            $id = $this->db->insert_id();
            $data = array(
                'manager' => $id
            );
            $this->db->where('id', $id);
            $this->db->update('users', $data);
        }
        return $password;
    }

     public function setUsersHistory($userInfo) {
        //error_log( print_r($userInfo['position'], TRUE) );
        $login=$this->input->post('login');
        $id =($this->getID($login))['id'];
        $position=$this->input->post('position');
        $jobcategory=$this->input->post('jobcategory');
        $rating=$this->input->post('rating');
        $grade=$this->input->post('grade');
        $salary=$this->input->post('salary');
        $salarypoint=$this->input->post('salarypoint');
        $change_type=array();
        $data=array (
            'user_id' => $id,
            'login' => $login
        );

        if (($position != $userInfo['position']) || ($jobcategory!= $userInfo['jobcategory']) || ($rating != $userInfo['rating']) || ($grade != $userInfo['grade']) || ($salary != $userInfo['salary']) || ($salarypoint != $userInfo['salarypoint'])) {

            if($position != $userInfo['position']) array_push($change_type,1);
            if($jobcategory!= $userInfo['jobcategory']) array_push($change_type,2);
            if($rating != $userInfo['rating']) array_push($change_type,3);;
            if($grade != $userInfo['grade']) array_push($change_type,4);
            if($salary != $userInfo['salary']) array_push($change_type,5);
            if($salarypoint != $userInfo['salarypoint']) array_push($change_type,6);
            //$data['change_type'] = $change_type;
            for($i=0;$i< count($change_type);$i++) {
                $data['change_type'] = $change_type[$i];
                $data['position'] = $this->input->post('position');
                $data['jobcategory'] = $this->input->post('jobcategory');
                $data['rating'] = $this->input->post('rating');
                $data['grade'] = $this->input->post('grade');
                $data['salary'] = $this->input->post('salary');
                $data['salarypoint'] = $this->input->post('salarypoint');
                $this->db->insert('users_history', $data);
            }
            return $change_type;
        }

     } 

     public function createUsersHistory() {
        //error_log( print_r($userInfo['position'], TRUE) );
        $login=$this->input->post('login');
        $id =($this->getID($login))['id'];
        $data=array (
            'user_id' => $id,
            'login' => $login
        );
        $data['position']=$this->input->post('position');
        $data['jobcategory']=$this->input->post('jobcategory');
        $data['salary']=$this->input->post('salary');
        $data['salarypoint']=$this->input->post('salarypoint');
        $data['grade']=0;
        $data['rating']="未評定";
        $data['change_type']=1;
        $this->db->insert('users_history', $data);
     }

     public function getUsersHistory($id,$num,$change_type) {
        $this->db->select('*');
        $this->db->from('users_history');
        $this->db->where('user_id',$id);
        if($change_type != 0) $this->db->where('change_type',$change_type);
        $this->db->order_by("change_date", "desc");
        if($num != 0) $this->db->limit($num);
        $query=$this->db->get();
        if($num !=0) {
            return $query->row_array();
        }else {
             return $query->result_array();
        }
     } 

     public function deleteUsersHistory($id) {
        $this->db->delete('users_history', array('user_id' => $id));
     } 

    
    /**
     * Create a user record in the database. the difference with set_users function is that it doesn't rely
     * on values posted by en HTML form. Can be used by a mass importer for example.
     * @param string $firstname User firstname
     * @param string $lastname User lastname
     * @param string $login User login
     * @param string $email User e-mail
     * @param string $password User password
     * @param int $role role mask (2 for user or 8 for manager)
     * @param int $manager Id of the manager or NULL
     * @param int $organization Id of the organization or NULL
     * @param int $contract Id of the contract or NULL
     * @param int $position Id of the position or NULL
     * @param date $datehired Date of hiring or NULL
     * @param string $identifier Internal identifier or NULL
     * @param string $language language code or NULL
     * @param string $timezone timezone or NULL
     * @param string $ldap_path ldap path or NULL
     * @param bool $active Is user active or NULL
     * @param string $country country of the employee or NULL
     * @param string $calendar calendar path or NULL
     * @return int Inserted User Identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function insertUserByApi($firstname, $lastname, $login, $email, $password, $role,
            $manager = NULL,
            $organization = NULL,
            $contract = NULL,
            $position = NULL,
            $datehired = NULL,
            $identifier = NULL,
            $language = NULL,
            $timezone = NULL,
            $ldap_path = NULL,
            $active = NULL,
            $country = NULL,
            $calendar = NULL) {

        //Hash the clear password using bcrypt (8 iterations)
        $salt = '$2a$08$' . substr(strtr(base64_encode($this->getRandomBytes(16)), '+', '.'), 0, 22) . '$';
        $hash = crypt($password, $salt);
        $this->db->set('firstname', $firstname);
        $this->db->set('lastname', $lastname);
        $this->db->set('login', $login);
        $this->db->set('email', $email);
        $this->db->set('password', $hash);
        $this->db->set('role', $role);
        if (isset($manager)) $this->db->set('manager', $manager);
        if (isset($organization)) $this->db->set('organization', $organization);
        if (isset($contract)) $this->db->set('contract', $contract);
        if (isset($position)) $this->db->set('position', $position);
        if (isset($datehired)) $this->db->set('datehired', $datehired);
        if (isset($identifier)) $this->db->set('identifier', $identifier);
        if (isset($language)) $this->db->set('language', $language);
        if (isset($timezone)) $this->db->set('timezone', $timezone);
        if (isset($ldap_path)) $this->db->set('ldap_path', $ldap_path);
        if (isset($active)) $this->db->set('active', $active);
        if (isset($country)) $this->db->set('country', $country);
        if (isset($calendar)) $this->db->set('calendar', $calendar);
        $this->db->insert('users');
        return $this->db->insert_id();
    }

    /**
     * Update a user record in the database. the difference with update_users function is that it doesn't rely
     * on values posted by en HTML form. Can be used by a mass importer for example.
     * @param int $id Id of the user
     * @param array $data Associative array of fields to be updated
     * @return int Number of affected rows
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function updateUserByApi($id, $data) {
        if (isset($password)){
            //Hash the clear password using bcrypt (8 iterations)
            $salt = '$2a$08$' . substr(strtr(base64_encode($this->getRandomBytes(16)), '+', '.'), 0, 22) . '$';
            $hash = crypt($password, $salt);
            $this->db->set('password', $hash);
        }
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }
    
    /**
     * Update a given user in the database. Update data are coming from an HTML form
     * @return int number of affected rows
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function updateUsers() {
        
        //Role field is a binary mask
        $role = 0;
        foreach($this->input->post("role") as $role_bit){
            $role = $role | $role_bit;
        }
        
        //Deal with user having no line manager
        if ($this->input->post('manager') == -1) {
            $manager = $this->input->post('id');
        } else {
            $manager = $this->input->post('manager');
        }
        
        $data = array(
            'firstname' => $this->input->post('firstname'),
            'lastname' => $this->input->post('lastname'),
            'login' => $this->input->post('login'),
            'email' => $this->input->post('email'),
            'role' => $role,
            'manager' => $manager,
            'contract' => $this->input->post('contract'),
            'identifier' => $this->input->post('identifier'),
            'jobcategory' => $this->input->post('jobcategory'),
            'salarypoint' => $this->input->post('salarypoint'),
            'salary' => $this->input->post('salary'),
            'rating' => $this->input->post('rating'),
            'grade' => $this->input->post('grade'),
            'stationedorg' => $this->input->post('stationedorg'),
            'stationedunit' => $this->input->post('stationedunit'),
            'bidname' => $this->input->post('bidname'),
            'language' => $this->input->post('language'),
            'timezone' => $this->input->post('timezone')
        );
        if ($this->input->post('entity') != NULL && $this->input->post('entity') != '') {
            $data['organization'] = $this->input->post('entity');
        }
        if ($this->input->post('position') != NULL && $this->input->post('position') != '') {
            $data['position'] = $this->input->post('position');
        }
        if ($this->input->post('datehired') != NULL && $this->input->post('datehired') != '') {
            $data['datehired'] = $this->input->post('datehired');
        }
        if ($this->config->item('ldap_basedn_db') !== FALSE) {
            $data['ldap_path'] = $this->input->post('ldap_path');
        }

        $this->db->where('id', $this->input->post('id'));
        $result = $this->db->update('users', $data);
        return $result;
    }

    /**
     * Update a given user in the database. Update data are coming from an HTML form
     * @return int number of affected rows
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function resetPassword($id, $CipheredNewPassword) {
        //Decipher the password value (RSA encoded -> base64 -> decode -> decrypt)
        $password = '';
        if (function_exists('openssl_pkey_get_private')) {
            $privateKey = openssl_pkey_get_private(file_get_contents('./assets/keys/private.pem', TRUE));
            openssl_private_decrypt(base64_decode($this->input->post('CipheredValue')), $password, $privateKey);
        } else {
            require_once FCPATH . "vendor/autoload.php";
            $rsa = new phpseclib\Crypt\RSA();
            $private_key = file_get_contents('./assets/keys/private.pem', TRUE);
            $rsa->setEncryptionMode(phpseclib\Crypt\RSA::ENCRYPTION_PKCS1);
            $rsa->loadKey($private_key, phpseclib\Crypt\RSA::PRIVATE_FORMAT_PKCS1);
            $password = $rsa->decrypt(base64_decode($CipheredNewPassword));
        }
        //Hash the clear password using bcrypt (8 iterations)
        $salt = '$2a$08$' . substr(strtr(base64_encode($this->getRandomBytes(16)), '+', '.'), 0, 22) . '$';
        $hash = crypt($password, $salt);
        $data = array(
            'password' => $hash
        );
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }
    
    /**
     * Reset a password. Generate a new password and store its hash into db.
     * @param int $id User identifier
     * @return string clear password
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function resetClearPassword($id) {
        //generate a random password of length 10
        $password = $this->randomPassword(10);
        //Hash the clear password using bcrypt (8 iterations)
        $salt = '$2a$08$' . substr(strtr(base64_encode($this->getRandomBytes(16)), '+', '.'), 0, 22) . '$';
        $hash = crypt($password, $salt);
        //Store the new password into db
        $data = array(
            'password' => $hash
        );
        $this->db->where('id', $id);
        $this->db->update('users', $data);
        return $password;
    }
    
    /**
     * Generate a random password
     * @param int $length length of the generated password
     * @return string generated password
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function randomPassword($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr( str_shuffle( $chars ), 0, $length );
        return $password;
    }
    
    /**
     * Load the profile of a user from the database to the session variables
     * @param array $row database record of a user
     */
    private function loadProfile($row) {
        if (((int) $row->role & 1)) {
            $is_admin = TRUE;
        } else {
            $is_admin = FALSE;
        }

        if ((int) $row->role == 32) {
            $is_boss = TRUE;
        } else {
            $is_boss = FALSE;
        }

        /*
          00000001 1  Admin
          00000100 8  HR Officier / Local HR Manager
          00001000 16 HR Manager
          = 00001101 25 Can access to HR functions
         */
        if (((int) $row->role & 25) && ((int) $row->role != 1)) {
            $is_hr = TRUE;
        } else {
            $is_hr = FALSE;
        }

        //Determine if the connected user is a manager or if he has any delegation
        $isManager = FALSE;
        if (count($this->getCollaboratorsOfManager($row->id)) > 0) {
            $isManager = TRUE;
        } else {
            $this->load->model('delegations_model');
            if ($this->delegations_model->hasDelegation($row->id))
                $isManager = TRUE;
        }

        $newdata = array(
            'login' => $row->login,
            'id' => $row->id,
            'firstname' => $row->firstname,
            'lastname' => $row->lastname,
            'is_manager' => $isManager,
            'is_admin' => $is_admin,
            'is_hr' => $is_hr,
            'manager' => $row->manager,
            'logged_in' => TRUE,
            'is_boss' => $is_boss
        );
        $this->session->set_userdata($newdata);
    }

    /**
     * Check the provided credentials and load user's profile if they are correct
     * @param string $login user login
     * @param string $password password
     * @return bool TRUE if the user is succesfully authenticated, FALSE otherwise
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function checkCredentials($login, $password) {
        $this->db->from('users');
        $this->db->where('login', $login);
        $this->db->where('active = TRUE');
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            //No match found
            return FALSE;
        } else {
            $row = $query->row();
            $hash = crypt($password, $row->password);
            if ($hash == $row->password) {
                // Password does match stored password.
                $this->loadProfile($row);
                return TRUE;
            } else {
                // Password does not match stored password.
                return FALSE;
            }
        }
    }
    
    /**
     * Check the provided credentials and load user's profile if they are correct
     * It is the LDAP binding operation that checks if Password is correct.
     * @param string $login user login
     * @return bool TRUE if user was found into the database, FALSE otherwise
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function checkCredentialsLDAP($login) {
        $this->db->from('users');
        $this->db->where('login', $login);
        $this->db->where('active = TRUE');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $this->loadProfile($row);
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * Check the provided credentials and load user's profile if they are correct
     * Mostly used for alternative signin mechanisms such as SSO
     * @param string $email E-mail address of the user
     * @param string $password Optional password
     * @return bool TRUE if user was found into the database, FALSE otherwise
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function checkCredentialsEmail($email, $password = NULL) {
        $this->db->from('users');
        $this->db->where('email', $email);
        $this->db->where('active = TRUE');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            if (!is_null($password)) {
                $hash = crypt($password, $row->password);
                if ($hash == $row->password) {
                    $this->loadProfile($row);
                }
            } else {
                $this->loadProfile($row);
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
     /**
     * Get the LDAP Authentication path of a user
     * @param string $login user login
     * @return string LDAP Authentication path, empty string otherwise
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function getBaseDN($login) {
        $this->db->select('ldap_path');
        $this->db->from('users');
        $this->db->where('login', $login);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->ldap_path;
        } else {
            return "";
        }
    }
    
    /**
     * Get the list of employees or one employee
     * @param int $id optional id of the entity, all entities if 0
     * @param bool $children TRUE : include sub entities, FALSE otherwise
     * @param string $filterActive "all"; "active" (only), or "inactive" (only)
     * @param string $criterion1 "lesser" or "greater" (optional)
     * @param string $date1 Date Hired (optional)
     * @param string $criterion2 "lesser" or "greater" (optional)
     * @param string $date2 Date Hired (optional)
     * @return array record of users
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
  
   public function employeesOfEntity($id = 0, $children = TRUE, $filterActive = "all",
            $criterion1 = NULL, $date1 = NULL, $criterion2 = NULL, $date2 = NULL) {
        $this->load->model('organization_model');
        $grp_id = $this->getGroup($this->user_id);
        $grpTree = $this->organization_model->getAllChildren($grp_id);
        $grpTreeArr = explode(",",$grpTree[0]['id']);
        $this->db->select('users.id as id,'
                . ' users.firstname as firstname,'
                . ' users.lastname as lastname,'
                . ' users.email as email,'
                . ' users.identifier as identifier,'
                . ' users.datehired as datehired,'
                . ' positions.name as position,'
                . ' organization.name as entity,'
                . ' contracts.name as contract,'
                . ' CONCAT_WS(\' \',managers.firstname,  managers.lastname) as manager_name', FALSE);
        $this->db->from('users');
        $this->db->join('contracts', 'contracts.id = users.contract', 'left outer');
        $this->db->join('positions', 'positions.id = users.position', 'left outer');
        $this->db->join('users as managers', 'managers.id = users.manager', 'left outer');
        $this->db->join('organization', 'organization.id = users.organization', 'left outer');
        if ($children == TRUE) {
            if($this->user_id == 1) {
                    $list = $this->organization_model->getAllChildren($id);
                 } else {
                if (!in_array($id,$grpTreeArr)) {
                    $list = $this->organization_model->getAllChildren($grp_id);
                }else {
                    $list = $this->organization_model->getAllChildren($id);
                }
            }
            $ids = array();
            if (count($list) > 0) {
                if ($list[0]['id'] != '') {
                    if($id == $grp_id || $this->user_id ==1) {
                        $ids = explode(",", $list[0]['id']);
                        array_push($ids, $id);
                        $this->db->where_in('organization.id', $ids);}
                    else {
                        $this->db->where('organization.id','9999');     
                    }
                } else {
                     if($id == $grp_id || $this->user_id ==1) { 
                        $this->db->where('organization.id', $id);
                     }else {
                        if (!in_array($id,$grpTreeArr) && $id != $grp_id) {
                            $this->db->where('organization.id','9999'); 

                         }else {
                            $this->db->where('organization.id', $id); 
                         }
                     }
                }
            }
        } else {
            if (!in_array($id,$grpTreeArr) && $id != $grp_id) {
                $this->db->where('organization.id','9999'); 

            }else {
                $this->db->where('organization.id', $id); 
            }
        }
        
        //Triple value for active filter ("all" = no where criteria)
        if ($filterActive == "active") {
            $this->db->where('users.active', TRUE);
        }
        if ($filterActive == "inactive") {
            $this->db->where('users.active', FALSE);
        }
        
        if (!is_null($criterion1) && !is_null($date1) && $date1!="empty" && $date1!="undefined") {
            $criterion1 = ($criterion1 == "greater"?">":"<");
            $this->db->where("users.datehired " . $criterion1 . " STR_TO_DATE('" . $date1 . "', '%Y-%m-%d')");
        }
        if (!is_null($criterion2) && !is_null($date2) && $date2!="empty" && $date2!="undefined") {
            $criterion2 = ($criterion2 == "greater"?">":"<");
            $this->db->where("users.datehired " . $criterion2 . " STR_TO_DATE('" . $date2 . "', '%Y-%m-%d')");
        }
        
        return $this->db->get()->result();
    } 
    
    /**
     * Update all employees when a contract is deleted (set the field to NULL)
     * @param int $id Contract ID
     * @return int number of affected rows
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function updateUsersCascadeContract($id) {
        $this->db->set('contract', NULL);
        $this->db->where('contract', $id);
        $result = $this->db->update('users');
        return $result;
    }
    
    /**
     * Set a user as active (TRUE) or inactive (FALSE)
     * @param int $id User identifier
     * @param bool $active active (TRUE) or inactive (FALSE)
     * @return int number of affected rows
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function setActive($id, $active) {
        $this->db->set('active', $active);
        $this->db->where('id', $id);
        return $this->db->update('users');
    }
    
    /**
     * Check if a user is active (TRUE) or inactive (FALSE)
     * @param string $login login of a user
     * @return bool active (TRUE) or inactive (FALSE)
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function isActive($login) {
        $this->db->from('users');
        $this->db->where('login', $login);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->active;
        } else {
            return FALSE;
        }
    }
    
    /**
     * Check if a user is active (TRUE) or inactive (FALSE)
     * @param string $email e-mail of a user
     * @return bool active (TRUE) or inactive (FALSE)
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function isActiveByEmail($login) {
        $this->db->from('users');
        $this->db->where('email', $email);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->active;
        } else {
            return FALSE;
        }
    }
    
    /**
     * Try to return the user information from the login field
     * @param string $login Login
     * @return User data row or null if no user was found
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function getUserByLogin($login) {
        $this->db->from('users');
        $this->db->where('login', $login);
        $query = $this->db->get();
        if ($query->num_rows() == 0) {
            //No match found
            return null;
        } else {
            return $query->row();
        }
    }
    
    /**
     * Generate some random bytes by using openssl, dev/urandom or random
     * @param int $count length of the random string
     * @return string a string of pseudo-random bytes (must be encoded)
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    protected function getRandomBytes($length) {
        if(function_exists('openssl_random_pseudo_bytes')) {
          $rnd = openssl_random_pseudo_bytes($length, $strong);
          if ($strong === TRUE)
            return $rnd;
        }
        $sha =''; $rnd ='';
        if (file_exists('/dev/urandom')) {
          $fp = fopen('/dev/urandom', 'rb');
          if ($fp) {
              if (function_exists('stream_set_read_buffer')) {
                  stream_set_read_buffer($fp, 0);
              }
              $sha = fread($fp, $length);
              fclose($fp);
          }
        }
        for ($i=0; $i<$length; $i++) {
          $sha  = hash('sha256',$sha.mt_rand());
          $char = mt_rand(0,62);
          $rnd .= chr(hexdec($sha[$char].$sha[$char+1]));
        }
        return $rnd;
    }
    
    /**
     * Update the manager of a list of employees
     * @param int $managerId DB Identifier of the manager
     * @param array $usersList List of DB ID of the affected employees
     * @return int number of affected rows
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function updateManagerForUserList($managerId, $usersList) {
        $data = array(
            'manager' => $managerId
        );
        $this->db->where_in('id', $usersList);
        $result = $this->db->update('users', $data);
        return $result;
    }
    
    /**
     * Update the entity of a list of employees
     * @param int $entityId DB Identifier of the entity
     * @param array $usersList List of DB ID of the affected employees
     * @return int number of affected rows
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function updateEntityForUserList($entityId, $usersList) {
        $data = array(
            'organization' => $entityId
        );
        $this->db->where_in('id', $usersList);
        $result = $this->db->update('users', $data);
        return $result;
    }
    
    /**
     * Update the contract of a list of employees
     * @param int $contractId DB Identifier of the contract
     * @param array $usersList List of DB ID of the affected employees
     * @return int number of affected rows
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function updateContractForUserList($contractId, $usersList) {
        $data = array(
            'contract' => $contractId
        );
        $this->db->where_in('id', $usersList);
        $result = $this->db->update('users', $data);
        return $result;
    }
}
