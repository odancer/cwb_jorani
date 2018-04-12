<?php
/**
 * This controller serves the user management pages and tools.
 * @copyright  Copyright (c) 2014-2017 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.4.2
 */

if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * This controller serves the user management pages and tools.
 * The difference with HR Controller is that operations are technical (CRUD, etc.).
 */
class Users extends CI_Controller {

    /**
     * Default constructor
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function __construct() {
        parent::__construct();
        setUserContext($this);
        $this->load->model('users_model');
        $this->load->model('organization_model');
        $this->load->model('positions_model');
        $this->lang->load('users', $this->language);
    }

    /**
     * Display the list of all users
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function index() {
        $this->auth->checkIfOperationIsAllowed('list_users');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->lang->load('datatable', $this->language);
        $id=$data['user_id'];
        $grp_info =$this->users_model->getGroup($id);
        $data['users'] = $this->users_model->getUsersAndRoles2($grp_info);
        $data['title'] = lang('users_index_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_list_users');
        $data['flash_partial_view'] = $this->load->view('templates/flash', $data, TRUE);
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('users/index', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Set a user as active (TRUE) or inactive (FALSE)
     * @param int $id User identifier
     * @param bool $active active (TRUE) or inactive (FALSE)
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function active($id, $active) {
        $this->auth->checkIfOperationIsAllowed('list_users');
        $this->users_model->setActive($id, $active);
        $this->session->set_flashdata('msg', lang('users_edit_flash_msg_success'));
        redirect('users');
    }
    
    /**
     * Enable a user 
     * @param int $id User identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function enable($id) {
        $this->active($id, TRUE);
    }
    
    /**
     * Disable a user 
     * @param int $id User identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function disable($id) {
        $this->active($id, FALSE);
    }

    /**
     * Display the modal pop-up content of the list of employees
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function employees() {
        $this->auth->checkIfOperationIsAllowed('employees_list');
        $data = getUserContext($this);
        $this->lang->load('datatable', $this->language);
        $userid=$data['user_id'];
        $grp_info=$this->users_model->getGroup($userid);
        if($grp_info == 0) {
            $data['employees'] = $this->users_model->getAllEmployees();
        }else{
            $data['employees'] = $this->users_model->getAllEmployees2($grp_info);    
        }
        $data['title'] = lang('employees_index_title');
        $this->load->view('users/employees', $data);
    }

    /**
     * Display the modal pop-up content of the list of employees.
     * The differences with the function employees are that multi select is
     * allowed and the last column contains the name of the entity the employee
     * belongs to.
     * @see employees
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function employeesMultiSelect() {
        $this->auth->checkIfOperationIsAllowed('employees_list');
        $data = getUserContext($this);
        $this->lang->load('datatable', $this->language);
        $data['employees'] = $this->users_model->getAllEmployeesAndTheirEntities();
        $data['title'] = lang('employees_index_title');
        $this->load->view('users/multiselect', $data);
    }
    
    /**
     * Display details of the connected user (contract, line manager, etc.)
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function myProfile() {
        $this->auth->checkIfOperationIsAllowed('view_myprofile');
        $this->load->library('polyglot');
        $data = getUserContext($this);
        $data['user'] = $this->users_model->getUsers($this->user_id);
        if (empty($data['user'])) {
            redirect('notfound');
        }
        $data['title'] = lang('users_myprofile_html_title');
        $this->load->model('positions_model');
        $this->load->model('contracts_model');
        $this->load->model('organization_model');
        $this->load->model('oauthclients_model');
        $data['manager_label'] = $this->users_model->getName($data['user']['manager']);
        $data['contract_id'] = intval($data['user']['contract']);
        $data['contract_label'] = $this->contracts_model->getName($data['user']['contract']);
        $data['position_label'] = $this->positions_model->getName($data['user']['position']);
        $data['organization_label'] = $this->organization_model->getName($data['user']['organization']);
        $data['apps'] = $this->oauthclients_model->listOAuthApps($this->user_id);
        $data['change_date'] =$this->users_model->getUsersHistory($this->user_id,1,1)['change_date'];
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('users/myprofile', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Display a for that allows updating a given user
     * @param int $id User identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function edit($id) {
        $this->auth->checkIfOperationIsAllowed('edit_user');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('polyglot');
        $data['title'] = lang('users_edit_html_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_create_user');
        
        $this->form_validation->set_rules('firstname', lang('users_edit_field_firstname'), 'required|strip_tags');
        $this->form_validation->set_rules('lastname', lang('users_edit_field_lastname'), 'required|strip_tags');
        $this->form_validation->set_rules('login', lang('users_edit_field_login'), 'required|strip_tags');
        $this->form_validation->set_rules('email', lang('users_edit_field_email'), 'required|strip_tags');
        $this->form_validation->set_rules('role[]', lang('users_edit_field_role'), 'required');
        $this->form_validation->set_rules('manager', lang('users_edit_field_manager'), 'required|strip_tags');
        $this->form_validation->set_rules('contract', lang('users_edit_field_contract'), 'strip_tags');
        $this->form_validation->set_rules('entity', lang('users_edit_field_entity'), 'strip_tags');
        $this->form_validation->set_rules('position', lang('users_edit_field_position'), 'strip_tags');
        $this->form_validation->set_rules('datehired', lang('users_edit_field_hired'), 'strip_tags');
        $this->form_validation->set_rules('identifier', lang('users_edit_field_identifier'), 'strip_tags');
        $this->form_validation->set_rules('jobcategory', lang('users_edit_field_job_category'), 'required|strip_tags');
        $this->form_validation->set_rules('salarypoint', lang('users_edit_field_salary_point'), 'required|strip_tags');
        $this->form_validation->set_rules('salary', lang('users_edit_field_salary'), 'required|strip_tags');
        $this->form_validation->set_rules('stationedorg', lang('users_edit_field_stationedorg'), 'required|strip_tags');
        $this->form_validation->set_rules('stationedunit', lang('users_edit_field_stationedunit'), 'required|strip_tags');
        $this->form_validation->set_rules('bidname', lang('users_edit_field_bidname'), 'required|strip_tags');
       // $this->form_validation->set_rules('rating', lang('users_edit_field_rating'), 'required|strip_tags');
       // $this->form_validation->set_rules('grade', lang('users_edit_field_grade'), 'required|strip_tags');
        $this->form_validation->set_rules('language', lang('users_edit_field_language'), 'strip_tags');
        $this->form_validation->set_rules('timezone', lang('users_edit_field_timezone'), 'strip_tags');
        if ($this->config->item('ldap_basedn_db')) $this->form_validation->set_rules('ldap_path', lang('users_edit_field_ldap_path'), 'strip_tags');
        $data['users_item'] = $this->users_model->getUsers($id);
        $data['change_date'] = ($this->users_model->getUsersHistory($id,1,0))['change_date'];

        if (empty($data['users_item'])) {
            redirect('notfound');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->load->model('roles_model');
            $this->load->model('positions_model');
            $this->load->model('organization_model');
            $this->load->model('contracts_model');
            $data['contracts'] = $this->contracts_model->getContracts();
            $data['manager_label'] = $this->users_model->getName($data['users_item']['manager']);
            $data['position_label'] = $this->positions_model->getName($data['users_item']['position']);
            $data['organization_label'] = $this->organization_model->getName($data['users_item']['organization']);
            $data['roles'] = $this->roles_model->getRoles();
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('users/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $this->users_model->updateUsers();
            $rtn = $this->users_model->setUsersHistory($data['users_item']);
            if ($rtn) $this->sendMail($id,$rtn);
            $this->session->set_flashdata('msg', lang('users_edit_flash_msg_success'));
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('users');
            }
        }
    }

    public function view($id) {
        $this->auth->checkIfOperationIsAllowed('edit_user');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('polyglot');
        $data['title'] = lang('users_view_html_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_create_user');
        
        $this->form_validation->set_rules('firstname', lang('users_edit_field_firstname'), 'required|strip_tags');
        $this->form_validation->set_rules('lastname', lang('users_edit_field_lastname'), 'required|strip_tags');
        $this->form_validation->set_rules('login', lang('users_edit_field_login'), 'required|strip_tags');
        $this->form_validation->set_rules('email', lang('users_edit_field_email'), 'required|strip_tags');
        $this->form_validation->set_rules('role[]', lang('users_edit_field_role'), 'required');
        $this->form_validation->set_rules('manager', lang('users_edit_field_manager'), 'required|strip_tags');
        $this->form_validation->set_rules('contract', lang('users_edit_field_contract'), 'strip_tags');
        $this->form_validation->set_rules('entity', lang('users_edit_field_entity'), 'strip_tags');
        $this->form_validation->set_rules('position', lang('users_edit_field_position'), 'strip_tags');
        $this->form_validation->set_rules('datehired', lang('users_edit_field_hired'), 'strip_tags');
        $this->form_validation->set_rules('identifier', lang('users_edit_field_identifier'), 'strip_tags');
        $this->form_validation->set_rules('language', lang('users_edit_field_language'), 'strip_tags');
        $this->form_validation->set_rules('timezone', lang('users_edit_field_timezone'), 'strip_tags');
        if ($this->config->item('ldap_basedn_db')) $this->form_validation->set_rules('ldap_path', lang('users_edit_field_ldap_path'), 'strip_tags');
        $data['users_item'] = $this->users_model->getUsers($id);
        $data['change_date'] = ($this->users_model->getUsersHistory($id,1,0))['change_date'];
        $data['users_history'] = $this->users_model->getUsersHistory($id,0,0);
        $data['raise_date'] = ($this->users_model->getUsersHistory($id,1,1))['change_date'];

        for( $i=0; $i<count($data['users_history']); $i++) {
            $this->load->model('positions_model');
            $position_name=$this->positions_model->getName($data['users_history'][$i]['position']);
            $data['users_history'][$i]=array_merge($data['users_history'][$i],array('position_name'=>$position_name));

            switch($data['users_history'][$i]['change_type']) {
                 case 0:
                  $change_type_name="新建資料";
                  break;
                case 1:
                  $change_type_name="變更職稱/職等";
                  break;
                case 2:                
                  $change_type_name="變更工作類別";
                  break;
                case 3:                
                  $change_type_name="變更等第";
                  break;
                case 4:                
                  $change_type_name="變更分數";
                  break;
                case 5:                
                  $change_type_name="變更薪俸";
                  break;
                case 6:                
                  $change_type_name="變更薪點";
                  break;

            }
            $data['users_history'][$i]=array_merge($data['users_history'][$i],array('change_type_name'=>$change_type_name));
        }



        if (empty($data['users_item'])) {
            redirect('notfound');
        }

            $this->load->model('roles_model');
            $this->load->model('positions_model');
            $this->load->model('organization_model');
            $this->load->model('contracts_model');
            $data['contract'] = ($this->contracts_model->getContracts($data['users_item']['contract']))['name'];;
            $data['manager_label'] = $this->users_model->getName($data['users_item']['manager']);
            $data['position_label'] = $this->positions_model->getName($data['users_item']['position']);
            $data['organization_label'] = $this->organization_model->getName($data['users_item']['organization']);
            $data['role'] = ($this->roles_model->getRoles($data['users_item']['role']))['name'];
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('users/view', $data);
            $this->load->view('templates/footer');
    }

      public function exporthistory($id) {
        $this->auth->checkIfOperationIsAllowed('edit_user');
        $data = getUserContext($this);
        $data['users_item'] = $this->users_model->getUsers($id);
        $data['change_date'] = ($this->users_model->getUsersHistory($id,1,0))['change_date'];
        $data['users_history'] = $this->users_model->getUsersHistory($id,0,0);
        $data['raise_date'] = ($this->users_model->getUsersHistory($id,1,1))['change_date'];
        for( $i=0; $i<count($data['users_history']); $i++) {
            $this->load->model('positions_model');
            $position_name=$this->positions_model->getName($data['users_history'][$i]['position']);
            $data['users_history'][$i]=array_merge($data['users_history'][$i],array('position_name'=>$position_name));

            switch($data['users_history'][$i]['change_type']) {
                 case 0:
                  $change_type_name="新建資料";
                  break;
                case 1:
                  $change_type_name="變更職稱/職等";
                  break;
                case 2:                
                  $change_type_name="變更工作類別";
                  break;
                case 3:                
                  $change_type_name="變更等第";
                  break;
                case 4:                
                  $change_type_name="變更分數";
                  break;
                case 5:                
                  $change_type_name="變更薪俸";
                  break;
                case 6:                
                  $change_type_name="變更薪點";
                  break;

            }
            $data['users_history'][$i]=array_merge($data['users_history'][$i],array('change_type_name'=>$change_type_name));
        }


        if (empty($data['users_item'])) {
            redirect('notfound');
        }
            $this->load->library('excel');
            $this->load->view('users/exporthistory', $data);
    }

    /**
     * Delete a user. Log it as an error.
     * @param int $id User identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function delete($id) { 
        $this->auth->checkIfOperationIsAllowed('delete_user');
        //Test if user exists
        $data['users_item'] = $this->users_model->getUsers($id);
        if (empty($data['users_item'])) {
            redirect('notfound');
        } else {
            $this->users_model->deleteUser($id);
        }
        log_message('error', 'User #' . $id . ' has been deleted by user #' . $this->session->userdata('id'));
        $this->session->set_flashdata('msg', lang('users_delete_flash_msg_success'));
        redirect('users');
    }

    /**
     * Reset the password of a user
     * Can be accessed by the user itself or by admin
     * @param int $id User identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function reset($id) {
        $this->auth->checkIfOperationIsAllowed('change_password', $id);

        //Test if user exists
        $data['users_item'] = $this->users_model->getUsers($id);
        if (empty($data['users_item'])) {
            log_message('debug', '{controllers/users/reset} user not found');
            redirect('notfound');
        } else {
            $data = getUserContext($this);
            $data['target_user_id'] = $id;
            $this->load->helper('form');
            $this->load->library('form_validation');
            $this->form_validation->set_rules('CipheredValue', 'Password', 'required');
            if ($this->form_validation->run() === FALSE) {
                $data['public_key'] = file_get_contents('./assets/keys/public.pem', TRUE);
                $this->load->view('users/reset', $data);
            } else {
                $this->users_model->resetPassword($id, $this->input->post('CipheredValue'));
                
                //Send an e-mail to the user so as to inform that its password has been changed
                $user = $this->users_model->getUsers($id);
                $this->load->library('email');
                $this->load->library('polyglot');
                $usr_lang = $this->polyglot->code2language($user['language']);
                //We need to instance an different object as the languages of connected user may differ from the UI lang
                $lang_mail = new CI_Lang();
                $lang_mail->load('email', $usr_lang);

                $this->load->library('parser');
                $data = array(
                    'Title' => $lang_mail->line('email_password_reset_title'),
                    'Firstname' => $user['firstname'],
                    'Lastname' => $user['lastname']
                );
                $message = $this->parser->parse('emails/' . $user['language'] . '/password_reset', $data, TRUE);
                $this->email->set_encoding('quoted-printable');
                
                if ($this->config->item('from_mail') != FALSE && $this->config->item('from_name') != FALSE ) {
                    $this->email->from($this->config->item('from_mail'), $this->config->item('from_name'));
                } else {
                    $this->email->from('do.not@reply.me', 'LMS');
                }
                $this->email->to($user['email']);
                if ($this->config->item('subject_prefix') != FALSE) {
                    $subject = $this->config->item('subject_prefix');
                } else {
                   $subject = '[Jorani] ';
                }
                $this->email->subject($subject . $lang_mail->line('email_password_reset_subject'));
                $this->email->message($message);
                $this->email->send();
                
                //Inform back the user by flash message
                $this->session->set_flashdata('msg', lang('users_reset_flash_msg_success'));
                if ($this->is_hr) {
                    redirect('users');
                }
                else {
                    redirect('home');
                }
            }
        }
    }

    /**
     * Display the form / action Create a new user
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function create() {
        $this->auth->checkIfOperationIsAllowed('create_user');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('polyglot');
        $data['title'] = lang('users_create_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_create_user');

        $this->load->model('roles_model');
        $data['roles'] = $this->roles_model->getRoles();
        $this->load->model('contracts_model');
        $data['contracts'] = $this->contracts_model->getContracts();
        $data['public_key'] = file_get_contents('./assets/keys/public.pem', TRUE);

        $this->form_validation->set_rules('firstname', lang('users_create_field_firstname'), 'required|strip_tags');
        $this->form_validation->set_rules('lastname', lang('users_create_field_lastname'), 'required|strip_tags');
        $this->form_validation->set_rules('login', lang('users_create_field_login'), 'required|callback_checkLogin|strip_tags');
        $this->form_validation->set_rules('email', lang('users_create_field_email'), 'required|strip_tags');
        if (!$this->config->item('ldap_enabled')) $this->form_validation->set_rules('CipheredValue', lang('users_create_field_password'), 'required');
        $this->form_validation->set_rules('role[]', lang('users_create_field_role'), 'required');
        $this->form_validation->set_rules('manager', lang('users_create_field_manager'), 'required|strip_tags');
        $this->form_validation->set_rules('contract', lang('users_create_field_contract'), 'strip_tags');
        $this->form_validation->set_rules('position', lang('users_create_field_position'), 'strip_tags');
        $this->form_validation->set_rules('entity', lang('users_create_field_entity'), 'strip_tags');
        $this->form_validation->set_rules('datehired', lang('users_create_field_hired'), 'strip_tags');
        $this->form_validation->set_rules('identifier', lang('users_create_field_identifier'), 'strip_tags');
        $this->form_validation->set_rules('language', lang('users_create_field_language'), 'strip_tags');
        $this->form_validation->set_rules('timezone', lang('users_create_field_timezone'), 'strip_tags');
        $this->form_validation->set_rules('jobcategory', lang('users_create_field_job_category'), 'required|strip_tags');
        $this->form_validation->set_rules('salarypoint', lang('users_create_field_salary_point'), 'required|strip_tags');
        $this->form_validation->set_rules('salary', lang('users_create_field_salary'), 'required|strip_tags');
        $this->form_validation->set_rules('stationedorg', lang('users_create_field_stationedorg'), 'required|strip_tags');
        $this->form_validation->set_rules('stationedunit', lang('users_create_field_stationedorg'), 'required|strip_tags');
        $this->form_validation->set_rules('bidname', lang('users_create_field_bidname'), 'required|strip_tags');

        if ($this->config->item('ldap_basedn_db')) $this->form_validation->set_rules('ldap_path', lang('users_create_field_ldap_path'), 'strip_tags');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('users/create', $data);
            $this->load->view('templates/footer');
        } else {
            $password = $this->users_model->setUsers();
            $this->users_model->createUsersHistory();
            
            //Send an e-mail to the user so as to inform that its account has been created
            $this->load->library('email');
            $usr_lang = $this->polyglot->code2language($this->input->post('language'));
            //We need to instance an different object as the languages of connected user may differ from the UI lang
            $lang_mail = new CI_Lang();
            $lang_mail->load('email', $usr_lang);
            
            $this->load->library('parser');
            $data = array(
                'Title' => $lang_mail->line('email_user_create_title'),
                'BaseURL' => base_url(),
                'Firstname' => $this->input->post('firstname'),
                'Lastname' => $this->input->post('lastname'),
                'Login' => $this->input->post('login'),
                'Password' => $password
            );
            $message = $this->parser->parse('emails/' . $this->input->post('language') . '/new_user', $data, TRUE);
            $this->email->set_encoding('quoted-printable');

            if ($this->config->item('from_mail') != FALSE && $this->config->item('from_name') != FALSE ) {
                $this->email->from($this->config->item('from_mail'), $this->config->item('from_name'));
            } else {
               $this->email->from('do.not@reply.me', 'LMS');
            }
            $this->email->to($this->input->post('email'));
            if ($this->config->item('subject_prefix') != FALSE) {
                $subject = $this->config->item('subject_prefix');
            } else {
               $subject = '[Jorani] ';
            }
            $this->email->subject($subject . $lang_mail->line('email_user_create_subject'));
            $this->email->message($message);
            $this->email->send();
            
            $this->session->set_flashdata('msg', lang('users_create_flash_msg_success'));
            redirect('users');
        }
    }
   
    /**
     * Form validation callback : prevent from login duplication
     * @param string $login Login
     * @return boolean TRUE if the field is valid, FALSE otherwise
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function checkLogin($login) {
        if (!$this->users_model->isLoginAvailable($login)) {
            $this->form_validation->set_message('checkLogin', lang('users_create_checkLogin'));
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    /**
     * Ajax endpoint : check login duplication
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function checkLoginByAjax() {
        $this->output->set_content_type('text/plain');
        if ($this->users_model->isLoginAvailable($this->input->post('login'))) {
            $this->output->set_output('true');
        } else {
            $this->output->set_output('false');
        }
    }

    /**
     * Action: export the list of all users into an Excel file
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function export() {
        $this->auth->checkIfOperationIsAllowed('export_user');
        $data['user_info'] = $this->users_model->getUsers($this->user_id); 
        //error_log( print_r($data['user']['organization'], TRUE) );    
        $this->load->library('excel');
        $this->load->view('users/export', $data);
    }

    public function exportrating() {
        $this->auth->checkIfOperationIsAllowed('export_user');
        $data = getUserContext($this);
        $data['user_info'] = $this->users_model->getUsers($this->user_id);
        //error_log( print_r($data['user']['organization'], TRUE) ); 
        $this->load->library('excel');
        $this->load->view('users/exportrating', $data);
    }


     private function sendMail($id,$change_type) {
        $user = $this->users_model->getUsers($id);
        $email = $user['email'];
        $connectUrl = base_url() . 'jorani';
        $change_type_name=array();
        for($i=0;$i<count($change_type);$i++) {
         switch($change_type[$i]) {
                case 1:
                  $position = ($this->positions_model->getPositions($user['position']))['name'];
                  array_push($change_type_name,"即日起變更職稱/職等為:".$position);
                  break;
                case 2:
                  array_push($change_type_name,"即日起變更工作類別為".$user['jobcategory']);                
                  break;                                 
                case 5:  
                  array_push($change_type_name,"即日起變更薪俸為".$user['salary']);                
                  break;                              
          }
        }
            //Send an e-mail to the manager
            $this->load->library('email');
            $this->load->library('polyglot');
            $usr_lang = $this->polyglot->code2language($user['language']);
            //We need to instance an different object as the languages of connected user may differ from the UI lang
            $lang_mail = new CI_Lang();
            $lang_mail->load('email', $usr_lang);
            $lang_mail->load('global', $usr_lang);
            for ($i=0; $i<count($change_type_name); $i++) {
              $changeStr.=("'$change_type_name[$i]'<br>"); //Now it is string...
            }
            $date = new DateTime($this->input->post('date'));
            $startdate = $date->format($lang_mail->line('global_date_format'));

            $this->load->library('parser');
            $data = array(
                'Title' => $lang_mail->line('email_user_info_title'),
                'Firstname' => $user['firstname'],
                'Lastname' => $user['lastname'],
                'Date' => $startdate,
                'Url' => $connectUrl,
                'Changeinfo' => $changeStr,
            );
            $message = $this->parser->parse('emails/' . $user['language'] . '/user_notice', $data, TRUE);
            $subject = $lang_mail->line('email_user_info_subject') . ' ' .
                                $user['firstname'] . ' ' .$user['lastname'];
            sendMailByWrapper($this, $subject, $message, $user['email'], $delegates);
        }
    }
