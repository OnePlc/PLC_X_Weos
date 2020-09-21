<?php
/**
 * WeosController.php - Main Controller
 *
 * Main Controller Weos Module
 *
 * @category Controller
 * @package Weos
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\Weos\Controller;

use Application\Controller\CoreEntityController;
use Application\Model\CoreEntityModel;
use OnePlace\Weos\Model\Weos;
use OnePlace\Weos\Model\WeosTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;

class ApiController extends CoreEntityController
{
    /**
     * Weos Table Object
     *
     * @since 1.0.0
     */
    protected $oTableGateway;

    /**
     * Tables from other Modules
     *
     * @var $aPluginTbls
     * @since 1.0.'
     */
    protected $aPluginTbls;

    /**
     * WeosController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param WeosTable $oTableGateway
     * @param array $aPluginTbls
     * @param $oServiceManager
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter, $oTableGateway, $aPluginTbls, $oServiceManager)
    {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'weos-single';
        $this->aPluginTbls = $aPluginTbls;
        parent::__construct($oDbAdapter, $oTableGateway, $oServiceManager);
    }

    public function startAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $sLat = $oReq->getPost('location_lat');
            $sLong = $oReq->getPost('location_long');
            $sUser = $oReq->getPost('email');

            $oUser = CoreEntityController::$aCoreTables['user']->select([
                'email' => $sUser
            ]);

            if(count($oUser) > 0) {
                $oUser = $oUser->current();

                # create new order
                $this->oTableGateway->insert([
                    'date_start' => date('Y-m-d', time()),
                    'date_complete' => '0000-00-00 00:00:00',
                    'user_idfs' =>  $oUser->User_ID,
                    'deliverydate_start' => '0000-00-00 00:00:00',
                    'deliverydate_end' => '0000-00-00 00:00:00',
                    'contact_idfs' => 0,
                    'reminder' => 0,
                ]);

                $iOrderID = $this->oTableGateway->lastInsertValue;

                # Get contact categories
                $aCategories = [];
                $oCatsDB = CoreEntityController::$aCoreTables['core-entity-tag']->select([
                    'entity_form_idfs' => 'contact-single',
                    'tag_idfs' => 1,
                ]);
                if(count($oCatsDB) > 0) {
                    foreach($oCatsDB as $oCat) {
                        $aCategories[] = (object)['id' => $oCat->Entitytag_ID, 'label' => $oCat->tag_value];
                    }
                }

                $aReturn = [
                    'state' => 'success',
                    'order_id' => $iOrderID,
                    'categories' => $aCategories,
                ];

            } else {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'User not found',
                ];
            }

            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    public function dateAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $iOrderID = $oReq->getPost('order_id');
            $iCategoryID = $oReq->getPost('category_id');

            $oOrder = $this->oTableGateway->select([
                'Order_ID' => $iOrderID,
            ]);
            if(count($oOrder) > 0) {
                $oOrder = $oOrder->current();
                $this->oTableGateway->update([
                    'category_idfs' => (int)$iCategoryID
                ],['Order_ID' => $iOrderID]);

                $aReturn = [
                    'state' => 'success',
                    'message' => 'category saved',
                ];
            } else {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'Order not found',
                ];
            }

            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    public function timeAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $iOrderID = $oReq->getPost('order_id');
            $sDate = $oReq->getPost('date');

            $oOrder = $this->oTableGateway->select([
                'Order_ID' => $iOrderID,
            ]);
            if(count($oOrder) > 0) {
                $oOrder = $oOrder->current();
                $this->oTableGateway->update([
                    'deliverydate_start' => date('Y-m-d',strtotime($sDate)).' 00:00:00',
                ],['Order_ID' => $iOrderID]);

                $aContacts = [];
                $oContactsDB = $this->aPluginTbls['contact']->fetchAll(true,['multi_tag' => $oOrder->category_idfs]);
                if(count($oContactsDB) > 0) {
                    foreach($oContactsDB as $oContact) {
                        $aContacts[] = (object)[
                            'id' => $oContact->getID(),
                            'name' => $oContact->getLabel(),
                            'company' => 'Example',
                            'image' => '/img/avatar.png',
                        ];
                    }
                }
                $aReturn = [
                    'state' => 'success',
                    'message' => 'date saved',
                    'contacts' => $aContacts,
                ];
            } else {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'Order not found',
                ];
            }

            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    public function contactAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $iOrderID = $oReq->getPost('order_id');
            $sTimeStart = $oReq->getPost('time_start');
            $sTimeEnd = $oReq->getPost('time_end');

            $oOrder = $this->oTableGateway->select([
                'Order_ID' => $iOrderID,
            ]);
            if (count($oOrder) > 0) {
                $oOrder = $oOrder->current();
                $this->oTableGateway->update([
                    'deliverydate_start' => date('Y-m-d', strtotime($oOrder->deliverydate_start)) . ' '.date('H:i',strtotime($sTimeStart)),
                    'deliverydate_end' => date('Y-m-d', strtotime($oOrder->deliverydate_start)) . ' '.date('H:i',strtotime($sTimeEnd)),
                ], ['Order_ID' => $iOrderID]);

                $aReturn = [
                    'state' => 'success',
                    'message' => 'time saved',
                ];
            } else {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'Order not found',
                ];
            }

            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    public function confirmAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $iOrderID = $oReq->getPost('order_id');
            $iContactID = $oReq->getPost('contact_id');

            $oOrder = $this->oTableGateway->select([
                'Order_ID' => $iOrderID,
            ]);
            if (count($oOrder) > 0) {
                $oOrder = $oOrder->current();
                $this->oTableGateway->update([
                    'contact_idfs' => $iContactID,
                ], ['Order_ID' => $iOrderID]);

                $aReturn = [
                    'state' => 'success',
                    'message' => 'order confirmed',
                ];
            } else {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'Order not found',
                ];
            }

            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    public function completeAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $iOrderID = $oReq->getPost('order_id');
            $bSetReminder = (int)$oReq->getPost('reminder');
            if($bSetReminder == 1) {
                // ok
            } else {
                $bSetReminder = 0;
            }

            $oOrder = $this->oTableGateway->select([
                'Order_ID' => $iOrderID,
            ]);
            if (count($oOrder) > 0) {
                $oOrder = $oOrder->current();
                $this->oTableGateway->update([
                    'reminder' => (int)$bSetReminder,
                ], ['Order_ID' => $iOrderID]);

                $aReturn = [
                    'state' => 'success',
                    'message' => 'order placed',
                ];
            } else {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'Order not found',
                ];
            }

            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    public function registerAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $sUser = $oReq->getPost('email');
            $sPinHash = $oReq->getPost('pin_hash');
            $sStreet = $oReq->getPost('street');
            $iZip = $oReq->getPost('zip');
            $sCity = $oReq->getPost('city');
            $sPhone = $oReq->getPost('phone');

            $oCheck = CoreEntityController::$aCoreTables['user']->select([
                'email' => $sUser,
            ]);

            if(count($oCheck) == 0) {
                CoreEntityController::$aCoreTables['user']->insert([
                    'username' => 'weostest',
                    'full_name' => 'Weos Test',
                    'email' => $sUser,
                    'password' => $sPinHash,
                    'authkey' => '',
                    'lang' => 'de_DE',
                    'created_by' => 1,
                    'created_date' => date('Y-m-d H:i:s', time()),
                    'modified_by' => 1,
                    'modified_date' => date('Y-m-d H:i:s', time()),
                ]);

                $aReturn = [
                    'state' => 'success',
                    'message' => 'user created',
                ];
            } else {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'user already exists - please login',
                ];
            }

            echo json_encode($aReturn);

            return false;
        }
    }

    public function loginAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $sUser = $oReq->getPost('email');

            $oCheck = CoreEntityController::$aCoreTables['user']->select([
                'email' => $sUser,
            ]);

            if(count($oCheck) > 0) {
                $aReturn = [
                    'state' => 'success',
                    'message' => 'user logged in',
                    'mode' => 'user',
                ];
            } else {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'user not found',
                ];
            }

            echo json_encode($aReturn);

            return false;
        }
    }

    public function cancelAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $iOrderID = $oReq->getPost('order_id');

            $aReturn = [
                'state' => 'success',
                'message' => 'order closed',
            ];

            echo json_encode($aReturn);

            return false;
        }
    }
}