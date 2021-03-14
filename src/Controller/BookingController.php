<?php
/**
 * BookingController.php - Booking Controller
 *
 * Main Controller for WEOS Bookings
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

class BookingController extends CoreEntityController
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

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->setThemeBasedLayout('weos');

        # Add Buttons for breadcrumb
        $this->setViewButtons('weos-booking-index');

        $aOpenEvs = [];
        $oOpenEvsDB = $this->aPluginTbls['event']->fetchAll(false, ['is_confirmed-like' => 0]);
        if(count($oOpenEvsDB) > 0 ) {
            foreach($oOpenEvsDB as $oOpenEv) {
                $aOpenEvs[] = $oOpenEv;
            }
        }

        $aEventSources = [];
        $aCalendars = [];
        $aCalendarsDB = $this->aPluginTbls['event-calendar']->fetchAll(false,[]);
        foreach($aCalendarsDB as $oCal) {
            $aEventSources[] = (object)[
                'url' => '/calendar/load/' . $oCal->getID(),
                'color' => $oCal->getColor('background'),
                'border' => $oCal->getColor('background'),
                'textColor' => $oCal->getColor('text'),
            ];
            $aCalendars[] = $oCal;
        }

        return new ViewModel([
            'aOpenEvs' => $aOpenEvs,
            'aEventSources' => $aEventSources,
            'aCalendars' => $aCalendars,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function slotsAction()
    {
        $this->setThemeBasedLayout('weos');

        # Add Buttons for breadcrumb
        $this->setViewButtons('weos-booking-slots');

        $iContactID = CoreEntityController::$oSession->oUser->getSetting('weos-base-contact');
        $aSlots = $this->aPluginTbls['booking-slot']->select(['contact_idfs' => $iContactID]);

        return new ViewModel([
            'aSlots' => $aSlots,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function addslotAction()
    {
        $this->setThemeBasedLayout('weos');

        $oRequest = $this->getRequest();

        if(!$oRequest->isPost()) {
            return new ViewModel([]);
        }

        $iWeekday = $oRequest->getPost('slot_weekday');
        $aStartTimes = $oRequest->getPost('slot_timestart');
        $aEndTimes = $oRequest->getPost('slot_timeend');

        $iContactID = CoreEntityController::$oSession->oUser->getSetting('weos-base-contact');

        if(count($aStartTimes) > 0) {
            $iCount = 0;
            foreach($aStartTimes as $sTime) {
                $this->aPluginTbls['booking-slot']->insert([
                    'contact_idfs' => $iContactID,
                    'weekday' => $iWeekday,
                    'time_start' => $sTime,
                    'time_end' => $aEndTimes[$iCount],
                ]);

                $iCount++;
            }
        }

        return $this->redirect()->toRoute('weos-bookings', ['action' => 'slots']);
    }

    /**
     * @return ViewModel
     */
    public function calendarAction()
    {
        $this->setThemeBasedLayout('weos');

        return new ViewModel([]);
    }

    public function confirmAction()
    {
        $this->layout('layout/json');

        $iEventID = $this->params()->fromRoute('id', 0);

        $this->aPluginTbls['event']->updateAttribute('calendar_idfs', 1, 'Event_ID', $iEventID);
        $this->aPluginTbls['event']->updateAttribute('is_confirmed', 1, 'Event_ID', $iEventID);

        $this->flashMessenger()->addSuccessMessage('Termin erfolgreich bestätigt. Kunde wird informiert.');

        return $this->redirect()->toRoute('weos-bookings', ['action' => 'index']);
    }
}