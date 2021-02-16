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
use Laminas\Http\ClientStatic;


class WebController extends CoreEntityController
{
    /**
     * Weos Table Object
     *
     * @since 1.0.0
     */
    protected $oTableGateway;

    protected $aPluginTbls;

    /**
     * WeosController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param WeosTable $oTableGateway
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter, $oTableGateway, $aPluginTbls, $oServiceManager)
    {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'weos-single';
        $this->aPluginTbls = $aPluginTbls;
        parent::__construct($oDbAdapter, $oTableGateway, $oServiceManager);
    }

    public function homeAction() {
        $this->layout('layout/web');

        return [];
    }

    public function listAction() {
        $this->layout('layout/web');

        $iZip = $this->params()->fromRoute('zip', '');
        $oCity = false;
        $oZip = $this->aPluginTbls['zip']->select([
            'zip' => $iZip,
        ]);
        if(count($oZip) > 0) {
            $oCity = $oZip->current();
        }
        $aZipContacts = $this->aPluginTbls['contact-zip']->select([
            'zip' => $iZip
        ]);
        $aMyContacts = [];
        if(count($aZipContacts) > 0) {
            foreach($aZipContacts as $oZip) {
                $aMyContacts[] = $this->aPluginTbls['contact']->getSingle($oZip->contact_idfs);
            }
        }
        return [
            'iZip' => $iZip,
            'oCity' => $oCity,
            'aMyContacts' => $aMyContacts,
        ];
    }

    public function contactlistAction() {
        $this->layout('layout/json');

        $oJSON = json_decode($this->getRequest()->getContent());
        if(!is_object($oJSON)) {
            $aReturn = [
                'state' => 'error',
                'message' => 'invalid json',
            ];
            echo json_encode($aReturn);
            return false;
        } else {
            # get data from javascript call
            $iCategoryID = $oJSON->category_idfs;
            $iZip = $oJSON->zip;

            # make call to WEOS API Server
            $sApiCallUrl = CoreEntityController::$aGlobalSettings['weos-apiserver-url'] . '/app/supplier/list?authtoken=' .
                CoreEntityController::$aGlobalSettings['weos-apiserver-apitoken'] . '&authkey=' .
                CoreEntityController::$aGlobalSettings['weos-apiserver-apikey'];

            $oApiCall = ClientStatic::post(
                $sApiCallUrl,
                [
                    'authtoken' => CoreEntityController::$aGlobalSettings['weos-apiserver-apitoken'],
                    'authkey' => CoreEntityController::$aGlobalSettings['weos-apiserver-apikey'],
                ],
                [],
                json_encode((object)['zip' => $iZip,
                    'category_idfs' => $iCategoryID,
                    'is_external' => true])
            );

            # Parse Response of WEOS Api Server
            $sJson = $oApiCall->getBody();
            $oApiJson = json_decode($sJson);
            $aResults = $oApiJson->results;

            # Generate View
            return new ViewModel([
                'aResults' => $aResults,
                'iZip' => $iZip,
            ]);
        }

        return false;
    }

    public function viewAction()
    {
        $this->layout('layout/web');

        $iContactID = $this->params()->fromRoute('contact', 0);
        $sZip = $this->params()->fromRoute('zip', 0);

        $oContact = $this->aPluginTbls['contact']->getSingle($iContactID);

        return [
            'oContact' => $oContact,
            'sZip' => $sZip,
        ];
    }

    public function datepickerAction()
    {
        $this->layout('layout/json');

        $iContactID = $_REQUEST['contact_id'];
        $iMonth = $_REQUEST['month'];

        # make call to WEOS API Server
        $sApiCallUrl = CoreEntityController::$aGlobalSettings['weos-apiserver-url'] . '/app/calendar/dates?authtoken=' .
            CoreEntityController::$aGlobalSettings['weos-apiserver-apitoken'] . '&authkey=' .
            CoreEntityController::$aGlobalSettings['weos-apiserver-apikey'];

        $oApiCall = ClientStatic::post(
            $sApiCallUrl,
            [
                'authtoken' => CoreEntityController::$aGlobalSettings['weos-apiserver-apitoken'],
                'authkey' => CoreEntityController::$aGlobalSettings['weos-apiserver-apikey'],
            ],
            [],
            json_encode((object)['contact_id' => $iContactID,
                'month' => '2021-'.str_pad($iMonth,2,'0',STR_PAD_LEFT)])
        );

        # Parse Response of WEOS Api Server
        $sJson = $oApiCall->getBody();
        $oApiJson = json_decode($sJson);
        $aResults = $oApiJson->results;

        echo json_encode($aResults);

        return false;
    }

    public function timeslotsAction()
    {
        $this->layout('layout/json');

        $iContactID = $_REQUEST['contact_id'];
        $sDate = date('Y-m-d', strtotime($_REQUEST['date']));

        # make call to WEOS API Server
        $sApiCallUrl = CoreEntityController::$aGlobalSettings['weos-apiserver-url'] . '/app/calendar/slots?authtoken=' .
            CoreEntityController::$aGlobalSettings['weos-apiserver-apitoken'] . '&authkey=' .
            CoreEntityController::$aGlobalSettings['weos-apiserver-apikey'];

        $oApiCall = ClientStatic::post(
            $sApiCallUrl,
            [
                'authtoken' => CoreEntityController::$aGlobalSettings['weos-apiserver-apitoken'],
                'authkey' => CoreEntityController::$aGlobalSettings['weos-apiserver-apikey'],
            ],
            [],
            json_encode((object)['contact_id' => $iContactID,
                'date' => $sDate])
        );

        # Parse Response of WEOS Api Server
        $sJson = $oApiCall->getBody();
        $oApiJson = json_decode($sJson);
        $aResults = $oApiJson->results;

        return new ViewModel([
            'sDate' => $sDate,
            'aResults' => $aResults,
        ]);
    }
}