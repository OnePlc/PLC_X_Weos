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
use Laminas\Db\Sql\Where;

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

    /**
     * Start a new order from weos app
     *
     * @return bool - no view file, raw json response
     */
    public function homeAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $oJSON = json_decode($this->getRequest()->getContent());
            if(!is_object($oJSON)) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } else {
                /**
                $sUser = $oJSON->email;

                $oUser = CoreEntityController::$aCoreTables['user']->select([
                    'email' => $sUser
                ]); **/

                //if(count($oUser) > 0) {
                //    $oUser = $oUser->current();

                    # Get contact categories
                    $aCategories = [];
                    $oCatsDB = CoreEntityController::$aCoreTables['core-entity-tag']->select([
                        'entity_form_idfs' => 'contact-single',
                        'tag_idfs' => 1,
                    ]);
                    if(count($oCatsDB) > 0) {
                        foreach($oCatsDB as $oCat) {
                            $aCategories[] = (object)[
                                'id' => $oCat->Entitytag_ID,
                                'label' => $oCat->tag_value,
                                'icon' => $oCat->tag_icon,
                            ];
                        }
                    }

                    $aReturn = [
                        'state' => 'success',
                        'categories' => $aCategories,
                    ];

                //} else {
                //    $aReturn = [
                //        'state' => 'error',
                //        'message' => 'User not found',
                //    ];
                //}
            }

            header('Content-Type: application/json');
            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    /**
     * Start a new order from weos app
     *
     * @return bool - no view file, raw json response
     */
    public function startAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $oJSON = json_decode($this->getRequest()->getContent());
            if(!is_object($oJSON)) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } elseif(!property_exists($oJSON,'email')) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } else {
                $sLat = $oJSON->location_lat;
                $sLong = $oJSON->location_long;
                $sUser = $oJSON->email;
                $iCategoryID = $oReq->category_id;

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

                    $this->oTableGateway->update([
                        'category_idfs' => (int)$iCategoryID
                    ],['Order_ID' => $iOrderID]);

                    $aReturn = [
                        'state' => 'success',
                        'order_id' => $iOrderID,
                    ];

                } else {
                    $aReturn = [
                        'state' => 'error',
                        'message' => 'User not found',
                    ];
                }
            }

            header('Content-Type: application/json');
            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    /**
     * Set time for weos app order
     *
     * @return bool - no view file, raw json response
     */
    public function timeAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $oJSON = json_decode($this->getRequest()->getContent());
            if(!is_object($oJSON)) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } elseif(!property_exists($oJSON,'order_id')) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } else {
                $iOrderID = $oJSON->order_id;
                $sDate = $oJSON->date;

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
            }

            header('Content-Type: application/json');
            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    /**
     * Set contat for weos app order
     *
     * @return bool - no view file, raw json response
     */
    public function contactAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $oJSON = json_decode($this->getRequest()->getContent());
            if(!is_object($oJSON)) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } elseif(!property_exists($oJSON,'order_id')) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } else {
                $iOrderID = $oJSON->order_id;
                $sTimeStart = $oJSON->time_start;
                $sTimeEnd = $oJSON->time_end;

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
            }

            header('Content-Type: application/json');
            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    /**
     * confirm weos app order
     *
     * @return bool - no view file, raw json response
     */
    public function confirmAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $oJSON = json_decode($this->getRequest()->getContent());
            if(!is_object($oJSON)) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } elseif(!property_exists($oJSON,'order_id')) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } else {
                $iOrderID = $oJSON->order_id;
                $iContactID = $oJSON->contact_id;

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
            }

            header('Content-Type: application/json');
            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    /**
     * Complete weos app order
     *
     * @return bool - no view file, raw json response
     */
    public function completeAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $oJSON = json_decode($this->getRequest()->getContent());
            if(!is_object($oJSON)) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } elseif(!property_exists($oJSON,'order_id')) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } else {
                $iOrderID = $oJSON->order_id;
                $bSetReminder = (int)$oJSON->reminder;
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
            }

            header('Content-Type: application/json');
            echo json_encode($aReturn);

            return false;
        }

        return false;
    }

    /**
     * Register a new user from weos app
     *
     * @return bool - no view file, raw json response
     */
    public function registerAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $oJSON = json_decode($this->getRequest()->getContent());
            if(!is_object($oJSON)) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } elseif(!property_exists($oJSON,'email')) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } else {
                $sUser = $oJSON->email;
                $sPinHash = $oJSON->pin_hash;
                $sStreet = $oJSON->street;
                $iZip = $oJSON->zip;
                $sCity = $oJSON->city;
                $sPhone = $oJSON->phone;

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
            }

            header('Content-Type: application/json');
            echo json_encode($aReturn);

            return false;
        }
    }

    /**
     * User Login for Weos App
     *
     * @return bool - no view file, raw json response
     */
    public function loginAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $oJSON = json_decode($this->getRequest()->getContent());
            if(!is_object($oJSON)) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } elseif(!property_exists($oJSON,'email')) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } else {
                $sUser = $oJSON->email;
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
                        'message' => 'user '.$sUser.' not found',
                    ];
                }
            }

            header('Content-Type: application/json');
            echo json_encode($aReturn);

            return false;
        }
    }

    public function cancelAction() {
        $this->layout('layout/json');

        $oReq = $this->getRequest();

        if($oReq->isPost()) {
            $oJSON = json_decode($this->getRequest()->getContent());
            if(!is_object($oJSON)) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } elseif(!property_exists($oJSON,'order_id')) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid json',
                ];
            } else {
                $iOrderID = $oJSON->order_id;

                $aReturn = [
                    'state' => 'success',
                    'message' => 'order closed',
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($aReturn);

            return false;
        }
    }

    public function zipAction() {
        $this->layout('layout/json');

        $oRequest = $this->getRequest();

        if($oRequest->isPost()) {
            $sZip = $oRequest->getPost('term');
            $aReturn = [
                'results' => [],
                'pagination' => (object)['more'=>false],
            ];

            $oWh = new Where();
            $oWh->like('zip',(int)$sZip.'%')->or->like('city',$sZip.'%');

            $oResultsDB = $this->aPluginTbls['zip']->select($oWh);

            foreach($oResultsDB as $oCat) {
                $aReturn['results'][] = (object)[
                    'id'=>$oCat->zip,
                    'text'=>$oCat->zip.' '.$oCat->city,
                ];
            }
        } else {
            $aReturn = [
                'state' => 'error',
                'message' => 'not allowed',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($aReturn);

        return false;
    }

    public function helpAction()
    {
        $this->layout('layout/json');

        $sPage = $this->params()->fromRoute('id', 'help');

        return [
            'sPage' => $sPage,
        ];
    }

    /**
     * get available dates for a specific month
     *
     * @since 1.0.0
     * @return false
     */
    public function datesAction()
    {
        $this->layout('layout/json');

        $oJSON = json_decode($this->getRequest()->getContent());
        if(!is_object($oJSON)) {
            $aReturn = [
                'state' => 'error',
                'message' => 'invalid json',
            ];
        } else {
            $sDate = $oJSON->month;
            $aDateInfo = explode('-',$sDate);
            $iMonth = (int)$aDateInfo[1];
            $iYear = (int)$aDateInfo[0];
            $iContactID = $oJSON->contact_id;

            $oSlotsDB = $this->aPluginTbls['booking-slot']->select([
                'contact_idfs' => $iContactID,
            ]);
            $aDatesThisMonth = [];
            $aSlotsByWeekDay = [];
            if(count($oSlotsDB) > 0) {
                foreach ($oSlotsDB as $oSlot) {
                    if(!array_key_exists($oSlot->weekday,$aSlotsByWeekDay)) {
                        $aSlotsByWeekDay[$oSlot->weekday] = [];
                    }
                    $aSlotsByWeekDay[$oSlot->weekday][] = (object)['start' => $oSlot->time_start,'end' => $oSlot->time_end];
                }
            }

            $iDays = cal_days_in_month( 0, $iMonth, $iYear);
            for($iDay = 1;$iDay <= $iDays;$iDay++) {
                $sDateCheck = $iYear.'-'.
                    str_pad("".$iMonth."", 2, '0', STR_PAD_LEFT).'-'.
                    str_pad("".$iDay."", 2, '0', STR_PAD_LEFT);
                $iWeekday = date('w', strtotime($sDateCheck));
                if(array_key_exists($iWeekday,$aSlotsByWeekDay)) {
                    $aDatesThisMonth[] = $sDateCheck;
                }
            }

            $aReturn = [
                'state' => 'success',
                'results' => $aDatesThisMonth,
                'message' => 'found '.count($aDatesThisMonth).' dates available for month '.$iMonth,
                'pagination' => (object)['more'=>false],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($aReturn);

        return false;
    }

    /**
     * Get free slots for a specific date
     *
     * @since 1.0.0
     * @return false - JSON
     */
    public function slotsAction()
    {
        $this->layout('layout/json');

        $oJSON = json_decode($this->getRequest()->getContent());
        if(!is_object($oJSON)) {
            $aReturn = [
                'state' => 'error',
                'message' => 'invalid json',
            ];
        } else {
            $sDate = $oJSON->date;
            $aDateInfo = explode('-',$sDate);
            $iWeekday = date('w', strtotime($sDate));

            $oSlotsDB = $this->aPluginTbls['booking-slot']->select([
                'contact_idfs' => $oJSON->contact_id,
                'weekday' => $iWeekday,
            ]);

            $aEventsThisDay = [];
            $oEventsFromDB = $this->aPluginTbls['event']->fetchAll(false, ['date_start-like' => $sDate]);
            if(count($oEventsFromDB) > 0) {
                foreach($oEventsFromDB as $oEv) {
                    $aEventsThisDay[] = $oEv;
                }
            }

            $aFreeSlots = [];
            if(count($oSlotsDB) > 0) {
                $dEventTime = 3600;
                foreach ($oSlotsDB as $oSlot) {
                    $bAddSlot = true;
                    $iCount = 0;
                    $dBase = strtotime($sDate.' '.$oSlot->time_start);
                    $dEnd = strtotime($sDate.' '.$oSlot->time_end);

                    while($bAddSlot) {
                        $dSlot = $dBase+($iCount*900);
                        if(($dSlot+$dEventTime) < $dEnd) {
                            if(count($aEventsThisDay) > 0) {
                                $bAdd = true;
                                foreach($aEventsThisDay as $oEv) {
                                    if(strtotime($oEv->date_start) >= ($dSlot+$dEventTime)) {

                                    } else {
                                        if(strtotime($oEv->date_end) >= ($dSlot)) {
                                            $bAdd = false;
                                        }
                                    }
                                }
                                if($bAdd) {
                                    $aFreeSlots[] = (object)['start' => date('H:i',$dSlot),'end' => date('H:i',$dSlot+$dEventTime)];
                                }
                            } else {
                                $aFreeSlots[] = (object)['start' => date('H:i',$dSlot),'end' => date('H:i',$dSlot+$dEventTime)];
                            }
                        } else {
                            $bAddSlot = false;
                        }
                        $iCount++;
                    }
                }
            }


            $aReturn = [
                'state' => 'success',
                'results' => $aFreeSlots,
                'message' => 'found '.count($aFreeSlots).' free slots for date '.$sDate,
                'pagination' => (object)['more'=>false],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($aReturn);

        return false;
    }

    public function timeslotsAction()
    {
        $this->layout('layout/json');

        $iContactID = $this->params()->fromRoute('id', 0);

        $sDate = $_REQUEST['start'];
        $sDateEnd = $_REQUEST['end'];

        $aDateInfo = explode('-',$sDate);
        $iMonth = (int)$aDateInfo[1];
        $iYear = (int)$aDateInfo[0];

        $oSlotsDB = $this->aPluginTbls['booking-slot']->select([
            'contact_idfs' => $iContactID,
        ]);
        $aDatesThisMonth = [];
        $aSlotsByWeekDay = [];
        if(count($oSlotsDB) > 0) {
            foreach ($oSlotsDB as $oSlot) {
                if(!array_key_exists($oSlot->weekday,$aSlotsByWeekDay)) {
                    $aSlotsByWeekDay[$oSlot->weekday] = [];
                }
                $aSlotsByWeekDay[$oSlot->weekday][] = (object)['start' => $oSlot->time_start,'end' => $oSlot->time_end];
            }
        }

        for($sDay = strtotime($sDate);$sDay <= strtotime($sDateEnd);$sDay = $sDay+(3600*24)) {
            $iWeekday = date('w', $sDay);
            if(array_key_exists($iWeekday,$aSlotsByWeekDay)) {
                foreach(array_keys($aSlotsByWeekDay[$iWeekday]) as $iDaySlot) {
                    $aNewEv = [];

                    $sStart = date('Y-m-d',$sDay);
                    $sStart .= 'T'.date('H:i:s',strtotime($aSlotsByWeekDay[$iWeekday][$iDaySlot]->start));

                    $sEnd = date('Y-m-d',$sDay);
                    $sEnd .= 'T'.date('H:i:s',strtotime($aSlotsByWeekDay[$iWeekday][$iDaySlot]->end));

                    $aNewEv['start'] = $sStart;
                    $aNewEv['end'] = $sEnd;
                    $aNewEv['display'] = 'background';

                    $aDatesThisMonth[] = $aNewEv;
                }
            }
        }

        //header('Content-Type: application/json');
        echo json_encode($aDatesThisMonth);

        return false;
    }

    public function listAction()
    {
        $this->layout('layout/json');

        $oJSON = json_decode($this->getRequest()->getContent());
        if(!is_object($oJSON)) {
            $aReturn = [
                'state' => 'error',
                'message' => 'invalid json',
            ];
        } else {
            $iZip = $oJSON->zip;
            $iCategoryID = $oJSON->category_idfs;
            $bExternal = $oJSON->is_external;

            $aContactIDs = $this->aPluginTbls['contact-zip']->select(['zip' => $iZip]);
            $aContacts = [];
            if(count($aContactIDs) > 0) {
                foreach($aContactIDs as $oCID) {
                    $oContact = $this->aPluginTbls['contact']->getSingle($oCID->contact_idfs);
                    $aCategoryIDs = $oContact->getMultiSelectFieldIDs('category');
                    if(count($aCategoryIDs) > 0) {
                        foreach($aCategoryIDs as $iCheckID) {
                            if($iCheckID == $iCategoryID) {
                                $aContacts[] = (object)[
                                    'id' => $oContact->getID(),
                                    'label' => $oContact->getLabel(),
                                    'hasEmployees' => false,
                                    'icon' => '',
                                    'featured_image' => '',
                                    'rating' => 4.5,
                                    'ratingCount' => 3,
                                ];
                            }
                        }
                    }
                }
            }

            $aReturn = [
                'state' => 'success',
                'results' => $aContacts,
                'message' => 'found '.count($aContacts).' matching contacts in '.$iZip,
                'pagination' => (object)['more' => false],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($aReturn);

        return false;
    }

    public function ziplocatorAction()
    {
        $this->layout('layout/json');

        $oJSON = json_decode($this->getRequest()->getContent());
        if(!is_object($oJSON)) {
            $aReturn = [
                'state' => 'error',
                'message' => 'invalid json',
            ];
        } else {
            $sZip = $oJSON->term;

            $aReturn = [
                'results' => [],
                'message' => 'no results for zip '.$sZip,
                'pagination' => (object)['more'=>false],
            ];

            $oWh = new Where();
            $oWh->like('zip',(int)$sZip.'%')->or->like('city',$sZip.'%');

            $oResultsDB = $this->aPluginTbls['zip']->select($oWh);

            if(count($oResultsDB) > 0) {
                $aReturn['message'] = count($oResultsDB).' results for zip '.$sZip;

                foreach($oResultsDB as $oCat) {
                    $aReturn['results'][] = (object)[
                        'id'=>$oCat->zip,
                        'text'=>$oCat->zip.' '.$oCat->city,
                    ];
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($aReturn);

        return false;
    }

    public function placeAction()
    {
        $this->layout('layout/json');

        $oJSON = json_decode($this->getRequest()->getContent());
        if(!is_object($oJSON)) {
            $aReturn = [
                'state' => 'error',
                'message' => 'invalid json',
            ];
        } else {
            $iCompanyID = $oJSON->company_id;
            $iContactID = $oJSON->contact_id;
            $iUserID = $oJSON->user_id;
            $sDate = $oJSON->date;
            $sTime = $oJSON->time;
            $sFirstname = $oJSON->firstname;
            $sLastname = $oJSON->lastname;
            $iZip = $oJSON->zip;
            $sStreet = $oJSON->street;
            $sAppartment = $oJSON->appartment;
            $sPhone = $oJSON->phone;
            $sEmail = $oJSON->email;

            $aCheckEvs = [];
            $oEvCheck = $this->aPluginTbls['event']->fetchAll(true, ['date_start-like' => date('Y-m-d H:i', strtotime($sDate.' '.$sTime))]);
            if(count($oEvCheck) > 0) {
                foreach($oEvCheck as $oEv) {
                    $aCheckEvs[] = $oEv;
                }
            }
            if(count($aCheckEvs) == 0) {
                $oNewEvent = new \OnePlace\Event\Model\Event(CoreEntityController::$oDbAdapter);
                $oNewEvent->exchangeArray([
                    'label' => 'Termin von WEOS.ch',
                    'date_start' => date('Y-m-d H:i', strtotime($sDate.' '.$sTime)),
                    'date_end' => date('Y-m-d H:i', strtotime($sDate.' '.$sTime)+3600),
                    'calendar_idfs' => 1,
                    'created_by' => 1,
                    'modified_by' => 1,
                    'created_date' => date('Y-m-d H:i:s', time()),
                    'modified_date' => date('Y-m-d H:i:s', time()),
                ]);

                $iEventID = $this->aPluginTbls['event']->saveSingle($oNewEvent);

                $aReturn = [
                    'state' => 'success',
                    'message' => 'order successfully placed',
                ];
            } else {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'there is already an event at this time',
                ];
            }
        }

        header('Content-Type: application/json');
        echo json_encode($aReturn);

        return false;
    }
}