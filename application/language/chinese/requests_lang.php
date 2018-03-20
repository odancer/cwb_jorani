<?php
/**
 * Translation file
 * @copyright  Copyright (c) 2014-2017 Benjamin BALET
 * @license     http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link          https://github.com/bbalet/jorani
 * @since       0.4.7
 * @author      Ceibga Bao <info@sansin.com.tw>
 */

$lang['requests_index_title'] = '休假申請傳送給我';
$lang['requests_index_description'] = '休假申請列表已傳送給你,如你不是管理者此表將為空白';
$lang['requests_filter_title'] = '狀態欄位過濾 (勾選顯示)';
$lang['requests_index_thead_tip_view'] = '預覽';
$lang['requests_index_thead_tip_accept'] = '接受';
$lang['requests_index_thead_tip_accept_cancellation'] = '接受取消';
$lang['requests_index_thead_tip_reject'] = '拒絕';
$lang['requests_index_thead_tip_reject_cancellation'] = '拒絕取消';
$lang['requests_index_thead_tip_history'] = '歷史資訊';
$lang['requests_index_thead_id'] = '序號';
$lang['requests_index_thead_fullname'] = '申請人';
$lang['requests_index_thead_startdate'] = '開始日期';
$lang['requests_index_thead_enddate'] = '結束日期';
$lang['requests_index_thead_duration'] = '申請時數';
$lang['requests_index_thead_type'] = '假別';
$lang['requests_index_thead_status'] = '狀態';
$lang['requests_index_thead_requested_date'] = '申請送出日';
$lang['requests_index_thead_last_change'] = '最後變更日';

$lang['requests_collaborators_title'] = '我的部屬清單';
$lang['requests_collaborators_description'] = '此表為你的部屬直接報告,如你不是管理者此表將為空白';
$lang['requests_collaborators_thead_id'] = '序號';
$lang['requests_collaborators_thead_link_balance'] = '休假平衡點';
$lang['requests_collaborators_thead_link_presence'] = '簽署報告';
$lang['requests_collaborators_thead_link_year'] = '年曆';
$lang['requests_collaborators_thead_link_create_leave'] = '建立此部屬休假申請表現';
$lang['requests_collaborators_thead_firstname'] = '名字';
$lang['requests_collaborators_thead_lastname'] = '姓氏';
$lang['requests_collaborators_thead_email'] = 'E-mail';
$lang['requests_collaborators_thead_identifier'] = '內部識別碼';

$lang['requests_summary_title'] = '使用者的休假平衡';
$lang['requests_summary_thead_type'] = '假別';
$lang['requests_summary_thead_available'] = '未休時數';
$lang['requests_summary_thead_taken'] = '已休時數';
$lang['requests_summary_thead_entitled'] = '可享有休假時數';
$lang['requests_summary_thead_description'] = '描述';
$lang['requests_summary_flash_msg_error'] = '此員工無類別';
$lang['requests_summary_flash_msg_forbidden'] = '你不是此員工管理者';
$lang['requests_summary_button_list'] = '部屬列表';

$lang['requests_index_button_export'] = '匯出此單';
$lang['requests_index_button_show_all'] = '全部列表';
$lang['requests_index_button_show_pending'] = '列表申請中';

$lang['requests_accept_flash_msg_error'] = '你不是此員工隸屬管理者,你不能接受此休假申請';
$lang['requests_accept_flash_msg_success'] = '休假申請已成功受理';
$lang['requests_reject_flash_msg_error'] = '你不是此員工隸屬管理者,你不能拒絕此休假申請';
$lang['requests_reject_flash_msg_success'] = '休假申請已成功拒絕';

$lang['requests_export_title'] = '休假申請列表';
$lang['requests_export_thead_id'] = '序號';
$lang['requests_export_thead_fullname'] = '全名';
$lang['requests_export_thead_startdate'] = '開始日期';
$lang['requests_export_thead_startdate_type'] = '上午/下午';
$lang['requests_export_thead_enddate'] = '結束日期';
$lang['requests_export_thead_enddate_type'] = '上午/下午';
$lang['requests_export_thead_duration'] = '申請時數';
$lang['requests_export_thead_type'] = '假別';
$lang['requests_export_thead_cause'] = '理由';
$lang['requests_export_thead_status'] = '狀態';

$lang['requests_delegations_title'] = '委託列表';
$lang['requests_delegations_description'] = '此列表為可受理你的申請的管理者';
$lang['requests_delegations_thead_employee'] = '員工';
$lang['requests_delegations_thead_tip_delete'] = '取消';
$lang['requests_delegations_button_add'] = '外加';
$lang['requests_delegations_popup_delegate_title'] = '增加委託';
$lang['requests_delegations_popup_delegate_button_ok'] = '確定';
$lang['requests_delegations_popup_delegate_button_cancel'] = '取消';
$lang['requests_delegations_confirm_delete_message'] = '你確定你要取消此委託嗎？';
$lang['requests_delegations_confirm_delete_cancel'] = '取消';
$lang['requests_delegations_confirm_delete_yes'] = '是';
$lang['requests_balance_title'] = '請假餘額(部屬)';
$lang['requests_balance_description'] = 'Leave balance of my direct report subordinates. If you are not a manager, this list will always be empty.';
$lang['requests_balance_date_field'] = '報告建立日期';

$lang['requests_comment_reject_request_title'] = 'Comment';
$lang['requests_comment_reject_request_button_cancel'] = 'Cancel';
$lang['requests_comment_reject_request_button_reject'] = 'Reject';


$lang['requests_view_title'] = '預覽休假申請';
$lang['requests_view_html_title'] = '預覽一休假申請';
$lang['requests_view_field_start'] = '開始日期';
$lang['requests_view_field_end'] = '結束日期';
$lang['requests_view_field_type'] = '假別';
$lang['requests_view_field_duration'] = '申請時數';
$lang['requests_view_field_cause'] = '理由';
$lang['requests_view_field_status'] = '狀態';
$lang['requests_view_button_edit'] = '編輯';
$lang['requests_view_button_back_list'] = '返回列表';
$lang['requests_comment_title'] = 'Comments';
$lang['requests_comment_new_comment'] = 'New comment';
$lang['requests_comment_send_comment'] = 'Send comment';
$lang['requests_comment_author_saying'] = ' says';
$lang['requests_comment_status_changed'] = 'The status of the leave have been changed to ';
