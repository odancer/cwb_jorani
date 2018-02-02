<?php
/**
 * This controller allows a manager to list and manage leave requests submitted to him
 * @copyright  Copyright (c) 2014-2017 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.1.0
 */

if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * This class allows a manager to list and manage leave requests submitted to him.
 * Since 0.3.0, we expose the list of collaborators and allow a manager to access to some reports:
 *  - presence report of an employee.
 *  - counters of an employee (leave balance).
 *  - Yearly calendar of an employee.
 * But those reports are not served by this controller (either HR or Calendar controller).
 */
class Requests extends CI_Controller {

    /**
     * Default constructor
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function __construct() {
        parent::__construct();
        setUserContext($this);
        $this->load->model('leaves_model');
        $this->lang->load('requests', $this->language);
        $this->lang->load('global', $this->language);
    }

    /**
     * Display the list of all requests submitted to you
     * Status is submitted or accepted/rejected depending on the filter parameter.
     * @param string $name Filter the list of submitted leave requests (all or requested)
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function index($filter = 'requested') {
        $this->auth->checkIfOperationIsAllowed('list_requests');
        $data = getUserContext($this);
        $this->load->model('types_model');
        $this->lang->load('datatable', $this->language);
        $this->load->helper('form');
        $data['filter'] = $filter;
        $data['title'] = lang('requests_index_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_leave_validation');
        ($filter == 'all')? $showAll = TRUE : $showAll = FALSE;
        //$data['requests'] = $this->leaves_model->getLeavesRequestedToManager($this->user_id, $showAll);
        if ($this->config->item('enable_history') == TRUE){
          $data['requests'] = $this->leaves_model->getLeavesRequestedToManagerWithHistory($this->session->userdata('id'), $showAll);
        }else{
          $data['requests'] = $this->leaves_model->getLeavesRequestedToManager($this->session->userdata('id'), $showAll);
        }
        $data['types'] = $this->types_model->getTypes();
        $data['showAll'] = $showAll;
        $data['flash_partial_view'] = $this->load->view('templates/flash', $data, TRUE);
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('requests/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Accept a leave request
     * @param int $id leave request identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function accept($id) {
        $this->auth->checkIfOperationIsAllowed('accept_requests');
        $this->load->model('users_model');
        $this->load->model('delegations_model');
        $leave = $this->leaves_model->getLeaves($id);
        if (empty($leave)) {
            redirect('notfound');
        }
        $employee = $this->users_model->getUsers($leave['employee']);
        $is_delegate = $this->delegations_model->isDelegateOfManager($this->user_id, $employee['manager']);
        $agent_info = $this->leaves_model->getLevesAgentInfo($id);
        $agent_id = (int)$agent_info[0]->agent;
        $status_id = (int)$agent_info[0]->status;
        //if (($this->user_id == $employee['manager']) || ($this->is_hr)  || ($is_delegate) || ($this->user_id == $agent_id) || ($this->is_boss)) {
         if ($this->user_id != 0) {
            switch ($status_id) {
            case LMS_REQUESTED_AGENT;
                $this->leaves_model->switchStatus($id, LMS_REQUESTED);
                $this->sendMailOnLeaveRequestCreation($id);
                break;
            case LMS_REQUESTED;
                $this->leaves_model->switchStatus($id, LMS_REQUESTED_BOSS); 
                $this->sendMailOnLeaveRequestCreation($id);
                break;
            case LMS_REQUESTED_BOSS;
                $this->leaves_model->switchStatus($id, LMS_ACCEPTED); 
                break;
            case LMS_CANCELLATION_AGENT;
                $this->leaves_model->switchStatus($id, LMS_CANCELLATION_MANAGER); 
                $this->sendMailOnLeaveRequestCancellation($id);
                break;
             case LMS_CANCELLATION_MANAGER;
                $this->leaves_model->switchStatus($id, LMS_CANCELLATION_BOSS); 
                $this->sendMailOnLeaveRequestCancellation($id);
                break;
            case LMS_CANCELLATION_BOSS;
                $this->leaves_model->switchStatus($id, LMS_CANCELLATION); 
                break;
            }
            #$this->leaves_model->switchStatus($id, LMS_ACCEPTED);
            #$this->sendMail($id, LMS_ACCEPTED);
            if($status_id == LMS_REQUESTED_AGENT || LMS_REQUESTED || LMS_REQUESTED_BOSS) $this->sendMail($id, LMS_REQUESTED_ACCEPTED);
             if($status_id == LMS_CANCELLATION_AGENT || LMS_CANCELLATION_MANAGER|| LMS_CANCELLATION_BOSS) $this->sendMail($id, LMS_CANCELLATION_REQUESTED);
            $this->session->set_flashdata('msg', lang('requests_accept_flash_msg_success'));
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('requests');
            }
        } else {
            log_message('error', 'User #' . $this->user_id . ' illegally tried to accept leave #' . $id);
            $this->session->set_flashdata('msg', lang('requests_accept_flash_msg_error'));
            redirect('leaves');
        }
    }

    /**
     * Reject a leave request
     * @param int $id leave request identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function reject($id) {
        $this->auth->checkIfOperationIsAllowed('reject_requests');
        $this->load->model('users_model');
        $this->load->model('delegations_model');
        $leave = $this->leaves_model->getLeaves($id);
        if (empty($leave)) {
            redirect('notfound');
        }
        $employee = $this->users_model->getUsers($leave['employee']);
        $is_delegate = $this->delegations_model->isDelegateOfManager($this->user_id, $employee['manager']);
        $agent_info = $this->leaves_model->getLevesAgentInfo($id);
        $agent_id = (int)$agent_info[0]->agent;
        $status_id = (int)$agent_info[0]->status;
        if (($this->user_id == $employee['manager']) || ($this->is_hr)  || ($is_delegate) || ($this->user_id == $agent_id) || ($this->is_boss)) {
            if(isset($_POST['comment'])){
              $this->leaves_model->switchStatusAndComment($id, LMS_REJECTED, $_POST['comment']);
            } else {
              $this->leaves_model->switchStatus($id, LMS_REJECTED);
            }
            $this->sendMail($id, LMS_REQUESTED_REJECTED);
            $this->session->set_flashdata('msg',  lang('requests_reject_flash_msg_success'));
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('requests');
            }
        } else {
            log_message('error', 'User #' . $this->user_id . ' illegally tried to reject leave #' . $id);
            $this->session->set_flashdata('msg', lang('requests_reject_flash_msg_error'));
            redirect('leaves');
        }
    }

    /**
     * Accept the cancellation of a leave request
     * @param int $id leave request identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function acceptCancellation($id) {
        $this->auth->checkIfOperationIsAllowed('accept_requests');
        $this->load->model('users_model');
        $this->load->model('delegations_model');
        $leave = $this->leaves_model->getLeaves($id);
        if (empty($leave)) {
            redirect('notfound');
        }
        $employee = $this->users_model->getUsers($leave['employee']);
        $is_delegate = $this->delegations_model->isDelegateOfManager($this->user_id, $employee['manager']);
        if (($this->user_id == $employee['manager']) || ($this->is_hr)  || ($is_delegate)) {
            $this->leaves_model->switchStatus($id, LMS_CANCELED);
            $this->sendMail($id, LMS_CANCELLATION_CANCELED);
            $this->session->set_flashdata('msg', lang('requests_cancellation_accept_flash_msg_success'));
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('requests');
            }
        } else {
            log_message('error', 'User #' . $this->user_id . ' illegally tried to accept the cancellation of leave #' . $id);
            $this->session->set_flashdata('msg', lang('requests_cancellation_accept_flash_msg_error'));
            redirect('leaves');
        }
    }

    /**
     * Reject the cancellation of a leave request
     * @param int $id leave request identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function rejectCancellation($id) {
        $this->auth->checkIfOperationIsAllowed('reject_requests');
        $this->load->model('users_model');
        $this->load->model('delegations_model');
        $leave = $this->leaves_model->getLeaves($id);
        if (empty($leave)) {
            redirect('notfound');
        }
        $employee = $this->users_model->getUsers($leave['employee']);
        $is_delegate = $this->delegations_model->isDelegateOfManager($this->user_id, $employee['manager']);
        if (($this->user_id == $employee['manager']) || ($this->is_hr)  || ($is_delegate)) {
            //$this->leaves_model->switchStatus($id, LMS_ACCEPTED);
            if(isset($_POST['comment'])){
              $this->leaves_model->switchStatusAndComment($id, LMS_ACCEPTED, $_POST['comment']);
            } else {
              $this->leaves_model->switchStatus($id, LMS_ACCEPTED);
            }
            $this->sendMail($id, LMS_CANCELLATION_REQUESTED);
            $this->session->set_flashdata('msg', lang('requests_cancellation_reject_flash_msg_success'));
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('requests');
            }
        } else {
            log_message('error', 'User #' . $this->user_id . ' illegally tried to accept the cancellation of leave #' . $id);
            $this->session->set_flashdata('msg', lang('requests_cancellation_reject_flash_msg_error'));
            redirect('leaves');
        }
    }

    /**
     * Display the list of all requests submitted to the line manager (Status is submitted)
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function collaborators() {
        $this->auth->checkIfOperationIsAllowed('list_collaborators');
        $data = getUserContext($this);
        $this->lang->load('datatable', $this->language);
        $data['title'] = lang('requests_collaborators_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_collaborators_list');
        $this->load->model('users_model');
        $data['collaborators'] = $this->users_model->getCollaboratorsOfManager($this->user_id);
        $data['flash_partial_view'] = $this->load->view('templates/flash', $data, TRUE);
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('requests/collaborators', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Display the list of delegations
     * @param int $id Identifier of the manager (from HR/Employee) or 0 if self
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function delegations($id = 0) {
        if ($id == 0) $id = $this->user_id;
        //Self modification or by HR
        if (($this->user_id == $id) || ($this->is_hr)) {
            $data = getUserContext($this);
            $this->lang->load('datatable', $this->language);
            $data['title'] = lang('requests_delegations_title');
            $data['help'] = $this->help->create_help_link('global_link_doc_page_delegations');
            $this->load->model('users_model');
            $data['name'] = $this->users_model->getName($id);
            $data['id'] = $id;
            $this->load->model('delegations_model');
            $data['delegations'] = $this->delegations_model->listDelegationsForManager($id);
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('requests/delegations', $data);
            $this->load->view('templates/footer');
        } else {
            log_message('error', 'User #' . $this->user_id . ' illegally tried to access to list_delegations');
            $this->session->set_flashdata('msg', sprintf(lang('global_msg_error_forbidden'), 'list_delegations'));
            redirect('leaves');
        }
    }

    /**
     * Ajax endpoint : Delete a delegation for a manager
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function deleteDelegations() {
        $manager = $this->input->post('manager_id', TRUE);
        $delegation = $this->input->post('delegation_id', TRUE);
        if (($this->user_id != $manager) && ($this->is_hr == FALSE)) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            if (isset($manager) && isset($delegation)) {
                $this->output->set_content_type('text/plain');
                $this->load->model('delegations_model');
                $this->delegations_model->deleteDelegation($delegation);
                $this->output->set_output($delegation);
            } else {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            }
        }
    }

    /**
     * Ajax endpoint : Add a delegation for a manager
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function addDelegations() {
        $manager = $this->input->post('manager_id', TRUE);
        $delegate = $this->input->post('delegate_id', TRUE);
        if (($this->user_id != $manager) && ($this->is_hr === FALSE)) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            if (isset($manager) && isset($delegate)) {
                $this->output->set_content_type('text/plain');
                $this->load->model('delegations_model');
                if (!$this->delegations_model->isDelegateOfManager($delegate, $manager)) {
                    $id = $this->delegations_model->addDelegate($manager, $delegate);
                    $this->output->set_output($id);
                } else {
                    $this->output->set_output('null');
                }
            } else {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            }
        }
    }

    /**
     * Create a leave request in behalf of a collaborator
     * @param int $id Identifier of the employee
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function createleave($id) {
        $this->lang->load('hr', $this->language);
        $this->load->model('users_model');
        $employee = $this->users_model->getUsers($id);
        if (($this->user_id != $employee['manager']) && ($this->is_hr === FALSE)) {
            log_message('error', 'User #' . $this->user_id . ' illegally tried to access to collaborators/leave/create  #' . $id);
            $this->session->set_flashdata('msg', lang('requests_summary_flash_msg_forbidden'));
            redirect('leaves');
        } else {
            $data = getUserContext($this);
            $this->load->helper('form');
            $this->load->library('form_validation');
            $data['title'] = lang('hr_leaves_create_title');
            $data['form_action'] = 'requests/createleave/' . $id;
            $data['source'] = 'requests/collaborators';
            $data['employee'] = $id;

            $this->form_validation->set_rules('startdate', lang('hr_leaves_create_field_start'), 'required|strip_tags');
            $this->form_validation->set_rules('startdatetype', 'Start Date type', 'required|strip_tags');
            $this->form_validation->set_rules('enddate', lang('leaves_create_field_end'), 'required|strip_tags');
            $this->form_validation->set_rules('enddatetype', 'End Date type', 'required|strip_tags');
            $this->form_validation->set_rules('duration', lang('hr_leaves_create_field_duration'), 'required|strip_tags');
            $this->form_validation->set_rules('type', lang('hr_leaves_create_field_type'), 'required|strip_tags');
            $this->form_validation->set_rules('cause', lang('hr_leaves_create_field_cause'), 'strip_tags');
            $this->form_validation->set_rules('status', lang('hr_leaves_create_field_status'), 'required|strip_tags');

            $data['credit'] = 0;
            $default_type = $this->config->item('default_leave_type');
            $default_type = $default_type == FALSE ? 0 : $default_type;
            if ($this->form_validation->run() === FALSE) {
                $this->load->model('contracts_model');
                $leaveTypesDetails = $this->contracts_model->getLeaveTypesDetailsOTypesForUser($id);
                $data['defaultType'] = $leaveTypesDetails->defaultType;
                $data['credit'] = $leaveTypesDetails->credit;
                $data['types'] = $leaveTypesDetails->types;
                $this->load->model('users_model');
                $data['name'] = $this->users_model->getName($id);
                $this->load->view('templates/header', $data);
                $this->load->view('menu/index', $data);
                $this->load->view('hr/createleave');
                $this->load->view('templates/footer');
            } else {
                $this->leaves_model->setLeaves($id);       //We don't use the return value
                $this->session->set_flashdata('msg', lang('hr_leaves_create_flash_msg_success'));
                //No mail is sent, because the manager would set the leave status to accepted
                redirect('requests/collaborators');
            }
        }
    }

    /**
     * Send a leave request email to the employee that requested the leave.
     * @param int $id Leave request identifier
     * @param int $transition Transition in the workflow of leave request
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    private function sendMail($id, $transition)
    {
        $this->load->model('users_model');
        $this->load->model('organization_model');
        $leave = $this->leaves_model->getLeaves($id);
        $employee = $this->users_model->getUsers($leave['employee']);
        $supervisor = $this->organization_model->getSupervisor($employee['organization']);
        $status = $leave['status'];
        //Send an e-mail to the employee
        $this->load->library('email');
        $this->load->library('polyglot');
        $usr_lang = $this->polyglot->code2language($employee['language']);

        //We need to instance an different object as the languages of connected user may differ from the UI lang
        $lang_mail = new CI_Lang();
        $lang_mail->load('email', $usr_lang);
        $lang_mail->load('global', $usr_lang);

        $date = new DateTime($leave['startdate']);
        $startdate = $date->format($lang_mail->line('global_date_format'));
        $date = new DateTime($leave['enddate']);
        $enddate = $date->format($lang_mail->line('global_date_format'));
        switch ($transition) {
            case LMS_REQUESTED_ACCEPTED:
                if ($status == 2) $subject = $lang_mail->line('email_leave_request_accept_subject_agent');
                if ($status == 8) $subject = $lang_mail->line('email_leave_request_accept_subject_manager');
                if ($status == 3) $subject = $lang_mail->line('email_leave_request_accept_subject_boss');
                $title = $lang_mail->line('email_leave_request_validation_title');
                break;
            case LMS_REQUESTED_REJECTED:
                $title = $lang_mail->line('email_leave_request_validation_title');
                $subject = $lang_mail->line('email_leave_request_reject_subject');
                break;
            case LMS_CANCELLATION_REQUESTED:
                if ($status == 10) $subject = $lang_mail->line('email_leave_cancel_accept_subject_agent');
                if ($status == 11) $subject = $lang_mail->line('email_leave_cancel_accept_subject_manager');
                if ($status == 5)  $subject = $lang_mail->line('email_leave_cancel_accept_subject_boss');
                $title = $lang_mail->line('email_leave_request_cancellation_title');
                break;
            case LMS_CANCELLATION_CANCELED:
                $title = $lang_mail->line('email_leave_request_cancellation_title');
                $subject = $lang_mail->line('email_leave_cancel_accept_subject');
                break;
        }
        $comments=$leave['comments'];
        $comment = '';
        if(!empty($comments)){
          $comments=json_decode($comments);
          foreach ($comments->comments as $comments_item) {
            if($comments_item->type =="comment"){
              $comment = $comments_item->value;
            }
          }
        }

        $data = array(
            'Title' => $title,
            'Firstname' => $employee['firstname'],
            'Lastname' => $employee['lastname'],
            'StartDate' => $startdate,
            'EndDate' => $enddate,
            'StartDateType' => $lang_mail->line($leave['startdatetype']),
            'EndDateType' => $lang_mail->line($leave['enddatetype']),
            'Cause' => $leave['cause'],
            'Type' => $leave['type_name'],
            'Comments' => $comment
        );
        $this->load->library('parser');
        switch ($transition) {
            case LMS_REQUESTED_ACCEPTED:
                $message = $this->parser->parse('emails/' . $employee['language'] . '/request_accepted', $data, TRUE);
                break;
            case LMS_REQUESTED_REJECTED:
                $message = $this->parser->parse('emails/' . $employee['language'] . '/request_rejected', $data, TRUE);
                break;
            case LMS_CANCELLATION_REQUESTED:
                $message = $this->parser->parse('emails/' . $employee['language'] . '/cancel_rejected', $data, TRUE);
                $supervisor = NULL; //No need to warn the supervisor as nothing changes
                break;
            case LMS_CANCELLATION_CANCELED:
                $message = $this->parser->parse('emails/' . $employee['language'] . '/cancel_accepted', $data, TRUE);
                break;
        }
        sendMailByWrapper($this, $subject, $message, $employee['email'], is_null($supervisor)?NULL:$supervisor->email);
    }

    /**
     * Export the list of all leave requests (sent to the connected user) into an Excel file
     * @param string $filter Filter the list of submitted leave requests (all or requested)
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function export($filter = 'requested') {
        $this->load->library('excel');
        $data['filter'] = $filter;
        $this->load->view('requests/export', $data);
    }

    /**
     * Leave balance report limited to the subordinates of the connected manager
     * Status is submitted or accepted/rejected depending on the filter parameter.
     * @param int $dateTmp (Timestamp) date of report
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function balance($dateTmp = NULL) {
        $this->auth->checkIfOperationIsAllowed('list_requests');
        $data = getUserContext($this);
        $this->lang->load('datatable', $this->language);
        $data['title'] = lang('requests_balance_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_leave_balance_report');

        if ($dateTmp === NULL) {
            $refDate = date("Y-m-d");
            $data['isDefault'] = 1;
        } else {
            $refDate = date("Y-m-d", $dateTmp);
            $data['isDefault'] = 0;
        }
        $data['refDate'] = $refDate;

        $this->load->model('types_model');
        $data['types'] = $this->types_model->getTypes();

        $result = array();
        $this->load->model('users_model');
        $users = $this->users_model->getCollaboratorsOfManager($this->user_id);
        foreach ($users as $user) {
            $result[$user['id']]['identifier'] = $user['identifier'];
            $result[$user['id']]['firstname'] = $user['firstname'];
            $result[$user['id']]['lastname'] = $user['lastname'];
            $date = new DateTime($user['datehired']);
            $result[$user['id']]['datehired'] = $date->format(lang('global_date_format'));
            $result[$user['id']]['position'] = $user['position_name'];
            foreach ($data['types'] as $type) {
                $result[$user['id']][$type['name']] = '';
            }

            $summary = $this->leaves_model->getLeaveBalanceForEmployee($user['id'], TRUE, $refDate);
            if (count($summary) > 0 ) {
                foreach ($summary as $key => $value) {
                    $result[$user['id']][$key] = round($value[1] - $value[0], 3, PHP_ROUND_HALF_DOWN);
                }
            }
        }
        $data['result'] = $result;

        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('requests/balance', $data);
        $this->load->view('templates/footer');
    }

        /**
     * Send a leave request creation email to the manager of the connected employee
     * @param int $id Leave request identifier
     * @param int $reminder In case where the employee wants to send a reminder
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    private function sendMailOnLeaveRequestCreation($id, $reminder=FALSE) {
        $this->load->model('users_model');
        $this->load->model('types_model');
        $this->load->model('delegations_model');
        //We load everything from DB as the LR can be edited from HR/Employees
        $leave = $this->leaves_model->getLeaves($id);
        $user = $this->users_model->getUsers($leave['employee']);
        $leave_status = $leave['status'];
        switch ($leave_status) {
          case 7:
            $manager = $this->users_model->getUsers($leave['agent']);
            break;
          case 2:
            $manager = $this->users_model->getUsers($user['manager']);
            break;
          case 8:
            $grp_id = $user['organization'];
            $this->load->model('organization_model');
            $boss = ($this->organization_model->getSupervisor2($grp_id))->supervisor2;
            $manager = $this->users_model->getUsers($boss);
            break;
        }
        if (empty($manager['email'])) {
            $this->session->set_flashdata('msg', lang('leaves_create_flash_msg_error'));
        } else {
            //Send an e-mail to the manager
            $this->load->library('email');
            $this->load->library('polyglot');
            $usr_lang = $this->polyglot->code2language($manager['language']);

            //We need to instance an different object as the languages of connected user may differ from the UI lang
            $lang_mail = new CI_Lang();
            $lang_mail->load('email', $usr_lang);
            $lang_mail->load('global', $usr_lang);
            
            if ($reminder) {
                $this->sendGenericMail($leave, $user, $manager, $lang_mail,
                    $lang_mail->line('email_leave_request_reminder') . ' ' .
                    $lang_mail->line('email_leave_request_creation_title'),
                    $lang_mail->line('email_leave_request_reminder') . ' ' .
                    $lang_mail->line('email_leave_request_creation_subject'),
                    'request');
            } else {
                $this->sendGenericMail($leave, $user, $manager, $lang_mail,
                    $lang_mail->line('email_leave_request_creation_title'),
                    $lang_mail->line('email_leave_request_creation_subject'),
                    'request');
            }
        }
    }

    
    /**
     * Send a notification to the manager of the connected employee when the
     * leave request has been canceled by its collaborator.
     * @param int $id Leave request identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    private function sendMailOnLeaveRequestCanceled($id) {
        $this->load->model('users_model');
        $this->load->model('types_model');
        $this->load->model('delegations_model');
        //We load everything from DB as the LR can be edited from HR/Employees
        $leave = $this->leaves_model->getLeaves($id);
        $user = $this->users_model->getUsers($leave['employee']);
        $manager = $this->users_model->getUsers($user['manager']);
        if (empty($manager['email'])) {
            //TODO: create specific error message when the employee has no manager
            $this->session->set_flashdata('msg', lang('leaves_cancel_flash_msg_error'));
        } else {
            //Send an e-mail to the manager
            $this->load->library('email');
            $this->load->library('polyglot');
            $usr_lang = $this->polyglot->code2language($manager['language']);

            //We need to instance an different object as the languages of connected user may differ from the UI lang
            $lang_mail = new CI_Lang();
            $lang_mail->load('email', $usr_lang);
            $lang_mail->load('global', $usr_lang);
            
            $this->sendGenericMail($leave, $user, $manager, $lang_mail,
                $lang_mail->line('email_leave_request_cancellation_title'),
                $lang_mail->line('email_leave_request_cancellation_subject'),
                'cancelled');
        }
    }

    /**
     * Send a leave request cancellation email to the manager of the connected employee
     * @param int $id Leave request identifier
     * @param int $reminder In case where the employee wants to send a reminder
     * @author Guillaume Blaquiere <guillaume.blaquiere@gmail.com>
     */
    private function sendMailOnLeaveRequestCancellation($id, $reminder=FALSE) {
        $this->load->model('users_model');
        $this->load->model('types_model');
        $this->load->model('delegations_model');
        //We load everything from DB as the LR can be edited from HR/Employees
        $leave = $this->leaves_model->getLeaves($id);
        $user = $this->users_model->getUsers($leave['employee']);
        $leave_status = $leave['status'];
        switch ($leave_status) {
          case 9:
            $manager = $this->users_model->getUsers($leave['agent']);
            break;
          case 10:
            $manager = $this->users_model->getUsers($user['manager']);
            break;
          case 11:
            $grp_id = $user['organization'];
            $this->load->model('organization_model');
            $boss = ($this->organization_model->getSupervisor2($grp_id))->supervisor2;
            $manager = $this->users_model->getUsers($boss);
            break;
        }
        if (empty($manager['email'])) {
            $this->session->set_flashdata('msg', lang('leaves_cancel_flash_msg_error'));
        } else {
            //Send an e-mail to the manager
            $this->load->library('email');
            $this->load->library('polyglot');
            $usr_lang = $this->polyglot->code2language($manager['language']);

            //We need to instance an different object as the languages of connected user may differ from the UI lang
            $lang_mail = new CI_Lang();
            $lang_mail->load('email', $usr_lang);
            $lang_mail->load('global', $usr_lang);
            
            if ($reminder) {
                $this->sendGenericMail($leave, $user, $manager, $lang_mail,
                    $lang_mail->line('email_leave_request_reminder') . ' ' .
                    $lang_mail->line('email_leave_request_cancellation_title'),
                    $lang_mail->line('email_leave_request_reminder') . ' ' .
                    $lang_mail->line('email_leave_request_cancellation_subject'),
                    'request');
            } else {
                $this->sendGenericMail($leave, $user, $manager, $lang_mail,
                    $lang_mail->line('email_leave_request_cancellation_title'),
                    $lang_mail->line('email_leave_request_cancellation_subject'),
                    'cancel');
            }
        }
    }

    /**
     * Send a generic email from the collaborator to the manager (delegate in copy) when a leave request is created or cancelled
     * @param $leave Leave request
     * @param $user Connected employee
     * @param $manager Manger of connected employee
     * @param $lang_mail Email language library
     * @param $title Email Title
     * @param $detailledSubject Email detailled Subject
     * @param $emailModel template email to use
     * @author Guillaume Blaquiere <guillaume.blaquiere@gmail.com>
     *
     */
    private function sendGenericMail($leave, $user, $manager, $lang_mail, $title, $detailledSubject, $emailModel) {
        $date = new DateTime($leave['startdate']);
        $startdate = $date->format($lang_mail->line('global_date_format'));
        $date = new DateTime($leave['enddate']);
        $enddate = $date->format($lang_mail->line('global_date_format'));

        $comments=$leave['comments'];
        $comment = '';
        if(!empty($comments)){
          $comments=json_decode($comments);
          foreach ($comments->comments as $comments_item) {
            if($comments_item->type == "comment"){
              $comment = $comments_item->value;
            }
          }
        }
        log_message('info', "comment : " . $comment);
        $this->load->library('parser');
        $data = array(
            'Title' => $title,
            'Firstname' => $user['firstname'],
            'Lastname' => $user['lastname'],
            'StartDate' => $startdate,
            'EndDate' => $enddate,
            'StartDateType' => $lang_mail->line($leave['startdatetype']),
            'EndDateType' => $lang_mail->line($leave['enddatetype']),
            'Type' => $this->types_model->getName($leave['type']),
            'Duration' => $leave['duration'],
            'Balance' => $this->leaves_model->getLeavesTypeBalanceForEmployee($leave['employee'] , $leave['type_name'], $leave['startdate']),
            'Reason' => $leave['cause'],
            'BaseUrl' => $this->config->base_url(),
            'LeaveId' => $leave['id'],
            'UserId' => $this->user_id,
            'Comments' => $comment
        );
        $message = $this->parser->parse('emails/' . $manager['language'] . '/'.$emailModel, $data, TRUE);

        $to = $manager['email'];
        $subject = $detailledSubject . ' ' . $user['firstname'] . ' ' . $user['lastname'];
        //Copy to the delegates, if any
        $cc = NULL;
        $delegates = $this->delegations_model->listMailsOfDelegates($manager['id']);
        if ($delegates != '') {
            $cc = $delegates;
        }

        sendMailByWrapper($this, $subject, $message, $to, $cc);
    }

}
