<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getPrepareList()
    {
        $employee_id = $this->input->get('employee_id');

        if (!isset($employee_id) || $employee_id == "") $this->FailedResponse('Invalid Param');

        $where = "employee_id = '" . $employee_id . "'";
        $purchaseCostCenterList = $this->purchase_cost_center->get_by($where);
        $purchaseDeliveryModeList = $this->purchase_delivery_mode->get();
        $purchaseSupplierList = $this->purchase_supplier->get();
        $purchaseDeliveryList = $this->purchase_delivery->get();
        $purchaseUomList = $this->purchase_uom->get();
        $purchaseLevelList = $this->purchase_level->get();

        $data = array(
            "purchaseCostCenterList" => $purchaseCostCenterList,
            "purchaseDeliveryModeList" => $purchaseDeliveryModeList,
            "purchaseSupplierList" => $purchaseSupplierList,
            "purchaseDeliveryList" => $purchaseDeliveryList,
            "purchaseUomList" => $purchaseUomList,
            "purchaseLevelList" => $purchaseLevelList,
            "purchasePriorityLevelList" =>$this->purchase_priority_level->get(),
        );

        $this->SuccessResponse($data);
    }

    public function getPurchaseRequisitionList()
    {
        $employee_id = $this->input->get('employee_id');
        $type = $this->input->get('type');

        if (!isset($employee_id) || $employee_id == "") $this->FailedResponse('Invalid Param');
        if (!isset($type) || $type == "") $this->FailedResponse('Invalid Param');

        $where = "owner = '" . $employee_id . "' and type = '" . $type . "'";
        $purchaseRequisitionList = $this->purchase_requisition->get_limit(null, null, $where, "id", true);

        $this->SuccessResponse($purchaseRequisitionList);
    }

    public function getPurchaseRequisitionDetail()
    {
        $id = $this->input->get('id');

        if (!isset($id) || $id == "") $this->FailedResponse('Invalid Param');

        $purchaseRequisitionDetail = $this->purchase_requisition->get($id, TRUE);

        $this->SuccessResponse($purchaseRequisitionDetail);
    }

    public function savePurchaseRequisition()
    {
        $id = $oldid = $this->input->post('id');
        $owner = $this->input->post('owner');
        $price = $this->input->post('price');
        $quantity = $this->input->post('quantity');
        $currency = $this->input->post('currency');
        $po_date = $this->input->post('po_date');
        $cost_center_id = $this->input->post('cost_center_id');
        $product_code = $this->input->post('product_code');

        if (!isset($owner) || $owner == "") $this->FailedResponse('Invalid Param');
        if (!isset($price) || $price == "") $this->FailedResponse('Invalid Param');
        if (!isset($quantity) || $quantity == "") $this->FailedResponse('Invalid Param');
        if (!isset($currency) || $currency == "") $this->FailedResponse('Invalid Param');
        if (!isset($po_date) || $po_date == "") $this->FailedResponse('Invalid Param');
        if (!isset($cost_center_id) || $cost_center_id == "") $this->FailedResponse('Invalid Param');
        if (!isset($product_code) || $product_code == "") $this->FailedResponse('Invalid Param');

        $data = $this->input->post();
        if (array_key_exists("id", $data)) unset($data['id']);

        $photo_path = UploadPhoto('photo');
        if ($photo_path) $data['photo'] = base_url($photo_path);

        $sum_price = 0;
        $where = "currency_type = '" . $currency . "' and date <= '" . $po_date . "'";
        $currencyLisst = $this->purchase_currency->get_limit(1, 0, $where, "id", true);
        if ($currencyLisst != null && count($currencyLisst) == 1) {
            $sum_price = $price * $quantity * $currencyLisst[0]['currency_rate'];
        } else $this->FailedResponse('There is no currency rate.');
        $data['sum_price'] = $sum_price;

        $where = "employee_id = '" . $owner . "' and amount_min<=" . $sum_price . " and amount_max>=" . $sum_price;
        $purchaseApprovalLimit = $this->purchase_approval_limit->get_by($where, TRUE);
        if ($purchaseApprovalLimit != null) {
            $data['approval_no1'] = $purchaseApprovalLimit['approver_no_1'];
            $data['approval_no2'] = $purchaseApprovalLimit['approver_no_2'];
        } else $this->FailedResponse('There is no approvers to process your request.');

        $where = "id = '" . $cost_center_id . "' and employee_id = '" . $owner."'";
        $purchaseCostCenter = $this->purchase_cost_center->get_by($where, TRUE);
        if ($purchaseCostCenter != null) {
            $updating_amount = $purchaseCostCenter['updating_amount'];
            $pendingSumPrice = 0;
            $sqlstr = "select sum(sum_price) as pendingSumPrice from man_mob_purchase_requisition where approval_no1_state!=1 or approval_no2_state!=1";
            $sumPriceList = $this->purchase_requisition->getQuerySqlList($sqlstr);
            if ($sumPriceList != null && count($sumPriceList) == 1) $pendingSumPrice = $sumPriceList[0]['pendingSumPrice'];
            if ($updating_amount - $pendingSumPrice < $sum_price) $this->FailedResponse('Insufficient amount.');
        } else $this->FailedResponse('There is no cost center to process your request.');

        $where = "product_code = '" . $product_code . "'";
        $purchaseApprovalTeam = $this->purchase_product->get_by($where, TRUE);
        if (!$purchaseApprovalTeam) $this->FailedResponse('There is no product now.');

        if (!isset($id) || $id == "" || intval($id) < 1) {
            $id = $this->purchase_requisition->save($this->purchase_requisition->checkTableFiled($data));
        } else {
            $id = $this->purchase_requisition->save($this->purchase_requisition->checkTableFiled($data), $id);
        }

        if ($id){
            $this->purchase_requisition->update(array(
                'pr_number' =>  date('Ymd').sprintf("%02d", $id)
            ), $id);
        }
        if ($id > 0) {
            $purchaseApprovalTeam = array();
            $purchaseApprovalTeam['product_code'] = $product_code;
            $purchaseApprovalTeam['main_appr_ids_l1'] = array_key_exists("approval_no1", $data) ? $data['approval_no1'] : "";
            $purchaseApprovalTeam['main_appr_ids_l2'] = array_key_exists("approval_no2", $data) ? $data['approval_no2'] : "";
            $purchaseApprovalTeam['referece_no'] = $id;
            $purchaseApprovalTeam['type'] = array_key_exists("type", $data) ? $data['type'] : "";
            $purchaseApprovalTeam['pr_date'] = array_key_exists("request_date", $data) ? $data['request_date'] : "";
            $purchaseApprovalTeam['pr_id'] = $owner;
            $req_id = $this->purchase_approval_team->save($purchaseApprovalTeam);


            if (array_key_exists("warranty", $data) && $data['warranty']) {
                //---------------------- api ------------------------
                $purchaseCostCenter = $this->purchase_cost_center->get($cost_center_id, TRUE);
                if ($purchaseCostCenter) {
                    $purchaseCostCenter['updating_amount'] = $purchaseCostCenter['updating_amount'] - $sum_price;
                    $this->purchase_cost_center->save($purchaseCostCenter, $purchaseCostCenter[ID]);
                }
            } else {

                if (array_key_exists("approval_no1", $data)) {
                    $title = "Purchase " . ($purchaseApprovalTeam['type'] == "0" ? "request" : ($purchaseApprovalTeam['type'] == "1" ? "maintenance" : "Title"));
                    $body = json_encode(
                        array(
                            "id" => $id,
                            "approvers" => $data['approval_no1'],
                            "content" => "Received " . $purchaseApprovalTeam['product_code'] . " to be approved."
                        )
                    );
                    $this->sendMessage($title, $body, 7, null);
                }
            }
            $this->SuccessResponse('Success');
        } else {
            $this->FailedResponse('Failed');
        }
    }

    public function deletePurchaseRequisition()
    {
        $id = $this->input->get('id');

        if (!isset($id) || $id == "") $this->FailedResponse('Invalid Param');

        $purchaseRequisitionDetail = $this->purchase_requisition->get($id, TRUE);
        if (!$purchaseRequisitionDetail || $purchaseRequisitionDetail['approval_no1_checker'] != null || $purchaseRequisitionDetail['approval_no2_checker'] != null) {
            $this->FailedResponse('Failed');
        }

        $ret = $this->purchase_requisition->delete($id);

   //     if ($ret) {
            $where = "referece_no = '" . $id . "'";
            $purchaseApprovalTeam = $this->purchase_approval_team->get_by($where, TRUE);
            if ($purchaseApprovalTeam) {
                $this->purchase_approval_team->delete($purchaseApprovalTeam[ID]);

//                $purchaseApprovalTeam['main_appr_ids_l1'] = "";
//                $purchaseApprovalTeam['main_appr_ids_l2'] = "";
//                $purchaseApprovalTeam['main_start_ids'] = "";
//                $purchaseApprovalTeam['main_comptd_ids'] = "";
//                $purchaseApprovalTeam['acc_post_ids'] = "";
//                $purchaseApprovalTeam['payment_ids'] = "";
//                $purchaseApprovalTeam['main_start_ids_sent_status'] = 0;
//                $purchaseApprovalTeam['main_comptd_ids_sent_status'] = 0;
//                $purchaseApprovalTeam['acc_post_ids_sent_status'] = 0;
//                $purchaseApprovalTeam['payment_ids_sent_status'] = 0;
//                $purchaseApprovalTeam['referece_no'] = "";
//                $purchaseApprovalTeam['type'] = "";
//                $purchaseApprovalTeam['pr_date'] = "";
//                $purchaseApprovalTeam['pr_id'] = "";
//                $purchaseApprovalTeam['main_appr_l1_date'] = "";
//                $purchaseApprovalTeam['main_appr_l2_date'] = "";
//                $purchaseApprovalTeam['main_start_date'] = "";
//                $purchaseApprovalTeam['main_comptd_date'] = "";
//                $purchaseApprovalTeam['acc_post_date'] = "";
//                $purchaseApprovalTeam['payment_date'] = "";
//                $this->purchase_approval_team->save($purchaseApprovalTeam, $purchaseApprovalTeam[ID]);
            }

            $this->SuccessResponse('Success');
 //       } else {
 //           $this->FailedResponse('Failed');
//        }
    }

    public function getReceivedPurchaseRequisitionList()
    {
        $employee_id = $this->input->get('employee_id');

        if (!isset($employee_id) || $employee_id == "") $this->FailedResponse('Invalid Param');

        $sqlstr = "SELECT * FROM man_mob_purchase_requisition WHERE (FIND_IN_SET('" . $employee_id . "',approval_no1) OR ( approval_no1_state = 1 AND FIND_IN_SET('" . $employee_id . "',approval_no2))) ORDER BY id DESC";
        $receivedPurchaseRequisitionList = $this->purchase_requisition->getQuerySqlList($sqlstr);

        $this->SuccessResponse($receivedPurchaseRequisitionList);
    }

    public function getPurchaseTrackList()
    {
        $employee_id = $this->input->get('employee_id');

        if (!isset($employee_id) || $employee_id == "") $this->FailedResponse('Invalid Param');

        $where = "pr_id = '" . $employee_id . "'";
        $purchaseTrackList = $this->purchase_approval_team->get_limit(null, null, $where, "id", true);

        $this->SuccessResponse($purchaseTrackList);
    }

    public function processPurchaseRequisition()
    {
        $employee_id = $this->input->get('employee_id');
        $id = $this->input->get('id');
        $state = $this->input->get('state');

        if (!isset($employee_id) || $employee_id == "") $this->FailedResponse('Invalid Param');
        if (!isset($id) || $id == "") $this->FailedResponse('Invalid Param');
        if (!isset($state) || $state == "") $this->FailedResponse('Invalid Param');

        $purchaseRequisitionDetail = $this->purchase_requisition->get($id, TRUE);
        if (!$purchaseRequisitionDetail) $this->FailedResponse('Failed');

        $type = "1";

        $data = array();
        if ($purchaseRequisitionDetail['approval_no1_checker'] == null || $purchaseRequisitionDetail['approval_no1_state'] != 1) {
            $type = "1";
            $data['approval_no1_checker'] = $employee_id;
            $data['approval_no1_date'] = date("Y-m-d");
            $data['approval_no1_state'] = $state == "1" ? "1" : "2";
        } else if ($purchaseRequisitionDetail['approval_no1_checker'] != null && $purchaseRequisitionDetail['approval_no1_state'] == 1) {
            $type = "2";
            $data['approval_no2_checker'] = $employee_id;
            $data['approval_no2_date'] = date("Y-m-d");
            $data['approval_no2_state'] = $state == 1 ? "1" : "2";
        } else $this->FailedResponse('Failed');

        $this->purchase_requisition->save($this->purchase_requisition->checkTableFiled($data), $id);

        $data2 = array();
        if ($type == "1" && $state == "1" && $purchaseRequisitionDetail['approval_no2'] != null) {
            $data2['main_appr_l1_date'] = date("Y-m-d");
        } else if ($type == "2" && $state == "1") {
            $data2['main_appr_l2_date'] = date("Y-m-d");
        }
        if (count($data2)) {
            $where = "referece_no = '" . $id . "'";
            $this->purchase_approval_team->update_by($data2, $where);
        }

        $title = ($state == "1" ? "Accepted" : "Rejected") . " your request ";
        $body = json_encode(
            array(
                "id" => $id,
                "owner" => $purchaseRequisitionDetail['owner'],
                "content" => ($state == "1" ? "Accepted" : "Rejected") . $purchaseRequisitionDetail['product_code'] . " in level " . ($type == "1" ? "1" : "2"),
                "state" => $state
            )
        );
        $this->sendMessage($title, $body, 8, null);

        if ($type == "1" && $state == "1" && $purchaseRequisitionDetail['approval_no2'] != null) {
            $title = "Purchase " . ($purchaseRequisitionDetail['type'] == "0" ? "request" : ($purchaseRequisitionDetail['type'] == "1" ? "maintenance" : "Title"));
            $body = json_encode(
                array(
                    "id" => $id,
                    "approvers" => $purchaseRequisitionDetail['approval_no2'],
                    "content" => "Received " . $purchaseRequisitionDetail['product_code'] . " to be approved."
                )
            );
            $this->sendMessage($title, $body, 9, null);
        } else if ($type == "2" && $state == "1") {
            $purchaseCostCenter = $this->purchase_cost_center->get($purchaseRequisitionDetail['cost_center_id'], TRUE);
            if ($purchaseCostCenter) {
                $purchaseCostCenter['updating_amount'] = $purchaseCostCenter['updating_amount'] - $purchaseRequisitionDetail['sum_price'];
                $this->purchase_cost_center->save($purchaseCostCenter, $purchaseCostCenter[ID]);
            }
        }

        $this->SuccessResponse('Success');
    }

    public function getPurchaseMessageList()
    {
        $sender = $this->input->get('sender');
        $reference_id = $this->input->get('reference_id');
        $page = $this->input->get('page');

        if (!isset($sender) || $sender == "") $this->FailedResponse('Invalid Param');
        if (!isset($reference_id) || $reference_id == "") $this->FailedResponse('Invalid Param');
        if (!isset($page) || $page == "") $this->FailedResponse('Invalid Param');

        $receiver = "";
        $requisitionInfo = $this->purchase_requisition->get($reference_id, TRUE);
        if ($requisitionInfo) {
            $approval_no1 = ($requisitionInfo['approval_no1'] != null && $requisitionInfo['approval_no1'] != "") ? $requisitionInfo['approval_no1'] : "";
            $approval_no2 = ($requisitionInfo['approval_no2'] != null && $requisitionInfo['approval_no2'] != "") ? $requisitionInfo['approval_no2'] : "";
            $invited_ids = ($requisitionInfo['invited_ids'] != null && $requisitionInfo['invited_ids'] != "") ? $requisitionInfo['invited_ids'] : "";
            $owner = ($requisitionInfo['owner'] != null && $requisitionInfo['owner'] != "") ? $requisitionInfo['owner'] : "";
            if ($approval_no1 != "") $receiver = $receiver != "" ? $receiver . "," . $approval_no1 : $approval_no1;
            if ($approval_no2 != "") $receiver = $receiver != "" ? $receiver . "," . $approval_no2 : $approval_no2;
            if ($invited_ids != "") $receiver = $receiver != "" ? $receiver . "," . $invited_ids : $invited_ids;
            if ($owner != "") $receiver = $receiver != "" ? $receiver . "," . $owner : $owner;
        } else $this->FailedResponse('Invalid Param');

        if ($receiver == "" || !in_array($sender, explode(",", $receiver))) $this->FailedResponse('Invalid Param');

        $receiver = "'".str_replace(",","','",$receiver)."'";
        $where = "EMPLOYEE_ID in (" . $receiver . ")";
        $receiverList = $this->user_m->get_by($where);
        if ($receiverList == null || count($receiverList) < 1) $this->FailedResponse('Invalid Param');

        $limit = null;
        $offset = null;
        if ($page != -1) {
            if ($page < 1) $page = 1;
            $limit = 20;
            $offset = $limit * ($page - 1);
        }

        $where = "reference_id = '" . $reference_id . "'";
        $list = $this->purchase_message->get_limit($limit, $offset, $where, "id", true);

        for ($i = 0; $i < count($list); $i++) {
            $list[$i]['receiver'] = $receiver;
            $list[$i]['sender_name'] = "";
            $list[$i]['sender_photo'] = "";
            for ($j = 0; $j < count($receiverList); $j++) {
                if ($list[$i]['sender'] == $receiverList[$j]['id']) {
                    $list[$i]['sender_name'] = $receiverList[$j]['EMPLOYEE_NAME'];
                    $list[$i]['sender_photo'] = $receiverList[$j]['PHOTO'];
                    break;
                }
            }
        }

        $this->SuccessResponse($list);
    }

    public function sendPurchaseMessage()
    {
        $sender = $this->input->post('sender');
        $message = $this->input->post('message');
        $reference_id = $this->input->post('reference_id');
        $level = $this->input->post('level');

        if (!isset($sender) || $sender == "") $this->FailedResponse('Invalid Param');
        if (!isset($reference_id) || $reference_id == "") $this->FailedResponse('Invalid Param');
        if (!isset($level) || $level == "") $this->FailedResponse('Invalid Param');

        $type = 0; // text

        $photo_path = UploadPhoto('photo');
        if ($photo_path) {
            $message = base_url($photo_path);
            $type = 1; // image
        } else if (!isset($message) || $message == "") $this->FailedResponse('Invalid Param');

        $data = array();
        $data['sender'] = $sender;
        $data['message'] = $message;
        $data['type'] = $type;
        $data['reference_id'] = $reference_id;
        $data['level'] = $level;
        $data['date_created'] = $this->currentTime();
        $id = $this->purchase_message->save($data);

        $data['id'] = $id;
        $data['receiver'] = "";
        $data['sender_name'] = "";
        $data['sender_photo'] = "";

        $requisitionInfo = $this->purchase_requisition->get($reference_id, TRUE);
        if ($requisitionInfo) {
            $approval_no1 = ($requisitionInfo['approval_no1'] != null && $requisitionInfo['approval_no1'] != "") ? $requisitionInfo['approval_no1'] : "";
            $approval_no2 = ($requisitionInfo['approval_no2'] != null && $requisitionInfo['approval_no2'] != "") ? $requisitionInfo['approval_no2'] : "";
            $invited_ids = ($requisitionInfo['invited_ids'] != null && $requisitionInfo['invited_ids'] != "") ? $requisitionInfo['invited_ids'] : "";
            $owner = ($requisitionInfo['owner'] != null && $requisitionInfo['owner'] != "") ? $requisitionInfo['owner'] : "";
            if ($approval_no1 != "") $data['receiver'] = $data['receiver'] != "" ? $data['receiver'] . "," . $approval_no1 : $approval_no1;
            if ($approval_no2 != "") $data['receiver'] = $data['receiver'] != "" ? $data['receiver'] . "," . $approval_no2 : $approval_no2;
            if ($invited_ids != "") $data['receiver'] = $data['receiver'] != "" ? $data['receiver'] . "," . $invited_ids : $invited_ids;
            if ($owner != "") $data['receiver'] = $data['receiver'] != "" ? $data['receiver'] . "," . $owner : $owner;
        }

        $senderInfo = $this->user_m->get($sender, TRUE);
        if ($senderInfo) {
            $data['sender_name'] = $senderInfo['EMPLOYEE_NAME'];
            $data['sender_photo'] = $senderInfo['PHOTO'];
        }

        $title = "New message";
        $body = json_encode($data);
        $this->sendMessage($title, $body, 10, null);

        $this->SuccessResponse('Success');
    }

    public function getPurchaseReportList()
    {
        $employee_id = $this->input->get('employee_id');

        if (!isset($employee_id) || $employee_id == "") $this->FailedResponse('Invalid Param');

        $where = "employee_id = '" . $employee_id . "'";
        $purchaseReportList = $this->purchase_report->get_limit(null, null, $where, "id", true);

        $this->SuccessResponse($purchaseReportList);
    }

   
    public function savePurchaseReport()
    {
        $employee_id = $this->input->get('employee_id');
        $content = $this->input->get('content');

        if (!isset($employee_id) || $employee_id == "") $this->FailedResponse('Invalid Param');
        if (!isset($content) || $content == "") $this->FailedResponse('Invalid Param');

        $data = array();
        $data['employee_id'] = $employee_id;
        $data['content'] = $content;
        $data['created_time'] = $this->currentTime();

        $id = $this->purchase_report->save($data);

        if ($id > 0) {
            $item = $this->purchase_requisition->get_by(array(
                'pr_number' => $content
            ), true);
            $this->SuccessResponse($item);
        } else {
            $this->FailedResponse('Failed');
        }
    }


    public function getInvitedIds()
    {
        $reference_id = $this->input->get('reference_id');
        $query = $this->input->get('query');

        if (!isset($reference_id) || $reference_id == "") $this->FailedResponse('Invalid Param');

        $requisitionInfo = $this->purchase_requisition->get($reference_id, TRUE);
        if (!$requisitionInfo) $this->FailedResponse('Invalid Param');

        $noEmployeeIds = "";
        $approval_no1 = ($requisitionInfo['approval_no1'] != null && $requisitionInfo['approval_no1'] != "") ? $requisitionInfo['approval_no1'] : "";
        $approval_no2 = ($requisitionInfo['approval_no2'] != null && $requisitionInfo['approval_no2'] != "") ? $requisitionInfo['approval_no2'] : "";
        $invited_ids = ($requisitionInfo['invited_ids'] != null && $requisitionInfo['invited_ids'] != "") ? $requisitionInfo['invited_ids'] : "";
        $owner = ($requisitionInfo['owner'] != null && $requisitionInfo['owner'] != "") ? $requisitionInfo['owner'] : "";
        if ($approval_no1 != "") $noEmployeeIds = $noEmployeeIds != "" ? $noEmployeeIds . "," . $approval_no1 : $approval_no1;
        if ($approval_no2 != "") $noEmployeeIds = $noEmployeeIds != "" ? $noEmployeeIds . "," . $approval_no2 : $approval_no2;
        if ($invited_ids != "") $noEmployeeIds = $noEmployeeIds != "" ? $noEmployeeIds . "," . $invited_ids : $invited_ids;

        $noEmployeeIdsArr = array();
        if ($noEmployeeIds != "") $noEmployeeIdsArr = explode(",", $noEmployeeIds);

        $where = "EMPLOYEE_ID <> '" . $owner . "' and GROUP_NAME='PURCHASER'";
        if ($query != "") {
            $where .= " and EMPLOYEE_NAME like '%" . $query . "%'";
        }
        $purchaseReportList = $this->user_m->get_by($where);

        foreach ($purchaseReportList as $key => $item) {
            $item['is_invited'] = 0;
            for ($j = 0; $j < count($noEmployeeIdsArr); $j++) {
                if ($item['EMPLOYEE_ID'] == $noEmployeeIdsArr[$j]) {
                    $item['is_invited'] = 1;
                    break;
                }
            }
            unset($item[GROUP_NAME]);
            unset($item[REFERENCES_INVITED]);
            $purchaseReportList[$key] = $item;
        }

        $this->SuccessResponse($purchaseReportList);
    }


    public function saveInvitedIds()
    {
        $reference_id = $this->input->get('reference_id');
        $invited_ids = $this->input->get('invited_ids');

        if (!isset($reference_id) || $reference_id == "") $this->FailedResponse('Invalid Param');
        if (!isset($invited_ids) || $invited_ids == "") $this->FailedResponse('Invalid Param');

        $requisitionInfo = $this->purchase_requisition->get($reference_id, TRUE);
        if (!$requisitionInfo)
            $this->FailedResponse("Invalid request");

        $ids = $requisitionInfo['invited_ids'];
        $data = array();
        if ($ids == "") $ids = $invited_ids;
        else $ids .= ("," . $invited_ids);
        $data['invited_ids'] = $ids;
        $id = $this->purchase_requisition->save($data, $reference_id);

        $this->SuccessResponse('Success');
    }

    public function checkPurchaseTeam()
    {
        $where = "main_start_ids_sent_status = 0 and main_start_ids is not NULL and main_start_ids<>''";
        $purchaseTeamList = $this->purchase_approval_team->get_by($where);
        if ($purchaseTeamList != null && count($purchaseTeamList)) {
            $title = "You received main start";
            for ($i = 0; $i < count($purchaseTeamList); $i++) {
                $body = json_encode(
                    array(
                        "id" => $purchaseTeamList[$i]['referece_no'],
                        "receivers" => $purchaseTeamList[$i]['main_start_ids'],
                        "content" => "Product code - " . $purchaseTeamList[$i]['product_code'] . ", Requestor - " . $purchaseTeamList[$i]['pr_id']
                    )
                );
                $this->sendMessage($title, $body, 11, null);
            }
            $this->purchase_approval_team->update_by(array("main_start_ids_sent_status" => 1), $where);
        }

        $where = "main_comptd_ids_sent_status = 0 and main_comptd_ids is not NULL and main_comptd_ids<>''";
        $purchaseTeamList = $this->purchase_approval_team->get_by($where);
        if ($purchaseTeamList != null && count($purchaseTeamList)) {
            $title = "You received main comptd";
            for ($i = 0; $i < count($purchaseTeamList); $i++) {
                $body = json_encode(
                    array(
                        "id" => $purchaseTeamList[$i]['referece_no'],
                        "receivers" => $purchaseTeamList[$i]['main_comptd_ids'],
                        "content" => "Product code - " . $purchaseTeamList[$i]['product_code'] . ", Requestor - " . $purchaseTeamList[$i]['pr_id']
                    )
                );
                $this->sendMessage($title, $body, 11, null);
            }
            $this->purchase_approval_team->update_by(array("main_comptd_ids_sent_status" => 1), $where);
        }

        $where = "acc_post_ids_sent_status = 0 and acc_post_ids is not NULL and acc_post_ids<>''";
        $purchaseTeamList = $this->purchase_approval_team->get_by($where);
        if ($purchaseTeamList != null && count($purchaseTeamList)) {
            $title = "You received acc post";
            for ($i = 0; $i < count($purchaseTeamList); $i++) {
                $body = json_encode(
                    array(
                        "id" => $purchaseTeamList[$i]['referece_no'],
                        "receivers" => $purchaseTeamList[$i]['acc_post_ids'],
                        "content" => "Product code - " . $purchaseTeamList[$i]['product_code'] . ", Requestor - " . $purchaseTeamList[$i]['pr_id']
                    )
                );
                $this->sendMessage($title, $body, 11, null);
            }
            $this->purchase_approval_team->update_by(array("acc_post_ids_sent_status" => 1), $where);
        }

        $where = "payment_ids_sent_status = 0 and payment_ids is not NULL and payment_ids<>''";
        $purchaseTeamList = $this->purchase_approval_team->get_by($where);
        if ($purchaseTeamList != null && count($purchaseTeamList)) {
            $title = "You received payment";
            for ($i = 0; $i < count($purchaseTeamList); $i++) {
                $body = json_encode(
                    array(
                        "id" => $purchaseTeamList[$i]['referece_no'],
                        "receivers" => $purchaseTeamList[$i]['payment_ids'],
                        "content" => "Product code - " . $purchaseTeamList[$i]['product_code'] . ", Requestor - " . $purchaseTeamList[$i]['pr_id']
                    )
                );
                $this->sendMessage($title, $body, 11, null);
            }
            $this->purchase_approval_team->update_by(array("payment_ids_sent_status" => 1), $where);
        }
    }
}